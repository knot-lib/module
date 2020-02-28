<?php /** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Module\ModuleDependencyMap;
use KnotLib\Module\ModuleDependencySorter;
use KnotLib\Module\Test\Component\EventStreamModule;
use KnotLib\Module\Test\Component\ExHandlerModule;
use KnotLib\Module\Test\Component\LoggerModule;
use KnotLib\Module\Test\Component\PipelineModule;
use KnotLib\Module\Test\Component\ResponseModule;
use PHPUnit\Framework\TestCase;

final class ModuleDependencySorterTest extends TestCase
{
    /**
     * @throws
     */
    public function testSort1()
    {
        //===================================
        // ModuleA, ModuleB
        //===================================
        $module_list = [ ModuleA::class, ModuleB::class ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class], $sorted_module_list);

        $module_list = [ ModuleB::class, ModuleA::class ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $result = $sorter->sort();

        $this->assertEquals(
            [
                ModuleA::class,
                ModuleB::class
            ],
            $result);
    }

    /**
     * @throws
     */
    public function testSort2()
    {
        //===================================
        // ModuleA, ModuleB, ModuleC
        //===================================
        $module_list = [ ModuleA::class, ModuleB::class, ModuleC::class ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class, ModuleC::class], $sorted_module_list);

        $module_list = [ ModuleC::class, ModuleB::class, ModuleA::class ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $result = $sorter->sort();

        $this->assertEquals(
            [
                ModuleA::class,
                ModuleB::class,
                ModuleC::class
            ],
            $result);
    }

    /**
     * @throws
     */
    public function testSort3()
    {
        //======================================================================
        // ModuleA, ExHandlerModule, LoggerModule, EventStreamModule
        //======================================================================
        $module_list = [ ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([EventStreamModule::class, ExHandlerModule::class, LoggerModule::class, ModuleA::class], $sorted_module_list);

        $module_list = [ EventStreamModule::class, LoggerModule::class, ModuleA::class, ExHandlerModule::class,  ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $result = $sorter->sort();

        $this->assertEquals(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class, ModuleA::class
            ],
            $result);
    }

    /**
     * @throws
     */
    public function testSort4()
    {
        //======================================================================
        // ModuleA, ModuleB, ExHandlerModule, LoggerModule, EventStreamModule, ResponseModule, PipelineModule,
        //======================================================================
        $module_list = [
            ModuleA::class,
            ModuleB::class,
            ExHandlerModule::class,
            LoggerModule::class,
            EventStreamModule::class,
            ResponseModule::class,
            PipelineModule::class,
        ];
        $dependency_map = new ModuleDependencyMap($module_list);
        $sorter = new ModuleDependencySorter($dependency_map->resolve(), $module_list);
        $result = $sorter->sort();

        $this->assertEquals(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ResponseModule::class,
                PipelineModule::class,
                ModuleA::class,
                ModuleB::class,
            ],
            $result);
    }

}