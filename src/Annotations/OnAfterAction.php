<?php

namespace Nish\Annotations;

/**
 * @Annotation
 *
 * Class OnAfterAction
 * @package Nish\Annotations
 */
#[\Attribute]
class OnAfterAction implements IAnnotation
{
    private $params = [];

    public function __construct(...$params)
    {
        if ($this->isPHP8()) {
            $this->params = $params;
        } else {
            if (is_array($params) && array_key_exists('value',$params[0])) {
                $this->params = $params[0]['value'];
            }
        }
    }

    public function run(...$args)
    {
    }

    /**
     * @return array|mixed
     */
    public function getParams(): array
    {
        return $this->params;
    }

    private function isPHP8()
    {
        return phpversion() >= 8;
    }
}