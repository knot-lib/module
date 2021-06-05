<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use Throwable;

class ModuleClassNotFoundException extends ModuleException
{
    /**
     * construct
     *
     * @param string $module
     * @param Throwable|null $prev
     */
    public function __construct(string $module, Throwable $prev = null){
        $msg = 'Module not found: ' . $module;
        parent::__construct($msg, $prev);
    }
}