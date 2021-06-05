<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use Throwable;

class ClassNotFoundException extends ModuleException
{
    /**
     * ClassNotFoundException constructor.
     *
     * @param string $class_name
     * @param Throwable|NULL $prev
     */
    public function __construct( string $class_name, Throwable $prev = NULL )
    {
        parent::__construct( "Class not found: $class_name", $prev );
    }
}