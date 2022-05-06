<?php

namespace Nish\Cachers;


use Nish\Commons\Di;
use Nish\Exceptions\InvalidTypeException;

class NishCacherContainer
{
    public const DEFAULT_CACHER_CONTAINER_KEY = '__defaultCacher__';

    /**
     * @param callable $configure
     * @param string $containerName
     * @throws InvalidTypeException
     */
    public static function configure(callable $configure, $containerName = self::DEFAULT_CACHER_CONTAINER_KEY)
    {
        if (empty($channel) || empty($containerKey)) {
            throw new InvalidTypeException('Empty cacher container key!');
        }

        Di::put($containerName, $configure);
    }

    /**
     * @param string $containerKey
     * @return mixed
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     */
    public static function get(string $containerKey = self::DEFAULT_CACHER_CONTAINER_KEY)
    {
        return Di::get($containerKey);
    }

    /**
     * @param string $containerKey
     * @return bool
     */
    public static function exists(string $containerKey = self::DEFAULT_CACHER_CONTAINER_KEY)
    {
        return Di::has($containerKey);
    }
}