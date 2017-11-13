<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */

/* Routes sur le planning et associés */
$app->group('/plannings', function () {
    $planningNS = '\LibertAPI\Planning\PlanningController';
    $this->group('/{planningId:[0-9]+}', function () use ($planningNS) {
        /* Detail */
        $this->get('', $planningNS . ':get')->setName('getPlanningDetail');
        $this->put('', $planningNS . ':put')->setName('putPlanningDetail');
        $this->delete('', $planningNS . ':delete')->setName('deletePlanningDetail');

        /* Dependances de plannings */
        $this->group('/creneaux', function () {
            $creneauNS = '\LibertAPI\Planning\Creneau\CreneauController';
            /* Detail creneaux */
            $this->get('/{creneauId:[0-9]+}', $creneauNS . ':get')->setName('getPlanningCreneauDetail');
            $this->put('/{creneauId:[0-9]+}', $creneauNS . ':put')->setName('putPlanningCreneauDetail');
            //$this->delete('/{creneauId:[0-9]+}', $creneauNS . ':delete')->setName('deletePlanningCreneauDetail');

            /* Collection creneaux */
            $this->get('', $creneauNS . ':get')->setName('getPlanningCreneauListe');
            $this->post('', $creneauNS . ':post')->setName('postPlanningCreneauListe');
        });
    });

    /* Collection */
    $this->get('', $planningNS .  ':get')->setName('getPlanningListe');
    $this->post('', $planningNS .  ':post')->setName('postPlanningListe');
});
