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
     * A list of validation objects that have yet to be resolved
     * @var array<string,class-string>
     */
    private static array $unresolvedValidators = [];

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
     * Indicate that a given validator class has not been resolved
     * @param string $name
     * @return bool
     */
    protected static function isUnresolved(string $name): bool
    {
        return isset(self::$unresolvedValidators[$name]);
    }

    /**
     * Resolve the Validator class
     * @param string $name the name by which the Validator will be referenced
     * @return Validator
     * @throws \Exception
     */
    protected static function resolveValidator(string $name): Validator
    {

        if (!self::isUnresolved($name)) {
            throw new \Exception("Validator '$name' has not been added");
        }
        if(self::isRegistered($name)) {
            return self::$registeredValidators[$name];
        }
        $classString = self::$unresolvedValidators[$name];
        if(!class_exists($classString)) {
            throw new \Exception("Validator '$name' does not exist");
        }
        $validator = new $classString($name);
        if(!($validator instanceof Validator)) {
            throw new \Exception("Validator '$name' does not descend from Validator");
        }
        return $validator;
    }

    /**
     * Remove an Validator class from the unresolved list
     * @param string $name
     * @return void
     */
    private static function removeUnresolvedValidator(string $name): void
    {
        if(self::isUnresolved($name)) {
            unset(self::$unresolvedValidators[$name]);
        }
    }

    /**
     * Register a Validator class
     * @param string $name the name by which the Validator will be referenced
     * @param callable $validator the invokable class or callable function that will perform the test
     * @return void
     * @throws \Exception If the $name argument has already been registered
     */
    protected static function registerValidator(string $name, callable $validator): void
    {
        if(self::isRegistered($name)) {
            throw new \Exception("Validator '$name' has already been registered");
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
     * @throws \Exception if the $name argument has not been registered
     */
    public static function __callStatic(string $name, array $arguments):bool
    {
        if(self::isUnresolved($name)) {
            self::resolveValidator($name);
        }
        if(!self::isRegistered($name)) {
            throw new \Exception("Validator '$name' has not been registered");
        }
        return self::$registeredValidators[$name](...$arguments);
    }

    /**
     * Create a validator from a lambda function or Closure
     * @param string $name
     * @param callable $callable
     * @return void
     * @throws \Exception
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
     * @throws \Exception
     */
    public static function Register(string $className,string $name=''):void
    {
        $name=$name==''
            ? substr($className,strrpos($className,'\\')+1)
            : $name;
        if(!class_exists($className)) {
            throw new \Exception("Validator '$name' does not exist");
        }
        self::$unresolvedValidators[$name] = $className;
    }

    public function __construct(string $name='')
    {
        $this->name = $name==''
            ? substr(static::class, strrpos(static::class, '\\')+1)
            : $name;
        self::registerValidator($this->name,$this);
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