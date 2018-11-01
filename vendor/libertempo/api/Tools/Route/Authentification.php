<?php declare(strict_types = 1);

use LibertAPI\Tools\Controllers\AuthentificationController as Auth;

/*
 * Doit être importé après la création de $app. Ne créé rien.
 * On décrit à la main le callable pour passer la configuration. Peut être pas idéal, mais ça fait le job.
 * On verra s'il y a besoin d'une solution plus robuste
 */
$app->get('/authentification', function () {
    return $this->get(Auth::class)->get(
        $this->get('request'),
        $this->get('response'),
        ['configurationFileData' => $this->get('configurationFileData')]
    );
})->setName('authentification');
