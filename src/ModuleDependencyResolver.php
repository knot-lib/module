<?php
namespace KnotLib\Module;

use KnotLib\Module\Exception\CyclicDependencyException;
use KnotLib\Module\Exception\InvalidComponentNameException;
use KnotLib\Module\Exception\MethodNotFoundException;
use KnotLib\Module\Exception\ModuleDependencyResolvingException;
use KnotLib\Module\Exception\InvalidModuleFqcnException;
use KnotLib\Module\Exception\ModuleClassNotFoundException;

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
     * @param callable $explain_callback
     *
     * @return array
     *
     * @throws ModuleDependencyResolvingException
     */
    public function resolve(callable $explain_callback = null) : array
    {
        $module_list = $this->required_modules;

        // build module list by component type
        $modules_by_component = [];
        $module_component_map = [];
        foreach($module_list as $module)
        {
            if (!class_exists($module)){
                throw new ModuleClassNotFoundException($module);
            }
            if (!method_exists($module, 'declareComponentType')){
                throw new MethodNotFoundException($module, 'declareComponentType');
            }
            $component_type = forward_static_call([$module, 'declareComponentType']);

            $module_component_map[$module] = $component_type;

            $modules = $modules_by_component[$component_type] ?? [];

            $modules[] = $module;

            $modules_by_component[$component_type] = $modules;
        }

        // make dependency map
        $dependency_map = new ModuleDependencyMap($modules_by_component);

        try{
            foreach($module_list as $module)
            {
                $dependency_map->addModuleDependency($module);
            }
        }
        catch(InvalidModuleFqcnException $e){
            throw new ModuleDependencyResolvingException('Invalid module FQCN specified.', 0, $e);
        }
        catch(InvalidComponentNameException $e){
            throw new ModuleDependencyResolvingException('Invalid component name specified.', 0, $e);
        }

        // sort modules
        try{
            $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
            $sorted_module_list = $sorter->sort();

            if ($explain_callback){
                ($explain_callback)($dependency_map->toArray(), $modules_by_component);
            }

            return $sorted_module_list;
        }
        catch(CyclicDependencyException $e){
            throw new ModuleDependencyResolvingException('Detected a cyclic module dependency.', 0, $e);
        }
        catch(InvalidModuleFqcnException $e){
            throw new ModuleDependencyResolvingException('Invalid module FQCN specified.', 0, $e);
        }
    }

}