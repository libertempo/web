<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 */
$app->get('/authentification', '\App\Components\Authentification\Controller:get')->setName('authentification');
