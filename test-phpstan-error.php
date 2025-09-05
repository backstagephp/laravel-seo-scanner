<?php

// This file is intentionally created to test the PHPStan auto-fix solution
// It contains common PHPStan issues that the auto-fix should handle

class TestClass
{
    private $property; // Missing type hint

    public $anotherProperty; // Missing type hint

    public function testMethod($param) // Missing return type and parameter type
    {
        $array = []; // Generic array type

        return $array;
    }

    public function anotherMethod() // Missing return type
    {
        // Some logic here
        echo 'test';
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($value)
    {
        $this->property = $value;
    }
}
