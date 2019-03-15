<?php

defined('_PHP_CONGES') or die('Restricted access');

$titre = "Clôture d'exercice globale";
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$DateReliquats = $config->getDateLimiteReliquats();
$isReliquatsAutorise = $config->isReliquatsAutorise();

$datePickerOpts = [
    'format' => "yyyy",
    'viewMode' => "years",
    'minViewMode' => "years",
];

if (1 == getpost_variable('cloture_globale', 0)) {
    $error = '';
    $anneeFinReliquats = intval(getpost_variable('annee', 0));
    $feries = getpost_variable('feries', 0);
    $typeConges = (\App\ProtoControllers\Conge::getTypesAbsences($sql, "conges") 
                + \App\ProtoControllers\Conge::getTypesAbsences($sql, "conges_exceptionnels"));
    $employes = \App\ProtoControllers\Utilisateur::getDonneesTousUtilisateurs($config);

    if (0 != count($employes)) {
        if (\App\ProtoControllers\HautResponsable\clotureExercice::traitementClotureEmploye($employes, $typeConges, $error, $sql, $config)) {
            $ExerciceResult = \App\ProtoControllers\HautResponsable\clotureExercice::updateNumExerciceGlobal($error, $sql);
            if($isReliquatsAutorise && $ExerciceResult) {
                \App\ProtoControllers\HautResponsable\clotureExercice::updateDateLimiteReliquats($anneeFinReliquats, $error, $sql, $config);
            }
        }
        if(1 == $feries) {
            \App\ProtoControllers\HautResponsable\clotureExercice::setJoursFeriesFrance();
        }
    }
}

require_once VIEW_PATH . 'ClotureExercice/ClotureGlobale.php';
