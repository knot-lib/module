<?php
declare(strict_types=1);

namespace KnotLib\Module\Sample\Module;

use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Kernel\Module\AbstractModule;

final class CarBodyModule extends AbstractModule implements ModuleInterface
{
    /**
     * {@inheritDoc}
     */
    public static function requiredModules() : array
    {
        return [];
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
        $app->logger()->info('Car body is installed.');
    }
}