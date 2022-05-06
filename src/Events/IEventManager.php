<?php
namespace Nish\Events;


interface IEventManager
{
    public function addEventListener(string $eventName, string $listenerType, $listener, bool $receivePrevResult = true, array $extraParams = array());
    public function unsetEventListener(string $eventName, ?string $listenerType = null);
    public function hasEventListener(string $eventName, ?string $listenerType = null): bool;
    public function trigger(string $eventName, ?string $listenerType = null, $startParams = null);
}