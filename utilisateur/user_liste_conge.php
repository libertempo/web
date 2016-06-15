<?php
defined('_PHP_CONGES') or die('Restricted access');
$conge = new \App\ProtoControllers\Conge();
echo $conge->getListe();
