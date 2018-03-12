<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 */

/* Routes sur l'utilisateur et associés */
$app->group('/utilisateur', function () {
    $this->group('/{utilisateurId:[0-9]+}', function () {
        /* Detail */
        $this->get('', 'controller:get')->setName('getUtilisateurDetail');
    });

    /* Collection */
    $this->get('', 'controller:get')->setName('getUtilisateurListe');
});
