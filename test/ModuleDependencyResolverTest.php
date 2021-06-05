<?php
/** @noinspection PhpRedundantCatchClauseInspection */

namespace knotlib\module\test;

use PHPUnit\Framework\TestCase;

use knotlib\kernel\module\componentTypes;
use knotlib\module\exception\ModuleClassNotFoundException;
use knotlib\module\exception\NotModuleClassException;
use knotlib\module\ModuleDependencyResolver;
use knotlib\module\test\classes\component\CacheModule;
use knotlib\module\test\classes\component\DiModule;
use knotlib\module\test\classes\component\EventStreamModule;
use knotlib\module\test\classes\component\ExHandlerModule;
use knotlib\module\test\classes\component\LoggerModule;
use knotlib\module\test\classes\component\PipelineModule;
use knotlib\module\test\classes\component\ResponseModule;
use knotlib\module\test\classes\component\RouterModule;
use knotlib\module\exception\CyclicDependencyException;
use knotlib\module\test\classes\ModuleA;
use knotlib\module\test\classes\ModuleB;
use knotlib\module\test\classes\ModuleC;
use knotlib\module\test\classes\ModuleD;
use knotlib\module\test\classes\ModuleE;
use knotlib\module\test\classes\ModuleF;
use knotlib\module\test\classes\ModuleG;
use knotlib\module\test\classes\ModuleH;

