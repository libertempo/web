<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

//var pour resp_ajout_conges_all.php
$ajout_conges            = getpost_variable('ajout_conges');
$tab_champ_saisie        = getpost_variable('tab_champ_saisie');
$tab_commentaire_saisie        = getpost_variable('tab_commentaire_saisie');
//$tab_champ_saisie_rtt    = getpost_variable('tab_champ_saisie_rtt') ;
$ajout_global            = getpost_variable('ajout_global');
$ajout_groupe            = getpost_variable('ajout_groupe');
$choix_groupe            = getpost_variable('choix_groupe');
$tab_new_nb_conges_all   = getpost_variable('tab_new_nb_conges_all');
$tab_calcul_proportionnel = getpost_variable('tab_calcul_proportionnel');
$tab_new_comment_all     = getpost_variable('tab_new_comment_all');
$return = '';

if($ajout_conges=="TRUE") {
    \responsable\Fonctions::ajout_conges($tab_champ_saisie, $tab_commentaire_saisie);
} elseif($ajout_global=="TRUE") {
    \responsable\Fonctions::ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
} elseif($ajout_groupe=="TRUE") {
    \responsable\Fonctions::ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
}

$filterActif = function (array $subalternes) {
    return array_filter($subalternes, function ($employe) {
        return 'Y' == $employe['is_active'];
    });
};

$subalternesResponsable = recup_infos_all_users_du_resp($_SESSION['userlogin']);
$subalternesActifsResponsable = $filterActif($subalternesResponsable);
asort($subalternesActifsResponsable);

$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
$subalternesGrandResponsable = ($config->isDoubleValidationActive()) && ($config->canGrandResponsableAjouteConge())
    ? recup_infos_all_users_du_grand_resp($_SESSION['userlogin'])
    : [];
$subalternesActifsGrandResponsable = $filterActif($subalternesGrandResponsable);
asort($subalternesActifsGrandResponsable);

$hasSubalternes = (bool) (count($subalternesActifsResponsable) + count($subalternesActifsGrandResponsable));

$list_group_resp=get_list_groupes_du_resp($_SESSION['userlogin']);
if( ($config->isDoubleValidationActive()) && ($config->canGrandResponsableAjouteConge()) ) {
    $list_group_grd_resp=get_list_groupes_du_grand_resp($_SESSION['userlogin']);
} else {
    $list_group_grd_resp="";
}

$list_group="";
if($list_group_resp!="") {
    $list_group = $list_group_resp;
    if($list_group_grd_resp!="") {
        $list_group = $list_group.",".$list_group_grd_resp;
    }
} else {
    if($list_group_grd_resp!="") {
        $list_group = $list_group_grd_resp;
    }
}
$groupes = [];
if ('' != $list_group) {
    $sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
    $ReqLog_group = \includes\SQL::query($sql_group) ;

    while ($resultat_group = $ReqLog_group->fetch_array()) {
        $groupes[$resultat_group["g_gid"]] = $resultat_group["g_groupename"];
    }
}

require_once VIEW_PATH . 'Responsable/AjoutAbsence.php';
