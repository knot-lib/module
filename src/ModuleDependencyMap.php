<?php
declare(strict_types=1);

namespace KnotLib\Module;

use KnotLib\Kernel\Module\Components;
use KnotLib\Module\Exception\InvalidComponentNameException;
use KnotLib\Module\Exception\InvalidModuleFqcnException;
use KnotLib\Module\Exception\MethodNotFoundException;
use KnotLib\Module\Exception\ModuleClassNotFoundException;

final class ModuleDependencyMap
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
     * Resolve module dependencies and add to the map
     *
     * @param string $module
     *
     * @throws InvalidModuleFqcnException
     * @throws InvalidComponentNameException
     * @throws MethodNotFoundException
     * @throws ModuleClassNotFoundException
     */
    public function addModuleDependencies(string $module)
    {
        $this->map[$module] = self::resolveModuleDependencies($module, $this->modules_by_component);
    }

    /**
     * Get dependent modules of a module
     *
     * @param string $module
     *
     * @return array
     */
    public function getDependentModules(string $module)
    {
        return $this->map[$module];
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
     * @param array $modules_by_component
     *
     * @return array
     *
     * @throws InvalidModuleFqcnException
     * @throws InvalidComponentNameException
     * @throws MethodNotFoundException
     * @throws ModuleClassNotFoundException
     */
    private static function resolveModuleDependencies(string $module, array $modules_by_component) : array
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

            $ret = array_merge($ret, self::resolveModuleDependencies($m, $modules_by_component));
        }

        $required_components = forward_static_call([$module, 'requiredComponents']);
        foreach($required_components as $c){
            if (!Components::isComponent($c)){
                throw new InvalidComponentNameException($c);
            }
            $modules = $modules_by_component[$c] ?? [];
            foreach($modules as $m){
                $ret[] = $m;

                $ret = array_merge($ret, self::resolveModuleDependencies($m, $modules_by_component));
            }
        }

        return array_values(array_unique($ret));
    }

}