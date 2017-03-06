<?php

//Config SMTP
defined( '_PHP_CONGES' ) or die( 'Restricted access' );


$config_SMTP_host = "";		//adresse serveur smtp
$config_SMTP_port = 25; 	//port smtp
$config_SMTP_sec  = "";		//ssl, tls ou vide
$config_SMTP_user = "";		//nom utilisateur (peut être vide)
$config_SMTP_pwd = "";		//mot de passe (peut être vide)

// uncomment this if you want receive mails when a SQL error is found.
//if (!defined( 'ERROR_MAIL_REPORT' ))
// define('ERROR_MAIL_REPORT',	'your@mail.adress');

