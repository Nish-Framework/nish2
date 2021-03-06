<?php
namespace Nish\Events;

use Nish\Pipes\Pipe;
use Nish\PrimitiveBeast;

class EventManager extends PrimitiveBeast implements IEventManager
{

    public const ON_BEFORE_SEND_RESPONSE = '__onBeforeSendResponse__';

    protected $eventStack = [];

    /**
     * @override
     *
     * @param string $eventName
     * @param string $listenerType
     * @param mixed $listener
     */
    public function addEventListener(string $eventName, string $listenerType, $listener, bool $receivePrevResult = true, array $extraParams = array())
    {
        if (!isset($this->eventStack[$eventName])) {
            $this->eventStack[$eventName] = [];
        }

        if (!isset($this->eventStack[$eventName][$listenerType])) {
            $this->eventStack[$eventName][$listenerType] = new Pipe();
        }

        $this->eventStack[$eventName][$listenerType]->push($listener, $receivePrevResult, $extraParams);
    }

    /**
     * @override
     *
     * @param string $eventName
     * @param string|null $listenerType
     */
    public function unsetEventListener(string $eventName, ?string $listenerType = null)
    {
        if (is_null($listenerType)) {
            if (isset($this->eventStack[$eventName])) {
                unset($this->eventStack[$eventName]);
            }
        } else {
            if (isset($this->eventStack[$eventName][$listenerType])) {
                unset($this->eventStack[$eventName][$listenerType]);
            }
        }
    }


    /**
     * @override
     *
     * @param string $eventName
     * @param string|null $listenerType
     * @return bool
     */
    public function hasEventListener(string $eventName, ?string $listenerType = null): bool
    {
        if (is_null($listenerType)) {
            return isset($this->eventStack[$eventName]) && count($this->eventStack[$eventName]) > 0;
        } else {
            return isset($this->eventStack[$eventName]) && isset($this->eventStack[$eventName][$listenerType]) && !$this->eventStack[$eventName][$listenerType]->isEmpty();
        }
    }

    /**
     * @override
     *
     * @param string $eventName
     * @param string|null $listenerType
     * @return array|mixed
     */
    public function trigger(string $eventName, ?string $listenerType = null, $startParams = null)
    {
        $result = $startParams;

        if ($this->hasEventListener($eventName, $listenerType)) {
            if (empty($startParams)) {
                $result = [];
            } elseif (is_array($startParams)) {
                $result = $startParams;
            } else {
                $result = [$startParams];
            }


            if (is_null($listenerType)) {
                /* @var Pipe $pipe */
                foreach ($this->eventStack[$eventName] as $pipe) {
                    $result = $pipe->flush($result);
                }
            } else {
                $result = $this->eventStack[$eventName][$listenerType]->flush($result);
            }
        }

        return $result;
    }
}