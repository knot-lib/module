<?php
declare(strict_types=1);

namespace KnotLib\Module\Test;

use Throwable;

use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\AbstractModule;
use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Module\ModuleInterface;

final class TestRequiredModule extends AbstractModule implements ModuleInterface
{
    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponents() : array
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
        return Components::MODULE;
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