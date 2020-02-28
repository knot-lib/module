<?php /** @noinspection PhpDocRedundantThrowsInspection */
declare(strict_types=1);

namespace KnotLib\Module;

use KnotLib\Kernel\Module\Components;

final class ModuleDependencySorter
{
    /** @var array */
    private $module_dependency_map;

    /** @var array */
    private $module_list;

    /**
     * ModuleDependencySorter constructor.
     *
     * @param array $module_dependency_map
     * @param array $module_list
     */
    public function __construct(array $module_dependency_map, array $module_list)
    {
        $this->module_dependency_map = $module_dependency_map;
        $this->module_list = $module_list;
    }

    /**
     * Sort by module's dependency
     *
     * @return array
     */
    public function sort() : array
    {
        $dependency_map = $this->module_dependency_map;
        $ret = $this->module_list;

        usort($ret, function($a, $b) use($dependency_map){
            $a_dependent_modules = $dependency_map[$a];
            if (in_array($b, $a_dependent_modules)){
                return 1;
            }

            $b_dependent_modules = $dependency_map[$b];
            if (in_array($a, $b_dependent_modules)){
                return -1;
            }

            $component_a = $this->getComponentType($a);
            $component_b = $this->getComponentType($b);

            return $this->compareComponentPriority($component_a, $component_b);
        });

        return $ret;
    }

    /**
     * Compare priority of two components
     *
     * @param string $component_a
     * @param string $component_b
     *
     * @return mixed
     */
    private function compareComponentPriority(string $component_a, string $component_b)
    {
        $component_priority_table = [
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

        return $component_priority_table[$component_a] - $component_priority_table[$component_b];
    }

    /**
     * Get component type of specified module
     *
     * @param string $module
     *
     * @return mixed
     */
    private function getComponentType(string $module)
    {
        return forward_static_call([$module, 'declareComponentType']);
    }
}