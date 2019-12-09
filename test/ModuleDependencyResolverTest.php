<?php
/** @noinspection PhpRedundantCatchClauseInspection */

namespace KnotLib\Module\Test;

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
use KnotLib\Module\Exception\ModuleDependencyResolvingException;
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
     */
    public function testResolveCase1()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertEquals([ExHandlerModule::class, EventStreamModule::class, LoggerModule::class, ModuleA::class], $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 2]
     *
     * Modules: ModuleA, ModuleB
     */
    public function testResolveCase2()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ExHandlerModule::class, EventStreamModule::class, LoggerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame([ExHandlerModule::class, EventStreamModule::class, LoggerModule::class, ModuleA::class, ModuleB::class], $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 3]
     *
     * Modules: ModuleA, ModuleB, ModuleC
     */
    public function testResolveCase3()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ModuleC::class, ExHandlerModule::class, EventStreamModule::class, LoggerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $expected = [
                ExHandlerModule::class,
                EventStreamModule::class,
                LoggerModule::class,
                ModuleA::class,
                ModuleB::class,
                ModuleC::class,
            ];
            $this->assertSame($expected, $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 4]
     * CACHE component required, but the component is not loaded
     *
     * Modules: ModuleD(requires CACHE), LoggerModule, EventStreamModule, ExHandlerModule
     */
    public function testResolveCase4()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleD::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class,
        ]);

        try{
            $resolver->resolve();
            $this->assertTrue(true);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 5]
     *
     * Modules: ModuleD, CacheModule, DiModule, LoggerModule, EventStreamModule, ExHandlerModule
     */
    public function testResolveCase5()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleD::class, CacheModule::class, DiModule::class, LoggerModule::class, EventStreamModule::class,
            ExHandlerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    CacheModule::class,
                    DiModule::class,
                    ModuleD::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 6]
     * DI component is required, but the component is not loaded.
     *
     * Modules: ModuleE(requires DI), CacheModule, LoggerModule, EventStreamModule, ExHandlerModule
     */
    public function testResolveCase6()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleE::class, CacheModule::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class,
        ]);

        try{
            $resolver->resolve();
            $this->assertTrue(true);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 7]
     *
     * Modules: CacheModule, LoggerModule, DiModule, EventStreamModule, ExHandlerModule
     */
    public function testResolveCase7()
    {
        $resolver = new ModuleDependencyResolver([
            CacheModule::class, LoggerModule::class, DiModule::class, EventStreamModule::class, ExHandlerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    CacheModule::class,
                    DiModule::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 8]
     * Cyclic referenced modules
     *
     * Modules: ModuleF, ModuleG
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
        catch(ModuleDependencyResolvingException $e){
            if ($e->getPrevious() instanceof CyclicDependencyException){
                $this->assertTrue(true);
            }
            else{
                $this->fail($e->getMessage());
            }
        }
    }

    /**
     * [Test Case 9]
     *
     * Modules: ModuleA, ModuleB, ExHandlerModule, LoggerModule, EventStreamModule
     */
    public function testResolveCase9()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ModuleA::class,
                    ModuleB::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 10]
     *
     * Modules: ModuleA, ModuleB, LoggerModule, EventStreamModule
     */
    public function testResolveCase10()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, LoggerModule::class, EventStreamModule::class, ExHandlerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ModuleA::class,
                    ModuleB::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 11]
     *
     * Modules: ModuleA, ModuleB, PipelineModule, EventStreamModule, ResponseModule
     */
    public function testResolveCase11()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, PipelineModule::class, EventStreamModule::class, ResponseModule::class,
            ExHandlerModule::class, LoggerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ResponseModule::class,
                    PipelineModule::class,
                    ModuleA::class,
                    ModuleB::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 12]
     * Resolve multiple components
     *
     * Modules: CacheModule, LoggerModule, DiModule, PipelineModule, ModuleD, ExHandlerModule, EventStreamModule,
     *         ResponseModule
     */
    public function testResolveCase12()
    {
        $resolver = new ModuleDependencyResolver([
            CacheModule::class, LoggerModule::class, DiModule::class, PipelineModule::class, ModuleD::class,
            ExHandlerModule::class, EventStreamModule::class, ResponseModule::class,
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ResponseModule::class,
                    PipelineModule::class,
                    CacheModule::class,
                    DiModule::class,
                    ModuleD::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 13]
     * Resolve inherited modules
     *
     * Modules: ModuleH, ModuleA, ModuleB, ExHandlerModule, LoggerModule, EventStreamModule
     */
    public function testResolveCase13()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleH::class, ModuleA::class, ModuleB::class, ExHandlerModule::class, LoggerModule::class, EventStreamModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ModuleA::class,
                    ModuleB::class,
                    ModuleH::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 14]
     * Router and response
     *
     * Modules: ModuleA, RouterModule, ModuleB, ResponseModule, EventStreamModule, PipelineModule,
     *        LoggerModule, ExHandlerModule
     */
    public function testResolveCase14()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, RouterModule::class, ModuleB::class, ResponseModule::class, EventStreamModule::class,
            PipelineModule::class, LoggerModule::class, ExHandlerModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    ResponseModule::class,
                    PipelineModule::class,
                    RouterModule::class,
                    ModuleA::class,
                    ModuleB::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }

    /**
     * [Test Case 15]
     * Router and response
     *
     * Modules: ModuleA, ModuleB, DiModule, EventStreamModule, LoggerModule, ExHandlerModule,
     *        CacheModule
     */
    public function testResolveCase15()
    {
        $resolver = new ModuleDependencyResolver([
            ModuleA::class, ModuleB::class, DiModule::class, EventStreamModule::class,
            LoggerModule::class, ExHandlerModule::class, CacheModule::class
        ]);

        try{
            $result = $resolver->resolve();
            $this->assertSame(
                [
                    ExHandlerModule::class,
                    EventStreamModule::class,
                    LoggerModule::class,
                    CacheModule::class,
                    DiModule::class,
                    ModuleA::class,
                    ModuleB::class,
                ],
                $result);
        }
        catch(ModuleDependencyResolvingException $e){
            $this->fail($e->getMessage());
        }
    }
}