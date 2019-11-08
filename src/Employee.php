<?php

namespace Console;

use Console\Interfaces\EmployeeInterface;

class Employee implements EmployeeInterface
{
    public $name;

    public $birthdate;
}