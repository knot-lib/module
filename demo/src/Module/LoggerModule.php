<?php
declare(strict_types=1);

namespace KnotLib\Module\Sample\Module;

use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Logger\AbstractLogger;
use KnotLib\Kernel\Logger\LoggerChannelInterface;
use KnotLib\Kernel\Logger\LoggerInterface;
use KnotLib\Kernel\Module\ModuleInterface;
use KnotLib\Kernel\Module\AbstractModule;

final class LoggerModule extends AbstractModule implements ModuleInterface
{
    /**
     * {@inheritDoc}
     */
    public static function declareComponentType() : string
    {
        return Components::LOGGER;
    }

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
    public function install(ApplicationInterface $app)
    {
        $app->logger(new class extends AbstractLogger implements LoggerInterface{
            public function log($level, $message, array $context = array())
            {
                echo $message . PHP_EOL;
            }
            public function channel(string $channel_id) : LoggerChannelInterface
            {
                return null;
            }
        })
        ->info('Logger installed.');
    }
}