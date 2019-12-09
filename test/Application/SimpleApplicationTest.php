<?php
namespace KnotLib\Module\Test;

use Throwable;

use PHPUnit\Framework\TestCase;

use KnotLib\Module\Test\Component\EventStreamModule;
use KnotLib\Module\Test\Component\LoggerModule;
use KnotLib\Module\Test\Component\ExHandlerModule;

class SimpleApplicationTest extends TestCase
{
    public function testDependecyCache()
    {
        $app = new TestSimpleApplication();
        $this->assertSame([], $app->getInstalledModules());

        $app = new TestSimpleApplication(TestFileSystemFactory::createFileSystem());

        $app->requireModule(ModuleA::class);
        $app->requireModule(ExHandlerModule::class);
        $app->requireModule(EventStreamModule::class);
        $app->requireModule(LoggerModule::class);
        try{
            $app->install();
        }
        catch(Throwable $e){
            $this->fail($e->getMessage());
        }
        $expected = [
            ExHandlerModule::class,
            EventStreamModule::class,
            LoggerModule::class,
            ModuleA::class,
        ];
        $this->assertSame($expected, $app->getInstalledModules());
        //$cache_file = vfsStream::url('root/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php');
        $cache_file = dirname(__DIR__) . '/files/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php';
        $this->assertFileExists($cache_file);
        /** @noinspection PhpIncludeInspection */
        $this->assertEquals($expected, require($cache_file));

        $app = new TestSimpleApplication(TestFileSystemFactory::createFileSystem());

        $app->requireModule(ModuleA::class);
        $app->requireModule(ExHandlerModule::class);
        $app->requireModule(EventStreamModule::class);
        $app->requireModule(LoggerModule::class);
        try{
            $app->install();
        }
        catch(Throwable $e){
            $this->fail($e->getMessage());
        }
        $expected = [
            ExHandlerModule::class,
            EventStreamModule::class,
            LoggerModule::class,
            ModuleA::class,
        ];
        $this->assertSame($expected, $app->getInstalledModules());
        //$cache_file = vfsStream::url('root/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php');
        $cache_file = dirname(__DIR__) . '/files/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php';
        $this->assertFileExists($cache_file);
        /** @noinspection PhpIncludeInspection */
        $this->assertEquals($expected, require($cache_file));

        $app = new TestSimpleApplication(TestFileSystemFactory::createFileSystem());

        $app->requireModule(ModuleA::class);
        $app->requireModule(ModuleB::class);
        $app->requireModule(ExHandlerModule::class);
        $app->requireModule(EventStreamModule::class);
        $app->requireModule(LoggerModule::class);
        try{
            $app->install();
        }
        catch(Throwable $e){
            $this->fail($e->getMessage());
        }
        $expected = [
            ExHandlerModule::class,
            EventStreamModule::class,
            LoggerModule::class,
            ModuleA::class,
            ModuleB::class,
        ];
        $this->assertSame($expected, $app->getInstalledModules());
        //$cache_file = vfsStream::url('root/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php');
        $cache_file = dirname(__DIR__) . '/files/cache/dependency.' . sha1(implode("\n",$app->getRequiredModules())) . '.cache.php';
        $this->assertFileExists($cache_file);
        /** @noinspection PhpIncludeInspection */
        $this->assertEquals($expected, require($cache_file));
    }
}