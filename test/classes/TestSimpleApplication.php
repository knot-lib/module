<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use knotlib\kernel\kernel\ApplicationType;
use knotlib\module\application\SimpleApplication;

final class TestSimpleApplication extends SimpleApplication
{
    public static function type() : ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

}