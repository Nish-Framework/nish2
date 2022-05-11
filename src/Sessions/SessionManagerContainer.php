<?php

namespace Nish\Sessions;

use Nish\Commons\Di;
use Nish\Exceptions\InvalidTypeException;

class SessionManagerContainer
{
    public const DEFAULT_MANAGER_CONTAINER_KEY = '__defaultSessionManager__';

    /**
     * @param callable $configurationCallable
     * @param string $containerKey
     * @throws InvalidTypeException
     */
    public static function set($configurationCallable, $containerKey = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        if (empty($containerKey)) {
            throw new InvalidTypeException('Empty session manager container key!');
        }

        Di::put($containerKey, $configurationCallable);
    }

    /**
     * @param string $containerKey
     * @return mixed
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     */
    public static function get(string $containerKey = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        return Di::get($containerKey);
    }

    public static function getIfExists(string $containerKey = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        return Di::getIfExists($containerKey);
    }

    /**
     * @param string $containerKey
     * @return bool
     */
    public static function exists(string $containerKey = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        return Di::has($containerKey);
    }
}