<?php

namespace Nish\Cachers;


use Nish\Commons\Di;
use Nish\Exceptions\InvalidTypeException;

class NishCacherContainer
{
    public const DEFAULT_CACHER_CONTAINER_KEY = '__defaultCacher__';

    /**
     * @param callable $configurationCallable
     * @param string $containerKey
     * @throws InvalidTypeException
     */
    public static function configure($configurationCallable, $containerKey = self::DEFAULT_CACHER_CONTAINER_KEY)
    {
        if (empty($containerKey)) {
            throw new InvalidTypeException('Empty cacher container key!');
        }

        Di::put($containerKey, $configurationCallable);
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