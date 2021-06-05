<?php
declare(strict_types=1);

namespace knotlib\module;

use knotlib\kernel\module\ModuleInterface;
use knotlib\module\exception\CyclicDependencyException;
use knotlib\module\exception\NotModuleClassException;
use knotlib\module\exception\ModuleClassNotFoundException;

final class ModuleDependencyMap
{
    /** @var array */
    private $map;

    /** @var array */
    private $module_list;

    /**
     * ModuleDependencyMap constructor.
     *
     * @param array $module_list
     */
    public function __construct(array $module_list)
    {
        $this->map = [];
        $this->module_list = $module_list;
    }

    /**
     * Resolve dependency
     *
     * @param array $module_list_by_component
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws ModuleClassNotFoundException
     * @throws NotModuleClassException
     */
    public function resolve(array $module_list_by_component = []) : array
    {
        $cyclic_check = [];
        foreach($this->module_list as $module){
            $this->map[$module] = self::resolveModuleDependencies($module, $module_list_by_component, $cyclic_check);
        }

        return $this->map;
    }

    /**
     * @param string $module
     *
     * @throws ModuleClassNotFoundException
     * @throws NotModuleClassException
     */
    private function checkModuleClass(string $module)
    {
        if (!class_exists($module)){
            throw new ModuleClassNotFoundException($module);
        }
        if (!in_array(ModuleInterface::class, class_implements($module))){
            throw new NotModuleClassException($module);
        }
    }

    /**
     * @param string $module
     * @param array $module_list_by_component
     * @param array $cyclic_check
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws ModuleClassNotFoundException
     * @throws NotModuleClassException
     */
    private function resolveModuleDependencies(string $module, array $module_list_by_component, array $cyclic_check) : array
    {
        $cyclic_check[] = $module;

        $this->checkModuleClass($module);

        $dependent_modules = forward_static_call([$module, 'requiredModules']);

        $ret = [];
        foreach($dependent_modules as $m){
            $ret[] = $m;

            $this->checkModuleClass($m);

            if (in_array($m, $cyclic_check)){
                throw new CyclicDependencyException($module, $m);
            }

            $ret = array_merge($ret, self::resolveModuleDependencies($m, $module_list_by_component, $cyclic_check));
        }

        $required_components = forward_static_call([$module, 'requiredComponentTypes']);
        foreach($required_components as $c){
            $modules = $module_list_by_component[$c] ?? [];
            foreach($modules as $m){
                $ret[] = $m;

                if (in_array($m, $cyclic_check)){
                    throw new CyclicDependencyException($module, $m);
                }

                $ret = array_merge($ret, self::resolveModuleDependencies($m, $module_list_by_component, $cyclic_check));
            }
        }

        return array_values(array_unique($ret));
    }
}