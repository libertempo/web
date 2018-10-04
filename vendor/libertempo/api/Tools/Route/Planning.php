<?php declare(strict_types = 1);

use LibertAPI\Tools\Controllers\PlanningController;
use LibertAPI\Tools\Controllers\PlanningCreneauController;

/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au singulier
 */

/* Routes sur le planning et associés */
$app->group('/planning', function () {
    $this->group('/{planningId:[0-9]+}', function () {
        /* Detail */
        $this->get('', [PlanningController::class, 'get'])->setName('getPlanningDetail');
        $this->put('', [PlanningController::class, 'put'])->setName('putPlanningDetail');
        $this->delete('', [PlanningController::class, 'delete'])->setName('deletePlanningDetail');

        /* Dependances de plannings */
        $this->group('/creneau', function () {
            /* Detail creneaux */
            $this->get('/{creneauId:[0-9]+}', [PlanningCreneauController::class, 'get'])->setName('getPlanningCreneauDetail');
            $this->put('/{creneauId:[0-9]+}', [PlanningCreneauController::class, 'put'])->setName('putPlanningCreneauDetail');
            //$this->delete('/{creneauId:[0-9]+}', $creneauNS . ':delete')->setName('deletePlanningCreneauDetail');

            /* Collection creneaux */
            $this->get('', [PlanningCreneauController::class, 'get'])->setName('getPlanningCreneauListe');
            $this->post('', [PlanningCreneauController::class, 'post'])->setName('postPlanningCreneauListe');
        });
    });

    /* Collection */
    $this->get('', [PlanningController::class, 'get'])->setName('getPlanningListe');
    $this->post('', [PlanningController::class, 'post'])->setName('postPlanningListe');
});
