<?php
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
    $urlCalendrier = ROOT_PATH . 'calendrier';
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
$isICalActive = $config->isIcalActive();

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

// Récupération des responsables pour les afficher avant les membres des groupes concernés.
$responsablesATrouver = \App\ProtoControllers\Groupe\Responsable::getListResponsableByGroupeIds($groupesAVoir);
$utilisateursATrouver = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesAVoir);

// Merge obligatoire pour que les responsables soient
// pris en compte lors du "fetchEvenements" et affichés par le fichier "Jour.php".
$utilisateursATrouver = array_merge($responsablesATrouver, $utilisateursATrouver);

$tousEmployes = \App\ProtoControllers\Utilisateur::getDonneesTousUtilisateurs($config);
$employes = array_filter($tousEmployes, function ($employe) use ($utilisateursATrouver) {
    return 'Y' == $employe['u_is_active'] && in_array($employe['u_login'], $utilisateursATrouver);
});

$employesATrouver = [];
foreach ($employes as $employe) {
    $employesATrouver[$employe['u_login']] = [
        'nom' => \App\ProtoControllers\Utilisateur::getNomComplet($employe['u_prenom'], $employe['u_nom'], true),
        'isResponsable' => \App\ProtoControllers\Utilisateur::isResponsable($employe['u_login']),
    ];
}

$responsablesPremier = function (array $a, array $b) {
    if ($a['isResponsable'] && !$b['isResponsable']) {
        return -1;
    } elseif (!$a['isResponsable'] && $b['isResponsable']) {
        return 1;
    }
    return strcmp($a['nom'], $b['nom']);
};

uasort($employesATrouver, $responsablesPremier);

// Cette variable est utilisée par le fichier "Mois.php" pour ajouter
// une séparation visuelle entre les responsables et les autres utilisateurs.
$indexSeparator = count($responsablesATrouver);

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
