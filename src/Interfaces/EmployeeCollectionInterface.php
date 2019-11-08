<?php

namespace Console\Interfaces;

interface EmployeeCollectionInterface
{
    public function add(EmployeeInterface $employee): void;

    public function get(int $index): EmployeeInterface;

    public function all(): \Iterator;
}