<?php
namespace KnotLib\Module\Exception;

use Throwable;

class ClassNotFoundException extends ModuleException
{
    /**
     * ClassNotFoundException constructor.
     *
     * @param string $class_name
     * @param int $code
     * @param Throwable|NULL $prev
     */
    public function __construct( string $class_name, int $code = 0, Throwable $prev = NULL )
    {
        parent::__construct( "Class not found: $class_name", $code, $prev );
    }
}