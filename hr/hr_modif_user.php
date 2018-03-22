<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );
$login = htmlentities($_GET['login'], ENT_QUOTES | ENT_HTML401);
echo \App\ProtoControllers\HautResponsable\Utilisateur::getFormUser($login);
