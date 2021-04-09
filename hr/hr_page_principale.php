<?php

defined('_PHP_CONGES') or die('Restricted access');
$message = '';

if (getpost_variable('notice') !== "") {
    $notice = getpost_variable('notice');
    switch ($notice) {
        case 'inserted':
            $message = _('Utilisateur ajouté');
            break;
        case 'modified':
            $message = _('Utilisateur modifié');
            break;
        case 'deleted':
            $message = _('Utilisateur supprimé');
            break;
        case 'credit-added':
            $message = _('Compte(s) crédités(s)');
        default:
            break;
    }
}

$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);

$isHeuresAutorises = $config->isHeuresAutorise();
$return = '';

$titre = _('admin_onglet_gestion_user');

$typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
$typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');

$infoUsers = \App\ProtoControllers\Utilisateur::getDonneesTousUtilisateurs($config);
asort($infoUsers);
uasort($infoUsers, 'sortParActif');
foreach ($infoUsers as $login => $infosUser) {
    $rights = [];
    if ($infosUser['u_is_active'] === 'N') {
        $rights[] = 'inactif';
    }
    if ($infosUser['u_is_admin'] === 'Y') {
        $rights[] = 'administrateur';
    }
    if ($infosUser['u_is_resp'] === 'Y') {
        $rights[] = 'responsable';
    }
    if ($infosUser['u_is_hr'] === 'Y') {
        $rights[] = 'RH';
    }

    $infoUsers[$login]['rights'] = $rights;
    $infoUsers[$login]['responsables'] = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($login);
    $infoUsers[$login]['soldes'] = \App\ProtoControllers\Utilisateur::getSoldesEmploye($sql, $login);
}

/**
 * Tri les tableaux, d'abord par activité, puis par ordre lexicographique
 *
 * @return int {-1, 0, 1}
 * @TODEL
 */
function sortParActif(array $a, array $b)
{
    if ($a['u_is_active'] === 'Y' && $b['u_is_active'] === 'N') {
        return -1; // $a est avant $b
    } elseif ($a['u_is_active'] === 'N' && $b['u_is_active'] === 'Y') {
        return 1; // $a est derrière $b
    }

    return strnatcmp($a['u_nom'], $b['u_nom']);
}

require_once VIEW_PATH . 'HautResponsable/Employe/Liste.php';
