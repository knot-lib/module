<?php
namespace KnotLib\Module\Exception;

use Throwable;

class InvalidComponentNameException extends ModuleException
{
    /**
     * construct
     *
     * @param string $component_name
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $component_name, int $code = 0, Throwable $prev = null){
        $msg = sprintf("Invalid component name: %s", $component_name);
        parent::__construct($msg, $code, $prev);
    }
}