<?php
namespace Nish\MVC;

interface IModule
{
    public function configure();
    public function getViewDir();
    public function setViewDir(string $viewDir);
    public function areViewsDisabled();
    public function disableViews();
    public function enableViews();
    public function getLayout(): ILayout;
}