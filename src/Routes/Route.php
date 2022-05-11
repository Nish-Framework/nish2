<?php

namespace Nish\Routes;


use Nish\Exceptions\InvalidTypeException;
use Nish\MVC\IModule;
use Nish\Utils\CallableHelper;

class Route
{
    private $name;
    private $path; //The path pattern to match
    private $methods = ['GET', 'POST']; //A required HTTP method or an array of restricted methods
    private $conditions = null; //A condition that should evaluate to true for the route to match
    private $moduleClassNameOrObj = null;
    private $action;
    private $parameterRequirements = []; //An array of requirements for parameters (regexes)
    private $parameterDefaults = []; //An array of default parameter values
    private $priority = 0;
    private $schemes = []; //A required URI scheme or an array of restricted schemes
    private $middleware = null; //A callable that will run before action call

    /**
     * Route constructor.
     * @param string $name Route name
     * @param string $path The path pattern to match
     * @param callable $action Action to run
     * @param null|string|object $moduleClassNameOrObj
     * @param array|string[] $methods A required HTTP method or an array of restricted methods
     * @param null|callable $middleware A callable that will run before action call
     * @param array $parameterDefaults An array of requirements for parameters (regexes)
     * @param array $parameterRequirements An array of requirements for parameters (regexes)
     * @param null $conditions A condition that should evaluate to true for the route to match
     * @param array $schemes A required URI scheme or an array of restricted schemes
     * @param int $priority
     * @throws InvalidTypeException
     */
    public function __construct(string $name, string $path, $action, $moduleClassNameOrObj = null, array $methods = ['GET', 'POST'], $middleware = null, array $parameterDefaults = [], array $parameterRequirements = [], $conditions = null, array $schemes = [], int $priority = 0)
    {

        $this->name = $name;
        $this->path = $path;
        $this->setAction($action);
        $this->setModuleClassNameOrObj($moduleClassNameOrObj);
        $this->methods = $methods;
        $this->parameterDefaults = $parameterDefaults;
        $this->parameterRequirements = $parameterRequirements;
        $this->conditions = $conditions;
        $this->schemes = $schemes;
        $this->priority = $priority;
        $this->setMiddleware($middleware);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }



    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string|null $conditions
     * @return $this
     */
    public function setConditions(?string $conditions = null)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return callable
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param callable $action
     * @return $this
     * @throws InvalidTypeException
     */
    public function setAction($action)
    {
        if (!is_array($action)) {
            throw new InvalidTypeException('Route action must be a class method! Given ' . CallableHelper::getCallableName($action));
        }

        if (!CallableHelper::isCallable($action)) {
            throw new InvalidTypeException('Invalid callable parameter: ' . CallableHelper::getCallableName($action));
        }

        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameterRequirements(): array
    {
        return $this->parameterRequirements;
    }

    /**
     * @param array $parameterRequirements
     * @return $this
     */
    public function setParameterRequirements(array $parameterRequirements)
    {
        $this->parameterRequirements = $parameterRequirements;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameterDefaults(): array
    {
        return $this->parameterDefaults;
    }

    /**
     * @param array $parameterDefaults
     * @return $this
     */
    public function setParameterDefaults(array $parameterDefaults)
    {
        $this->parameterDefaults = $parameterDefaults;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return array
     */
    public function getSchemes(): array
    {
        return $this->schemes;
    }

    /**
     * @param array $schemes
     * @return $this
     */
    public function setSchemes(array $schemes)
    {
        $this->schemes = $schemes;
        return $this;
    }


    /**
     * @return null
     */
    public function getModuleClassNameOrObj()
    {
        return $this->moduleClassNameOrObj;
    }

    /**
     * @param $moduleClassNameOrObj
     * @return $this
     * @throws InvalidTypeException
     */
    public function setModuleClassNameOrObj($moduleClassNameOrObj)
    {
        if ($moduleClassNameOrObj != null && !in_array(IModule::class, class_implements($moduleClassNameOrObj))) {
            throw new InvalidTypeException('Module must be a type of IModule! Given ' . $moduleClassNameOrObj);
        }

        $this->moduleClassNameOrObj = $moduleClassNameOrObj;
        return $this;
    }





    /**
     * @return callable|null
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param callable|null $middleware
     * @return $this
     * @throws InvalidTypeException
     */
    public function setMiddleware($middleware)
    {
        if ($middleware != null && !CallableHelper::isCallable($middleware)) {
            throw new InvalidTypeException('Invalid callable parameter: ' . CallableHelper::getCallableName($middleware));
        }

        $this->middleware = $middleware;
        return $this;
    }
}