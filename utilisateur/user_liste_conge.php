<?php
defined('_PHP_CONGES') or die('Restricted access');
$conge = new \App\ProtoControllers\Employe\Conge();
echo $conge->getListe();
