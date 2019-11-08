<?php

namespace Console;

use Console\Factory\EmployeeFactory;
use Console\Interfaces\ReaderInterface;
use Console\Interfaces\EmployeeCollectionInterface;

class Reader implements ReaderInterface
{
    /**
     * @var EmployeeCollectionInterface
     */
    protected $collection;

    /**
     * @var EmployeeFactory
     */
    protected $factory;

    /**
     * Reader constructor.
     *
     * @param EmployeeCollectionInterface $collection
     * @param EmployeeFactory $factory
     */
    public function __construct(EmployeeCollectionInterface $collection, EmployeeFactory $factory)
    {
        $this->collection = $collection;
        $this->factory    = $factory;
    }

    /**
     * @param string $file
     * @return EmployeeCollectionInterface
     * @throws \Exception
     */
    public function read(string $file): EmployeeCollectionInterface
    {
        // Check if the file exists
        if (!file_exists($file)) {
            throw new \Exception('File not found - ' . $file);
        }

        // Open the file for reading
        if (($h = fopen($file, "r")) !== false)
        {
            // Convert each line into the local $data variable
            while (($data = fgetcsv($h, 1000, ",")) !== false)
            {
                $employee = $this->factory->createEmployee($data);
                $this->collection->add($employee);
            }

            // Close the file
            fclose($h);
        }

        return $this->collection;
    }
}