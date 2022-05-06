<?php
namespace Nish\Pipes;

use Nish\Exceptions\InvalidTypeException;

class Pipe
{
    protected $funcList = [];

    public function __toString(): string
    {
        return print_r($this->funcList, true);
    }

    /**
     * Adds a new transform function to the pipe
     *
     * @param callable $transformer
     * @param bool $receivePrevResult
     * @param array $extraParams
     * @return $this
     */
    public function push(callable $transformer, bool $receivePrevResult = true, array $extraParams = [])
    {

        $this->funcList[] = [
            'transformer' => $transformer,
            'receivePrevRes' => $receivePrevResult,
            'extraParams' => $extraParams
        ];

        return $this;
    }

    /**
     * Empties the pipe
     */
    public function unload()
    {
        $this->funcList = [];
    }

    /**
     * Checks if the pipe is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->funcList);
    }

    /**
     * Flushes the pipe.
     *
     * @param array|null $startParams
     * @return array|false|mixed|null
     * @throws InvalidTypeException
     */
    public function flush(?array $startParams = null)
    {
        $result = $startParams;

        if (!$this->isEmpty()) {
            foreach ($this->funcList as $runnableObj) {
                $result = $this->runFunc($runnableObj, $result);
            }
        }

        return $result;
    }

    private function runFunc(array $runnableObj, $previousResult)
    {
        $params = [];
        if ($runnableObj['receivePrevRes']) {
            if (!empty($previousResult)) {
                if (is_array($previousResult)) {
                    $params = $previousResult;
                } else {
                    $params = [$previousResult];
                }
            }
        }

        if (!empty($runnableObj['extraParams'])) {
            foreach ($runnableObj['extraParams'] as $i => $param) {
                if (is_callable($param)) {
                    $runnableObj['extraParams'][$i] = call_user_func($param);
                }
            }

            $params = array_merge($params, $runnableObj['extraParams']);
        }


        return call_user_func_array($runnableObj['func'], $params);
    }
}