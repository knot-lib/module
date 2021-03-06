<?php
declare(strict_types=1);

namespace knotlib\module;

use knotlib\kernel\module\ModuleInterface;
use knotlib\module\exception\CyclicDependencyException;
use knotlib\module\exception\ModuleClassNotFoundException;
use knotlib\module\exception\NotModuleClassException;

class ModuleDependencyResolver
{
    /** @var string[] */
    private $required_modules;

    /**
     * ModuleDependencyResolver constructor.
     *
     * @param array $required_modules
     */
    public function __construct(array $required_modules)
    {
        $this->required_modules = $required_modules;
    }

    /**
     * Return resolved modules
     *
     * @param callable|null $explain_callback
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws ModuleClassNotFoundException
     * @throws NotModuleClassException
     */
    public function resolve(callable $explain_callback = null) : array
    {
        // build module list by component type
        $modules_by_component = [];
        foreach($this->required_modules as $module)
        {
            $this->checkModuleClass($module);

            $component_type = forward_static_call([$module, 'declareComponentType']);

            $modules = $modules_by_component[$component_type] ?? [];

            $modules[] = $module;

            $modules_by_component[$component_type] = $modules;
        }

        // make dependency map
        $dependency_map = new ModuleDependencyMap($this->required_modules);

        $dependency_map_result = $dependency_map->resolve($modules_by_component);

        // sort modules
        $sorter = new ModuleDependencySorter($dependency_map_result, $this->required_modules);
        $sort_logs = [];
        $sorted_module_list = $sorter->sort(function($log) use(&$sort_logs){
            $sort_logs[] = $log;
        });

        if ($explain_callback){
            ($explain_callback)($dependency_map_result, $modules_by_component, $sort_logs);
        }

        return $sorted_module_list;
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

}