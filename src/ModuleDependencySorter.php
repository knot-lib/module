<?php /** @noinspection PhpDocRedundantThrowsInspection */
declare(strict_types=1);

namespace knotlib\module;

use knotlib\kernel\module\ComponentTypes;

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
     * @param callable|null $sort_callback
     *
     * @return array
     */
    public function sort(callable $sort_callback = null) : array
    {
        $dependency_map = $this->module_dependency_map;
        $ret = $this->module_list;

        usort($ret, function($a, $b) use($dependency_map, $sort_callback){

            $component_a = $this->getComponentType($a);
            $component_b = $this->getComponentType($b);

            $res = $this->compareComponentPriority($component_a, $component_b);

            if ($res !== 0){
                if ($sort_callback){
                    $log = $res > 0 ? "$a > $b" : "$a < $b";
                    ($sort_callback)("$log(component priority)");
                }
                return $res;
            }

            $a_dependent_modules = $dependency_map[$a];
            if (in_array($b, $a_dependent_modules)){
                if ($sort_callback){
                    ($sort_callback)("$a > $b(module dependency)");
                }
                return 1;
            }

            $b_dependent_modules = $dependency_map[$b];
            if (in_array($a, $b_dependent_modules)){
                if ($sort_callback){
                    ($sort_callback)("$a < $b(module dependency)");
                }
                return -1;
            }

            if ($sort_callback){
                ($sort_callback)("$a = $b");
            }

            return $res;
        });

        return $ret;
    }

    /**
     * Compare priority of two components
     *
     * @param string $component_a
     * @param string $component_b
     *
     * @return int
     */
    private function compareComponentPriority(string $component_a, string $component_b): int
    {
        $component_priority_table = [
            ComponentTypes::EVENTSTREAM  => 1,
            ComponentTypes::EX_HANDLER   => 2,
            ComponentTypes::LOGGER       => 3,
            ComponentTypes::REQUEST      => 4,
            ComponentTypes::RESPONSE     => 5,
            ComponentTypes::PIPELINE     => 6,
            ComponentTypes::SESSION      => 7,
            ComponentTypes::CACHE        => 8,
            ComponentTypes::DI           => 9,
            ComponentTypes::ROUTER       => 10,
            ComponentTypes::RESPONDER    => 11,
            ComponentTypes::SERVICE      => 12,
            ComponentTypes::MIDDLEWARE   => 13,
            ComponentTypes::APPLICATION  => 14,
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