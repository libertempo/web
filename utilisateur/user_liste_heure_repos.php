<?php

defined('_PHP_CONGES') or die('Restricted access');
$repos = new \App\ProtoControllers\Employe\Heure\Repos();
echo $repos->getListe();
