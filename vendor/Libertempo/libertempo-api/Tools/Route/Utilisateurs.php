<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */

/* Routes sur l'utilisateur et associés */
$app->group('/utilisateurs', function () {
    $utilisateurNS = '\App\Components\Utilisateur\Controller';
    $this->group('/{utilisateurId:[0-9]+}', function () use ($utilisateurNS) {
        /* Detail */
        $this->get('', $utilisateurNS . ':get')->setName('getUtilisateurDetail');
    });

    /* Collection */
    $this->get('', $utilisateurNS .  ':get')->setName('getUtilisateurListe');
});
