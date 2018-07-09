<?php declare(strict_types = 1);
/**
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier.
 */

/* Route sur le jour férié */
$app->group('/jour_ferie', function () {
    /* Collection */
    $this->get('', 'controller:get')->setName('getJourFerieListe');
});
