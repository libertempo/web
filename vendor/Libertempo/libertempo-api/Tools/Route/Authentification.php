<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 */
$app->get('/authentification', '\LibertAPI\Authentification\AuthentificationController:get')->setName('authentification');
