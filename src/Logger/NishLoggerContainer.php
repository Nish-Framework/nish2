<?php

namespace Nish\Logger;

use Nish\Commons\Di;
use Nish\Exceptions\InvalidTypeException;

class NishLoggerContainer
{
    public const DEFAULT_LOGGER_CONTAINER_KEY = '__defaultLogger__';

    public function __construct($containerKey, $cloneFromIfNotExists = self::DEFAULT_LOGGER_CONTAINER_KEY, $channel = null)
    {
        if (Di::has($containerKey)) {
            return Di::get($containerKey);
        } else {
            if (empty($channel)) {
                $channel = $containerKey;
            }
            self::cloneLogger($containerKey, $channel, $cloneFromIfNotExists );
        }
    }

    public static function configure(array $handlerList, ?array $processorList = null, string $channel = 'DefaultLogger', string $containerKey = self::DEFAULT_LOGGER_CONTAINER_KEY)
    {
        if (empty($channel) || empty($containerKey)) {
            throw new InvalidTypeException('Empty logger channel name or container key!');
        }

        Di::put(
            $containerKey,
            function () use ($handlerList, $processorList, $channel) {
                $logger = new Logger($channel);

                foreach ($handlerList as $handler) {
                    $logger->pushHandler($handler);
                }

                if (!empty($processorList)) {
                    foreach ($processorList as $processor) {
                        $logger->pushProcessor($processor);
                    }
                }

                return $logger;
            }
        );
    }

    public static function cloneLogger(string $containerKey, string $channel, string $cloneFrom = self::DEFAULT_LOGGER_CONTAINER_KEY)
    {
        if (empty($channel) || empty($containerKey) || empty($cloneFrom)) {
            throw new InvalidTypeException('Empty logger channel name, new container key or cloned container key!');
        }

        Di::put(
            $containerKey,
            function () use ($cloneFrom, $channel) {
                return self::get($cloneFrom)->withName($channel);
            }
        );

    }

    /**
     * @param string $containerKey
     * @return Logger
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     */
    public static function get(string $containerKey = self::DEFAULT_LOGGER_CONTAINER_KEY): Logger
    {
        return Di::get($containerKey);
    }

    /**
     * @param string $containerKey
     * @return Logger|null
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     */
    public static function getIfExists(string $containerKey = self::DEFAULT_LOGGER_CONTAINER_KEY)
    {
        if (Di::has($containerKey)) {
            return Di::get($containerKey);
        } else {
            return null;
        }
    }

    /**
     * @param string $containerKey
     * @return bool
     */
    public static function exists(string $containerKey = self::DEFAULT_LOGGER_CONTAINER_KEY)
    {
        return Di::has($containerKey);
    }
}