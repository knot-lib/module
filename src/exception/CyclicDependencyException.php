<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use Throwable;

class CyclicDependencyException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param string $target_module
     * @param Throwable|null $prev
     */
    public function __construct(string $module, string $target_module, Throwable $prev = null)
    {
        $msg = 'Cyclic module dependency is detected at module(%s). The target module is: %s';
        $msg = sprintf($msg, $module, $target_module);
        parent::__construct($msg, $prev);
    }
}