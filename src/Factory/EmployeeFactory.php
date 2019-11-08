<?php

namespace Console\Factory;

use Console\Employee;
use Console\Interfaces\EmployeeInterface;

class EmployeeFactory
{
    /**
     * @param array $data
     * @return EmployeeInterface
     */
    public function createEmployee(array $data): EmployeeInterface
    {
        $employee = new Employee();
        $employee->name = $data[0];
        $employee->birthdate = $data[1];

        return $employee;
    }
}