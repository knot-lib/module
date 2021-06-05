<?php
declare(strict_types=1);

namespace knotlib\module\Sample\Module;

use knotlib\kernel\kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ComponentTypes;
use knotlib\kernel\module\ModuleInterface;

final class TireModule implements ModuleInterface
{
    /**
     * {@inheritDoc}
     */
    public static function requiredModules() : array
    {
        return [
            CarBodyModule::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function requiredComponentTypes() : array
    {
        return [
            ComponentTypes::LOGGER
        ];
    }

    public static function declareComponentType(): string
    {
        return ComponentTypes::APPLICATION;
    }

    /**
     * {@inheritDoc}
     */
    public function install(ApplicationInterface $app)
    {
        $app->logger()->info('Tire is installed.');
    }
}