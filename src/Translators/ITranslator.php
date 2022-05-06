<?php


namespace Nish\Utils;


interface ITranslator
{
    public function translate(string $key, $defaultTranslation = '');
    public function loadTranslations();
    public function addResource(array $resource);
    public function isEmpty();
}