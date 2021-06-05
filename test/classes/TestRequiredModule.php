<?php
declare(strict_types=1);

namespace knotlib\module\test\classes;

use Throwable;

use knotlib\kernel\exception\ModuleInstallationException;
use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\kernel\module\ComponentTypes;
use knotlib\kernel\module\ModuleInterface;

final class TestRequiredModule implements ModuleInterface
{
    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponentTypes() : array
    {
        return [
        ];
    }

    /**
     * Declare dependent on another modules
     *
     * @return array
     */
    public static function requiredModules() : array
    {
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function declareComponentType(): string
    {
        return ComponentTypes::APPLICATION;
    }

    /**
     * Install module
     *
     * @param ApplicationInterface $app
     *
     * @throws  ModuleInstallationException
     */
    public function install(ApplicationInterface $app)
    {
        try{
            echo 'Required module is installed.', PHP_EOL;
        }
        catch(Throwable $e)
        {
            throw new ModuleInstallationException(self::class, 'Failed to install module', 0, $e);
        }
    }
}