/**
 * Class ModuleDependencyResolverTest
 *
 * [Test Data]
 *                           | component type |  required components              | required modules |  extends  |
 * -------------------------------------------------------------------------------------------------------------------
 * ModuleA                   | APPLICATION    | EX_HANDLER,LOGGER,EVENTSTREAM     | -                | -         |
 * ModuleB                   | APPLICATION    | EX_HANDLER,LOGGER,EVENTSTREAM     | ModuleA          | -         |
 * ModuleC                   | APPLICATION    | -                                 | ModuleA,ModuleB  | -         |
 * ModuleD                   | APPLICATION    | CACHE,DI                          | -                | -         |
 * ModuleE                   | APPLICATION    | DI                                | -                | -         |
 * ModuleF                   | APPLICATION    | -                                 | ModuleG          | -         |
 * ModuleG                   | APPLICATION    | -                                 | ModuleF          | -         |
 * ModuleH                   | APPLICATION    | -                                 | -                | ModuleC   |
 * Comp../CacheModule        | CACHE          | LOGGER                            | -                | -         |
 * Comp../DiModule           | DI             | EVENTSTREAM                       | -                | -         |
 * Comp../ExHandlerModule    | EX_HANDLER     | EVENTSTREAM                       | -                | -         |
 * Comp../LoggerModule       | LOGGER         | EX_HANDLER,EVENTSTREAM            | -                | -         |
 * Comp../PipelineModule     | PIPELINE       | EX_HANDLER,EVENTSTREAM,RESPONSE   | -                | -         |
 * Comp../EventStreamModule  | EVENTSTREAM    | EX_HANDLER                        | -                | -         |
 * Comp../ResponseModule     | RESPONSE       | EX_HANDLER,EVENTSTREAM            | -                | -         |
 * Comp../RouterModule       | ROUTER         | EVENTSTREAM,PIPELINE              | -                | -         |
 *
 * [Test Pattern: 1-10]
 *                           | Case 1 | Case 2 | Case 3 | Case 4 | Case 5 | Case 6 | Case 7 | Case 8 | Case 9 | Case10 |
 * ---------------------------------------------------------------------------------------------------------------------
 * ModuleA                   | O      | -      | -      | -      | -      | -      | -      | -      | O      | O      |
 * ModuleB                   | O      | O      | -      | -      | -      | -      | -      | -      | O      | O      |
 * ModuleC                   | O      | O      | O      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleD                   | -      | -      | -      | O      | O      | -      | -      | -      | -      | -      |
 * ModuleE                   | -      | -      | -      | -      | -      | O      | -      | -      | -      | -      |
 * ModuleF                   | -      | -      | -      | -      | -      | -      | -      | O      | -      | -      |
 * ModuleG                   | -      | -      | -      | -      | -      | -      | -      | O      | -      | -      |
 * ModuleH                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * Comp../CacheModule        | -      | -      | -      | -      | O      | -      | O      | -      | -      | -      |
 * Comp../DiModule           | -      | -      | -      | -      | O      | O      | O      | -      | -      | -      |
 * Comp../ExHandlerModule    | -      | -      | -      | O      | O      | O      | O      | -      | O      | -      |
 * Comp../LoggerModule       | -      | -      | -      | O      | O      | O      | O      | -      | O      | O      |
 * Comp../PipelineModule     | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * Comp../EventStreamModule  | -      | -      | -      | O      | O      | O      | O      | -      | O      | O      |
 * Comp../ResponseModule     | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * Comp../RouterModule       | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 *
 * [Test Pattern: 11-20]
 *                           | Case11 | Case12 | Case13 | Case14 | Case15 | Case16 | Case17 | Case18 | Case19 | Case20 |
 * ---------------------------------------------------------------------------------------------------------------------
 * ModuleA                   | O      | -      | O      | O      | O      | O      | O      | -      | -      | -      |
 * ModuleB                   | O      | -      | O      | O      | O      | O      | O      | -      | -      | -      |
 * ModuleC                   | -      | -      | -      | -      | -      | O      | O      | -      | -      | -      |
 * ModuleD                   | -      | O      | -      | -      | -      | O      | O      | -      | -      | -      |
 * ModuleE                   | -      | -      | -      | -      | -      | O      | O      | -      | -      | -      |
 * ModuleF                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleG                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleH                   | -      | -      | O      | -      | -      | O      | O      | -      | -      | -      |
 * Comp../CacheModule        | -      | O      | -      | -      | -      | -      | O      | -      | -      | -      |
 * Comp../DiModule           | -      | O      | -      | -      | O      | -      | O      | -      | -      | -      |
 * Comp../ExHandlerModule    | -      | O      | O      | O      | O      | -      | O      | -      | -      | -      |
 * Comp../LoggerModule       | -      | O      | O      | O      | O      | -      | O      | -      | -      | -      |
 * Comp../PipelineModule     | O      | O      | -      | O      | -      | -      | O      | -      | -      | -      |
 * Comp../EventStreamModule  | O      | O      | O      | O      | O      | -      | O      | -      | -      | -      |
 * Comp../ResponseModule     | O      | O      | -      | O      | -      | -      | O      | -      | -      | -      |
 * Comp../RouterModule       | -      | -      | -      | O      | -      | -      | -      | -      | -      | -      |
 *
 */
