<?php


namespace Nish\Queues;


use Nish\Logger\Logger;

class SimpleQueueExecutor
{
    /* @var array */
    private $workers = [];

    private IQueue $queue;

    private ?Logger $logger;

    public function __construct(IQueue $queue, ?Logger $logger = null)
    {
        $this->queue = $queue;
        $this->logger = $logger;
    }


    public function run(?int $maxTaskCountTobeExecuted = null)
    {
        $doExecute = true;

        $i = 0;
        while ($doExecute) {
            $data = $this->queue->pullNext();

            if ($data) {
                if (array_key_exists($data->getType(), $this->workers)) {
                    try {
                        if ($data->getParams() !== null) {
                            call_user_func_array($this->workers[$data->getType()], $data->getParams());
                        } else {
                            call_user_func($this->workers[$data->getType()]);
                        }
                    } catch (\Throwable $e) {
                        if ($this->logger) {
                            $this->logger->error('Exception on executing queue task!', ['errorCode' => $e->getCode(), 'errorMsg' => $e->getMessage(), 'queueType' => $data->getType(), 'taskParams' => $data->getParams(), 'trace' => $e->getTraceAsString()]);
                        }

                        if ($data->isRequeueableOnFail()) {
                            $this->queue->sendToQueue($data);
                        }
                    }

                } else {//if worker not found, requeue the task
                    $this->queue->sendToQueue($data);
                }
            }

            $i++;

            if ($maxTaskCountTobeExecuted > 0 && $i >= $maxTaskCountTobeExecuted) {
                $doExecute = false;
            }

            sleep(1);
        }
    }

    /**
     * @param string $type
     * @param callable $worker
     * @return $this
     */
    public function addWorker(string $type, callable $worker)
    {
        $this->workers[$type] = $worker;
        return $this;
    }

    /**
     * @return IQueue
     */
    public function getQueue(): IQueue
    {
        return $this->queue;
    }

    /**
     * @param IQueue $queue
     * @return $this
     */
    public function setQueue(IQueue $queue)
    {
        $this->queue = $queue;
        return $this;
    }
}