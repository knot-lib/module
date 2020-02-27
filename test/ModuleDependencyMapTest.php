<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Module\ModuleDependencyMap;
use PHPUnit\Framework\TestCase;

final class ModuleDependencyMapTest extends TestCase
{
    /**
     * @throws
     */
    public function testAddModuleDependency()
    {
        //===================================
        // ModuleA
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleA::class);

        $this->assertEquals([ModuleA::class => []], $map->toArray());

        //===================================
        // ModuleB
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleB::class);

        $this->assertEquals([ModuleB::class => [ModuleA::class]], $map->toArray());

        //===================================
        // ModuleC
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleC::class);

        $this->assertEquals([ModuleC::class => [ModuleA::class, ModuleB::class]], $map->toArray());

        //===================================
        // ModuleF
        //===================================
        /*
        $map = new ModuleDependencyMap();

        try{
            $map->addModuleDependency(ModuleF::class);

            var_dump($map->toArray());

            $this->fail('ModuleF has cyclic dependency.');
        }
        catch(CyclicDependencyException $e)
        {
            $this->assertTrue(true);
            echo $e->getMessage();
        }
        */

        //===================================
        // ModuleB -> ModuleA
        // ModuleI -> ModuleB
        //         -> ModuleK
        // ModuleJ -> ModuleB
        //         -> ModuleI
        // ModuleK -> ModuleB
        //===================================
        $map = new ModuleDependencyMap();

        $map->addModuleDependencies(ModuleI::class);
        $map->addModuleDependencies(ModuleJ::class);

        $expected = [
            ModuleI::class => [
                ModuleB::class, ModuleA::class, ModuleK::class,
            ],
            ModuleJ::class => [
                ModuleB::class, ModuleA::class, ModuleI::class, ModuleK::class,
            ],
        ];

        $this->assertEquals($expected, $map->toArray());
    }
}