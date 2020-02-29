<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Module\ComponentTypes;
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
        $map = new ModuleDependencyMap([ModuleA::class]);

        $result = $map->resolve();

        $this->assertEquals([ModuleA::class => []], $result);
     }

    /**
     * @throws
     */
    public function testModuleB()
    {
        //===================================
        // ModuleB: depends on ModulA
        //===================================
        $map = new ModuleDependencyMap([ModuleB::class]);

        $result = $map->resolve();

        $this->assertEquals([ModuleB::class => [ModuleA::class]], $result);
    }

    /**
     * @throws
     */
    public function testModuleC()
    {
        //===================================
        // ModuleC: depends on ModulA and ModuleB
        //===================================
        $map = new ModuleDependencyMap([ModuleC::class]);

        $result = $map->resolve();

        $this->assertEquals([ModuleC::class => [ModuleA::class, ModuleB::class]], $result);
    }

    /**
     * @throws
     */
    public function testModuleF()
    {
        //===================================
        // ModuleF: has cyclic dependency(ModuleF depends on ModuleG, ModuleG depends on ModuleF)
        //===================================
        $map = new ModuleDependencyMap([ModuleF::class]);

        try {
            $map->resolve();

            $this->fail('ModuleF has cyclic dependency.');
        }
        catch (CyclicDependencyException $e) {
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
        $map = new ModuleDependencyMap([ModuleH::class]);

        $result = $map->resolve();

        $this->assertSame([
            ModuleH::class => [ ModuleA::class, ModuleB::class ]
        ],
        $result);
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
        $map = new ModuleDependencyMap([ModuleI::class, ModuleJ::class]);

        $module_list_by_component = [
            ComponentTypes::EX_HANDLER => [ ExHandlerModule::class ],
            ComponentTypes::LOGGER => [ LoggerModule::class ],
            ComponentTypes::EVENTSTREAM => [ EventStreamModule::class ],
        ];

        $map->resolve($module_list_by_component);

        $result = $map->resolve();

        $this->assertEquals([ModuleB::class, ModuleA::class, ModuleK::class], $result[ModuleI::class]);
        $this->assertEquals([ModuleB::class, ModuleA::class, ModuleI::class, ModuleK::class], $result[ModuleJ::class]);
    }

    /**
     * @throws
     */
    public function testModuleK()
    {
        //===================================
        // ModuleK: depends on ModuleB, ExHandler, Logger, EventStream
        //===================================

        $map = new ModuleDependencyMap([ModuleK::class]);

        $module_list_by_component = [
            ComponentTypes::EX_HANDLER => [ ExHandlerModule::class ],
            ComponentTypes::LOGGER => [ LoggerModule::class ],
            ComponentTypes::EVENTSTREAM => [ EventStreamModule::class ],
        ];

        $result = $map->resolve($module_list_by_component);

        $this->assertEquals([
            ModuleB::class, ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ], $result[ModuleK::class]);
    }
}