<?php
$runner->addTestsFromDirectory(__DIR__ . '/Tests/Units');
$script->bootstrapFile(__DIR__ . '/.bootstrap.atoum.php');

use mageekguy\atoum\reports;
use mageekguy\atoum\writers;

$runner->addTestsFromDirectory(__DIR__ . '/Tests/Units');
$script->bootstrapFile(__DIR__ . '/.bootstrap.atoum.php');

$script->addDefaultReport();

$clover = new reports\asynchronous\clover();
$writer = new writers\file('./clover.xml');
$clover->addWriter($writer);
$runner->addReport($clover);
