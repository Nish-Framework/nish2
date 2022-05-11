<?php
namespace Nish\MVC;

interface IController
{
    public function getView(): IView;
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

    /**
     * This method will run immediately before action call and after other middlewares
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function onJustBeforeAllActions(...$args);

    /**
     * This method will run immediately after action call and before other middlewares added to OnAfterAction.
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function onJustAfterAllActions(...$args);
}