<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\filesystem\AbstractFileSystem;
use knotlib\kernel\filesystem\Dir;

final class TestFileSystem extends AbstractFileSystem implements FileSystemInterface
{
    /** @var bool */
    private $empty_plugin_dir;
    
    private $dir_map = [
    ];

    public function __construct(string $base_dir, bool $empty_plugin_dir = false)
    {
        $this->empty_plugin_dir = $empty_plugin_dir;
        
        $this->dir_map = [
            Dir::DATA      => $base_dir . '/data',
            Dir::COMMAND   => $base_dir . '/command',
            Dir::CACHE     => $base_dir . '/cache',
            Dir::CONFIG    => $base_dir . '/config',
            Dir::LOGS      => $base_dir . '/logs',
            Dir::INCLUDE   => $base_dir . '/include',
        ];
    }

    public function directoryExists(string $dir): bool
    {
        return isset($this->dir_map[$dir]);
    }

    public function getDirectory(string $dir): string
    {
        return $this->dir_map[$dir] ?? parent::getDirectory($dir);
    }
}