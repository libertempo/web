<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// on insert l'ajout de conges dans la table periode
function insert_ajout_dans_periode($login, $nb_jours, $id_type_abs, $commentaire)
{
    $date_today=date("Y-m-d");

    $result = insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0);
}

function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';
    $db = \includes\SQL::singleton();

    foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
    {
      foreach($tab_conges as $id_conges => $user_nb_jours_ajout) {

        $valid=verif_saisie_decimal($user_nb_jours_ajout);   //verif la bonne saisie du nombre décimal
        if ($valid) {
          if ($user_nb_jours_ajout!=0) {
            /* Modification de la table conges_users */
            $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde+'.$user_nb_jours_ajout.' WHERE su_login="'. $db->quote($user_name).'" AND su_abs_id = "'. $db->quote($id_conges).'";';
            /* On valide l'UPDATE dans la table ! */
            $ReqLog1 = $db->query($sql1) ;

            // on insert l'ajout de conges dans la table periode
            $commentaire =  _('resp_ajout_conges_comment_periode_user') ;
            insert_ajout_dans_periode($user_name, $user_nb_jours_ajout, $id_conges, $commentaire);
          }
        }
      }
    }
}

function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all) : string
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';
    $db = \includes\SQL::singleton();

    // recup de la liste de TOUS les users dont $resp_login est responsable
    // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
    // renvoit une liste de login entre quotes et séparés par des virgules
    $list_users_du_resp = \hr\Fonctions::get_list_all_users_du_hr($_SESSION['userlogin']);

    foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
        if ($nb_jours!=0) {
            $comment = $tab_new_comment_all[$id_conges];

            $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
            $ReqLog1 = $db->query($sql1);

            while($resultat1 = $ReqLog1->fetch_array()) {
                $current_login  =$resultat1["u_login"];
                $current_quotite=$resultat1["u_quotite"];

                if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                    $nb_conges=$nb_jours;
                } else {
                    // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                    $nb_conges = (round(($nb_jours*($current_quotite/100))*2))/2  ;
                }
                $valid=verif_saisie_decimal($nb_conges);
                if ($valid) {
                    // 1 : update de la table conges_solde_user
                    $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde + '.$nb_conges.'
                            WHERE  su_login = "'. $db->quote($current_login).'"  AND su_abs_id = "'. $db->quote($id_conges).'";';
                    $ReqLog_update = $db->query($req_update);

                    // 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
                    $commentaire =  _('resp_ajout_conges_comment_periode_all') ;
                    // ajout conges
                    insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
                }
            }

            if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
            } else {
                $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
            }
            log_action(0, "ajout", "tous", $comment_log);
        }
    }
    return $return;
}

function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $db = \includes\SQL::singleton();

    // recup de la liste des users d'un groupe donné
    $list_users = get_list_users_du_groupe($choix_groupe);
    if (empty($list_users)) {
        return;
    }
    foreach ($tab_new_nb_conges_all as $id_conges => $nb_jours) {
        if ($nb_jours!=0) {
            $comment = $tab_new_comment_all[$id_conges];

            $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) AND u_is_active='Y' ORDER BY u_login ";
            $ReqLog1 = $db->query($sql1);

            while ($resultat1 = $ReqLog1->fetch_array()) {
                $current_login  =$resultat1["u_login"];
                $current_quotite=$resultat1["u_quotite"];

                if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                    $nb_conges=$nb_jours;
                } else {
                    // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                    $nb_conges = (round(($nb_jours*($current_quotite/100))*2))/2  ;
                }

                $valid=verif_saisie_decimal($nb_conges);
                if ($valid) {
                    // 1 : on update conges_solde_user
                    $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde+ '.$nb_conges.'
                            WHERE  su_login = "'. $db->quote($current_login).'" AND su_abs_id = '.intval($id_conges).';';
                    $ReqLog_update = $db->query($req_update);

                    // 2 : on insert l'ajout de conges dans la table periode
                    // recup du nom du groupe
                    $groupename= get_group_name_from_id($choix_groupe);
                    $commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";

                    // ajout conges
                    insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
                }
            }

            $group_name = get_group_name_from_id($choix_groupe);
            if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
            } else {
                $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
            }
            log_action(0, "ajout", "groupe", $comment_log);
        }
    }
}

