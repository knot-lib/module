<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Kernel\ApplicationType;
use KnotLib\Module\Application\PluginApplication;

final class TestPluginApplication extends PluginApplication
{
    public static function type() : ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

}