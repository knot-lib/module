<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Module\Components;
use KnotLib\Module\Exception\CyclicDependencyException;
use KnotLib\Module\ModuleDependencyMap;
use KnotLib\Module\Test\Component\EventStreamModule;
use KnotLib\Module\Test\Component\ExHandlerModule;
use KnotLib\Module\Test\Component\LoggerModule;
use PHPUnit\Framework\TestCase;

final class ModuleDependencyMapTest extends TestCase
{
    /**
     * @throws
     */
    public function testModuleA()
    {
        //===================================
        // ModuleA
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleA::class);

        $this->assertEquals([ModuleA::class => []], $map->toArray());
     }

    /**
     * @throws
     */
    public function testModuleB()
    {
        //===================================
        // ModuleB: depends on ModulA
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleB::class);

        $this->assertEquals([ModuleB::class => [ModuleA::class]], $map->toArray());
    }

    /**
     * @throws
     */
    public function testModuleC()
    {
        //===================================
        // ModuleC: depends on ModulA and ModuleB
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleC::class);

        $this->assertEquals([ModuleC::class => [ModuleA::class, ModuleB::class]], $map->toArray());
    }

    /**
     * @throws
     */
    public function testModuleF()
    {
        //===================================
        // ModuleF: has cyclic dependency(ModuleF depends on ModuleG, ModuleG depends on ModuleF)
        //===================================
        $map = new ModuleDependencyMap();

        try {
            $map->addModuleDependencies(ModuleF::class);

            var_dump($map->toArray());

            $this->fail('ModuleF has cyclic dependency.');
        } catch (CyclicDependencyException $e) {
            $this->assertTrue(true);
            echo $e->getMessage();
        }
    }

    /**
     * @throws
     */
    public function testModuleH()
    {
        //===================================
        // ModuleH: extends ModuleC
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleH::class);

        $this->assertSame([
            ModuleH::class => [ ModuleA::class, ModuleB::class ]
        ],
        $map->toArray());
    }

    /**
     * @throws
     */
    public function testModuleIJ()
    {
        //===================================
        // ModuleI: depends on ModuleB, ModuleK
        // ModuleJ: depends on ModuleB, ModuleI
        //
        // So, the dependency map mest be:
        //
        // ModuleI -> ModuleB
        //         -> ModuleA
        //         -> ModuleK
        // ModuleJ -> ModuleB
        //         -> ModuleA
        //         -> ModuleI
        //         -> ModuleK
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleI::class);
        $map->addModuleDependencies(ModuleJ::class);

        $map = $map->toArray();

        $this->assertEquals([ModuleB::class, ModuleA::class, ModuleK::class], $map[ModuleI::class]);
        $this->assertEquals([ModuleB::class, ModuleA::class, ModuleI::class, ModuleK::class], $map[ModuleJ::class]);
    }

    /**
     * @throws
     */
    public function testModuleK()
    {
        //===================================
        // ModuleK: depends on ModuleB, ExHandler, Logger, EventStream
        //===================================

        $modules_by_component[Components::EX_HANDLER] = [
            ExHandlerModule::class
        ];
        $modules_by_component[Components::LOGGER] = [
            LoggerModule::class
        ];
        $modules_by_component[Components::EVENTSTREAM] = [
            EventStreamModule::class
        ];

        $map = new ModuleDependencyMap($modules_by_component);

        $map->addModuleDependencies(ModuleK::class);

        $map = $map->toArray();

        $this->assertEquals([
            ModuleB::class, ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ], $map[ModuleK::class]);
    }
}