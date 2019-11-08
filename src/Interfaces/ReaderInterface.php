<?php

namespace Console\Interfaces;

interface ReaderInterface
{
    public function read(string $file): EmployeeCollectionInterface;
}