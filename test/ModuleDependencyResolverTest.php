<?php
/** @noinspection PhpRedundantCatchClauseInspection */

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Module\Components;
use KnotLib\Module\Exception\ModuleClassNotFoundException;
use KnotLib\Module\Exception\NotModuleClassException;
use KnotLib\Module\ModuleDependencyResolver;
use KnotLib\Module\Test\Component\CacheModule;
use KnotLib\Module\Test\Component\DiModule;
use KnotLib\Module\Test\Component\EventStreamModule;
use KnotLib\Module\Test\Component\ExHandlerModule;
use KnotLib\Module\Test\Component\LoggerModule;
use KnotLib\Module\Test\Component\PipelineModule;
use KnotLib\Module\Test\Component\ResponseModule;
use KnotLib\Module\Test\Component\RouterModule;
use KnotLib\Module\Exception\CyclicDependencyException;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleDependencyResolverTest
 *
 * [Test Data]
 *                           | component type |  required components              | required modules |  extends  |
 * -------------------------------------------------------------------------------------------------------------------
 * ModuleA                   | MODULE         | EX_HANDLER,LOGGER,EVENTSTREAM     | -                | -         |
 * ModuleB                   | MODULE         | EX_HANDLER,LOGGER,EVENTSTREAM     | ModuleA          | -         |
 * ModuleC                   | MODULE         | -                                 | ModuleA,ModuleB  | -         |
 * ModuleD                   | MODULE         | CACHE,DI                          | -                | -         |
 * ModuleE                   | MODULE         | DI                                | -                | -         |
 * ModuleF                   | MODULE         | -                                 | ModuleG          | -         |
 * ModuleG                   | MODULE         | -                                 | ModuleF          | -         |
 * ModuleH                   | MODULE         | -                                 | -                | ModuleC   |
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
 * ModuleA                   | O      | -      | O      | O      | O      | -      | -      | -      | -      | -      |
 * ModuleB                   | O      | -      | O      | O      | O      | -      | -      | -      | -      | -      |
 * ModuleC                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleD                   | -      | O      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleE                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleF                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleG                   | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      |
 * ModuleH                   | -      | -      | O      | -      | -      | -      | -      | -      | -      | -      |
 * Comp../CacheModule        | -      | O      | -      | -      | -      | -      | -      | -      | -      | -      |
 * Comp../DiModule           | -      | O      | -      | -      | O      | -      | -      | -      | -      | -      |
 * Comp../ExHandlerModule    | -      | O      | O      | O      | O      | -      | -      | -      | -      | -      |
 * Comp../LoggerModule       | -      | O      | O      | O      | O      | -      | -      | -      | -      | -      |
 * Comp../PipelineModule     | O      | O      | -      | O      | -      | -      | -      | -      | -      | -      |
 * Comp../EventStreamModule  | O      | O      | O      | O      | O      | -      | -      | -      | -      | -      |
 * Comp../ResponseModule     | O      | O      | -      | O      | -      | -      | -      | -      | -      | -      |
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
        catch(ModuleClassNotFoundException $e){
            $this->fail($e->getMessage());
        }
        catch(NotModuleClassException $e){
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
                Components::MODULE => [ ModuleA::class, ModuleB::class, ModuleD::class ],
                Components::DI => [ DiModule::class ],
                Components::EVENTSTREAM => [ EventStreamModule::class ],
                Components::LOGGER => [ LoggerModule::class ],
                Components::EX_HANDLER => [ ExHandlerModule::class ],
                Components::CACHE => [ CacheModule::class ],
            ], $modules_by_component);

            // check sort logs
            $this->assertEquals([
                0 => 'KnotLib\\Module\\Test\\ModuleA < KnotLib\\Module\\Test\\ModuleB(module dependency)',
                1 => 'KnotLib\\Module\\Test\\ModuleB = KnotLib\\Module\\Test\\ModuleD',
                2 => 'KnotLib\\Module\\Test\\ModuleD > KnotLib\\Module\\Test\\Component\\DiModule(component priority)',
                3 => 'KnotLib\\Module\\Test\\ModuleB > KnotLib\\Module\\Test\\Component\\DiModule(component priority)',
                4 => 'KnotLib\\Module\\Test\\ModuleA > KnotLib\\Module\\Test\\Component\\DiModule(component priority)',
                5 => 'KnotLib\\Module\\Test\\ModuleD > KnotLib\\Module\\Test\\Component\\EventStreamModule(component priority)',
                6 => 'KnotLib\\Module\\Test\\ModuleB > KnotLib\\Module\\Test\\Component\\EventStreamModule(component priority)',
                7 => 'KnotLib\\Module\\Test\\ModuleA > KnotLib\\Module\\Test\\Component\\EventStreamModule(component priority)',
                8 => 'KnotLib\\Module\\Test\\Component\\DiModule > KnotLib\\Module\\Test\\Component\\EventStreamModule(component priority)',
                9 => 'KnotLib\\Module\\Test\\ModuleD > KnotLib\\Module\\Test\\Component\\LoggerModule(component priority)',
                10 => 'KnotLib\\Module\\Test\\ModuleB > KnotLib\\Module\\Test\\Component\\LoggerModule(component priority)',
                11 => 'KnotLib\\Module\\Test\\ModuleA > KnotLib\\Module\\Test\\Component\\LoggerModule(component priority)',
                12 => 'KnotLib\\Module\\Test\\Component\\DiModule > KnotLib\\Module\\Test\\Component\\LoggerModule(component priority)',
                13 => 'KnotLib\\Module\\Test\\Component\\EventStreamModule < KnotLib\\Module\\Test\\Component\\LoggerModule(component priority)',
                14 => 'KnotLib\\Module\\Test\\ModuleD > KnotLib\\Module\\Test\\Component\\ExHandlerModule(component priority)',
                15 => 'KnotLib\\Module\\Test\\ModuleA > KnotLib\\Module\\Test\\Component\\ExHandlerModule(component priority)',
                16 => 'KnotLib\\Module\\Test\\Component\\LoggerModule > KnotLib\\Module\\Test\\Component\\ExHandlerModule(component priority)',
                17 => 'KnotLib\\Module\\Test\\Component\\ExHandlerModule > KnotLib\\Module\\Test\\Component\\EventStreamModule(component priority)',
                18 => 'KnotLib\\Module\\Test\\ModuleD > KnotLib\\Module\\Test\\Component\\CacheModule(component priority)',
                19 => 'KnotLib\\Module\\Test\\ModuleA > KnotLib\\Module\\Test\\Component\\CacheModule(component priority)',
                20 => 'KnotLib\\Module\\Test\\Component\\LoggerModule < KnotLib\\Module\\Test\\Component\\CacheModule(component priority)',
                21 => 'KnotLib\\Module\\Test\\Component\\DiModule > KnotLib\\Module\\Test\\Component\\CacheModule(component priority)',
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