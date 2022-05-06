<?php

namespace Nish\Commons;

class Environment
{
    public const ENV_DEV = 'dev';
    public const ENV_TEST = 'test';
    public const ENV_PROD = 'prod';

    private static $envName = self::ENV_DEV;

    /**
     * @return string
     */
    public static function getEnvName(): string
    {
        return self::$envName;
    }

    /**
     * @param string $envName
     */
    public static function setEnvName(string $envName): void
    {
        self::$envName = $envName;
    }


    /**
     * Check environment name
     *
     * @param string $envName
     * @return bool
     */
    public static function is(string $envName)
    {
        return $envName == self::$envName;
    }
}