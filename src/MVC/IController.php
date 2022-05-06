<?php
namespace Nish\MVC;

interface IController
{
    public function getView(): View;
    public function renderView($returnResult = false, $viewFile = null, $viewDir = null);
    public function getViewDir();
    public function setViewDir(string $viewDir);
    public function disableView(): void;
    public function enableView(): void;
    public function isViewDisabled(): bool;
    public function getModule();
    public function setModule($module): void;
    function getLayout();
    public function setLayout(ILayout $layout): void;
}