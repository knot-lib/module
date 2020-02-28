<?php
namespace KnotLib\Module\Exception;

use KnotLib\Kernel\Module\ModuleInterface;
use Throwable;

class NotModuleClassException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $module,int $code = 0, Throwable $prev = null){
        parent::__construct("Module class($module) must imprements " . ModuleInterface::class, $code, $prev);
    }
}