<?php
namespace Nish\Utils;
/**
 * Class CallableHelper
 *
 * Source: https://stackoverflow.com/questions/34324576/print-name-or-definition-of-callable-in-php
 *
 * @package Nish\Utils
 */
class CallableHelper
{
    public static function getCallableName(callable $callable) {
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
    public static function getCallableContext(callable $callable): array
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
}