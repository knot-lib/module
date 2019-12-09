<?php
namespace KnotLib\Module\Exception;

use Throwable;

class CyclicDependencyException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param string $target_module
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $module, string $target_module, int $code = 0, Throwable $prev = null)
    {
        $msg = 'Cyclic module dependency is detected at module(%s). The target module is: %s';
        $msg = sprintf($msg, $module, $target_module);
        parent::__construct($msg, $code, $prev);
    }
}