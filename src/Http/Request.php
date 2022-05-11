<?php
namespace Nish\Http;

use Nish\Commons\Di;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    public const DEFAULT_REQUEST_OBJ_CONTAINER_KEY = '__defaultRequestObj__';

    private $urlArgs = [];

    /**
     * @return Request
     * @throws \Nish\Exceptions\ContainerObjectNotFoundException
     * @throws \Nish\Exceptions\InvalidTypeException
     */
    public static function getFromGlobals(): Request
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

    /**
     * @return array
     */
    public function getUrlArgs(): ?array
    {
        return $this->urlArgs;
    }

    /**
     * @param array $urlArgs
     */
    public function setUrlArgs(?array $urlArgs): void
    {
        $this->urlArgs = $urlArgs;
    }

}