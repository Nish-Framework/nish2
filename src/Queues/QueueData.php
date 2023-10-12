<?php


namespace Nish\Queues;


use Nish\Commons\ObjectArrayConversionTrait;

class QueueData implements \JsonSerializable
{

    use ObjectArrayConversionTrait;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /* @var string */
    private $type;

    /* @var array */
    private $params = null;

    /* @var bool */
    private $requeueableOnFail = true;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array|null $params
     * @return $this
     */
    public function setParams(?array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequeueableOnFail(): bool
    {
        return $this->requeueableOnFail;
    }

    /**
     * @param bool $requeueableOnFail
     * @return $this
     */
    public function setRequeueableOnFail(bool $requeueableOnFail)
    {
        $this->requeueableOnFail = $requeueableOnFail;
        return $this;
    }
}