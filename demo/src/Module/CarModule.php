<?php
declare(strict_types=1);

namespace KnotLib\Module\Sample\Module;

use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Kernel\Module\AbstractModule;
use KnotLib\Kernel\Module\Components;

final class CarModule extends AbstractModule implements ModuleInterface
{
    /**
     * {@inheritDoc}
     */
    public static function requiredModules() : array
    {
        return [
            EngineModule::class, TireModule::class, CarBodyModule::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function requiredComponentTypes() : array
    {
        return [
            Components::LOGGER
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function install(ApplicationInterface $app)
    {
        $app->logger()->info('Car is installed.');
    }
}