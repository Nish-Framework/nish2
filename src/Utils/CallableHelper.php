<?php
namespace Nish\Utils;
use Nish\Exceptions\InvalidTypeException;
use Nish\PrimitiveBeast;

/**
 * Class CallableHelper
 *
 * Source: https://stackoverflow.com/questions/34324576/print-name-or-definition-of-callable-in-php
 *
 * @package Nish\Utils
 */
class CallableHelper extends PrimitiveBeast
{
    public static function getCallableName($callable) {
        switch (true) {
            case is_string($callable) && strpos($callable, '::'):
                return '[static] ' . $callable;
            case is_string($callable):
                return '[function] ' . $callable;
            case is_array($callable) && is_object($callable[0]):
                return '[method] ' . get_class($callable[0])  . '->' . $callable[1];
            case is_array($callable):
                return '[static] ' . $callable[0]  . '::' . $callable[1];
            case $callable instanceof \Closure:
                return '[closure]';
            case is_object($callable):
                return '[invokable] ' . get_class($callable);
            default:
                return '[unknown]';
        }
    }

    /**
     * Retrieve the context of callable for debugging purposes
     *
     * @param callable $callable
     * @return array
     */
    public static function getCallableContext($callable): array
    {
        switch (true) {
            case \is_string($callable) && \strpos($callable, '::'):
                return ['static method' => $callable];
            case \is_string($callable):
                return ['function' => $callable];
            case \is_array($callable) && \is_object($callable[0]):
                return ['class' => \get_class($callable[0]), 'method' => $callable[1]];
            case \is_array($callable):
                return ['class' => $callable[0], 'static method' => $callable[1]];
            case $callable instanceof \Closure:
                try {
                    $reflectedFunction = new \ReflectionFunction($callable);
                    $closureClass = $reflectedFunction->getClosureScopeClass();
                    $closureThis = $reflectedFunction->getClosureThis();
                } catch (\ReflectionException $e) {
                    return ['closure' => 'closure'];
                }

                return [
                    'closure this'  => $closureThis ? \get_class($closureThis) : $reflectedFunction->name,
                    'closure scope' => $closureClass ? $closureClass->getName() : $reflectedFunction->name,
                    'static variables' => self::formatVariablesArray($reflectedFunction->getStaticVariables()),
                ];
            case \is_object($callable):
                return ['invokable' => \get_class($callable)];
            default:
                return ['unknown' => 'unknown'];
        }
    }

    /**
     * Format variables array for debugging purposes in order to avoid huge objects dumping
     *
     * @param array $data
     * @return array
     */
    private static function formatVariablesArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (\is_object($value)) {
                $data[$key] = \get_class($value);
            } elseif (\is_array($value)) {
                $data[$key] = self::formatVariablesArray($value);
            }
        }

        return $data;
    }

    /**
     * Similar to is_callable function of PHP<8
     *
     * @param $callable
     * @return bool
     */
    public static function isCallable($callable): bool
    {
        if (!is_array($callable)) {
            return is_callable($callable);
        } else {
            if (count($callable) < 2) {
                return false;
            }

            return method_exists($callable[0], $callable[1]);
        }
    }

    /**
     * Similar to call_user_func function of PHP<8
     *
     * @param $callback
     * @param mixed ...$args
     * @return false|mixed
     */
    public static function callUserFunc($callback, ...$args)
    {
        if (is_array($callback) && is_string($callback[0])) {
            $className = $callback[0];

            return call_user_func([new $className(), $callback[1]], ...$args);
        } else {
            return call_user_func($callback, ...$args);
        }
    }

    /**
     * Similar to call_user_func_array function of PHP<8
     *
     * @param $callback
     * @param array $args
     * @return false|mixed
     */
    public static function callUserFuncArray($callback, array $args)
    {
        if (is_array($callback) && is_string($callback[0])) {
            $className = $callback[0];

            return call_user_func_array([new $className(), $callback[1]], $args);
        } else {
            return call_user_func_array($callback, $args);
        }
    }
}