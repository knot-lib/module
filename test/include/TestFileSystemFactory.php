<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\FileSystem\FileSystemFactoryInterface;
use KnotLib\Kernel\FileSystem\FileSystemInterface;

final class TestFileSystemFactory implements FileSystemFactoryInterface
{
    public static function createFileSystem(bool $empty_plugin_dir = false): FileSystemInterface
    {
        $base_dir = dirname(__DIR__) . '/files';
        return new TestFileSystem($base_dir, $empty_plugin_dir);
    }

}