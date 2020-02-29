<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use PHPUnit\Framework\TestCase;

final class PluginModuleLoaderTest extends TestCase
{
    /**
     * @runInSeparateProcess
     *
     * @throws
     */
    public function testInstall()
    {
        $app = new TestPluginLoadableApplication(TestFileSystemFactory::createFileSystem());

        ob_start();
        $app->install();
        $output = ob_get_clean();

        $expected = <<<OUTPUT
Required module is installed.
Test plugin is installed.
Test another plugin is installed.
Test yet another plugin is installed.
OUTPUT;
        $expected = str_replace("\n", PHP_EOL, $expected) . PHP_EOL;

        $this->assertEquals($expected, $output);
        $this->assertContains(TestPluginModule::class, get_declared_classes());
        $this->assertContains(TestAnotherPluginModule::class, get_declared_classes());
        $this->assertContains(TestYetAnotherPluginModule::class, get_declared_classes());

        $plugin_file = dirname(__DIR__) . '/include/TestPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertContains($plugin_file, get_included_files());

        $plugin_file = dirname(__DIR__) . '/files/TestAnotherPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertContains($plugin_file, get_included_files());

        $plugin_file = dirname(__DIR__) . '/files/plugin/src/TestYetAnotherPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertContains($plugin_file, get_included_files());
    }
    /**
     * @runInSeparateProcess
     *
     * @throws
     */
    public function testNotInstall()
    {
        $app = new TestPluginLoadableApplication(TestFileSystemFactory::createFileSystem(true));

        ob_start();
        $app->install();
        $output = ob_get_clean();

        $this->assertNotEquals('Test plugin is installed.', $output);
        $this->assertNotContains(TestPluginModule::class, get_declared_classes());
        $this->assertNotContains(TestAnotherPluginModule::class, get_declared_classes());
        $this->assertNotContains(TestYetAnotherPluginModule::class, get_declared_classes());

        $plugin_file = dirname(__DIR__) . '/files/plugin/TestPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertNotContains($plugin_file, get_included_files());

        $plugin_file = dirname(__DIR__) . '/files/TestAnotherPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertNotContains($plugin_file, get_included_files());

        $plugin_file = dirname(__DIR__) . '/files/plugin/src/TestYetAnotherPluginModule.php';
        $plugin_file = str_replace('/', DIRECTORY_SEPARATOR, $plugin_file);

        $this->assertNotContains($plugin_file, get_included_files());
    }
}