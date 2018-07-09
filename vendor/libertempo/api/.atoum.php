<?php declare(strict_types = 1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS);
require_once ROOT_PATH . 'Vendor' . DS . 'autoload.php';

use mageekguy\atoum\reports;
use mageekguy\atoum\reports\coverage;
use mageekguy\atoum\writers;

$runner->addTestsFromDirectory(__DIR__ . '/Tests/Units');
$script->bootstrapFile(__DIR__ . '/.bootstrap.atoum.php');

$script->addDefaultReport();

$clover = new \mageekguy\atoum\reports\asynchronous\clover();
$writer = new \mageekguy\atoum\writers\file('./clover.xml');
$clover->addWriter($writer);
$runner->addReport($clover);
