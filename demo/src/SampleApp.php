<?php
namespace KnotLib\Module\Sample;

use KnotLib\Kernel\FileSystem\FileSystemInterface;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Kernel\ApplicationType;
use KnotLib\Module\Application\SimpleApplication;
use KnotLib\Module\Sample\Module\CarBodyModule;
use KnotLib\Module\Sample\Module\LoggerModule;
use KnotLib\Module\Sample\Module\CarModule;
use KnotLib\Module\Sample\Module\EngineModule;
use KnotLib\Module\Sample\Module\TireModule;

class SampleApp extends SimpleApplication
{
    public static function type(): ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

    /**
     * SampleApp constructor.
     *
     * @param FileSystemInterface $filesystem
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct(FileSystemInterface $filesystem = null)
    {
        parent::__construct(new SampleFileSystem);
    }

    /**
     * {@inheritDoc}
     */
    public function configure() : ApplicationInterface
    {
        $this
            ->requireModule(CarModule::class)
            ->requireModule(CarBodyModule::class)
            ->requireModule(LoggerModule::class)
            ->requireModule(EngineModule::class)
            ->requireModule(TireModule::class);

        return $this;
    }
}