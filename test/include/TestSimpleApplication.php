<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\Kernel\ApplicationType;
use KnotLib\Module\Application\SimpleApplication;

final class TestSimpleApplication extends SimpleApplication
{
    public static function type() : ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

}