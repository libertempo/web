#!/usr/bin/php
<?php
define('ROOT_PATH', '');

require_once 'define.php';

$fileConnector = './cfg/dbconnect.php';

if (!file_exists($fileConnector)) {
    exit('Fichier de connexion introuvable' . "\n");
}

require_once $fileConnector;

$fileConnectorApi = API_PATH . 'configuration.json';
$dataConnectorApi = [
    'db' => [
        'serveur' => $mysql_serveur,
        'database' => $mysql_database,
        'utilisateur' => $mysql_user,
        'mot_de_passe' => $mysql_pass,
    ],
];

$fh = fopen($fileConnectorApi, 'r+');
if (false === fwrite($fh, json_encode($dataConnectorApi))) {
    exit('Écriture impossible.' . "\n");
}

echo 'Initialisation terminée.', "\n";
