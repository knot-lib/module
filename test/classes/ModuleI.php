<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use knotlib\kernel\module\ComponentTypes;
use knotlib\kernel\module\ModuleInterface;
use knotlib\kernel\kernel\ApplicationInterface;

class ModuleI implements ModuleInterface
{
    /**
     * ModuleInterface constructor.
     *
     * Module must not have any constructor parameters.
     */
    public function __construct()
    {
    }

    /**
     * Declare dependency on another modules
     *
     * @return array
     */
    public static function requiredModules() : array
    {
        return [
            ModuleB::class,
            ModuleK::class,
        ];
    }

    /**
     * Declare dependency on components
     *
     * @return array
     */
    public static function requiredComponentTypes() : array
    {
        return [
            ComponentTypes::EX_HANDLER,
            ComponentTypes::LOGGER,
            ComponentTypes::EVENTSTREAM,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return ComponentTypes::APPLICATION;
    }

    /**
     * Install module
     *
     * @param ApplicationInterface $app
     */
    public function install(ApplicationInterface $app)
    {
    }

}