class ModuleDependencyResolverTest extends TestCase
{
    /**
     * [Test Case 1]
     *
     * Modules: ModuleA, ExHandlerModule, LoggerModule, EventStreamModule
     *
     * @throws
     */
    public function testResolveCase1()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class
            ],
            $result);
    }

    /**
     * [Test Case 2]
     *
     * Modules: ModuleA, ModuleB
     *
     * @throws
     */
    public function testResolveCase2()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ExHandlerModule::class, EventStreamModule::class, LoggerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class
            ],
            $result);
    }

    /**
     * [Test Case 3]
     *
     * Modules: ModuleA, ModuleB, ModuleC
     *
     * @throws
     */
    public function testResolveCase3()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ModuleC::class, ExHandlerModule::class, EventStreamModule::class, LoggerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertEquals(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class,
                ModuleC::class,
            ],
            $result);
    }

    /**
     * [Test Case 4]
     * CACHE component required, but the component is not loaded
     *
     * Modules: ModuleD(requires CACHE), LoggerModule, EventStreamModule, ExHandlerModule
     *
     * @throws
     */
    public function testResolveCase4()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleD::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class,
        ]);

        $resolver->resolve();
        $this->assertTrue(true);
    }

    /**
     * [Test Case 5]
     *
     * Modules: ModuleD, CacheModule, DiModule, LoggerModule, EventStreamModule, ExHandlerModule
     *
     * @throws
     */
    public function testResolveCase5()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleD::class, CacheModule::class, DiModule::class, LoggerModule::class, EventStreamModule::class,
            ExHandlerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                CacheModule::class,
                DiModule::class,
                ModuleD::class,
            ],
            $result);
    }

    /**
     * [Test Case 6]
     * DI component is required, but the component is not loaded.
     *
     * Modules: ModuleE(requires DI), CacheModule, LoggerModule, EventStreamModule, ExHandlerModule
     *
     * @throws
     */
    public function testResolveCase6()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleE::class, CacheModule::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class,
        ]);

        $resolver->resolve();
        $this->assertTrue(true);
    }

    /**
     * [Test Case 7]
     *
     * Modules: CacheModule, LoggerModule, DiModule, EventStreamModule, ExHandlerModule
     *
     * @throws
     */
    public function testResolveCase7()
    {
        $resolver = new ModuleDependencyResolver([
            CacheModule::class, LoggerModule::class, DiModule::class, EventStreamModule::class, ExHandlerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                CacheModule::class,
                DiModule::class,
            ],
            $result);
    }

    /**
     * [Test Case 8]
     * Cyclic referenced modules
     *
     * Modules: ModuleF, ModuleG
     *
     */
    public function testResolveCase8()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleF::class, ModuleG::class,
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ModuleF::class,
                    ModuleG::class,
                ],
                $result);
            $this->fail();
        }
        catch(CyclicDependencyException $e){
            $this->assertTrue(true);
        }
        catch(ModuleClassNotFoundException | NotModuleClassException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 9]
     *
     * Modules: ModuleA, ModuleB, ExHandlerModule, LoggerModule, EventStreamModule
     *
     * @throws
     */
    public function testResolveCase9()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class,
            ],
            $result);
    }

    /**
     * [Test Case 10]
     *
     * Modules: ModuleA, ModuleB, LoggerModule, EventStreamModule
     *
     * @throws
     */
    public function testResolveCase10()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class,
            ],
            $result);
    }

    /**
     * [Test Case 11]
     *
     * Modules: ModuleA, ModuleB, PipelineModule, EventStreamModule, ResponseModule
     *
     * @throws
     */
    public function testResolveCase11()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, PipelineModule::class, EventStreamModule::class, ResponseModule::class,
            ExHandlerModule::class, LoggerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
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

    /**
     * [Test Case 12]
     * Resolve multiple components
     *
     * Modules: CacheModule, LoggerModule, DiModule, PipelineModule, ModuleD, ExHandlerModule, EventStreamModule,
     *         ResponseModule
     *
     * @throws
     */
    public function testResolveCase12()
    {
        $resolver = new ModuleDependencyResolver([
            CacheModule::class, LoggerModule::class, DiModule::class, PipelineModule::class, ModuleD::class,
            ExHandlerModule::class, EventStreamModule::class, ResponseModule::class,
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ResponseModule::class,
                PipelineModule::class,
                CacheModule::class,
                DiModule::class,
                ModuleD::class,
            ],
            $result);
    }

    /**
     * [Test Case 13]
     * Resolve inherited modules
     *
     * Modules: ModuleH, ModuleA, ModuleB, ExHandlerModule, LoggerModule, EventStreamModule
     *
     * @throws
     */
    public function testResolveCase13()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleH::class, ModuleA::class, ModuleB::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class,
                ModuleH::class,
            ],
            $result);
    }

    /**
     * [Test Case 14]
     * Router and response
     *
     * Modules: ModuleA, RouterModule, ModuleB, ResponseModule, EventStreamModule, PipelineModule,
     *        LoggerModule, ExHandlerModule
     *
     * @throws
     */
    public function testResolveCase14()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, RouterModule::class, ModuleB::class, ResponseModule::class, EventStreamModule::class,
            PipelineModule::class, LoggerModule::class, ExHandlerModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ResponseModule::class,
                PipelineModule::class,
                RouterModule::class,
                ModuleA::class,
                ModuleB::class,
            ],
            $result);
    }

    /**
     * [Test Case 15]
     * Router and response
     *
     * Modules: ModuleA, ModuleB, DiModule, EventStreamModule, LoggerModule, ExHandlerModule,
     *        CacheModule
     *
     * @throws
     */
    public function testResolveCase15()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, DiModule::class, EventStreamModule::class,
            LoggerModule::class, ExHandlerModule::class, CacheModule::class
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                CacheModule::class,
                DiModule::class,
                ModuleA::class,
                ModuleB::class,
            ],
            $result);
    }

    /**
     * [Test Case 16]
     * Router and response
     *
     * Modules: ModuleA, ModuleB, ModuleC, ModuleD, ModuleE, ModuleH
     *
     * @throws
     */
    public function testResolveCase16()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ModuleC::class, ModuleD::class, ModuleE::class, ModuleH::class,
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                ModuleA::class,
                ModuleB::class,
                ModuleC::class,
                ModuleD::class,
                ModuleE::class,
                ModuleH::class,
            ],
            $result);
    }

    /**
     * [Test Case 17]
     * Router and response
     *
     * Modules: ModuleA, ModuleB, ModuleC, ModuleD, ModuleE, ModuleH
     *      CacheModule, LoggerModule, DiModule, PipelineModule, ExHandlerModule, EventStreamModule, ResponseModule
     *
     * @throws
     */
    public function testResolveCase17()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ModuleC::class, ModuleD::class, ModuleE::class, ModuleH::class,
            CacheModule::class, LoggerModule::class, DiModule::class, PipelineModule::class, ExHandlerModule::class,
            EventStreamModule::class, ResponseModule::class,
        ]);

        $result = $resolver->resolve();
        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                ResponseModule::class,
                PipelineModule::class,
                CacheModule::class,
                DiModule::class,
                ModuleA::class,
                ModuleB::class,
                ModuleC::class,
                ModuleD::class,
                ModuleE::class,
                ModuleH::class,
            ],
            $result);
    }

    /**
     * @throws CyclicDependencyException
     * @throws ModuleClassNotFoundException
     * @throws NotModuleClassException
     */
    public function testExplain()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ModuleD::class, DiModule::class, EventStreamModule::class,
            LoggerModule::class, ExHandlerModule::class, CacheModule::class
        ]);

        $result = $resolver->resolve(function($dependency_map, $modules_by_component, $sort_logs){

            // check dependency map
            $this->assertEquals([
                ExHandlerModule::class, LoggerModule::class, EventStreamModule::class,
            ], $dependency_map[ModuleA::class]);

            $this->assertEquals([
                ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class,
            ], $dependency_map[ModuleB::class]);

            $this->assertEquals([
                EventStreamModule::class, ExHandlerModule::class,LoggerModule::class, CacheModule::class,
            ], $dependency_map[DiModule::class]);

            $this->assertEquals([
                ExHandlerModule::class,
            ], $dependency_map[EventStreamModule::class]);

            $this->assertEquals([
                EventStreamModule::class, ExHandlerModule::class,
            ], $dependency_map[LoggerModule::class]);

            $this->assertEquals([], $dependency_map[ExHandlerModule::class]);

            $this->assertEquals([
                LoggerModule::class, EventStreamModule::class, ExHandlerModule::class,
            ], $dependency_map[CacheModule::class]);

            $this->assertSame([
                CacheModule::class,
                LoggerModule::class,
                EventStreamModule::class,
                ExHandlerModule::class,
                DiModule::class,
            ], $dependency_map[ModuleD::class]);

            // check module list by component
            $this->assertEquals([
                componentTypes::APPLICATION => [ ModuleA::class, ModuleB::class, ModuleD::class ],
                componentTypes::DI => [ DiModule::class ],
                componentTypes::EVENTSTREAM => [ EventStreamModule::class ],
                componentTypes::LOGGER => [ LoggerModule::class ],
                componentTypes::EX_HANDLER => [ ExHandlerModule::class ],
                componentTypes::CACHE => [ CacheModule::class ],
            ], $modules_by_component);

            // check sort logs
            $this->assertEquals([
                0 => 'knotlib\\module\\test\\classes\\ModuleA < knotlib\\module\\test\\classes\\ModuleB(module dependency)',
                1 => 'knotlib\\module\\test\\classes\\ModuleB = knotlib\\module\\test\\classes\\ModuleD',
                2 => 'knotlib\\module\\test\\classes\\ModuleD > knotlib\\module\\test\\classes\\component\\DiModule(component priority)',
                3 => 'knotlib\\module\\test\\classes\\ModuleB > knotlib\\module\\test\\classes\\component\\DiModule(component priority)',
                4 => 'knotlib\\module\\test\\classes\\ModuleA > knotlib\\module\\test\\classes\\component\\DiModule(component priority)',
                5 => 'knotlib\\module\\test\\classes\\ModuleD > knotlib\\module\\test\\classes\\component\\EventStreamModule(component priority)',
                6 => 'knotlib\\module\\test\\classes\\ModuleB > knotlib\\module\\test\\classes\\component\\EventStreamModule(component priority)',
                7 => 'knotlib\\module\\test\\classes\\ModuleA > knotlib\\module\\test\\classes\\component\\EventStreamModule(component priority)',
                8 => 'knotlib\\module\\test\\classes\\component\\DiModule > knotlib\\module\\test\\classes\\component\\EventStreamModule(component priority)',
                9 => 'knotlib\\module\\test\\classes\\ModuleD > knotlib\\module\\test\\classes\\component\\LoggerModule(component priority)',
                10 => 'knotlib\\module\\test\\classes\\ModuleB > knotlib\\module\\test\\classes\\component\\LoggerModule(component priority)',
                11 => 'knotlib\\module\\test\\classes\\ModuleA > knotlib\\module\\test\\classes\\component\\LoggerModule(component priority)',
                12 => 'knotlib\\module\\test\\classes\\component\\DiModule > knotlib\\module\\test\\classes\\component\\LoggerModule(component priority)',
                13 => 'knotlib\\module\\test\\classes\\component\\EventStreamModule < knotlib\\module\\test\\classes\\component\\LoggerModule(component priority)',
                14 => 'knotlib\\module\\test\\classes\\ModuleD > knotlib\\module\\test\\classes\\component\\ExHandlerModule(component priority)',
                15 => 'knotlib\\module\\test\\classes\\ModuleA > knotlib\\module\\test\\classes\\component\\ExHandlerModule(component priority)',
                16 => 'knotlib\\module\\test\\classes\\component\\LoggerModule > knotlib\\module\\test\\classes\\component\\ExHandlerModule(component priority)',
                17 => 'knotlib\\module\\test\\classes\\component\\ExHandlerModule > knotlib\\module\\test\\classes\\component\\EventStreamModule(component priority)',
                18 => 'knotlib\\module\\test\\classes\\ModuleD > knotlib\\module\\test\\classes\\component\\CacheModule(component priority)',
                19 => 'knotlib\\module\\test\\classes\\ModuleA > knotlib\\module\\test\\classes\\component\\CacheModule(component priority)',
                20 => 'knotlib\\module\\test\\classes\\component\\LoggerModule < knotlib\\module\\test\\classes\\component\\CacheModule(component priority)',
                21 => 'knotlib\\module\\test\\classes\\component\\DiModule > knotlib\\module\\test\\classes\\component\\CacheModule(component priority)',
            ], $sort_logs);
        });

        $this->assertSame(
            [
                EventStreamModule::class,
                ExHandlerModule::class,
                LoggerModule::class,
                CacheModule::class,
                DiModule::class,
                ModuleA::class,
                ModuleB::class,
                ModuleD::class,
            ],
            $result);
    }
}