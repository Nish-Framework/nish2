<?php


namespace Nish\Queues;


use Nish\Exceptions\QueueException;

class SimpleRedisQueue implements IQueue
{
    /* @var string */
    private $queueName;

    /* @var \Redis|\RedisCluster */
    private $redisConn;

    /**
     * SimpleRedisQueue constructor.
     * @param \Redis|\RedisCluster $redisConnection
     */
    public function __construct($redisConnection)
    {
        $this->redisConn = $redisConnection;
    }

    /**
     * @override
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @override
     * @param string $queueName
     * @return $this
     */
    public function setQueueName(string $queueName)
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     * @override
     *
     * @param QueueData $data
     * @return int
     * @throws QueueException
     */
    public function sendToQueue(QueueData $data)
    {
        $newQueueSize = $this->redisConn->rPush($this->queueName, json_encode($data->toArray()));

        if ($newQueueSize === false) {
            throw new QueueException('Sending message to queue failed! The key might not be a list!', -1);
        }

        return $newQueueSize;
    }

    /**
     * @override
     * @return \Nish\Commons\ObjectArrayConversionTrait|QueueData|null
     */
    public function pullNext()
    {
        $queueData = $this->redisConn->lPop($this->queueName);

        if (!$queueData) {
            return null;
        }

        $queueData = json_decode($queueData, true);

        if (!is_array($queueData)) {
            return null;
        }

        return QueueData::fromArray($queueData);
    }
}