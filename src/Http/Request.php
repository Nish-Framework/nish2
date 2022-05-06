<?php
namespace Nish\Http;

use Nish\Commons\Di;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    public const DEFAULT_REQUEST_OBJ_CONTAINER_KEY = '__defaultRequestObj__';

    /**
     * @return static
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     * @throws \Nish\Exceptions\InvalidTypeException
     */
    public static function getFromGlobals(): static
    {
        if (!Di::has(self::DEFAULT_REQUEST_OBJ_CONTAINER_KEY)) {
            Di::put(
                self::DEFAULT_REQUEST_OBJ_CONTAINER_KEY,
                function () {
                    return self::createFromGlobals();
                }
            );
        }

        return Di::get(self::DEFAULT_REQUEST_OBJ_CONTAINER_KEY);
    }
}