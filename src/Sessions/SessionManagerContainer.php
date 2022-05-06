<?php

namespace Nish\Sessions;

use Nish\Commons\Di;
use Nish\Exceptions\InvalidTypeException;

class SessionManagerContainer
{
    public const DEFAULT_MANAGER_CONTAINER_KEY = '__defaultSessionManager__';

    /**
     * @param callable $configure
     * @param string $containerName
     * @throws InvalidTypeException
     */
    public static function set(callable $configure, $containerName = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        if (empty($channel) || empty($containerKey)) {
            throw new InvalidTypeException('Empty session manager container key!');
        }

        Di::put($containerName, $configure);
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

    /**
     * @param string $containerKey
     * @return bool
     */
    public static function exists(string $containerKey = self::DEFAULT_MANAGER_CONTAINER_KEY)
    {
        return Di::has($containerKey);
    }
}