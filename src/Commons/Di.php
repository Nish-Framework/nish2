<?php
namespace Nish\Commons;

use Nish\Exceptions\ContainerObjectNotFoundException;
use Nish\Exceptions\InvalidTypeException;
use Nish\Utils\CallableHelper;

class Di {
    private static $container = [];

    /**
     * @param string $objectKey
     * @param callable $callable
     * @throws InvalidTypeException
     */
    public static function put(string $objectKey, $callable)
    {
        if (empty($objectKey)) {
            throw new InvalidTypeException('DI container object key is empty!');
        }

        if (!CallableHelper::isCallable($callable)) {
            throw new InvalidTypeException('Invalid callable parameter: ' . CallableHelper::getCallableName($callable));
        }

        self::$container[$objectKey] = [
            'func' => $callable,
            'val' => null
        ];
    }

    /**
     * @param array $injectionPairList
     * @throws InvalidTypeException
     */
    public static function putAll(array $injectionPairList)
    {
        foreach ($injectionPairList as $objectKey => $callable) {
            if (!is_string($objectKey) || empty($objectKey)) {
                throw new InvalidTypeException('DI container object key is empty or not string!');
            }

            if (!is_callable($callable)) {
                throw new InvalidTypeException('DI container object is not a callable!');
            }

            self::$container[$objectKey] = [
                'func' => $callable,
                'val' => null
            ];
        }
    }

    /**
     * @param string $objectKey
     * @return mixed
     * @throws ContainerObjectNotFoundException
     */
    public static function get(string $objectKey)
    {
        if (!self::has($objectKey)) {
            throw new ContainerObjectNotFoundException($objectKey . ' key not found in DI container!');
        }

        if (self::$container[$objectKey]['val'] == null) {
            self::$container[$objectKey]['val'] = CallableHelper::callUserFunc(self::$container[$objectKey]['func']);
        }

        return self::$container[$objectKey]['val'];

    }

    /**
     * @param string $objectKey
     * @return mixed|null
     */
    public static function getIfExists(string $objectKey)
    {
        if (self::has($objectKey)) {
            if (self::$container[$objectKey]['val'] == null) {
                self::$container[$objectKey]['val'] = CallableHelper::callUserFunc(self::$container[$objectKey]['func']);
            }

            return self::$container[$objectKey]['val'];
        } else {
            return null;
        }
    }


    /**
     * @param string $objectKey
     * @return bool
     */
    public static function has(string $objectKey)
    {
        return array_key_exists($objectKey, self::$container);
    }
}