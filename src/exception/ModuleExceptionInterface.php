<?php
declare(strict_types=1);

namespace knotlib\module\exception;

use knotlib\exception\KnotPhpExceptionInterface;
use knotlib\exception\runtime\RuntimeExceptionInterface;

interface ModuleExceptionInterface extends KnotPhpExceptionInterface, RuntimeExceptionInterface
{

}