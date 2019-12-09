<?php
namespace KnotLib\Module\Exception;

use Throwable;

class ModuleDependencyResolvingException extends ModuleException
{
    /**
     * construct
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct($message, int $code = 0, Throwable $prev = null){
        parent::__construct($message, $code, $prev);
    }
}