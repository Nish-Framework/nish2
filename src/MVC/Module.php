<?php
namespace Nish\MVC;

use Nish\Http\Request;
use Nish\PrimitiveBeast;
use Nish\Routes\RouteManager;

abstract class Module extends PrimitiveBeast implements IModule
{
    use ModuleTrait;

    /**
     * @var RouteManager
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->router = new RouteManager();
        $this->request = Request::getFromGlobals();
    }
}