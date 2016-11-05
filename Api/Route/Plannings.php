<?php
/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */

/* Routes sur le planning et associés */
$app->group('/plannings', function() {
    $this->group('/{planningId:[0-9]+}', function () {
        /* Detail */
        $this->get('', '\Api\App\Components\Planning\Controller:get')->setName('getPlanningDetail');
        //$this->put('', '\Api\App\Components\Planning\Controller:put')->setName('putPlanningDetail');
        //$this->delete('', '\Api\App\Components\Planning\Controller:delete')->setName('putPlanningDetail');

        /* Dependances de plannings */
        $this->group('/creneaux', function () {
            /* Detail creneaux */
            $this->get('/{creneauId:[0-9]+}', '\Api\App\Components\Planning\Creneau\Controller:get')->setName('getPlanningCreneauDetail');
            //$this->put('/{creneauId:[0-9]+}', '\Api\App\Components\Planning\Creneau\Controller:put')->setName('putPlanningCreneauDetail');
            //$this->delete('/{creneauId:[0-9]+}', '\Api\App\Components\Planning\Creneau\Controller:delete')->setName('deletePlanningCreneauDetail');

            /* Collection creneaux */
            $this->get('', '\Api\App\Components\Planning\Creneau\Controller:get')->setName('getPlanningCreneauListe');
            //$this->post('', '\Api\App\Components\Planning\Creneau\Controller:post')->setName('postPlanningCreneauListe');
        });
    });

    /* Collection */
    $this->get('', '\Api\App\Components\Planning\Controller:get')->setName('getPlanningListe');
    $this->post('', '\Api\App\Components\Planning\Controller:post')->setName('postPlanningListe');
});
