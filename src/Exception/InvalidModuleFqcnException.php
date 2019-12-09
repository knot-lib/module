<?php
namespace KnotLib\Module\Exception;

use Throwable;

class InvalidModuleFqcnException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $module, int $code = 0, Throwable $prev = null){
        $msg = sprintf("Module(%s) not found.", $module);
        parent::__construct($msg, $code, $prev);
    }
}