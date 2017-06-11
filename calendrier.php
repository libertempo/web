<?php
define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
include_once ROOT_PATH .'fonctions_conges.php';

if(substr($session, 0, 9)!="phpconges") {
    session_start();
    $_SESSION['config']=init_config_tab();
    if(empty($_SESSION['userlogin'])) {
        redirect(ROOT_PATH . 'index.php');
    }
} else {
    include_once INCLUDE_PATH . 'session.php';
}

/**
 * @return bool
 */
function canSessionVoirEvenementEnTransit(array $donneesUtilisateur)
{
    return (isset($donneesUtilisateur['is_resp']) && 'Y' === $donneesUtilisateur['is_resp'])
        || (isset($donneesUtilisateur['is_rh']) && 'Y' === $donneesUtilisateur['is_rh'])
        || (isset($donneesUtilisateur['is_admin']) && 'Y' === $donneesUtilisateur['is_admin']);
}

function getUrlMois(\DateTimeInterface $date, $session, $idGroupe)
{
    $urlCalendrier = ROOT_PATH . 'calendrier.php';
    $queryBase = [
        'session' => $session,
        'groupe' => $idGroupe,
    ];

    return $urlCalendrier . '?' . http_build_query($queryBase + ['mois' => $date->format('Y-m')]);
}

function getClassesJour(\App\Libraries\Calendrier\Evenements $evenements, $nom, $jour, \DateTimeInterface $moisDemande)
{
    $moisJour = date('m', strtotime($jour));
    if ($moisDemande->format('m') !== $moisJour) {
        return 'horsMois';
    }

    return implode(' ', $evenements->getEvenementsDate($nom, $jour));
}

function getTitleJour(\App\Libraries\Calendrier\Evenements $evenements, $nom, $jour)
{
    $title = implode('<br>*&nbsp;', $evenements->getTitleDate($nom, $jour));
    if (!empty($title)) {
        return '*&nbsp;' . $title;
    }
    return '';
}

$calendar = new \CalendR\Calendar();
$jourDemande = null;
$moisDemande = null;

if (!empty($_GET['jour']) && false !== strtotime($_GET['jour'])) {
    $jourDemande = new \DateTimeImmutable($_GET['jour']);
} elseif (!empty($_GET['mois']) && false !== strtotime($_GET['mois'] . '-01')) {
    $moisDemande = new \DateTimeImmutable($_GET['mois'] . '-01');
} else {
    $moisDemande = new \DateTimeImmutable(date('Y-m') . '-01');
}
$idGroupe = !empty($_GET['groupe'])
    ? (int) $_GET['groupe']
    : NIL_INT;

$injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton());
$evenements = new \App\Libraries\Calendrier\Evenements($injectableCreator);
$groupesVisiblesUserCourant = \App\ProtoControllers\Utilisateur::getListeGroupesVisibles($_SESSION['userlogin']);
$utilisateursATrouver = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesVisiblesUserCourant);

header_menu('', 'Libertempo : '._('calendrier_titre'));

require_once VIEW_PATH . 'Calendrier.php';

bottom();
