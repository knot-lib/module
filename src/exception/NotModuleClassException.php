<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use knotlib\kernel\module\ModuleInterface;
use Throwable;

class NotModuleClassException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param Throwable|null $prev
     */
    public function __construct(string $module, Throwable $prev = null){
        parent::__construct("Module class($module) must imprements " . ModuleInterface::class, $prev);
    }
}