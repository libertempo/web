<?php

defined('_PHP_CONGES') or die('Restricted access');

$titre = "Clôture d'exercice globale";
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$DateReliquats = $config->getDateLimiteReliquats();
$isReliquatsAutorise = $config->isReliquatsAutorise();
$commitSuccess = false;

$datePickerOpts = [
    'format' => "yyyy",
    'viewMode' => "years",
    'minViewMode' => "years",
];

$commitCloture = intval(getpost_variable('cloture_globale', 0));
if (1 === $commitCloture && is_hr($_SESSION['userlogin'])) {
    $error = null;
    $anneeFinReliquats = intval(getpost_variable('annee', 0));
    $feries = getpost_variable('feries', 0);
    $typeConges = (\App\ProtoControllers\Conge::getTypesAbsences($sql, "conges")
                + \App\ProtoControllers\Conge::getTypesAbsences($sql, "conges_exceptionnels"));
    $employes = \App\ProtoControllers\Utilisateur::getDonneesTousUtilisateurs($config);

    if (0 !== count($employes)) {
        if (\App\ProtoControllers\HautResponsable\ClotureExercice::traitementClotureEmploye($employes, $typeConges, $error, $sql, $config)) {
            \App\ProtoControllers\HautResponsable\ClotureExercice::updateNumExerciceGlobal($sql);
            if ($isReliquatsAutorise) {
                \App\ProtoControllers\HautResponsable\ClotureExercice::updateDateLimiteReliquats($anneeFinReliquats, $error, $sql, $config);
            }
        }
        if (1 === $feries) {
            \App\ProtoControllers\HautResponsable\ClotureExercice::setJoursFeriesFrance();
        }
    }
    if (null === $error) {
        $commitSuccess = true;
    }
}

require_once VIEW_PATH . 'ClotureExercice/ClotureGlobale.php';
