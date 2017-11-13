<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/**
 * Encapsule le comportement du module d'ajout de congés
 *
 * @return void
 * @access public
 * @static
 */
function ajoutCongesModule($tab_type_cong)
{
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

    // titre
    $return .= '';

    if($ajout_conges=="TRUE") {
        $return .= \responsable\Fonctions::ajout_conges($tab_champ_saisie, $tab_commentaire_saisie);
    } elseif($ajout_global=="TRUE") {
        $return .= \responsable\Fonctions::ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
    } elseif($ajout_groupe=="TRUE") {
        $return .= \responsable\Fonctions::ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
    } else {
        $return .= \responsable\Fonctions::saisie_ajout($tab_type_cong);
    }
    return $return;
}

function saisie_ajout( $tab_type_conges)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    // recup du tableau des types de conges (seulement les congesexceptionnels )
    if ($config->isCongesExceptionnelsActive()) {
        $tab_type_conges_exceptionnels = recup_tableau_types_conges_exceptionnels();
    } else {
        $tab_type_conges_exceptionnels = array();
    }

    // recup de la liste de TOUS les users dont $resp_login est responsable
    // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
    // renvoit une liste de login entre quotes et séparés par des virgules
    $tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
    $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
    if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
        /************************************************************/
        /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
        $return .= \responsable\Fonctions::affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp);
        $return .= '<br>';
    } else {
        $return .= _('resp_etat_aucun_user') . '<br>';
    }
    return $return;
}

function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    /************************************************************/
    /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
    $return .= '<h2>Ajout par utilisateur</h2>';
    $return .= '<form action="' . $PHP_SELF . '?onglet=ajout_conges" method="POST">';

    // Récupération des informations
    // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
    //$tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
    //$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

    if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
        // AFFICHAGE TITRES TABLEAU
        $return .= '<div class="table-responsive"><table class="table table-hover table-condensed table-striped">';
        $return .= '<thead>';
        $return .= '<tr align="center">';
        $return .= '<th>' . _('divers_nom_maj_1') . '</th>';
        $return .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $return .= '<th>' . _('divers_quotite_maj_1') . '</td>';
        foreach($tab_type_conges as $id_conges => $libelle) {
            $return .= '<th>' . $libelle . '<br><i>(' . _('divers_solde') . ')</i></th>';
            $return .= '<th>' . $libelle . '<br>' . _('resp_ajout_conges_nb_jours_ajout') . '</th>';
        }
        if ($config->isCongesExceptionnelsActive()) {
            foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                $return .= '<th>' . $libelle . '<br><i>(' . _('divers_solde') . ')</i></th>';
                $return .= '<th>' . $libelle . '<br>' . _('resp_ajout_conges_nb_jours_ajout') . '</th>';
            }
        }
        $return .= '<th>' . _('divers_comment_maj_1') . '<br></th>';
        $return .= '</tr>';
        $return .= '</thead>';
        $return .= '<tbody>';

        // AFFICHAGE LIGNES TABLEAU
        $cpt_lignes=0 ;
        $tab_champ_saisie_conges=array();

        $i = true;
        asort($tab_all_users_du_resp);
        // affichage des users dont on est responsable :
        foreach($tab_all_users_du_resp as $current_login => $tab_current_user) {
            if($tab_current_user['is_active'] == "Y") {
                $return .= '<tr class="'.($i?'i':'p').'">';
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];

                /** sur la ligne ,   **/
                $return .= '<td>' . $tab_current_user['nom'] . '</td>';
                $return .= '<td>' . $tab_current_user['prenom'] . '</td>';
                $return .= '<td>' . $tab_current_user['quotite'] . '%</td>';

                foreach($tab_type_conges as $id_conges => $libelle) {
                    /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                    $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                    $return .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                    $return .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                }
                if ($config->isCongesExceptionnelsActive()) {
                    foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                        /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                        $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                        $return .= '<td><i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                        $return .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                    }
                }
                $return .= '<td align="center" class="histo"><input class="form-control" type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="30" maxlength="200" value=""></td>';
                $return .= '</tr>';
                $cpt_lignes++ ;
                $i = !$i;
            }
        }

        // affichage des users dont on est grand responsable :
        if( ($config->isDoubleValidationActive()) && ($config->canGrandResponsableAjouteConge()) ) {
            $nb_colspan=50;
            $return .= '<tr align="center"><td class="histo" style="background-color: #CCC;" colspan="' . $nb_colspan . '"><i>' . _('resp_etat_users_titre_double_valid') . '</i></td></tr>';

            $i = true;
            foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user) {
                if($tab_current_user['is_active'] == "Y") {
                    $return .= '<tr class="'.($i?'i':'p').'">';
                    //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                    $tab_conges=$tab_current_user['conges'];

                    /** sur la ligne ,   **/
                    $return .= '<td>' . $tab_current_user['nom'] . '</td>';
                    $return .= '<td>' . $tab_current_user['prenom'] . '</td>';
                    $return .= '<td>' . $tab_current_user['quotite'] . '%</td>';

                    foreach($tab_type_conges as $id_conges => $libelle) {
                        /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                        $champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                        $return .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                        $return .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                    }
                    if ($config->isCongesExceptionnelsActive()) {
                        foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                            /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                            $champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                            $return .= '<td><i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                            $return .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                        }
                    }
                    $return .= '<td align="center" class="histo"><input type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="30" maxlength="200" value=""></td>';
                    $return .= '</tr>';
                    $cpt_lignes++ ;
                    $i = !$i;
                }
            }
        }

        $return .= '</tbody>';
        $return .= '</table></div>';

        $return .= '<input type="hidden" name="ajout_conges" value="TRUE">';
        $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
        $return .= ' </form>';
    }
    return $return;
}
$hasSubalternes =
    count(recup_infos_all_users_du_resp($_SESSION['userlogin'])) +
    count(recup_infos_all_users_du_grand_resp($_SESSION['userlogin']));

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
