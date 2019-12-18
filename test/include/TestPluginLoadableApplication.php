<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Kernel\ApplicationType;
use KnotLib\Module\Application\PluginModuleLoader;
use KnotLib\Module\Application\SimpleApplication;

final class TestPluginLoadableApplication extends SimpleApplication
{
    public static function type() : ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

    public function install(): ApplicationInterface
    {
        PluginModuleLoader::loadPluginModules($this);
        return parent::install();
    }


}