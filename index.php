<?php

require_once './vendor/autoload.php';

use Symfony\Component\Console\Application;
use Console\Commands\CakeCommand;
use Console\Factory\EmployeeFactory;
use Console\EmployeeCollection;
use Console\Reader;

$app = new Application();

$employeeCollection = new EmployeeCollection();
$employeeFactory = new EmployeeFactory();

$reader = new Reader($employeeCollection, $employeeFactory);

$app->add(new CakeCommand($reader));

$app->run();
