<?php declare(strict_types = 1);

use LibertAPI\Tools\Controllers\UtilisateurController;

/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 */

/* Routes sur l'utilisateur et associés */
$app->group('/utilisateur', function () {
    $this->group('/{utilisateurId:[0-9]+}', function () {
        /* Detail */
        $this->get('', [UtilisateurController::class, 'get'])->setName('getUtilisateurDetail');
    });

    /* Collection */
    $this->get('', [UtilisateurController::class, 'get'])->setName('getUtilisateurListe');
});
