<?php


namespace Nish\Queues;


interface IQueue
{
    public function getQueueName(): string;
    public function setQueueName(string $queueName);
    public function sendToQueue(QueueData $data);
    public function pullNext();
}