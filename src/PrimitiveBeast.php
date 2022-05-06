<?php
namespace Nish;


use Nish\Cachers\NishCacherContainer;
use Nish\Commons\Di;
use Nish\Commons\GlobalSettings;
use Nish\Events\IEventManager;
use Nish\Exceptions\InvalidTypeException;
use Nish\Logger\NishLoggerContainer;

abstract class PrimitiveBeast
{
    public const DEFAULT_TRANSLATOR_CONTAINER_KEY = '__defaultTranslator__';
    public const DEFAULT_EVENT_MANAGER_CONTAINER_KEY = '__defaultEventManager__';

    /**
     * @param string $containerKey
     * @return Logger\Logger
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getLogger($containerKey = NishLoggerContainer::DEFAULT_LOGGER_CONTAINER_KEY)
    {
        return NishLoggerContainer::get($containerKey);
    }

    /**
     * @param callable $callable
     * @throws InvalidTypeException
     */
    public function setDefaultTranslator(callable $callable)
    {
        Di::put(self::DEFAULT_TRANSLATOR_CONTAINER_KEY, $callable);
    }

    /**
     * @return mixed
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getDefaultTranslator()
    {
        return Di::get(self::DEFAULT_TRANSLATOR_CONTAINER_KEY);
    }


    /**
     * @return Logger\Logger
     */
    public static function getDefaultLogger()
    {
        return NishLoggerContainer::get(NishLoggerContainer::DEFAULT_LOGGER_CONTAINER_KEY);
    }

    /**
     * @return mixed
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getAppRootDir()
    {
        return GlobalSettings::get(GlobalSettings::SETTING_APP_ROOT_DIR);
    }

    /**
     * @return IEventManager | null
     */
    public static function getDefaultEventManager()
    {
        return Di::get(self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY);
    }

    public static function setDefaultEventManager(callable $eventManagerSetter)
    {
        Di::put(self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY, $eventManagerSetter);
    }

    /**
     * @return bool
     */
    public static function isPHP8Available()
    {
        return phpversion() >= 8;
    }

    /**
     * @param callable $callable
     * @param array|null $args
     * @param int $expiresAfter
     * @param string $cacheContainerKey
     * @return false|mixed
     * @throws Exceptions\ContainerObjectNotFoundException
     * @throws InvalidTypeException
     */
    public function memoizedCall(callable $callable, ?array $args = null, int $expiresAfter = 3600, $cacheContainerKey = NishCacherContainer::DEFAULT_CACHER_CONTAINER_KEY)
    {
        if (!is_callable($callable, null, $callableName)) {
            throw new InvalidTypeException('Invalid callable '. $callableName);
        }

        if (empty($args)) $args = [];

        if (!NishCacherContainer::exists()) {
            $result = call_user_func_array($callable, $args);
        } else {
            $cacher = NishCacherContainer::get($cacheContainerKey);

            $serializedArgs = sha1(serialize($args));

            $key = 'methods|' . str_replace(['/','\\', '::'], ['|', '|', '.'], trim($callableName, '/\\')).'|'.$serializedArgs;

            $result = $cacher->get($key, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($callable, $args, $expiresAfter) {
                $item->expiresAfter($expiresAfter);

                return call_user_func_array($callable, $args);
            });
        }

        return $result;
    }

    public function getDefaultLogLineFormatter()
    {
        return new \Monolog\Formatter\LineFormatter("%datetime% :: %level_name% :: %message% :: %context% :: %extra%\n");
    }

    public static function isAppInDebugMode(): bool
    {
        return GlobalSettings::get(GlobalSettings::SETTING_DEBUG_MODE) === true;
    }

    public static function getLogLevel()
    {
        return GlobalSettings::get(GlobalSettings::SETTING_APP_LOG_LEVEL);
    }

    /**
     * @return mixed
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getResourceNotFoundAction()
    {
        return GlobalSettings::get(GlobalSettings::SETTING_RESOURCE_NOT_FOUND_ACTION);
    }

    /**
     * @param mixed ...$args
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function callResourceNotFoundAction(...$args)
    {
        call_user_func(self::getResourceNotFoundAction(), $args);
    }

    /**
     * @return mixed
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getUnexpectedBehaviourAction()
    {
        return GlobalSettings::get(GlobalSettings::SETTING_UNEXPECTED_BEHAVIOUR_ACTION);
    }

    /**
     * @param mixed ...$args
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function callUnexpectedBehaviourAction(...$args)
    {
        call_user_func(self::getUnexpectedBehaviourAction(), $args);
    }
}