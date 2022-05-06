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
    private $module = null;
    private $action;
    private $parameterRequirements = []; //An array of requirements for parameters (regexes)
    private $parameterDefaults = []; //An array of default parameter values
    private $priority = 0;
    private $schemes = []; //A required URI scheme or an array of restricted schemes

    public function __construct($name, $path, callable $action, $module = null, array $methods = ['GET', 'POST'], array $parameterDefaults = [], array $parameterRequirements = [], $conditions = null, array $schemes = [], int $priority = 0)
    {

        $this->name = $name;
        $this->path = $path;
        $this->setAction($action);
        $this->setModule($module);
        $this->methods = $methods;
        $this->parameterDefaults = $parameterDefaults;
        $this->parameterRequirements = $parameterRequirements;
        $this->conditions = $conditions;
        $this->schemes = $schemes;
        $this->priority = $priority;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
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
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     */
    public function setConditions(?string $conditions = null): string
    {
        $this->conditions = $conditions;
    }

    /**
     * @return callable
     */
    public function getAction(): callable
    {
        return $this->action;
    }

    /**
     * @param callable $action
     */
    public function setAction(callable $action): void
    {
        if (!is_array($action)) {
            throw new InvalidTypeException('Route action must be a class method! Given ' . CallableHelper::getCallableName($action));
        }
        $this->action = $action;
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
     */
    public function setParameterRequirements(array $parameterRequirements): void
    {
        $this->parameterRequirements = $parameterRequirements;
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
     */
    public function setParameterDefaults(array $parameterDefaults): void
    {
        $this->parameterDefaults = $parameterDefaults;
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
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
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
     */
    public function setSchemes(array $schemes): void
    {
        $this->schemes = $schemes;
    }

    /**
     * @return null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param null $module
     */
    public function setModule($module): void
    {
        if ($module != null && !($module instanceof IModule)) {
            throw new InvalidTypeException('Module must be a type of IModule! Given ' . $module);
        }

        $this->module = $module;
    }
}