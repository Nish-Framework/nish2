<?php

namespace Nish\Pipes;

interface ITransformable
{
    public function transform(...$args);
}