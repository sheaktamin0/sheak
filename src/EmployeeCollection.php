<?php

namespace Console;

use Console\Interfaces\EmployeeCollectionInterface;
use Console\Interfaces\EmployeeInterface;

class EmployeeCollection implements EmployeeCollectionInterface
{
    /**
     * @var array
     */
    protected $employees = [];

    /**
     * @param EmployeeInterface $employee
     */
    public function add(EmployeeInterface $employee): void
    {
        array_push($this->employees, $employee);
    }

    /**
     * @param int $index
     * @return EmployeeInterface
     */
    public function get(int $index): EmployeeInterface
    {
        return $this->employees[$index];
    }

    /**
     * @return \Iterator
     */
    public function all(): \Iterator
    {
        $employees = new \ArrayObject($this->employees);
        return $employees->getIterator();
    }
}