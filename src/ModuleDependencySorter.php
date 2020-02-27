<?php /** @noinspection PhpDocRedundantThrowsInspection */
declare(strict_types=1);

namespace KnotLib\Module;

use KnotLib\Kernel\Module\Components;
use KnotLib\Module\Exception\CyclicDependencyException;
use KnotLib\Module\Exception\InvalidModuleFqcnException;

final class ModuleDependencySorter
{
    /** @var array */
    private $module_component_map;

    /** @var ModuleDependencyMap */
    private $module_dependency_map;

    /** @var array */
    private $module_list;

    /**
     * ModuleDependencySorter constructor.
     *
     * @param array $module_component_map
     * @param ModuleDependencyMap $module_dependency_map
     * @param array $module_list
     */
    public function __construct(array $module_component_map, ModuleDependencyMap $module_dependency_map, array $module_list)
    {
        $this->module_component_map = $module_component_map;
        $this->module_dependency_map = $module_dependency_map;
        $this->module_list = $module_list;
    }

    /**
     * Sort by module's dependency
     *
     * @return array
     *
     * @throws CyclicDependencyException
     * @throws InvalidModuleFqcnException
     */
    public function sort() : array
    {
        $module_component_map = $this->module_component_map;
        $dependency_map = $this->module_dependency_map;
        $ret = $this->module_list;

        $component_priorities = [
            Components::EVENTSTREAM  => 1,
            Components::EX_HANDLER   => 2,
            Components::LOGGER       => 3,
            Components::REQUEST      => 4,
            Components::RESPONSE     => 5,
            Components::PIPELINE     => 6,
            Components::SESSION      => 7,
            Components::CACHE        => 8,
            Components::DI           => 9,
            Components::ROUTER       => 10,
            Components::RESPONDER    => 11,
            Components::MODULE       => 12,
        ];

        usort($ret, function($a, $b) use($module_component_map, $dependency_map, $component_priorities){
            $a_dependent_modules = $dependency_map->getDependentModules($a);
            if (in_array($b, $a_dependent_modules)){
                return 1;
            }

            $b_dependent_modules = $dependency_map->getDependentModules($b);
            if (in_array($a, $b_dependent_modules)){
                return -1;
            }

            $a_priority = $component_priorities[$module_component_map[$a]];
            $b_priority = $component_priorities[$module_component_map[$b]];

            return ($a_priority - $b_priority);
        });

        return $ret;
    }
}