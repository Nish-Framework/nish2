<?php


namespace Nish\Annotations;

interface IAnnotation {
    public function __construct(...$params);
    public function run(...$args);
}