<?php
namespace KnotLib\Module\Exception;

use Throwable;

class MethodNotFoundException extends ModuleDependencyResolvingException
{
    /**
     * construct
     *
     * @param string $module
     * @param string $method
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $module, string $method, int $code = 0, Throwable $prev = null){
        $msg = 'Method not found: ' . $method . ' at module: ' . $module;
        parent::__construct($msg, $code, $prev);
    }
}