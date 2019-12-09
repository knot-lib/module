<?php
return [
    KnotLib\Module\Test\TestPluginModule::class,
    KnotLib\Module\Test\TestAnotherPluginModule::class => dirname(__DIR__) . '/TestAnotherPluginModule.php',
    'KnotLib.Module.Test.TestYetAnotherPluginModule' => 'src/TestYetAnotherPluginModule.php',
];