function affichage_saisie_globale_pour_tous($tab_type_conges) : string
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    /************************************************************/
    /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
    $return .= '<h2>' . _('resp_ajout_conges_ajout_all') . '</h2>';
    $return .= '<form action="' . $PHP_SELF . '?onglet=ajout_conges" method="POST">';
    $return .= '<fieldset class="cal_saisie">';
    $return .= '<div class="table-responsive"><table class="table table-hover table-condensed table-striped">';
    $return .= '<thead>';
    $return .= '<tr>';
    $return .= '<th colspan="2">' . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . '</th>';
    $return .= '<th>' . _('resp_ajout_conges_calcul_prop') . '</th>';
    $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
    $return .= '</tr>';
    $return .= '</thead>';
    foreach($tab_type_conges as $id_conges => $libelle) {
        $return .= '<tr>';
        $return .= '<td><strong>' . $libelle . '<strong></td>';
        $return .= '<td><input class="form-control" type="text" name="tab_new_nb_conges_all[' . $id_conges . ']" size="6" maxlength="6" value="0"></td>';
        $return .= '<td>' . _('resp_ajout_conges_oui') . '<input type="checkbox" name="tab_calcul_proportionnel[' . $id_conges . ']" value="TRUE" checked></td>';
        $return .= '<td><input class="form-control" type="text" name="tab_new_comment_all[' . $id_conges . ']" size="30" maxlength="200" value=""></td>';
        $return .= '</tr>';
    }
    $return .= '</table></div>';
    // texte sur l'arrondi du calcul proportionnel
    $return .= '<p>' . _('resp_ajout_conges_calcul_prop_arondi') . '!</p>';
    // bouton valider
    $return .= '<input class="btn" type="submit" value="' . _('form_valid_global') . '">';
    $return .= '</fieldset>';
    $return .= '<input type="hidden" name="ajout_global" value="TRUE">';
    $return .= '</form>';
    return $return;
}

// recup de la liste de tous les groupes pour le mode RH
function get_list_groupes_pour_rh($user_login) : string
{
    $list_group="";

    $sql1="SELECT DISTINCT gu_gid FROM conges_groupe_users ORDER BY gu_gid"; // Le but est de sélectionner tous les groupes ayant des utilisateurs
    $ReqLog1 = \includes\SQL::singleton()->query($sql1);

    if ($ReqLog1->num_rows != 0) {
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $current_group=$resultat1["gu_gid"];
            if ($list_group=="")
                $list_group="$current_group";
            else
                $list_group=$list_group.", $current_group";
        }
    }
    return $list_group;
}

function affichage_saisie_globale_groupe($tab_type_conges) : string
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    /***********************************************************************/
    /* SAISIE GROUPE pour tous les utilisateurs */

    // on établi la liste complète des groupes pour le mode RH
    $list_group = get_list_groupes_pour_rh($_SESSION['userlogin']);

    if ($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
    {
        $return .= '<h2>' . _('resp_ajout_conges_ajout_groupe') . '</h2>';
        $return .= '<form action="' . $PHP_SELF . '?onglet=ajout_conges" method="POST">';
        $return .= '<fieldset class="cal_saisie">';
        $return .= '<div class="table-responsive"><table class="table table-hover table-condensed table-striped">';
        $return .= '<tr>';
        $return .= '<td class="big">' . _('resp_ajout_conges_choix_groupe') . ' : </td>';
        // création du select pour le choix du groupe
        $text_choix_group="<select name=\"choix_groupe\" >";
        $sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
        $ReqLog_group = \includes\SQL::singleton()->query($sql_group) ;

        while ($resultat_group = $ReqLog_group->fetch_array()) {
            $current_group_id=$resultat_group["g_gid"];
            $current_group_name=$resultat_group["g_groupename"];
            $text_choix_group=$text_choix_group."<option value=\"$current_group_id\" >$current_group_name</option>";
        }
        $text_choix_group=$text_choix_group."</select>" ;

        $return .= '<td colspan="3">' . $text_choix_group . '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= '<th colspan="2">' . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . '</th>';
        $return .= '<th>' ._('resp_ajout_conges_calcul_prop') . '</th>';
        $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $return .= '</tr>';
        foreach($tab_type_conges as $id_conges => $libelle) {
            $return .= '<tr>';
            $return .= '<td><strong>' . $libelle . '<strong></td>';
            $return .= '<td><input class="form-control" type="text" name="tab_new_nb_conges_all[' . $id_conges . ']" size="6" maxlength="6" value="0"></td>';
            $return .= '<td>' . _('resp_ajout_conges_oui') . '<input type="checkbox" name="tab_calcul_proportionnel[' . $id_conges . ']" value="TRUE" checked></td>';
            $return .= '<td><input class="form-control" type="text" name="tab_new_comment_all[' . $id_conges . ']" size="30" maxlength="200" value=""></td>';
            $return .= '</tr>';
        }
        $return .= '</table></div>';
        $return .= '<p>' . _('resp_ajout_conges_calcul_prop_arondi') . '! </p>';
        $return .= '<input class="btn" type="submit" value="' . _('form_valid_groupe') . '">';
        $return .= '</fieldset>';
        $return .= '<input type="hidden" name="ajout_groupe" value="TRUE">';
        $return .= '</form>';
    }
    return $return;
}

