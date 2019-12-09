<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Module\Components;
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
        $module_component_map = [ ModuleA::class => Components::MODULE, ModuleB::class => Components::MODULE ];
        $dependency_map = new ModuleDependencyMap();
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class], $sorted_module_list);

        $module_list = [ ModuleB::class, ModuleA::class ];
        $dependency_map = new ModuleDependencyMap();
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class], $sorted_module_list);
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
        $module_component_map = [ ModuleA::class => Components::MODULE, ModuleB::class => Components::MODULE, ModuleC::class => Components::MODULE ];
        $dependency_map = new ModuleDependencyMap();
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $dependency_map->addModuleDependency(ModuleC::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class, ModuleC::class], $sorted_module_list);

        $module_list = [ ModuleC::class, ModuleB::class, ModuleA::class ];
        $dependency_map = new ModuleDependencyMap();
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $dependency_map->addModuleDependency(ModuleC::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ModuleA::class, ModuleB::class, ModuleC::class], $sorted_module_list);
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
        $modules_by_component = [
            Components::EX_HANDLER => [ ExHandlerModule::class ],
            Components::LOGGER => [ LoggerModule::class ],
            Components::EVENTSTREAM => [ EventStreamModule::class ],
            Components::MODULE => [ ModuleA::class ],
        ];
        $module_component_map = [
            ModuleA::class => Components::MODULE,
            ExHandlerModule::class => Components::EX_HANDLER,
            LoggerModule::class => Components::LOGGER,
            EventStreamModule::class => Components::EVENTSTREAM,
        ];
        $dependency_map = new ModuleDependencyMap($modules_by_component);
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ExHandlerModule::class);
        $dependency_map->addModuleDependency(LoggerModule::class);
        $dependency_map->addModuleDependency(EventStreamModule::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ExHandlerModule::class, EventStreamModule::class, LoggerModule::class, ModuleA::class], $sorted_module_list);

        $module_list = [ EventStreamModule::class, LoggerModule::class, ModuleA::class, ExHandlerModule::class,  ];
        $dependency_map = new ModuleDependencyMap($modules_by_component);
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ExHandlerModule::class);
        $dependency_map->addModuleDependency(LoggerModule::class);
        $dependency_map->addModuleDependency(EventStreamModule::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals([ExHandlerModule::class, EventStreamModule::class, LoggerModule::class, ModuleA::class], $sorted_module_list);
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
        $module_component_map = [
            ModuleA::class => Components::MODULE,
            ModuleB::class => Components::MODULE,
            ExHandlerModule::class => Components::EX_HANDLER,
            LoggerModule::class => Components::LOGGER,
            EventStreamModule::class => Components::EVENTSTREAM,
            ResponseModule::class => Components::RESPONSE,
            PipelineModule::class => Components::PIPELINE,
        ];
        $modules_by_component = [
            Components::EX_HANDLER => [ ExHandlerModule::class ],
            Components::LOGGER => [ LoggerModule::class ],
            Components::EVENTSTREAM => [ EventStreamModule::class ],
            Components::RESPONSE => [ ResponseModule::class ],
            Components::PIPELINE => [ PipelineModule::class ],
            Components::MODULE => [ ModuleA::class, ModuleB::class ],
        ];
        $dependency_map = new ModuleDependencyMap($modules_by_component);
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $dependency_map->addModuleDependency(ExHandlerModule::class);
        $dependency_map->addModuleDependency(LoggerModule::class);
        $dependency_map->addModuleDependency(EventStreamModule::class);
        $dependency_map->addModuleDependency(ResponseModule::class);
        $dependency_map->addModuleDependency(PipelineModule::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $expected = [
            ExHandlerModule::class,
            EventStreamModule::class,
            LoggerModule::class,
            ResponseModule::class,
            PipelineModule::class,
            ModuleA::class,
            ModuleB::class,
        ];

        $this->assertEquals($expected, $sorted_module_list);

        $module_list = [
            ModuleA::class,
            ModuleB::class,
            ExHandlerModule::class,
            LoggerModule::class,
            EventStreamModule::class,
            ResponseModule::class,
            PipelineModule::class,
        ];
        $dependency_map = new ModuleDependencyMap($modules_by_component);
        $dependency_map->addModuleDependency(ModuleA::class);
        $dependency_map->addModuleDependency(ModuleB::class);
        $dependency_map->addModuleDependency(ExHandlerModule::class);
        $dependency_map->addModuleDependency(LoggerModule::class);
        $dependency_map->addModuleDependency(EventStreamModule::class);
        $dependency_map->addModuleDependency(ResponseModule::class);
        $dependency_map->addModuleDependency(PipelineModule::class);
        $sorter = new ModuleDependencySorter($module_component_map, $dependency_map, $module_list);
        $sorted_module_list = $sorter->sort();

        $this->assertEquals($expected, $sorted_module_list);
    }
}