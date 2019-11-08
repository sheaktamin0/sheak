<?php

namespace Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Console\Interfaces\EmployeeCollectionInterface;
use Console\Interfaces\ReaderInterface;

class CakeCommand extends Command
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var array
     */
    protected $birthdates = [];

    /**
     * CakeCommand constructor.
     * @param ReaderInterface $reader
     */
    public function __construct(ReaderInterface $reader)
    {
        parent::__construct();
        $this->reader = $reader;
    }

    /**
     * Configure the command
     */
    public function configure()
    {
        $this->setName('cake')
             ->setDescription('Give birthday cakes to employees.')
             ->setHelp('This command makes you give birthday cakes to employees')
             ->addArgument('file', InputArgument::REQUIRED, 'CSV File');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Read the csv file
        $data = $this->reader->read('storage/inputs/' . $input->getArgument('file'));

        // Find the cake taker employees
        $this->findCakeTakers($data);

        // Create the final csv file in storage/outputs folder
        if (!$this->makeCsv()) {
            return $output->writeln('An error has occured.');
        }

        return $output->writeln('cakes.csv has been created successfully - storage/outputs/cakes.csv');
    }

    /**
     * Make CSV file
     *
     * @return bool
     */
    private function makeCsv(): bool
    {
        $response = [];

        foreach ($this->birthdates as $date => $employees) {
            $big = 0;
            $small = 0;
            $people = [];
            foreach ($employees as $employee) {
                if ($employee['size'] == 'big') {
                    $big++;
                } else if ($employee['size'] == 'small') {
                    $small++;
                }
                $people[] = $employee['employee']->name;
            }

            $response[] = [$date, $small, $big, implode('-', $people)];
        }

        if (($fp = fopen('storage/outputs/cakes.csv', 'wb')) !== false) {
            foreach ( $response as $line ) {
                fputcsv($fp, $line, ',');
            }
            fclose($fp);

            return true;
        }

        return false;
    }

    /**
     * Find cake takers
     *
     * @param EmployeeCollectionInterface $collection
     */
    private function findCakeTakers(EmployeeCollectionInterface $collection): void
    {
        // Get all employees as a collection
        $iterator   = $collection->all();

        // Start loop for each employee
        while ($iterator->valid()) {
            // Get current employee from the collection
            $current = $iterator->current();

            // Get a day after the employee's birthdate
            $date    = date('Y-m-d', strtotime(date('Y') . '-' . date('m-d', strtotime($current->birthdate)) . '+1 day'));

            // Find cake day for the employee
            $cakeDay = $this->findCakeDay($date);

            // Add cake date to the birthdate array
            if ($this->isExist($cakeDay)) {
                $this->birthdates[$cakeDay][0]['size'] = 'big';
                $this->birthdates[$cakeDay][] = ['size' => 'big', 'employee' => $current];
            } else if ($this->isAfterCake($cakeDay)) {
                $cakeDayNew = $this->findCakeDay($this->addDay($cakeDay, 1));
                $this->birthdates[$cakeDayNew][] = ['size' => 'small', 'employee' => $current];
            } else if ($this->isSequential($cakeDay)) {
                $cakeDayForTwo = $this->findCakeDay($this->addDay($cakeDay, 1));
                $this->birthdates[$cakeDayForTwo][] = ['size' => 'big', 'employee' => $this->birthdates[$this->subDay($cakeDay, 1)][0]['employee']];
                unset($this->birthdates[$this->subDay($cakeDay, 1)]);
                $this->birthdates[$cakeDayForTwo][] = ['size' => 'big', 'employee' => $current];
            } else {
                $this->birthdates[$cakeDay][] = ['size' => 'small', 'employee' => $current];
            }

            // Go to the next employee
            $iterator->next();
        }
    }

    /**
     * Check whether the cake day exists
     *
     * @param string $date
     * @return bool
     */
    private function isExist(string $date): bool
    {
        return isset($this->birthdates[$date]) && count($this->birthdates[$date]) == 1;
    }

    /**
     * Check whether the cake day is sequential
     *
     * @param string $date
     * @return bool
     */
    private function isSequential(string $date): bool
    {
        return isset($this->birthdates[$this->subDay($date, 1)]) &&
               count($this->birthdates[$this->subDay($date, 1)]) == 1;
    }

    /**
     * Check whether the cake day is one day after from another cake day
     *
     * @param string $date
     * @return bool
     */
    private function isAfterCake(string $date): bool
    {
        return isset($this->birthdates[$this->subDay($date, 1)]) &&
               count($this->birthdates[$this->subDay($date, 1)]) > 1;
    }

    /**
     * Find cake day
     *
     * @param string $date
     * @return false|string
     */
    private function findCakeDay(string $date)
    {
        // if the date is an off date, get a day after it.
        // If it is an off date too call this method recursively
        if ($this->isOffDay($date)) {
            $date = $this->addDay($date, 1);
            return $this->findCakeDay($date);
        }

        return $date;
    }

    /**
     * Check if the date is an off day
     *
     * @param string $date
     * @return bool
     */
    private function isOffDay(string $date): bool
    {
        // Off days other than weekends
        $offDays = ['10-29', '11-24', '01-01'];

        // Check whether the date is an off date
        if (in_array(date('m-d', strtotime($date)), $offDays)) {
            return true;
        }

        // Check whether the date is a weekend
        return (date('N', strtotime($date)) >= 6);
    }

    /**
     * Add the given day number to the given date
     *
     * @param string $date
     * @param int $number
     * @return false|string
     */
    private function addDay(string $date, int $number): string
    {
        return date('Y-m-d', strtotime($date . '+' . $number . ' day'));
    }

    /**
     * Substract the given day number from the given date
     *
     * @param string $date
     * @param int $number
     * @return false|string
     */
    private function subDay(string $date, int $number): string
    {
        return date('Y-m-d', strtotime($date . '-' . $number . ' day'));
    }
}