<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use knotlib\kernel\module\ModuleInterface;
use knotlib\kernel\kernel\ApplicationInterface;

class ModuleH extends ModuleC implements ModuleInterface
{
    /**
     * Install module
     *
     * @param ApplicationInterface $app
     */
    public function install(ApplicationInterface $app)
    {
    }

}