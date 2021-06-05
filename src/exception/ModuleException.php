<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use Throwable;
use knotlib\exception\KnotPhpException;

class ModuleException extends KnotPhpException implements ModuleExceptionInterface
{
    /**
     * construct
     *
     * @param string $message
     * @param Throwable|null $prev
     */
    public function __construct(string $message, Throwable $prev = null){
        parent::__construct($message, 0, $prev);
    }
}