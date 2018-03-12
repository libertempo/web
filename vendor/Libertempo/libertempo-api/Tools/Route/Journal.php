<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 *
 * La particularité du journal est qu'il n'est qu'en lecture /destruction seule.
 * C'est le rôle de l'API d'insérer les événements quand ils arrivent, idem pour la modification.
 */

/* Routes sur le journal */
$app->group('/journal', function () {
    /* Collection */
    $this->get('', 'controller:get')->setName('getJournalListe');
});
