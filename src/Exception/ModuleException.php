<?php
namespace KnotLib\Module\Exception;

use Throwable;
use KnotLib\Exception\KnotPhpException;

class ModuleException extends KnotPhpException implements ModuleExceptionInterface
{
    /**
     * construct
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $message, int $code = 0, Throwable $prev = null){
        parent::__construct($message, $code, $prev);
    }
}