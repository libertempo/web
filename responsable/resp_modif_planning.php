<?php

defined('_PHP_CONGES') or die('Restricted access');
$planningId = (int) getpost_variable('id');
if (0 >= $planningId || !\App\ProtoControllers\Responsable\Planning::isVisible($planningId)) {
    redirect(ROOT_PATH . 'deconnexion.php');
}

$message   = '';
$errorsLst = [];
if (!empty($_POST)) {
    if (0 < (int) \App\ProtoControllers\Responsable\Planning::putPlanning($planningId, $_POST, $errorsLst)) {
        log_action(0, '', '', 'Édition des associations du planning ' . $planningId);
        redirect(ROOT_PATH . 'responsable/resp_index.php?onglet=liste_planning', false);
    } else {
        if (!empty($errorsLst)) {
            $errors = '';
            foreach ($errorsLst as $key => $value) {
                if (is_array($value)) {
                    $value = implode(' / ', $value);
                }
                $errors .= '<li>' . $key . ' : ' . $value . '</li>';
            }
            $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
        }
    }
}

$injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton());
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);
$planning = $api->get('plannings/' .  $planningId, $_SESSION['token'])->data;
$configuration = new \App\Libraries\Configuration(\includes\SQL::singleton());
$jours = [
    // ISO-8601
    1 => _('Lundi'),
    2 => _('Mardi'),
    3 => _('Mercredi'),
    4 => _('Jeudi'),
    5 => _('Vendredi'),
];
if ($configuration->isSamediOuvrable()) {
    $jours[6] = _('Samedi');
}
if ($configuration->isDimancheOuvrable()) {
    $jours[7] = _('Dimanche');
}

$creneauxGroupesCommuns = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE);
$creneauxGroupesImpairs = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE);
$creneauxGroupesPairs = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE);

$idToggleSemaine = uniqid();
$linkId = uniqid();

$optionsSemaine = [
    'typePeriodeMatin'      => \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN,
    'typeHeureDebut'        => \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT,
    'typeHeureFin'          => \App\Models\Planning\Creneau::TYPE_HEURE_FIN,
    'helperId'              => uniqid(),
    'nilInt'                => NIL_INT,
    'ajoutBoutonId' => uniqid(),
    'erreurFormatHeure'     => _('Format_heure_incorrect'),
    'erreurOptionManquante' => _('Option_manquante'),
];

$optionsSemaineCommune = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE,
    'dureeHebdoId'          => uniqid(),
];
$optionsSemaineImpaire = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE,
    'dureeHebdoId'          => uniqid(),
];
$optionsSemainePaire = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE,
    'dureeHebdoId'          => uniqid(),
];

$idSemaineCommune = uniqid();
$idSemaineImpaire = uniqid();
$idSemainePaire = uniqid();

$typesSemaines = [
    \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE => $idSemaineCommune,
    \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE => $idSemaineImpaire,
    \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE   => $idSemainePaire,
];
$text = [
    'common'    => _('Semaines_identiques'),
    'notCommon' => _('Semaines_differenciees'),
];

$optionsGroupes = \App\ProtoControllers\Groupe::getOptions();
$associationsGroupe = array_map(function ($groupe) {
        return $groupe['utilisateurs'];
    },
    $optionsGroupes
);
$utilisateursAssocies = \App\ProtoControllers\Responsable\Planning::getListeUtilisateursAssocies($planningId);
$subalternes = \App\ProtoControllers\Responsable::getUsersRespDirect($_SESSION['userlogin']);
$utilisateursAssocies = array_filter(
    $utilisateursAssocies,
    function ($utilisateurs) use ($subalternes) {
        return in_array($utilisateurs['login'], $subalternes);
    }
);

require_once VIEW_PATH . 'Planning/Formulaire/Responsable_Edit.php';
