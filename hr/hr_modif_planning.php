<?php

defined('_PHP_CONGES') or die('Restricted access');
$planningId = (int) getpost_variable('id');
if (0 >= $planningId || !\App\ProtoControllers\HautResponsable\Planning::isVisible($planningId)) {
    redirect(ROOT_PATH . 'deconnexion.php');
}

$message   = '';
$errorsLst = [];
$notice    = '';
$valueName = '';
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!empty($_POST)) {
    if (0 < (int) \App\ProtoControllers\HautResponsable\Planning::postPlanning($_POST, $errorsLst, $notice)) {
        log_action(0, '', '', 'Édition du planning ' . $_POST['name']);
        redirect(ROOT_PATH . 'hr/hr_index.php?onglet=liste_planning', false);
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
        $valueName = $_POST['name'];
    }
} elseif (NIL_INT !== $planningId) {
    $injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton(),$config);
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $planning = $api->get('plannings/' .  $planningId, $_SESSION['token'])->data;
    $valueName = $planning->name;
}

/* Recupération des créneaux (postés ou existants) pour le JS */
$jours = [
    // ISO-8601
    1 => _('Lundi'),
    2 => _('Mardi'),
    3 => _('Mercredi'),
    4 => _('Jeudi'),
    5 => _('Vendredi'),
];
if ($config->isSamediOuvrable()) {
    $jours[6] = _('Samedi');
}
if ($config->isDimancheOuvrable()) {
    $jours[7] = _('Dimanche');
}

$creneauxGroupesCommuns = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE);
$creneauxGroupesImpairs = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE);
$creneauxGroupesPairs = \App\ProtoControllers\HautResponsable\Planning\Creneau::getCreneauxGroupes($_POST, $planningId, \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE);


$idToggleSemaine = uniqid();
$idSemaineCommune = uniqid();
$idSemaineImpaire = uniqid();
$idSemainePaire = uniqid();
$isSemaineReadOnly = \App\ProtoControllers\HautResponsable\Planning::hasEmployeAvecSorties($planningId);

$optionsSemaine = [
    'typePeriodeMatin'      => \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN,
    'typeHeureDebut'        => \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT,
    'typeHeureFin'          => \App\Models\Planning\Creneau::TYPE_HEURE_FIN,
    'helperId'              => uniqid(),
    'nilInt'                => NIL_INT,
    'erreurFormatHeure'     => _('Format_heure_incorrect'),
    'erreurOptionManquante' => _('Option_manquante'),
];

$optionsSemaineCommune = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'ajoutBoutonId' => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE,
    'dureeHebdoId'          => uniqid(),
];
$optionsSemaineImpaire = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'ajoutBoutonId' => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE,
    'dureeHebdoId'          => uniqid(),
];
$optionsSemainePaire = $optionsSemaine + [
    'selectJourId'          => uniqid(),
    'ajoutBoutonId' => uniqid(),
    'tableId'               => uniqid(),
    'debutId'               => uniqid(),
    'finId'                 => uniqid(),
    'typeSemaine'           => \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE,
    'dureeHebdoId'          => uniqid(),
];

$typeSemaine = [
    \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE => $idSemaineCommune,
    \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE => $idSemaineImpaire,
    \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE   => $idSemainePaire,
];
$text = [
    'common'    => _('Semaines_identiques'),
    'notCommon' => _('Semaines_differenciees'),
];
$utilisateursAssocies = \App\ProtoControllers\HautResponsable\Planning::getListeUtilisateursAssocies($planningId);
$optionsGroupes = \App\ProtoControllers\Groupe::getOptions();
$associations = array_map(function ($groupe) {
        return $groupe['utilisateurs'];
    },
    $optionsGroupes
);


$titre = (NIL_INT !== $planningId)
    ? _('hr_modif_planning_titre')
    : _('hr_ajout_planning_titre');

require_once VIEW_PATH . 'Planning/Formulaire/HautResponsable_Edit.php';