function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp) : string
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    /************************************************************/
    /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
    $return .= '<h2>Ajout par utilisateur</h2>';
    $return .= '<form action="' . $PHP_SELF . '?onglet=ajout_conges" method="POST">';

    if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
        // AFFICHAGE TITRES TABLEAU
        $return .= '<div class="table-responsive"><table class="table table-hover table-condensed table-striped">';
        $return .= '<thead>';
        $return .= '<tr align="center">';
        $return .= '<th>' . _('divers_nom_maj_1') . '</th>';
        $return .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $return .= '<th>' . _('divers_quotite_maj_1') . '</th>';
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
        $return .= '<th>'. _('divers_comment_maj_1') . '<br></th>';
        $return .= '</tr>';
        $return .= '</thead>';
        $return .= '<tbody>';

        // AFFICHAGE LIGNES TABLEAU
        $cpt_lignes=0 ;
        $tab_champ_saisie_conges=array();

        $i = true;
        asort($tab_all_users_du_hr);
        // affichage des users dont on est responsable :
        foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
            if ($tab_current_user['is_active'] == "Y") {
                $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
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

        $return .= '</tbody>';
        $return .= '</table>';

        $return .= '<input type="hidden" name="ajout_conges" value="TRUE">';
        $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
        $return .= ' </form>';
    }

    return $return;
}

function saisie_ajout($tab_type_conges) : string
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

    // recup de la liste de TOUS les users pour le RH
    // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
    // renvoit une liste de login entre quotes et séparés par des virgules
    $tab_all_users_du_hr=\hr\Fonctions::recup_infos_all_users_du_hr($_SESSION['userlogin']);
    $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

    if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
        /************************************************************/
        /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
        $return .= affichage_saisie_globale_pour_tous($tab_type_conges);
        $return .= '<br>';

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
        $return .= affichage_saisie_globale_groupe($tab_type_conges);
        $return .= '<br>';

        /************************************************************/
        /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
        $return .= affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp);
        $return .= '<br>';

    } else {
        $return .= _('resp_etat_aucun_user') . '<br>';
    }
    return $return;
}

//var pour resp_ajout_conges_all.php
$ajout_conges = getpost_variable('ajout_conges');
$ajout_global = getpost_variable('ajout_global');
$ajout_groupe = getpost_variable('ajout_groupe');
$choix_groupe = getpost_variable('choix_groupe');
$return = '';

// titre
$return .= '<h1>'. _('resp_ajout_conges_titre') . '</h1>';

if ( $ajout_conges == "TRUE" ) {
    $tab_champ_saisie            = getpost_variable('tab_champ_saisie');
    $tab_commentaire_saisie        = getpost_variable('tab_commentaire_saisie');

    $return .= ajout_conges($tab_champ_saisie, $tab_commentaire_saisie);
    redirect( ROOT_PATH .'hr/hr_index.php', false);
    exit;
} elseif ( $ajout_global == "TRUE" ) {

    $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
    $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
    $tab_new_comment_all         = getpost_variable('tab_new_comment_all');

    $return .= ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
    redirect( ROOT_PATH .'hr/hr_index.php', false);
    exit;
} elseif ( $ajout_groupe == "TRUE" ) {

    $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
    $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
    $tab_new_comment_all         = getpost_variable('tab_new_comment_all');
    $choix_groupe                = getpost_variable('choix_groupe');

    ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
    redirect( ROOT_PATH .'hr/hr_index.php', false);
    exit;
} else {
    $return .= saisie_ajout($tab_type_cong);
}

echo $return;
