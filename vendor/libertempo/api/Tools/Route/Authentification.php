<?php declare(strict_types = 1);

use LibertAPI\Tools\Controllers\AuthentificationController as Auth;

/*
 * Doit être importé après la création de $app. Ne créé rien.
 */
$app->get('/authentification', [Auth::class, 'get'])->setName('authentification');
