<?php
namespace Nish;


use Nish\Cachers\NishCacherContainer;
use Nish\Commons\Di;
use Nish\Commons\GlobalSettings;
use Nish\Events\IEventManager;
use Nish\Exceptions\InvalidTypeException;
use Nish\Logger\NishLoggerContainer;
use Nish\Utils\CallableHelper;

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
    public static function setDefaultTranslator($callable)
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
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getDefaultLogger()
    {
        return self::getLogger();
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
     * IEventManager | null
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function getDefaultEventManager()
    {
        return Di::get(self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY);
    }

    /**
     * @param callable $eventManagerSetter
     * @throws InvalidTypeException
     */
    public static function setDefaultEventManager($eventManagerSetter)
    {
        Di::put(self::DEFAULT_EVENT_MANAGER_CONTAINER_KEY, $eventManagerSetter);

    }

    /**
     * @return bool
     */
    public static function isPHP8()
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
    public function memoizedCall($callable, ?array $args = null, int $expiresAfter = 3600, $cacheContainerKey = NishCacherContainer::DEFAULT_CACHER_CONTAINER_KEY)
    {
        if (!CallableHelper::isCallable($callable)) {
            throw new InvalidTypeException('Invalid callable parameter: ' . CallableHelper::getCallableName($callable));
        }

        if (empty($args)) $args = [];

        if (!NishCacherContainer::exists()) {
            $result = CallableHelper::callUserFuncArray($callable, $args);
        } else {
            $cacher = NishCacherContainer::get($cacheContainerKey);

            $serializedArgs = sha1(serialize($args));

            $key = 'methods|' . str_replace(['/','\\', '::'], ['|', '|', '.'], trim($callableName, '/\\')).'|'.$serializedArgs;

            $result = $cacher->get($key, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($callable, $args, $expiresAfter) {
                $item->expiresAfter($expiresAfter);

                return CallableHelper::callUserFuncArray($callable, $args);
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
    public static function callResourceNotFoundAction(\Exception $e)
    {
        if (GlobalSettings::has(GlobalSettings::SETTING_RESOURCE_NOT_FOUND_ACTION)) {
            call_user_func(self::getResourceNotFoundAction(), $e);
        }

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
     * @param \Exception $e
     * @throws Exceptions\ContainerObjectNotFoundException
     */
    public static function callUnexpectedBehaviourAction(\Exception $e)
    {
        if (GlobalSettings::has(GlobalSettings::SETTING_UNEXPECTED_BEHAVIOUR_ACTION)) {
            call_user_func(self::getUnexpectedBehaviourAction(), $e);
        }
    }


}