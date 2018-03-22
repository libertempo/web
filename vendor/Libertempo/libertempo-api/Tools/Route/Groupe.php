<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 */

/* Routes sur le groupe */
$app->group('/groupe', function () {
    $this->group('/{groupeId:[0-9]+}', function () {
        /* Detail */
        $this->get('', 'controller:get')->setName('getGroupeDetail');
    });

    /* Collection */
    $this->get('', 'controller:get')->setName('getGroupeListe');
});
