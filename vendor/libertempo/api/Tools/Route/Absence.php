<?php declare(strict_types = 1);

use LibertAPI\Tools\Controllers\AbsenceTypeController;

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
            $this->get('', [AbsenceTypeController::class, 'get'])->setName('getAbsenceTypeDetail');
            $this->put('', [AbsenceTypeController::class, 'put'])->setName('putAbsenceTypeDetail');
            $this->delete('', [AbsenceTypeController::class, 'delete'])->setName('deleteAbsenceTypeDetail');
        });
        /* Collection */
        $this->get('', [AbsenceTypeController::class, 'get'])->setName('getAbsenceTypeListe');
        $this->post('', [AbsenceTypeController::class, 'post'])->setName('postAbsenceTypeListe');
    });
});
