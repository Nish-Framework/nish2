<?php


namespace Nish\MVC;


interface IView
{
    public function getViewDir();
    public function setViewDir($viewDir);
    public function isRendered(): bool;
    public function setRendered(bool $rendered);
    public function render(string $file);
}