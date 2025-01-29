<?php

namespace Foamycastle\Util\Validator;

use Exception;

abstract class Validator
{
    protected string $name;

    /**
     * Used for creating tests from closures
     * @var callable $callableValidator
     */
    protected $callableValidator;
    /**
     * A list of registered validation objects.
     * @var array<string,Validator>
     */
    private static array $registeredValidators = [];

    /**
     * Indicate if a given name has a validator object registered to it.
     * @param string $name
     * @return bool
     */
    protected static function isRegistered(string $name): bool
    {
        return isset(self::$registeredValidators[$name]);
    }

    /**
     * Register a Validator class
     * @param string $name the name by which the Validator will be referenced
     * @param callable $validator the invokable class or callable function that will perform the test
     * @return void
     * @throws Exception If the $name argument has already been registered
     */
    protected static function registerValidator(string $name, callable $validator): void
    {
        if(self::isRegistered($name)) {
            throw new Exception("Validator '$name' has already been registered");
        }
        self::$registeredValidators[$name] = $validator;
    }

    /**
     * Unregister a Validator object
     * @param string $name the name by which the Validator is referenced
     * @return void
     */
    protected static function unregisterValidator(string $name): void
    {
        if(!self::isRegistered($name)) {
            return;
        }
        unset(self::$registeredValidators[$name]);
    }

    /**
     * Allow Validator objects to be access by their registered names
     * @param string $name
     * @param array $arguments the data to be tested
     * @return bool TRUE if the validation passed
     * @throws Exception if the $name argument has not been registered
     */
    public static function __callStatic(string $name, array $arguments):bool
    {
        if(!self::isRegistered($name)) {
            throw new Exception("Validator '$name' has not been registered");
        }
        return self::$registeredValidators[$name](...$arguments);
    }

    /**
     * Create a validator from a lambda function or Closure
     * @param string $name
     * @param callable $callable
     * @return void
     * @throws Exception
     */
    public static function From(string $name,callable $callable):void
    {
        self::registerValidator($name,$callable);
    }

    /**
     * Add a classname to the list of Validator classes to be registered
     * @param string $className
     * @param string $name
     * @return void
     * @throws Exception
     */
    public static function Register(string $className, ...$args):void
    {
        $name=substr($className,strrpos($className,'\\')+1);
        if(!class_exists($className)) {
            throw new Exception("Validator '$name' does not exist");
        }
        self::$registeredValidators[$name]=new $className(...$args);
    }

    public static function Unregister(string $name):void
    {
        if(self::isRegistered($name)) {
            unset(self::$registeredValidators[$name]);
        }
    }

    public function __construct()
    {
        $this->name = substr(static::class, strrpos(static::class, '\\')+1);
    }
    public function __destruct()
    {
        self::unregisterValidator($this->name);
    }
    protected function hasCallable():bool
    {
        return isset($this->callableValidator) && is_callable($this->callableValidator);
    }

    /**
     * Validation can be invoked
     * @param mixed $dataToTest
     * @return bool
     */
    public function __invoke(mixed $dataToTest):bool
    {
        if($this->hasCallable()) {
            return call_user_func($this->callableValidator, $dataToTest);
        }
        return $this->validation($dataToTest);
    }
    abstract protected function validation(mixed $dataToTest): bool;
}