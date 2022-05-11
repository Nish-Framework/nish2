<?php


namespace Nish\Annotations;

use Nish\Exceptions\InvalidTypeException;
use Nish\Utils\CallableHelper;

#[\Attribute]
class OnAfterAction implements IAnnotation
{
    public function __construct(...$params)
    {

    }

    public function run(...$args)
    {
    }
}