<?php
declare(strict_types=1);

namespace knotlib\module\Sample\Module;

use knotlib\kernel\kernel\ApplicationInterface;
use KnotLib\Kernel\Logger\AbstractLogger;
use KnotLib\Kernel\Logger\LoggerChannelInterface;
use KnotLib\Kernel\Logger\LoggerInterface;
use KnotLib\Kernel\Module\ComponentTypes;
use knotlib\kernel\module\ModuleInterface;
use KnotLib\Kernel\NullObject\NullLoggerChannel;

final class LoggerModule implements ModuleInterface
{
    /**
     * {@inheritDoc}
     */
    public static function declareComponentType() : string
    {
        return ComponentTypes::LOGGER;
    }

    /**
     * {@inheritDoc}
     */
    public static function requiredModules() : array
    {
        return [];
    }

    public static function requiredComponentTypes(): array
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
                return new NullLoggerChannel();
            }
        })
        ->info('Logger installed.');
    }
}