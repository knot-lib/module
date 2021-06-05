<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use knotlib\kernel\filesystem\FileSystemInterface;

final class TestFileSystemFactory
{
    public static function createFileSystem(bool $empty_plugin_dir = false): FileSystemInterface
    {
        $base_dir = dirname(__DIR__) . '/files';
        return new TestFileSystem($base_dir, $empty_plugin_dir);
    }

}