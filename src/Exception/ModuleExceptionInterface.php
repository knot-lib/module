<?php
namespace KnotLib\Module\Exception;

use KnotLib\Exception\KnotPhpExceptionInterface;
use KnotLib\Exception\Runtime\RuntimeExceptionInterface;

interface ModuleExceptionInterface extends KnotPhpExceptionInterface, RuntimeExceptionInterface
{

}