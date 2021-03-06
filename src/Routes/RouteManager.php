<?php

namespace Nish\Routes;


use Nish\Exceptions\InvalidTypeException;
use Nish\Exceptions\ResourceNotFoundException;
use Nish\Exceptions\RouteException;
use Nish\Http\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class RouteManager
{
    private static $routeList = [];

    /* @var RouteCollection $routeCollection */
    private static $routeCollection;

    /* @var RequestContext $context */
    private static $context;

    /* @var UrlMatcher $matcher */
    private static $matcher;

    private static $basePath = '';

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return self::$basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        self::$basePath = $basePath;
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        self::$routeList[$route->getName()] = $route;
    }

    /**
     * @param array $routeList
     * @throws InvalidTypeException
     */
    public function addRouteList(array $routeList)
    {
        foreach ($routeList as $route) {
            if (!($route instanceof Route)) {
                throw new InvalidTypeException('Invalid route type!');
            }

            self::$routeList[$route->getName()] = $route;
        }
    }

    /**
     * @param $routeName
     * @return Route|null
     */
    public function getRouteByName($routeName)
    {
        if (!array_key_exists($routeName, self::$routeList)) {
            return null;
        }

        return self::$routeList[$routeName];
    }

    public function getRouteList(): array
    {
        return self::$routeList;
    }


    public function boot()
    {
        self::$routeCollection = new RouteCollection();

        /* @var Route $route */
        foreach (self::$routeList as $route) {
            self::$routeCollection->add(
                $route->getName(),
                new \Symfony\Component\Routing\Route(
                    $route->getPath(),
                    $route->getParameterDefaults(),
                    $route->getParameterRequirements(),
                    [],
                    '',
                    $route->getSchemes(),
                    $route->getMethods(),
                    $route->getConditions()
                ),
                $route->getPriority()
            );
        }

        self::$context = new RequestContext();

        self::$context->fromRequest(Request::getFromGlobals());
        self::$matcher = new UrlMatcher(self::$routeCollection, self::$context);
    }

    /**
     * @param $path
     * @return array
     * @throws ResourceNotFoundException
     */
    public function matchPath($path)
    {
        try {
            return self::$matcher->match($path);
        } catch (\Exception $e) {
            throw new ResourceNotFoundException($e->getMessage());
        }
    }

    /**
     * Directs to given url
     *
     * @param $url
     */
    public function route($url)
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     *
     * @param $routeName
     * @throws RouteException
     */
    public function routeByName($routeName)
    {
        /* @var Route|null $route */
        $route = self::getRouteByName($routeName);

        if ($route == null) {
            throw new RouteException('Route not found with name: ' . $routeName);
        }

        $this->route( self::$basePath.$route->getPath());
    }

    /**
     * Return route path
     *
     * @param $routeName
     * @return string
     * @throws RouteException
     */
    public function getPath($routeName)
    {
        /* @var Route|null $route */
        $route = self::getRouteByName($routeName);

        if ($route == null) {
            throw new RouteException('Route not found with name: ' . $routeName);
        }

        return $route->getPath();
    }

    public function generate($routeName)
    {
        return $this->getPath($routeName);
    }
}