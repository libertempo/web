<?php declare(strict_types = 1);
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 */

/* Routes sur une absence et associés */
$app->group('/absence', function () {
    /* Route sur un type d'absence */
    $this->group('/type', function () {
        /* Détail */
        $this->group('/{typeId:[0-9]+}', function () {
            $this->get('', 'controller:get')->setName('getAbsenceTypeDetail');
            $this->put('', 'controller:put')->setName('putAbsenceTypeDetail');
            $this->delete('', 'controller:delete')->setName('deleteAbsenceTypeDetail');
        });
        /* Collection */
        $this->get('', 'controller:get')->setName('getAbsenceTypeListe');
        $this->post('', 'controller:post')->setName('postAbsenceTypeListe');
    });
});
