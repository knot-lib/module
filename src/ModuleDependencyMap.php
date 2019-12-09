<?php
declare(strict_types=1);

namespace KnotLib\Module;

use ArrayAccess;
use KnotLib\Kernel\Module\Components;
use KnotLib\Module\Exception\CyclicDependencyException;
use KnotLib\Module\Exception\InvalidComponentNameException;
use KnotLib\Module\Exception\InvalidModuleFqcnException;
use KnotLib\Module\Exception\MethodNotFoundException;
use KnotLib\Module\Exception\ModuleClassNotFoundException;

final class ModuleDependencyMap implements ArrayAccess
{
    /** @var array */
    private $map;

    /** @var array */
    private $modules_by_component;

    /**
     * ModuleDependencyMap constructor.
     *
     * @param array $modules_by_component
     */
    public function __construct(array $modules_by_component = [])
    {
        $this->modules_by_component = $modules_by_component;
        $this->map = [];
    }

    /**
     * @param string $module
     *
     * @throws InvalidModuleFqcnException
     * @throws InvalidComponentNameException
     * @throws MethodNotFoundException
     * @throws ModuleClassNotFoundException
     */
    public function addModuleDependency(string $module)
    {
        $this->map[$module] = $this->getModuleDependencies($module);
    }

    /**
     * Returns module's dependency
     *
     * @param string $module
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws InvalidModuleFqcnException
     */
    public function resolveModuleDependency(string $module) : array
    {
        $child_check = [];
        return $this->resolveModuleDependencyRecursive($module, $child_check);
    }

    /**
     * @param string $module
     * @param array $cyclic_check
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws InvalidModuleFqcnException
     */
    private function resolveModuleDependencyRecursive(string $module, array $cyclic_check) : array
    {
        $cyclic_check[] = $module;

        $ret = [];

        $dependency_list = $this->map[$module] ?? [];
        foreach($dependency_list as $m)
        {
            if (!class_exists($m)){
                throw new InvalidModuleFqcnException($module);
            }
            if (in_array($m, $cyclic_check)){
                throw new CyclicDependencyException($module, $m);
            }

            $child_check = $cyclic_check;
            $deps = $this->resolveModuleDependencyRecursive($m, $child_check);

            if (!empty($deps)){
                $ret = array_merge($ret, $deps);
            }
            $ret[] = $m;
        }
        return array_values(array_unique($ret));
    }

    /**
     * @param mixed $offset
     *
     * @return bool|void
     */
    public function offsetExists($offset)
    {
        return isset($this->map[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|void
     */
    public function offsetGet($offset)
    {
        return $this->map[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->map[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->map[$offset]);
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->map;
    }

    /**
     * @param string $module
     *
     * @return array
     *
     * @throws InvalidModuleFqcnException
     * @throws InvalidComponentNameException
     * @throws MethodNotFoundException
     * @throws ModuleClassNotFoundException
     */
    private function getModuleDependencies(string $module) : array
    {
        $cyclic_check[] = $module;

        if (!class_exists($module)){
            throw new ModuleClassNotFoundException($module);
        }
        if (!method_exists($module, 'requiredModules')){
            throw new MethodNotFoundException($module, 'requiredModules');
        }
        $dependent_modules = forward_static_call([$module, 'requiredModules']);
        $ret = [];
        foreach($dependent_modules as $m){
            if (!class_exists($m)){
                throw new InvalidModuleFqcnException($module);
            }
            $ret[] = $m;
        }

        $required_components = forward_static_call([$module, 'requiredComponents']);
        foreach($required_components as $c){
            if (!Components::isComponent($c)){
                throw new InvalidComponentNameException($c);
            }
            $modules = $this->modules_by_component[$c] ?? [];
            foreach($modules as $m){
                $ret[] = $m;
            }
        }

        return array_values(array_unique($ret));
    }

}