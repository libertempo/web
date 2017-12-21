<?php
define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH . 'session.php';

/**
 * @return bool
 */
function canSessionVoirEvenementEnTransit(array $donneesUtilisateur)
{
    return (isset($donneesUtilisateur['is_resp']) && 'Y' === $donneesUtilisateur['is_resp'])
        || (isset($donneesUtilisateur['is_rh']) && 'Y' === $donneesUtilisateur['is_rh'])
        || (isset($donneesUtilisateur['is_admin']) && 'Y' === $donneesUtilisateur['is_admin']);
}

function getUrlMois(\DateTimeInterface $date, $idGroupe)
{
    $urlCalendrier = ROOT_PATH . 'calendrier.php';
    $queryBase = [
        'groupe' => $idGroupe,
    ];

    return $urlCalendrier . '?' . http_build_query($queryBase + ['mois' => $date->format('Y-m')]);
}

function getClassesJour(\App\Libraries\Calendrier\Evenements $evenements, $nom, $jour, \DateTimeInterface $moisDemande)
{
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
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);

$injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
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

$evenements = new \App\Libraries\Calendrier\Evenements($injectableCreator);
$groupesAVoir = $groupesVisiblesUtilisateur = \App\ProtoControllers\Utilisateur::getListeGroupesVisibles($_SESSION['userlogin']);
$idGroupe = NIL_INT;
if (!empty($_GET['groupe']) && NIL_INT != $_GET['groupe']) {
    $idGroupe = (int) $_GET['groupe'];
    $groupesAVoir = array_intersect([$idGroupe], $groupesAVoir);
}
$utilisateursATrouver = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesAVoir);

$employes = \App\ProtoControllers\Utilisateur::getDonneesUtilisateurs($utilisateursATrouver);
foreach ($employes as $employe) {
    $employesATrouver[$employe['u_login']] = \App\ProtoControllers\Utilisateur::getNomComplet($employe['u_prenom'], $employe['u_nom'], true);
}

header_menu('', 'Libertempo : '._('calendrier_titre'));

if ($jourDemande instanceof \DateTimeInterface) {
    $evenements->fetchEvenements(
        $jourDemande,
        $jourDemande->modify('+1 day'),
        $utilisateursATrouver,
        canSessionVoirEvenementEnTransit($_SESSION),
        $config->isHeuresAutorise()
    );
    require_once VIEW_PATH . 'Calendrier/Jour.php';
} else {
    $evenements->fetchEvenements(
        $moisDemande,
        $moisDemande->modify('+1 month'),
        $utilisateursATrouver,
        canSessionVoirEvenementEnTransit($_SESSION),
        $config->isHeuresAutorise()
    );
    require_once VIEW_PATH . 'Calendrier/Mois.php';
}

bottom();
