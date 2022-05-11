<?php

namespace Nish\MVC;


interface ILayout
{
    public function setLayoutAction();

    /**
     * @return IView
     */
    public function getView();
    public function getViewFile();
    public function setViewFile(string $viewFile);
    public function getControllerOutput();
    public function setControllerOutput(string $controllerOutput);

    /**
     * @return IModule|null
     */
    public function getModule();
    public function setModule($module);
}