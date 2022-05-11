<?php


namespace Nish\Commons;


use Nish\Exceptions\ContainerObjectNotFoundException;
use Nish\Exceptions\InvalidTypeException;

class GlobalSettings
{
    private static $container = [];

    public const SETTING_DEBUG_MODE = '__debugMode__';
    public const SETTING_APP_LOG_LEVEL = '__appLogLevel__';
    public const SETTING_RESOURCE_NOT_FOUND_ACTION = '__resourceNotFoundAction__';
    public const SETTING_UNEXPECTED_BEHAVIOUR_ACTION = '__unexpectedBehaviourAction__';
    public const SETTING_APP_ROOT_DIR = '__appRootDir__';

    /**
     * @param string $key
     * @param $value
     * @throws InvalidTypeException
     */
    public static function put(string $key, $value)
    {
        if (empty($key)) {
            throw new InvalidTypeException('Global setting key is empty!');
        }

        self::$container[$key] = $value;
    }

    public static function putIfNotExists(string $key, $value)
    {
        if (empty($key)) {
            throw new InvalidTypeException('Global setting key is empty!');
        }

        if (!self::has($key)) {
            self::$container[$key] = $value;
        }
    }

    /**
     * @param array $settingList
     * @throws InvalidTypeException
     */
    public static function putAll(array $settingList)
    {
        foreach ($settingList as $key => $val) {
            if (!is_string($key) || empty($key)) {
                throw new InvalidTypeException('Global setting key is empty!');
            }

            self::$container[$key] = $val;
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ContainerObjectNotFoundException
     */
    public static function get(string $key)
    {
        if (!self::has($key)) {
            throw new ContainerObjectNotFoundException($key . ' key not found in global settings!');
        }

        return self::$container[$key];
    }

    public static function getIfExists(string $key)
    {
        if (self::has($key)) {
            return self::$container[$key];
        }
    }

    /**
     * Get all settings
     * @return array
     */
    public static function getAll(): array
    {
        return self::$container;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key)
    {
        return array_key_exists($key, self::$container);
    }
}