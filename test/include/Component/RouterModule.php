<?php
namespace KnotLib\Module\Test\Component;

use KnotLib\Kernel\Module\ComponentTypes;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Kernel\Kernel\ApplicationInterface;

class RouterModule implements ModuleInterface
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
        return [];
    }

    /**
     * Declare dependency on components
     *
     * @return array
     */
    public static function requiredComponents() : array
    {
        return [
            ComponentTypes::EVENTSTREAM,
            ComponentTypes::PIPELINE,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return ComponentTypes::ROUTER;
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