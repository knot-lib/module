<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use KnotLib\Kernel\FileSystem\FileSystemInterface;
use KnotLib\Kernel\FileSystem\AbstractFileSystem;
use KnotLib\Kernel\FileSystem\Dir;

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
            Dir::PLUGIN    => $this->empty_plugin_dir ? $base_dir . '/empty_plugin_dir' : $base_dir . '/plugin',
            Dir::CACHE     => $base_dir . '/cache',
            Dir::CONFIG    => $base_dir . '/config',
            Dir::LOGS      => $base_dir . '/logs',
            Dir::INCLUDE   => $base_dir . '/include',
        ];
    }

    public function directoryExists(int $dir): bool
    {
        return isset($this->dir_map[$dir]);
    }

    public function getDirectory(int $dir): string
    {
        return $this->dir_map[$dir] ?? parent::getDirectory($dir);
    }
}