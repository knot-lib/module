<?php
namespace knotlib\module\Sample;

use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\kernel\kernel\ApplicationType;
use knotlib\module\application\SimpleApplication;
use knotlib\module\Sample\Module\CarBodyModule;
use knotlib\module\Sample\Module\LoggerModule;
use knotlib\module\Sample\Module\CarModule;
use knotlib\module\Sample\Module\EngineModule;
use knotlib\module\Sample\Module\TireModule;

class SampleApp extends SimpleApplication
{
    public static function type(): ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }

    /**
     * SampleApp constructor.
     *
     * @param FileSystemInterface|null $filesystem
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