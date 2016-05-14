<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/
namespace responsable;

/**
* Regroupement des fonctions liées au responsable
*/
class Fonctions
{
    // on insert l'ajout de conges dans la table periode
    public static function insert_ajout_dans_periode($login, $nb_jours, $id_type_abs, $commentaire)
    {
        $date_today=date("Y-m-d");

        $result=insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0);
    }

    public static function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all)
    {
        // $tab_new_nb_conges_all[$id_conges]= nb_jours
        // $tab_calcul_proportionnel[$id_conges]= TRUE / FALSE

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        // recup de la liste des users d'un groupe donné
        $list_users = get_list_users_du_groupe($choix_groupe);

        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while ($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                        $nb_conges=$nb_jours;
                    } else {
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
                    }
                    $nb_conges_ok = verif_saisie_decimal($nb_conges);
                    if($nb_conges_ok){
                        // 1 : on update conges_solde_user
                        $req_update = "UPDATE conges_solde_user SET su_solde = su_solde+$nb_conges
                            WHERE  su_login = '$current_login' AND su_abs_id = $id_conges   ";
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges dans la table periode
                        // recup du nom du groupe
                        $groupename= get_group_name_from_id($choix_groupe);
                        $commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";

                        // ajout conges
                        \responsable\Fonctions::insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
                    }

                }

                $group_name = get_group_name_from_id($choix_groupe);
                // 3 : Enregistrement du commentaire relatif à l'ajout de jours de congés
                if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                    $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
                } else {
                    $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
                }
                log_action(0, "ajout", "groupe", $comment_log);
            }
        }
        $return .= ' ' . _('form_modif_ok') . '<br><br>';
        redirect( ROOT_PATH .'responsable/resp_index.php?session=' . $session );
        return $return;
    }

    public static function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        // $tab_new_nb_conges_all[$id_conges]= nb_jours
        // $tab_calcul_proportionnel[$id_conges]= TRUE / FALSE

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $list_users_du_resp = get_list_all_users_du_resp($_SESSION['userlogin']);

        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                        $nb_conges=$nb_jours;
                    } else {
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
                    }

                    $nb_conges_ok = verif_saisie_decimal($nb_conges);
                    if ($nb_conges_ok) {
                        // 1 : update de la table conges_solde_user
                        $req_update = "UPDATE conges_solde_user SET su_solde = su_solde+$nb_conges
                            WHERE  su_login = '$current_login' AND su_abs_id = $id_conges   ";
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
                        $commentaire =  _('resp_ajout_conges_comment_periode_all') ;
                        // ajout conges
                        \responsable\Fonctions::insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
                    }
                }
                // 3 : Enregistrement du commentaire relatif à l'ajout de jours de congés
                if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                    $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
                } else {
                    $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
                }
                log_action(0, "ajout", "tous", $comment_log);
            }
        }

        $return .= ' ' . _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        redirect( ROOT_PATH .'responsable/resp_index.php?session=' . $session );
        return $return;
    }

    public static function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
        {
            foreach($tab_conges as $id_conges => $user_nb_jours_ajout) {
                $user_nb_jours_ajout_float =(float) $user_nb_jours_ajout ;
                $valid=verif_saisie_decimal($user_nb_jours_ajout_float);   //verif la bonne saisie du nombre décimal
                if($valid) {
                    if($user_nb_jours_ajout_float!=0) {
                        /* Modification de la table conges_users */
                        $sql1 = "UPDATE conges_solde_user SET su_solde = su_solde+$user_nb_jours_ajout_float WHERE su_login='$user_name' AND su_abs_id = $id_conges " ;
                        /* On valide l'UPDATE dans la table ! */
                        $ReqLog1 = \includes\SQL::query($sql1) ;

                        /*			// Enregistrement du commentaire relatif à l'ajout de jours de congés
                                    $comment = $tab_commentaire_saisie[$user_name];
                                    $sql1 = "INSERT INTO conges_historique_ajout (ha_login, ha_date, ha_abs_id, ha_nb_jours, ha_commentaire)
                                    VALUES ('$user_name', NOW(), $id_conges, $user_nb_jours_ajout_float , '$comment')";
                                    $ReqLog1 = SQL::query($sql1) ;
                         */
                        // on insert l'ajout de conges dans la table periode
                        $commentaire =  _('resp_ajout_conges_comment_periode_user') ;
                        \responsable\Fonctions::insert_ajout_dans_periode($user_name, $user_nb_jours_ajout_float, $id_conges, $commentaire);
                    }
                }
            }
        }
        $return .= ' '. _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '">';
        return $return;
    }

    public static function affichage_saisie_globale_groupe($tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */

        // on établi la liste complète des groupes dont on est le resp (ou le grd resp)
        $list_group_resp=get_list_groupes_du_resp($_SESSION['userlogin']);
        if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) ) {
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


        if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
        {
            $return .= '<h2>' . _('resp_ajout_conges_ajout_groupe') . '</h2>';
            $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=ajout_conges" method="POST">';
            $return .= '<fieldset class="cal_saisie">';
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped'
            ]);
            $childTable = '<tr>';
            $childTable .= '<td class="big">' . _('resp_ajout_conges_choix_groupe') . ' : </td>';
            // création du select pour le choix du groupe
            $text_choix_group="<select name=\"choix_groupe\" >";
            $sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
            $ReqLog_group = \includes\SQL::query($sql_group) ;

            while ($resultat_group = $ReqLog_group->fetch_array()) {
                $current_group_id=$resultat_group["g_gid"];
                $current_group_name=$resultat_group["g_groupename"];
                $text_choix_group=$text_choix_group."<option value=\"$current_group_id\" >$current_group_name</option>";
            }
            $text_choix_group=$text_choix_group."</select>" ;

            $childTable .= '<td colspan="3">' . $text_choix_group . '</td>';
            $childTable .= '</tr>';
            $childTable .= '<tr>';
            $childTable .= '<th colspan="2">' . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . '</th>';
            $childTable .= '<th>' ._('resp_ajout_conges_calcul_prop') . '</th>';
            $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
            $childTable .= '</tr>';
            foreach($tab_type_conges as $id_conges => $libelle) {
                $childTable .= '<tr>';
                $childTable .= '<td><strong>' . $libelle . '<strong></td>';
                $childTable .= '<td><input class="form-control" type="text" name="tab_new_nb_conges_all[' . $id_conges . ']" size="6" maxlength="6" value="0"></td>';
                $childTable .= '<td>' . _('resp_ajout_conges_oui') . '<input type="checkbox" name="tab_calcul_proportionnel[' . $id_conges . ']" value="TRUE" checked></td>';
                $childTable .= '<td><input class="form-control" type="text" name="tab_new_comment_all[' . $id_conges . ']" size="30" maxlength="200" value=""></td>';
                $childTable .= '</tr>';
            }
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '<p>' . _('resp_ajout_conges_calcul_prop_arondi') . '! </p>';
            $return .= '<input class="btn" type="submit" value="' . _('form_valid_groupe') . '">';
            $return .= '</fieldset>';
            $return .= '<input type="hidden" name="ajout_groupe" value="TRUE">';
            $return .= '<input type="hidden" name="session" value="' . $session . '">';
            $return .= '</form>';
        }
        return $return;
    }

    public static function affichage_saisie_globale_pour_tous($tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        /************************************************************/
        /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
        $return .= '<h2>' . _('resp_ajout_conges_ajout_all') . '</h2>';
        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=ajout_conges" method="POST">';
        $return .= '<fieldset class="cal_saisie">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped'
        ]);
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th colspan="2">' . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . '</th>';
        $childTable .= '<th>' ._('resp_ajout_conges_calcul_prop') . '</th>';
        $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        foreach($tab_type_conges as $id_conges => $libelle) {
            $childTable .= '<tr>';
            $childTable .= '<td><strong>' . $libelle . '<strong></td>';
            $childTable .= '<td><input class="form-control" type="text" name="tab_new_nb_conges_all[' . $id_conges . ']" size="6" maxlength="6" value="0"></td>';
            $childTable .= '<td>' . _('resp_ajout_conges_oui') . '<input type="checkbox" name="tab_calcul_proportionnel[' . $id_conges . ']" value="TRUE" checked></td>';
            $childTable .= '<td><input class="form-control" type="text" name="tab_new_comment_all[' . $id_conges . ']" size="30" maxlength="200" value=""></td>';
            $childTable .= '</tr>';
        }
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        // texte sur l'arrondi du calcul proportionnel
        $return .= '<p>' . _('resp_ajout_conges_calcul_prop_arondi') . '!</p>';
        // bouton valider
        $return .= '<input class="btn" type="submit" value="' . _('form_valid_global') . '">';
        $return .= '</fieldset>';
        $return .= '<input type="hidden" name="ajout_global" value="TRUE">';
        $return .= '<input type="hidden" name="session" value="' . $session . '">';
        $return .= '</form>';
        return $return;
    }

    public static function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        /************************************************************/
        /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
        $return .= '<h2>Ajout par utilisateur</h2>';
        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=ajout_conges" method="POST">';

        // Récupération des informations
        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
        //$tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
        //$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

        if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // AFFICHAGE TITRES TABLEAU
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped'
            ]);
            $childTable = '<thead>';
            $childTable .= '<tr align="center">';
            $childTable .= '<th>' . _('divers_nom_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_quotite_maj_1') . '</td>';
            foreach($tab_type_conges as $id_conges => $libelle) {
                $childTable .= '<th>' . $libelle . '<br><i>(' . _('divers_solde') . ')</i></th>';
                $childTable .= '<th>' . $libelle . '<br>' . _('resp_ajout_conges_nb_jours_ajout') . '</th>';
            }
            if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                    $childTable .= '<th>' . $libelle . '<br><i>(' . _('divers_solde') . ')</i></th>';
                    $childTable .= '<th>' . $libelle . '<br>' . _('resp_ajout_conges_nb_jours_ajout') . '</th>';
                }
            }
            $childTable .= '<th>' . _('divers_comment_maj_1') . '<br></th>';
            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            // AFFICHAGE LIGNES TABLEAU
            $cpt_lignes=0 ;
            $tab_champ_saisie_conges=array();

            $i = true;
            // affichage des users dont on est responsable :
            foreach($tab_all_users_du_resp as $current_login => $tab_current_user) {
                $childTable .= '<tr class="'.($i?'i':'p').'">';
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];

                /** sur la ligne ,   **/
                $childTable .= '<td>' . $tab_current_user['nom'] . '</td>';
                $childTable .= '<td>' . $tab_current_user['prenom'] . '</td>';
                $childTable .= '<td>' . $tab_current_user['quotite'] . '%</td>';

                foreach($tab_type_conges as $id_conges => $libelle) {
                    /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                    $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                    $childTable .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                    $childTable .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                    foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                        /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                        $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                        $childTable .= '<td><i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                        $childTable .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                    }
                }
                $childTable .= '<td align="center" class="histo"><input class="form-control" type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="30" maxlength="200" value=""></td>';
                $childTable .= '</tr>';
                $cpt_lignes++ ;
                $i = !$i;
            }

            // affichage des users dont on est grand responsable :
            if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) ) {
                $nb_colspan=50;
                $childTable .= '<tr align="center"><td class="histo" style="background-color: #CCC;" colspan="' . $nb_colspan . '"><i>' . _('resp_etat_users_titre_double_valid') . '</i></td></tr>';

                $i = true;
                foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user) {
                    $childTable .= '<tr class="'.($i?'i':'p').'">';
                    //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                    $tab_conges=$tab_current_user['conges'];

                    /** sur la ligne ,   **/
                    $childTable .= '<td>' . $tab_current_user['nom'] . '</td>';
                    $childTable .= '<td>' . $tab_current_user['prenom'] . '</td>';
                    $childTable .= '<td>' . $tab_current_user['quotite'] . '%</td>';

                    foreach($tab_type_conges as $id_conges => $libelle) {
                        /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                        $champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                        $childTable .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                        $childTable .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                    }
                    if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                        foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                            /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                            $champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                            $childTable .= '<td><i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
                            $childTable .= '<td align="center" class="histo">' . $champ_saisie_conges . '</td>';
                        }
                    }
                    $childTable .= '<td align="center" class="histo"><input type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="30" maxlength="200" value=""></td>';
                    $childTable .= '</tr>';
                    $cpt_lignes++ ;
                    $i = !$i;
                }
            }

            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();

            $return .= '<input type="hidden" name="ajout_conges" value="TRUE">';
            $return .= '<input type="hidden" name="session" value="' . $session . '">';
            $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
            $return .= ' </form>';
        }
        return $return;
    }

    public static function saisie_ajout( $tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        // recup du tableau des types de conges (seulement les congesexceptionnels )
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
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
            /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
            $return .= \responsable\Fonctions::affichage_saisie_globale_pour_tous($tab_type_conges);
            $return .= '<br>';

            /***********************************************************************/
            /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
            if( $_SESSION['config']['gestion_groupes'] ) {
                $return .= \responsable\Fonctions::affichage_saisie_globale_groupe($tab_type_conges);
            }

            $return .= '<hr/>';

            /************************************************************/
            /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
            $return .= \responsable\Fonctions::affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp);
            $return .= '<br>';
        } else {
            $return .= _('resp_etat_aucun_user') . '<br>';
        }
        return $return;
    }

    /**
     * Encapsule le comportement du module d'ajout de congés
     *
     * @return void
     * @access public
     * @static
     */
    public static function ajoutCongesModule($tab_type_cong)
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
        $return .= '<h1>' . _('resp_ajout_conges_titre') . '</h1>';

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

    // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée) et stockage dans la table
    public static function set_nouvelle_date_limite_reliquat()
    {
        //si on autorise les reliquats
        if($_SESSION['config']['autorise_reliquats_exercice']) {
            // s'il y a une date limite d'utilisationdes reliquats (au format jj-mm)
            if($_SESSION['config']['jour_mois_limite_reliquats']!=0) {
                // nouvelle date limite au format aaa-mm-jj
                $t=explode("-", $_SESSION['config']['jour_mois_limite_reliquats']);
                $new_date_limite = date("Y")."-".$t[1]."-".$t[0];

                //si la date limite n'a pas encore été updatée
                if($_SESSION['config']['date_limite_reliquats'] < $new_date_limite) {
                    /* Modification de la table conges_appli */
                    $sql_update= "UPDATE conges_appli SET appli_valeur = '$new_date_limite' WHERE appli_variable='date_limite_reliquats' " ;
                    $ReqLog_update = \includes\SQL::query($sql_update) ;

                }
            }
        }
    }

    // cloture / debut d'exercice pour TOUS les users d'un groupe'
    public static function cloture_globale_groupe($group_id, $tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        // recup de la liste de TOUS les users du groupe
        $tab_all_users_du_groupe=recup_infos_all_users_du_groupe($group_id);

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if(count($tab_all_users_du_groupe)!=0) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_groupe as $current_login => $tab_current_user) {
                $return .= cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture);
            }
        }
        $return .= ' ' . _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '">';
        return $return;
    }

    // cloture / debut d'exercice pour TOUS les users du resp (ou grand resp)
    public static function cloture_globale($tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_resp as $current_login => $tab_current_user) {
                $return .= cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture);
            }
            // traitement des users dont on est grand responsable :
            if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) ) {
                foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user) {
                    $return .= cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture);
                }
            }
        }
        $return .= ' ' . _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '">';
        return $return;
    }

    // verifie si tous les users on été basculés de l'exerccice précédent vers le suivant.
    // si oui : on incrémente le num_exercice de l'application
    public static function update_appli_num_exercice()
    {
        // verif
        $appli_num_exercice = $_SESSION['config']['num_exercice'] ;
        $sql_verif = 'SELECT u_login FROM conges_users WHERE u_login != \'admin\' AND u_login != \'conges\' AND u_num_exercice != '. \includes\SQL::quote($appli_num_exercice).';';
        $ReqLog_verif = \includes\SQL::query($sql_verif);

        if($ReqLog_verif->num_rows == 0) {
            /* Modification de la table conges_appli */
            $sql_update= 'UPDATE conges_appli SET appli_valeur = appli_valeur+1 WHERE appli_variable=\'num_exercice\' ;';
            $ReqLog_update = \includes\SQL::query($sql_update) ;

            // ecriture dans les logs
            $new_appli_num_exercice = $appli_num_exercice+1 ;
            log_action(0, '', '', 'fin/debut exercice (appli_num_exercice : '.$appli_num_exercice.' -> '.$new_appli_num_exercice.')');
        }
    }

    public static function cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire)
    {
        $return = '';
        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on le bascule d'exercice
        if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice']) {
            // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée)
            \responsable\Fonctions::set_nouvelle_date_limite_reliquat();

            //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
            $tab_conges_current_user=$tab_current_user['conges'];
            foreach($tab_type_conges as $id_conges => $libelle) {
                $user_nb_jours_ajout_an = $tab_conges_current_user[$libelle]['nb_an'];
                $user_solde_actuel=$tab_conges_current_user[$libelle]['solde'];
                $user_reliquat_actuel=$tab_conges_current_user[$libelle]['reliquat'];

                /**********************************************/
                /* Modification de la table conges_solde_user */

                if($_SESSION['config']['autorise_reliquats_exercice']) {
                    // ATTENTION : si le solde du user est négatif, on ne compte pas de reliquat et le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if($user_solde_actuel>0) {
                        //calcul du reliquat pour l'exercice suivant
                        if($_SESSION['config']['nb_maxi_jours_reliquats']!=0) {
                            if($user_solde_actuel <= $_SESSION['config']['nb_maxi_jours_reliquats']) {
                                $new_reliquat = $user_solde_actuel ;
                            } else {
                                $new_reliquat = $_SESSION['config']['nb_maxi_jours_reliquats'] ;
                            }
                        } else {
                            $new_reliquat = $user_reliquat_actuel + $user_solde_actuel ;
                        }

                        //
                        // update D'ABORD du reliquat
                        $VerifDec=verif_saisie_decimal($new_reliquat);
                        $sql_reliquat = "UPDATE conges_solde_user SET su_reliquat = $new_reliquat WHERE su_login='$current_login' AND su_abs_id = $id_conges " ;
                        $ReqLog_reliquat = \includes\SQL::query($sql_reliquat) ;
                    } else {
                        $new_reliquat = $user_solde_actuel ; // qui est nul ou negatif
                    }


                    $new_solde = $user_nb_jours_ajout_an + $new_reliquat  ;
                    $VerifDec=verif_saisie_decimal($new_solde);
                    // update du solde
                    $sql_solde = 'UPDATE conges_solde_user SET su_solde = \''.$new_solde.'\' WHERE su_login="'. \includes\SQL::quote($current_login).'" AND su_abs_id ="'. \includes\SQL::quote($id_conges).'" ';
                    $ReqLog_solde = \includes\SQL::query($sql_solde) ;
                } else {
                    // ATTENTION : meme si on accepte pas les reliquats, si le solde du user est négatif, il faut le reporter: le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if($user_solde_actuel < 0) {
                        $new_solde = $user_nb_jours_ajout_an + $user_solde_actuel ; // qui est nul ou negatif
                    } else {
                        $new_solde = $user_nb_jours_ajout_an ;
                    }
                    $VerifDec=verif_saisie_decimal($new_solde);
                    $sql_solde = 'UPDATE conges_solde_user SET su_solde = \''.$new_solde.'\' WHERE su_login="'. \includes\SQL::quote($current_login).'"  AND su_abs_id = "'. \includes\SQL::quote($id_conges).'" ';
                    $ReqLog_solde = \includes\SQL::query($sql_solde) ;
                }

                /* Modification de la table conges_users */
                // ATTENTION : ne pas faire "SET u_num_exercice = u_num_exercice+1" dans la requete SQL car on incrémenterait pour chaque type d'absence !
                $new_num_exercice=$_SESSION['config']['num_exercice'] ;
                $sql2 = 'UPDATE conges_users SET u_num_exercice = \''.$new_num_exercice.'\' WHERE u_login="'. \includes\SQL::quote($current_login).'" ';
                $ReqLog2 = \includes\SQL::query($sql2) ;

                // on insert l'ajout de conges dans la table periode (avec le commentaire)
                $date_today=date("Y-m-d");
                insert_dans_periode($current_login, $date_today, "am", $date_today, "am", $user_nb_jours_ajout_an, $commentaire, $id_conges, "ajout", 0);
            }

            // on incrémente le num_exercice de l'application si tous les users on été basculés.
            \responsable\Fonctions::update_appli_num_exercice();
        }
        return $return;
    }

    // cloture / debut d'exercice user par user pour les users du resp (ou grand resp)
    public static function cloture_users($tab_type_conges, $tab_cloture_users, $tab_commentaire_saisie)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

        if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_resp as $current_login => $tab_current_user) {
                // tab_cloture_users[$current_login]=TRUE si checkbox "cloturer" est cochée
                if( (isset($tab_cloture_users[$current_login])) && ($tab_cloture_users[$current_login]=TRUE) ) {
                    $commentaire = $tab_commentaire_saisie[$current_login];
                    $return .= \responsable\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire);
                }
            }
            // traitement des users dont on est grand responsable :
            if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) ) {
                foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user) {
                    // tab_cloture_users[$current_login]=TRUE si checkbox "cloturer" est cochée
                    if( (isset($tab_cloture_users[$current_login])) && ($tab_cloture_users[$current_login]=TRUE) ) {
                        $commentaire = $tab_commentaire_saisie[$current_login];
                        $return .= \responsable\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire);
                    }
                }
            }
        }
        $return .= ' ' . _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '">';
        return $return;
    }

    public static function affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user)
    {
        $return .= '<tr align="center">';
        //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
        $tab_conges=$tab_current_user['conges'];

        /** sur la ligne ,   **/
        $return .= '<td>' . $tab_current_user['nom'] . '</td>';
        $return .= '<td>' . $tab_current_user['prenom'] . '</td>';
        $return .= '<td>' . $tab_current_user['quotite'] . '%</td>';

        foreach($tab_type_conges as $id_conges => $libelle) {
            $return .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
        }

        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on peut le cocher
        if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice']) {
            $return .= '<td align="center" class="histo"><input type="checkbox" name="tab_cloture_users[' . $current_login . ']" value="TRUE" checked></td>';
        } else {
            $return .= '<td align="center" class="histo"><img src="' . IMG_PATH . 'stop.png" width="16" height="16" border="0" ></td>';
        }

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");
        $return .= '<td align="center" class="histo"><input type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="20" maxlength="200" value="' . $comment_cloture . '"></td>';
        $return .= '</tr>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de page principale
     *
     * @return void
     * @access public
     * @static
     */
    public static function pagePrincipaleModule($tab_type_cong, $tab_type_conges_exceptionnels, $session)
    {
        $return = '';
        /***********************************/
        // AFFICHAGE ETAT CONGES TOUS USERS

        /***********************************/
        // AFFICHAGE TABLEAU (premiere ligne)
        $return .= '<h1>' . _('resp_traite_user_etat_conges') . '</h1>';

        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped'
        ]);
        $childTable = '<thead>';

        $nb_colonnes = 0;

        $childTable .= '<tr>';
        $childTable .= '<th>' . _('divers_nom_maj') .'</th>';
        $childTable .= '<th>'. _('divers_prenom_maj') .'</th>';
        $childTable .= '<th>'. _('divers_quotite_maj_1') .'</th>' ;
        $nb_colonnes = 3;
        foreach($tab_type_cong as $id_conges => $libelle) {
            // cas d'une absence ou d'un congé
            $childTable .= '<th>' . $libelle . ' / ' . _('divers_an_maj') . '</th>';
            $childTable .= '<th>'. _('divers_solde_maj') . ' ' . $libelle . '</th>';
            $nb_colonnes += 2;
        }
        // conges exceptionnels
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
                $childTable .= '<th>'. _('divers_solde_maj') . ' ' . $libelle . '</th>';
                $nb_colonnes += 1;
            }
        }
        $childTable .= '<th></th>';
        $nb_colonnes += 1;
        if($_SESSION['config']['editions_papier']) {
            $childTable .= '<th></th>';
            $nb_colonnes += 1;
        }
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        /***********************************/
        // AFFICHAGE USERS

        /***********************************/
        // AFFICHAGE DE USERS DIRECTS DU RESP

        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
        $tab_all_users=recup_infos_all_users_du_resp($_SESSION['userlogin']);
        if(count($tab_all_users)==0) {// si le tableau est vide (resp sans user !!) on affiche une alerte !
            $childTable .= '<tr align="center"><td class="histo" colspan="' .  $nb_colonnes . '">' . _('resp_etat_aucun_user') . '</td></tr>';
        } else {
            $i = true;
            foreach($tab_all_users as $current_login => $tab_current_user) {
                if($tab_current_user['is_active'] == "Y" || $_SESSION['config']['print_disable_users'] == 'TRUE') {
                    //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                    $tab_conges=$tab_current_user['conges'];
                    $text_affich_user="<a class=\"action show\" href=\"resp_index.php?session=$session&onglet=traite_user&user_login=$current_login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a>" ;
                    $text_edit_papier="<a class=\"action edit\" href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";

                    $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                    $childTable .= '<td>' . $tab_current_user['nom'] . '</td><td>' . $tab_current_user['prenom'] . '</td><td>' . $tab_current_user['quotite'] . '%</td>';
                    foreach($tab_type_cong as $id_conges => $libelle) {
                        $childTable .= '<td>' . $tab_conges[$libelle]['nb_an'] . '</td>';
                        $childTable .= '<td>' . $tab_conges[$libelle]['solde'] . '</td>';
                    }
                    if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                        foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
                            $nbSolde = (isset($tab_conges[$libelle])) ? $tab_conges[$libelle]['solde'] : 0;
                            $childTable .= '<td>' . $nbSolde . '</td>';
                        }
                    }
                    $childTable .= '<td>' . $text_affich_user . '</td>';
                    if($_SESSION['config']['editions_papier']) {
                        $childTable .= '<td>' . $text_edit_papier . '</td>';
                    }
                    $childTable .= '</tr>';
                    $i = !$i;
                }
            }
        }

        /***********************************/
        // AFFICHAGE DE USERS DONT LE RESP EST GRAND RESP

        if($_SESSION['config']['double_validation_conges']) {
            // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est GRAND responsable
            $tab_all_users_2=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

            $compteur=0;  // compteur de ligne a afficher en dessous (dés que passe à 1 : on affiche une ligne de titre)

            $i = true;
            foreach($tab_all_users_2 as $current_login_2 => $tab_current_user_2) {
                if( !array_key_exists($current_login_2, $tab_all_users) ) // si le user n'est pas déjà dans le tableau précédent (deja affiché)
                {
                    $compteur++;
                    if($compteur==1)  // alors on affiche une ligne de titre
                    {
                        $nb_colspan=9;
                        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                            $nb_colspan=10;
                        }

                        $childTable .= '<tr align="center"><td class="histo" style="background-color: #CCC;" colspan="' . $nb_colonnes . '"><i>' . _('resp_etat_users_titre_double_valid') . '</i></td></tr>';
                    }

                    //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                    $tab_conges_2=$tab_current_user_2['conges'];

                    $text_affich_user="<a class=\"action show\" href=\"resp_index.php?session=$session&onglet=traite_user&user_login=$current_login_2\" title=\"". _('resp_etat_users_afficher') ."\"><i class=\"fa fa-eye\"></i></a>" ;
                    $text_edit_papier="<a class=\"action print\" href=\"../edition/edit_user.php?session=$session&user_login=$current_login_2\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
                    $childTable .= '<tr class="'.($i?'i':'p').'">';
                    $childTable .= '<td>' . $tab_current_user_2['nom'] . '</td><td>' . $tab_current_user_2['prenom'] . '</td><td>' . $tab_current_user_2['quotite'] . '%</td>';
                    foreach($tab_type_cong as $id_conges => $libelle) {
                        $childTable .= '<td>' . $tab_conges_2[$libelle]['nb_an'] . '</td><td>' . $tab_conges_2[$libelle]['solde'] . '</td>';
                    }
                    if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                        foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
                            $childTable .= '<td>' . $tab_conges_2[$libelle]['solde'] . '</td>';
                        }
                    }
                    $childTable .= '<td>' . $text_affich_user . '</td>';
                    if($_SESSION['config']['editions_papier']) {
                        $childTable .= '<td>' . $text_edit_papier . '</td>';
                    }
                    $childTable .= '</tr>';
                    $i = !$i;
                }
            }
        }

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        return $return;
    }

    public static function new_conges($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        //conversion des dates
        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);

        // verif validité des valeurs saisies
        $valid=verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

        if ($valid) {
            $return .= $user_login . '---' . $new_debut . '_' . $new_demi_jour_deb . '---' . $new_fin . '_' .  $new_demi_jour_fin . '---' . $new_nb_jours . '---' . $new_comment . '---' . $new_type_id . '<br>';

            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_tout_type_abs = recup_tableau_tout_types_abs();

            /**********************************/
            /* insert dans conges_periode     */
            /**********************************/
            $new_etat="ok";
            $result=insert_dans_periode($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $new_etat, 0);

            /************************************************/
            /* UPDATE table "conges_solde_user" (jours restants) */
            // on retranche les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on annule est un "conges"
            if(isset($tab_tout_type_abs[$new_type_id]['type']) && $tab_tout_type_abs[$new_type_id]['type']=="conges") {
                $user_nb_jours_pris_float=(float) $new_nb_jours ;
                soustrait_solde_et_reliquat_user($user_login, "", $user_nb_jours_pris_float, $new_type_id, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin);
            }
            $comment_log = "saisie conges par le responsable pour $user_login ($new_nb_jours jour(s)) type_conges = $new_type_id ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
            log_action(0, "", $user_login, $comment_log);

            if($result) {
                $return .= _('form_modif_ok') . '<br><br>';
            } else {
                $return .= _('form_modif_not_ok') . '<br><br>';
            }
        } else {
            $return .= _('resp_traite_user_valeurs_not_ok') . '<br><br>';
        }

        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user&user_login=' . $user_login . '" method="POST">';
        $return .= '<input class="btn" type="submit" value="' . _('form_retour') . '">';
        $return .= '</form>';
        return $return;
    }

    public static function traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();
        $return = '';

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs();

        while($elem_tableau = each($tab_radio_traite_demande)) {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $user_nb_jours_pris_float=(float) $user_nb_jours_pris ;
            $value_type_abs_id=$champs[2];
            $date_deb=$champs[3];
            $demi_jour_deb=$champs[4];
            $date_fin=$champs[5];
            $demi_jour_fin=$champs[6];
            $reponse=$champs[7];
            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;

            if($reponse == "ACCEPTE") // acceptation definitive d'un conges
            {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat="ok", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';
                $ReqLog1 = \includes\SQL::query($sql1);

                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    /* UPDATE table "conges_solde_user" (jours restants) */
                    // on retranche les jours seulement pour des conges pris (pas pour les absences)
                    // donc seulement si le type de l'absence qu'on accepte est un "conges"
                    if(($tab_tout_type_abs[$value_type_abs_id]['type']=="conges")||($tab_tout_type_abs[$value_type_abs_id]['type']=="conges_exceptionnels")) {
                        soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $value_type_abs_id, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin);
                    }

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_valid_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges");
                    }
                }
            }
            elseif($reponse == "VALID") // première validation dans le cas d'une double validation
            {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat="valid", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND p_etat=\'demande\';';
                $ReqLog1 = \includes\SQL::query($sql1);

                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_valid_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges");
                    }
                }
            }
            elseif($reponse == "REFUSE") // refus d'un conges
            {
                // recup di motif de refus
                $motif_refus = addslashes($tab_text_refus[$numero_int]);
                $sql3 = 'UPDATE conges_periode SET p_etat="refus", p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';
                $ReqLog3 = \includes\SQL::query($sql3);

                if ($ReqLog3 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_refus_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges");
                    }
                }
            }
        }
        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '&user_login=' . $user_login . '">';
        return $return;
    }

    public static function annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;
        $return = '';

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs();

        while($elem_tableau = each($tab_checkbox_annule)) {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris_float=$champs[1];
            $VerifDec=verif_saisie_decimal($user_nb_jours_pris_float);
            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            $user_type_abs_id=$champs[2];

            $motif_annul=addslashes($tab_text_annul[$numero_int]);

            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="annul", p_motif_refus="'. \includes\SQL::quote($motif_annul).'", p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" AND p_etat=\'ok\';';
            $ReqLog1 = \includes\SQL::query($sql1);

            if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                // Log de l'action
                log_action($numero_int,"annul", $user_login, "annulation conges $numero ($user_login) ($user_nb_jours_pris_float jours)");

                /* UPDATE table "conges_solde_user" (jours restants) */
                // on re-crédite les jours seulement pour des conges pris (pas pour les absences)
                // donc seulement si le type de l'absence qu'on annule est un "conges"
                if($tab_tout_type_abs[$user_type_abs_id]['type']=="conges") {
                    $sql2 = 'UPDATE conges_solde_user SET su_solde = su_solde+"'. \includes\SQL::quote($user_nb_jours_pris_float).'" WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_id="'. \includes\SQL::quote($user_type_abs_id).'";';
                    $ReqLog2 = \includes\SQL::query($sql2);
                }

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_annul_conges_alerte_user']) {
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "annul_conges");
                }
            }
        }
        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?session=' . $session . '&user_login=' . $user_login . '">';
        return $return;
    }

    //affiche l'état des conges du user (avec le formulaire pour le responsable)
    public static function affiche_etat_conges_user_for_resp($user_login, $year_affichage, $tri_date, $onglet)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;
        $return = '';

        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;
        $return .= '<div class="calendar-nav">';
        $return .= '<ul>';
        $return .= '<li><a class="action previous" href="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user&user_login=' . $user_login . '&year_affichage=' . $year_affichage_prec . '"><i class="fa fa-chevron-left"></i></a></li>';
        $return .= '<li class="current-year">' . $year_affichage . '</li>';
        $return .= '<li><a class="action next" href="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user&user_login=' . $user_login . '&year_affichage=' . $year_affichage_suiv . '"><i class="fa fa-chevron-right"></i></a></li>';
        $return .= '</ul>';
        $return .= '</div>';

        $return .= '<h2>' . _('resp_traite_user_etat_conges') . $year_affichage . '</h2>';

        // Récupération des informations de speriodes de conges/absences
        $sql3 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num FROM conges_periode " .
            "WHERE p_login = '$user_login' " .
            "AND p_etat !='demande' " .
            "AND p_etat !='valid' " .
            "AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";
        if($tri_date=="descendant") {
            $sql3=$sql3." ORDER BY p_date_deb DESC ";
        } else {
            $sql3=$sql3." ORDER BY p_date_deb ASC ";
        }

        $ReqLog3 = \includes\SQL::query($sql3);

        $count3=$ReqLog3->num_rows;
        if($count3==0) {
            $return .= '<b>' . _('resp_traite_user_aucun_conges') . '</b><br><br>';
        } else {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_types_abs = recup_tableau_tout_types_abs() ;

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped'
            ]);
            $childTable = '<thead>';
            $childTable .= '<tr align="center">';
            $childTable .= '<th>';
            $childTable .= _('divers_debut_maj_1');
            $childTable .= '</th>';
            $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_nb_jours_pris_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_etat_maj_1') . '</th>';
            $childTable .= '<th>' . _('resp_traite_user_annul') . '</th>';
            $childTable .= '<th>' . _('resp_traite_user_motif_annul') . '</th>';
            if($_SESSION['config']['affiche_date_traitement']) {
                $childTable .= '<th>' . _('divers_date_traitement') . '</th>';
            }
            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';
            $tab_checkbox=array();
            $i = true;
            while ($resultat3 = $ReqLog3->fetch_array()) {
                $sql_login=$resultat3["p_login"] ;
                $sql_date_deb=eng_date_to_fr($resultat3["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat3["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                $sql_date_fin=eng_date_to_fr($resultat3["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat3["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }
                $sql_nb_jours=affiche_decimal($resultat3["p_nb_jours"]) ;
                $sql_commentaire=$resultat3["p_commentaire"] ;
                $sql_type=$resultat3["p_type"] ;
                $sql_etat=$resultat3["p_etat"] ;
                $sql_motif_refus=$resultat3["p_motif_refus"] ;
                $sql_p_date_demande = $resultat3["p_date_demande"];
                $sql_p_date_traitement = $resultat3["p_date_traitement"];
                $sql_num=$resultat3["p_num"] ;

                if(($sql_etat=="annul") || ($sql_etat=="refus") || ($sql_etat=="ajout")) {
                    $casecocher1="";
                    if($sql_etat=="refus") {
                        if($sql_motif_refus=="") {
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        }
                        //$text_annul="<i>motif du refus : $sql_motif_refus</i>";
                        $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                    } elseif($sql_etat=="annul") {
                        if($sql_motif_refus=="")
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        //$text_annul="<i>motif de l'annulation : $sql_motif_refus</i>";
                        $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                    } elseif($sql_etat=="ajout") {
                        $text_annul="&nbsp;";
                    }
                } else {
                    $casecocher1=sprintf("<input type=\"checkbox\" name=\"tab_checkbox_annule[$sql_num]\" value=\"$sql_login--$sql_nb_jours--$sql_type--ANNULE\">");
                    $text_annul="<input type=\"text\" name=\"tab_text_annul[$sql_num]\" size=\"20\" max=\"100\">";
                }

                $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $childTable .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td>';
                $childTable .= '<td>' . $sql_date_fin . '_' . $demi_j_fin . '</td>';
                $childTable .= '<td>' . $sql_nb_jours . '</td>';
                $childTable .= '<td>' . $sql_commentaire . '</td>';
                $childTable .= '<td>' . $tab_types_abs[$sql_type]['libelle'] . '</td>';
                $childTable .= '<td>';
                if($sql_etat=="refus") {
                    $childTable .= _('divers_refuse') ;
                } elseif($sql_etat=="annul") {
                    $childTable .= _('divers_annule') ;
                } else {
                    $childTable .= $sql_etat;
                }
                $childTable .= '</td>';
                $childTable .= '<td>' . $casecocher1 . '</td>';
                $childTable .= '<td>' . $text_annul . '</td>';
                if($_SESSION['config']['affiche_date_traitement']) {
                    if(empty($sql_p_date_traitement)) {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                    } else {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    }
                }
                $childTable .= '</tr>';
                $i = !$i;
            }
            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
            $return .= '</form>';
        }
        return $return;
    }

    //affiche l'état des demande en attente de 2ieme validation du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_2_valid_user_for_resp($user_login)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;
        $return = '';

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
            "FROM conges_periode " .
            "WHERE p_login = '$user_login' AND p_etat ='valid' ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0) {
            $return .= '<b>' . _('resp_traite_user_aucune_demande') . '</b><br><br>';
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped',
            ]);
            $childTable .= '<thead>';
            $childTable .= '<tr align="center">';
            $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_nb_jours_pris_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_refuser_maj_1') . '</th>';
            $childTable .= '<th>' . _('resp_traite_user_motif_refus') . '</th>';
            if($_SESSION['config']['affiche_date_traitement']) {
                $childTable .= '<th>' . _('divers_date_traitement') . '</th>';
            }
            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_date_deb = $resultat2["p_date_deb"];
                $sql_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                $sql_date_fin = $resultat2["p_date_fin"];
                $sql_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }
                $sql_nb_jours=affiche_decimal($resultat2["p_nb_jours"]) ;
                $sql_commentaire=$resultat2["p_commentaire"] ;
                $sql_type=$resultat2["p_type"] ;
                $sql_date_demande = $resultat2["p_date_demande"];
                $sql_date_traitement = $resultat2["p_date_traitement"];
                $sql_num=$resultat2["p_num"] ;

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";


                $casecocher1 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                $casecocher2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";
                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $childTable .= '<td>' . $sql_date_deb_fr . '_' . $demi_j_deb . '</td>';
                $childTable .= '<td>' . $sql_date_fin_fr . '_' . $demi_j_fin . '</td>';
                $childTable .= '<td>' . $sql_nb_jours . '</td>';
                $childTable .= '<td>' . $sql_commentaire . '</td>';
                $childTable .= '<td>' . $tab_type_all_abs[$sql_type]['libelle'] . '</td>';
                $childTable .= '<td>' . $casecocher1 . '</td>';
                $childTable .= '<td>' . $casecocher2 . '</td>';
                $childTable .= '<td>' . $text_refus . '</td>';
                if($_SESSION['config']['affiche_date_traitement']) {
                    if(empty($sql_date_traitement)) {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                    } else {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_date_traitement . '</td>';
                    }
                }
                $childTable .= '</tr>';
                $i = !$i;
            }
            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
            $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '">' . _('form_cancel') . '</a>';
            $return .= ' </form>';
        }
        return $return;
    }

    //affiche l'état des demandes du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();
        $return = '';

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
            "FROM conges_periode " .
            "WHERE p_login = '$user_login' AND p_etat ='demande' ".
            "ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0) {
            $return .= '<p><strong>' . _('resp_traite_user_aucune_demande') . '</strong></p>';
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=traite_user" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped'
            ]);
            $childTable = '<tr align="center">';
            $childTable .= '<td>' . _('divers_debut_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_fin_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_nb_jours_pris_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_comment_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_type_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_accepter_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_refuser_maj_1') . '</td>';
            $childTable .= '<td>' . _('resp_traite_user_motif_refus') . '</td>';
            if($_SESSION['config']['affiche_date_traitement']) {
                $childTable .= '<td>' . _('divers_date_traitement') . '</td>';
            }
            $childTable .= '</tr>';

            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_date_deb = $resultat2["p_date_deb"];
                $sql_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                $sql_date_fin = $resultat2["p_date_fin"];
                $sql_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }
                $sql_nb_jours=affiche_decimal($resultat2["p_nb_jours"]) ;
                $sql_commentaire=$resultat2["p_commentaire"] ;
                $sql_type=$resultat2["p_type"] ;
                $sql_date_demande = $resultat2["p_date_demande"];
                $sql_date_traitement = $resultat2["p_date_traitement"];
                $sql_num=$resultat2["p_num"] ;

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";

                // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
                if($tab_user['double_valid'] == "Y") {
                    /*******************************/
                    /* verif si le resp est grand_responsable pour ce user*/
                    if(in_array($_SESSION['userlogin'], $tab_grd_resp)) { // si user_login est dans le tableau des grand responsable
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                    } else {
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--VALID\">";
                    }
                } else {
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                }

                $boutonradio2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";

                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                $childTable .= '<tr align="center">';
                $childTable .= '<td>' . $sql_date_deb_fr . '_' . $demi_j_deb . '</td>';
                $childTable .= '<td>' . $sql_date_fin_fr . '_' . $demi_j_fin . '</td>';
                $childTable .= '<td>' . $sql_nb_jours . '</td>';
                $childTable .= '<td>' . $sql_commentaire . '</td>';
                $childTable .= '<td>' . $tab_type_all_abs[$sql_type]['libelle'] . '</td>';
                $childTable .= '<td>' . $boutonradio1 . '</td>';
                $childTable .= '<td>' . $boutonradio2 . '</td>';
                $childTable .= '<td>' . $text_refus . '</td>';
                $childTable .= '<td>' . $sql_date_demande . '</td>';

                if($_SESSION['config']['affiche_date_traitement']) {
                    if(empty($sql_date_traitement)) {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                    } else {
                        $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_date_traitement . '</td>';
                    }
                }

                $childTable .= '</tr>';
            }
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
            $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '">' . _('form_cancel') . '</a>';
            $return .= '</form>';
        }
        return $return;
    }

    public static function affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();
        $return = '';

        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        if(!isset($_SESSION["tab_j_feries"])) {
            init_tab_jours_feries();
        }

        /********************/
        /* Récupération des informations sur le user : */
        /********************/
        $list_group_dbl_valid_du_resp = get_list_groupes_double_valid_du_resp($_SESSION['userlogin']);
        $tab_user=array();
        $tab_user = recup_infos_du_user($user_login, $list_group_dbl_valid_du_resp);

        $list_all_users_du_resp=get_list_all_users_du_resp($_SESSION['userlogin']);

        // recup des grd resp du user
        $tab_grd_resp=array();
        if($_SESSION['config']['double_validation_conges']) {
            get_tab_grd_resp_du_user($user_login, $tab_grd_resp);
        }

        /********************/
        /* Titre */
        /********************/
        $return .= '<h1>' . $tab_user['prenom'] . ' ' . $tab_user['nom'] . '</h1>';


        /********************/
        /* Bilan des Conges */
        /********************/
        // AFFICHAGE TABLEAU
        // affichage du tableau récapitulatif des solde de congés d'un user
        $return .= affiche_tableau_bilan_conges_user($user_login);
        $return .= '<hr/>';

        /*************************/
        /* SAISIE NOUVEAU CONGES */
        /*************************/
        // dans le cas ou les users ne peuvent pas saisir de demande, le responsable saisi les congès :
        if( !$_SESSION['config']['user_saisie_demande'] || $_SESSION['config']['resp_saisie_mission'] ) {
            // si les mois et année ne sont pas renseignés, on prend ceux du jour
            if($year_calendrier_saisie_debut==0) {
                $year_calendrier_saisie_debut=date("Y");
            }
            if($mois_calendrier_saisie_debut==0) {
                $mois_calendrier_saisie_debut=date("m");
            }
            if($year_calendrier_saisie_fin==0) {
                $year_calendrier_saisie_fin=date("Y");
            }
            if($mois_calendrier_saisie_fin==0) {
                $mois_calendrier_saisie_fin=date("m");
            }

            $return .= '<h2>' . _('resp_traite_user_new_conges') . '</h2>';

            //affiche le formulaire de saisie d'une nouvelle demande de conges ou d'un  nouveau conges
            $onglet = "traite_user";
            $return .= saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);

            $return .= '<hr/>';
        }

        /*********************/
        /* Etat des Demandes */
        /*********************/
        if($_SESSION['config']['user_saisie_demande']) {
            //verif si le user est bien un user du resp (et pas seulement du grand resp)
            if(strstr($list_all_users_du_resp, "'$user_login'")!=FALSE) {
                $return .= '<h2>' . _('resp_traite_user_etat_demandes') . '</h2>';

                //affiche l'état des demandes du user (avec le formulaire pour le responsable)
                $return .= \responsable\Fonctions::affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp);

                $return .= '<hr/>';
            }
        }

        /*********************/
        /* Etat des Demandes en attente de 2ieme validation */
        /*********************/
        if($_SESSION['config']['double_validation_conges']) {
            /*******************************/
            /* verif si le resp est grand_responsable pour ce user*/

            if(in_array($_SESSION['userlogin'], $tab_grd_resp)) // si resp_login est dans le tableau
            {
                $return .= '<h2>' . _('resp_traite_user_etat_demandes_2_valid') . '</h2>';

                //affiche l'état des demande en attente de 2ieme valid du user (avec le formulaire pour le responsable)
                $return .= \responsable\Fonctions::affiche_etat_demande_2_valid_user_for_resp($user_login);

                $return .= '<hr/>';
            }
        }

        /*******************/
        /* Etat des Conges */
        /*******************/
        //affiche l'état des conges du user (avec le formulaire pour le responsable)
        $onglet = "traite_user";
        $return .= \responsable\Fonctions::affiche_etat_conges_user_for_resp($user_login,  $year_affichage, $tri_date, $onglet);
        return $return;
    }

    /**
     * Encapsule le comportement du module de gestion des congés des utilisateurs
     *
     * @return void
     * @access public
     * @static
     */
    public static function traiteUserModule()
    {
        //var pour resp_traite_user.php
        $user_login   = getpost_variable('user_login') ;
        $year_calendrier_saisie_debut = getpost_variable('year_calendrier_saisie_debut', 0) ;
        $mois_calendrier_saisie_debut = getpost_variable('mois_calendrier_saisie_debut', 0) ;
        $year_calendrier_saisie_fin = getpost_variable('year_calendrier_saisie_fin', 0) ;
        $mois_calendrier_saisie_fin = getpost_variable('mois_calendrier_saisie_fin', 0) ;
        $tri_date = getpost_variable('tri_date', "ascendant") ;
        $tab_checkbox_annule = getpost_variable('tab_checkbox_annule') ;
        $tab_radio_traite_demande = getpost_variable('tab_radio_traite_demande') ;
        $tab_text_refus = getpost_variable('tab_text_refus') ;
        $tab_text_annul = getpost_variable('tab_text_annul') ;
        $new_demande_conges = getpost_variable('new_demande_conges', 0) ;
        $new_debut = getpost_variable('new_debut') ;
        $new_demi_jour_deb = getpost_variable('new_demi_jour_deb') ;
        $new_fin = getpost_variable('new_fin') ;
        $new_demi_jour_fin = getpost_variable('new_demi_jour_fin') ;
        $return = '';

        if($_SESSION['config']['disable_saise_champ_nb_jours_pris']) { // zone de texte en readonly et grisée
            $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $comment);
        } else {
            $new_nb_jours = getpost_variable('new_nb_jours') ;
        }

        $new_comment = getpost_variable('new_comment') ;
        $new_type = getpost_variable('new_type') ;
        $year_affichage = getpost_variable('year_affichage' , date("Y") );

        /*************************************/

        if ( !is_resp_of_user($_SESSION['userlogin'] , $user_login)) {
            redirect(ROOT_PATH . 'deconnexion.php');
            exit;
        }

        /************************************/


        // si une annulation de conges a été selectionée :
        if($tab_checkbox_annule!="") {
            $return .= \responsable\Fonctions::annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul);
        }
        // si le traitement des demandes a été selectionée :
        elseif($tab_radio_traite_demande!="") {
            $return .= \responsable\Fonctions::traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus);
        }
        // si un nouveau conges ou absence a été saisi pour un user :
        elseif($new_demande_conges==1) {
            $return .= \responsable\Fonctions::new_conges($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type);
        } else {
            $return .= \responsable\Fonctions::affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date);
        }
        return $return;
    }

    public static function traite_all_demande_en_cours( $tab_bt_radio, $tab_text_refus)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        while($elem_tableau = each($tab_bt_radio)) {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $type_abs=$champs[2];   // id du type de conges demandé
            $date_deb=$champs[3];
            $demi_jour_deb=$champs[4];
            $date_fin=$champs[5];
            $demi_jour_fin=$champs[6];
            $reponse=$champs[7];

            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            $return .= $numero . '---' . $user_login . '---' . $user_nb_jours_pris . '---' . $reponse . '<br>';

            /* Modification de la table conges_periode */
            if(strcmp($reponse, "VALID")==0) {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat=\'valid\', p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" AND p_etat=\'demande\';';
                /* On valide l'UPDATE dans la table "conges_periode" ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {

                    // Log de l'action
                    log_action($numero_int, "valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse");

                    //envoi d'un mail d'alerte au user et au responsable du resp (pour double validation) (si demandé dans config de libertempo)
                    if($_SESSION['config']['mail_prem_valid_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges");
                    }
                }
            }

            if(strcmp($reponse, "OK")==0) {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat="ok", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';
                /* On valide l'UPDATE dans la table "conges_periode" ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {

                    // Log de l'action
                    log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse");

                    /* UPDATE table "conges_solde_user" (jours restants) */
                    soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin);

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_valid_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges");
                    }
                }
            } elseif(strcmp($reponse, "not_OK")==0) {
                // recup du motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql1 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';

                /* On valide l'UPDATE dans la table ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {

                    // Log de l'action
                    log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : refus");


                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_refus_conges_alerte_user']) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges");
                    }
                }
            }
        }
        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF  . '?session=' . $session . '&onglet=traitement_demandes">';
        return $return;
    }

    public static function affiche_all_demandes_en_cours($tab_type_conges)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $count1=0;
        $count2=0;
        $return = '';

        $tab_type_all_abs = recup_tableau_tout_types_abs();

        // recup du tableau des types de conges (seulement les conges exceptionnels)
        $tab_type_conges_exceptionnels=array();
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        }

        /*********************************/
        // Récupération des informations
        /*********************************/

        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
        $tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);

        // si tableau des users du resp n'est pas vide
        if( count($tab_all_users_du_resp) !=0 ) {
            // constitution de la liste (séparé par des virgules) des logins ...
            $list_users_du_resp="";
            foreach($tab_all_users_du_resp as $current_login => $tab_current_user) {
                if($list_users_du_resp=="") {
                    $list_users_du_resp= "'$current_login'" ;
                } else {
                    $list_users_du_resp=$list_users_du_resp.", '$current_login'" ;
                }
            }
        }


        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est GRAND responsable
        if( $_SESSION['config']['double_validation_conges'] ) {
            $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

            // si tableau des users du grand resp n'est pas vide
            if( count($tab_all_users_du_grand_resp)!=0 ) {
                // constitution de la liste (séparé par des virgules) des logins ...
                $list_users_du_grand_resp="";
                foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user) {
                    if($list_users_du_grand_resp=="") {
                        $list_users_du_grand_resp= "'$current_login'" ;
                    } else {
                        $list_users_du_grand_resp=$list_users_du_grand_resp.", '$current_login'" ;
                    }
                }
            }
        }

        /*********************************/

        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=traitement_demandes" method="POST">';

        /*********************************/
        /* TABLEAU DES DEMANDES DES USERS DONT ON EST LE RESP */
        /*********************************/

        // si tableau des users du resp n'est pas vide
        if( count($tab_all_users_du_resp)!=0 ) {

            // Récup des demandes en cours pour les users dont $_SESSION['userlogin'] est responsable :
            $sql1 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
            $sql1=$sql1." WHERE p_etat =\"demande\" ";
            $sql1=$sql1." AND p_login IN ($list_users_du_resp)  ";
            $sql1=$sql1." ORDER BY p_num";

            $ReqLog1 = \includes\SQL::query($sql1) ;

            $count1=$ReqLog1->num_rows;
            if($count1!=0) {
                // AFFICHAGE TABLEAU DES DEMANDES EN COURS

                $return .= '<h3>' . _('resp_traite_demandes_titre_tableau_1') . '</h3>';
                $table = new \App\Libraries\Structure\Table();
                $table->addClasses([
                    'table',
                    'table-hover',
                    'table-responsive',
                    'table-condensed',
                    'table-striped'
                ]);
                $childTable = '<thead>';
                $childTable .= '<tr>';
                $childTable .= '<th>' . _('divers_nom_maj_1') . '<br>' . _('divers_prenom_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>' ;
                $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
                $childTable .= '<th>' . _('resp_traite_demandes_nb_jours') . '</th>';
                // foreach($tab_type_conges as $id_conges => $libelle)
                // {
                // 	echo "<th>". _('divers_solde_maj_1') ."<br>$libelle</th>" ;
                // }
                // if ($_SESSION['config']['gestion_conges_exceptionnels'])
                // foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
                // {
                // 	echo "<th>". _('divers_solde_maj_1') ."<br>$libelle</th>" ;
                // }
                $childTable .= '<th>' . _('divers_solde') . '</th>';
                $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_refuser_maj_1') . '</th>';
                $childTable .= '<th>' . _('resp_traite_demandes_attente') . '</th>' ;
                $childTable .= '<th>' . _('resp_traite_demandes_motif_refus') . '</th>';
                if($_SESSION['config']['affiche_date_traitement']) {
                    $childTable .= '<th>' . _('divers_date_traitement') . '</th>';
                }
                $childTable .= '</tr>';
                $childTable .= '</thead>';
                $childTable .= '<tbody>';

                $i = true;
                $tab_bt_radio=array();
                while ($resultat1 = $ReqLog1->fetch_array()) {
                    /** sur la ligne ,   **/
                    /** le 1er bouton radio est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--OK"> */
                    /**  et le 2ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--not_OK"> */
                    /**  et le 3ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--RIEN"> */

                    $sql_p_date_deb = $resultat1["p_date_deb"];
                    $sql_p_date_deb_fr = eng_date_to_fr($resultat1["p_date_deb"]);
                    $sql_p_demi_jour_deb=$resultat1["p_demi_jour_deb"] ;
                    if($sql_p_demi_jour_deb=="am") $demi_j_deb="matin";  else $demi_j_deb="après-midi";
                    $sql_p_date_fin = $resultat1["p_date_fin"];
                    $sql_p_date_fin_fr = eng_date_to_fr($resultat1["p_date_fin"]);
                    $sql_p_demi_jour_fin=$resultat1["p_demi_jour_fin"] ;
                    if($sql_p_demi_jour_fin=="am") $demi_j_fin="matin";  else $demi_j_fin="après-midi";
                    $sql_p_commentaire = $resultat1["p_commentaire"];
                    $sql_p_num = $resultat1["p_num"];
                    $sql_p_login = $resultat1["p_login"];
                    $sql_p_nb_jours = affiche_decimal($resultat1["p_nb_jours"]);
                    $sql_p_type = $resultat1["p_type"];
                    $sql_p_date_demande = $resultat1["p_date_demande"];
                    $sql_p_date_traitement = $resultat1["p_date_traitement"];

                    // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                    $chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";

                    // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
                    if($tab_all_users_du_resp[$sql_p_login]['double_valid'] == "Y") {
                        // si on est a la fois resp et grand resp
                        if( (count($tab_all_users_du_grand_resp)!=0 ) && (array_key_exists($sql_p_login, $tab_all_users_du_grand_resp)) ) {
                            $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
                        } else { //on est QUe resp
                            $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--VALID\">";
                        }
                    } else {
                        $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
                    }

                    $boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
                    $boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";

                    $text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

                    $childTable .= '<tr class="'.($i?'i':'p').'">';
                    $childTable .= '<td><b>' . $tab_all_users_du_resp[$sql_p_login]['nom'] . '</b><br>' . $tab_all_users_du_resp[$sql_p_login]['prenom'] . '</td><td>' . $tab_all_users_du_resp[$sql_p_login]['quotite'] . '%</td>';
                    $childTable .= '<td>' . $tab_type_all_abs[$sql_p_type]['libelle'] . '</td>';
                    $childTable .= '<td>' . $sql_p_date_deb_fr . '<span class="demi">' . $demi_j_deb . '</span></td><td>' . $sql_p_date_fin_fr . '<span class="demi">' . $demi_j_fin . '</span></td><td>' . $sql_p_commentaire . '</td><td><b>' . $sql_p_nb_jours . '</b></td>';
                    $tab_conges=$tab_all_users_du_resp[$sql_p_login]['conges'];
                    $childTable .= '<td>' . $tab_conges[$tab_type_all_abs[$sql_p_type]['libelle']]['solde'] . '</td>';
                    // foreach($tab_type_conges as $id_conges => $libelle)
                    // {
                    // 	echo "<td>".$tab_conges[$libelle]['solde']."</td>";
                    // }
                    // if ($_SESSION['config']['gestion_conges_exceptionnels'])
                    // foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
                    // {
                    // 	echo "<td>".$tab_conges[$libelle]['solde']."</td>";
                    // }
                    // echo "<td>".$tab_type_all_abs[$sql_p_type]['libelle']."</td>\n";
                    $childTable .= '<td>' . $boutonradio1 . '</td><td>' . $boutonradio2 . '</td><td>' . $boutonradio3 . '</td><td>' . $text_refus . '</td>';
                    if($_SESSION['config']['affiche_date_traitement']) {
                        if($sql_p_date_demande == NULL) {
                            $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                        } else {
                            $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                        }
                    }

                    $childTable .= '</tr>';
                    $i = !$i;
                } // while
                $childTable .= '</tbody>';
                $table->addChild($childTable);
                ob_start();
                $table->render();
                $return .= ob_get_clean();
            } //if($count1!=0)
        } //if( count($tab_all_users_du_resp)!=0 )


        /*********************************/
        /* TABLEAU DES DEMANDES DES USERS DONT ON EST LE GRAND RESP */
        /*********************************/

        if($_SESSION['config']['double_validation_conges']) {

            // si tableau des users du grand resp n'est pas vide
            if( count($tab_all_users_du_grand_resp)!=0 ) {

                // Récup des demandes en cours pour les users dont $_SESSION['userlogin'] est GRAND responsable :
                $sql2 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
                $sql2=$sql2." WHERE p_etat =\"valid\" ";
                $sql2=$sql2." AND p_login IN ($list_users_du_grand_resp) ";
                $sql2=$sql2." ORDER BY p_num";

                $ReqLog2 = \includes\SQL::query($sql2) ;

                $count2=$ReqLog2->num_rows;
                if($count2!=0) {
                    // AFFICHAGE TABLEAU DES DEMANDES EN COURS POUR DEUXIEME VALIDATION

                    $return .= '<h3>' . _('resp_traite_demandes_titre_tableau_2') . '</h3>';

                    $table = new \App\Libraries\Structure\Table();
                    $table->addClasses([
                        'table',
                        'table-hover',
                        'table-responsive',
                        'table-condensed',
                        'table-striped'
                    ]);
                    $childTable = '<thead>';
                    $childTable .= '<tr>';
                    $childTable .= '<th><b>' . _('divers_nom_maj_1') . '</b><br>' . _('divers_prenom_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
                    $childTable .= '<th>' . _('resp_traite_demandes_nb_jours') . '</th>';
                    foreach($tab_type_conges as $id_conges => $libelle) {
                        $childTable .= '<th>' . _('divers_solde_maj_1') . '<br>' . $libelle . '</th>';
                    }
                    $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th>';
                    $childTable .= '<th>' . _('divers_refuser_maj_1') . '</th>';
                    $childTable .= '<th>' . _('resp_traite_demandes_attente') . '</th>';
                    $childTable .= '<th>' . _('resp_traite_demandes_motif_refus') . '</th>';
                    if($_SESSION['config']['affiche_date_traitement']) {
                        $childTable .= '<th>' . _('divers_date_traitement') . '</th>';
                    }
                    $childTable .= '</tr>';
                    $childTable .= '</thead>';
                    $childTable .= '<tbody>';

                    $i = true;
                    $tab_bt_radio=array();
                    while ($resultat2 = $ReqLog2->fetch_array()) {
                        /** sur la ligne ,   **/
                        /** le 1er bouton radio est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--OK"> */
                        /**  et le 2ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--not_OK"> */
                        /**  et le 3ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--RIEN"> */

                        $sql_p_date_deb = $resultat2["p_date_deb"];
                        $sql_p_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]);
                        $sql_p_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                        if($sql_p_demi_jour_deb=="am") $demi_j_deb="matin";  else $demi_j_deb="après-midi";
                        $sql_p_date_fin = $resultat2["p_date_fin"];
                        $sql_p_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]);
                        $sql_p_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                        if($sql_p_demi_jour_fin=="am") $demi_j_fin="matin";  else $demi_j_fin="après-midi";
                        $sql_p_commentaire = $resultat2["p_commentaire"];
                        $sql_p_num = $resultat2["p_num"];
                        $sql_p_login = $resultat2["p_login"];
                        $sql_p_nb_jours = affiche_decimal($resultat2["p_nb_jours"]);
                        $sql_p_type = $resultat2["p_type"];
                        $sql_p_date_demande = $resultat2["p_date_demande"];
                        $sql_p_date_traitement = $resultat2["p_date_traitement"];

                        // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                        $chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";

                        $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
                        $boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
                        $boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";

                        $text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

                        $childTable .= '<tr class="'.($i?'i':'p').'">';
                        $childTable .= '<td><strong>' . $tab_all_users_du_grand_resp[$sql_p_login]['nom'] . '</strong><br>' . $tab_all_users_du_grand_resp[$sql_p_login]['prenom'] . '</td><td>' . $tab_all_users_du_grand_resp[$sql_p_login]['quotite'] . '%</td>';
                        $childTable .= '<td>' . $sql_p_date_deb_fr . '<span class="demi">' . $demi_j_deb . '<span></td><td>' . $sql_p_date_fin_fr . '<span class="demi">' . $demi_j_fin . '</span></td><td>' . $sql_p_commentaire . '</td><td><b>' . $sql_p_nb_jours . '</b></td>';
                        $tab_conges=$tab_all_users_du_grand_resp[$sql_p_login]['conges'];
                        foreach($tab_type_conges as $id_conges => $libelle) {
                            $childTable .= '<td>' . $tab_conges[$libelle]['solde'] . '</td>';
                        }
                        $childTable .= '<td>' . $tab_type_all_abs[$sql_p_type]['libelle'] . '</td>';
                        $childTable .= '<td>' . $boutonradio1 . '</td><td>' . $boutonradio2 . '</td><td>' . $boutonradio3 . '</td><td>' . $text_refus . '</td>';
                        if($_SESSION['config']['affiche_date_traitement']) {
                            $childTable .= '<td class="histo-left">' . _('divers_demande') . ' : '. $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                        }

                        $childTable .= '</tr>';
                        $i = !$i;
                    } //while
                    $childTable .= '</tbody>';
                    $table->addChild($childTable);
                    ob_start();
                    $table->render();
                    $return .= ob_get_clean();
                } //if($count2!=0)
            } //if( count($tab_all_users_du_grand_resp)!=0 )
        } //if($_SESSION['config']['double_validation_conges'])

        $return .= '<br>';

        if(($count1==0) && ($count2==0)) {
            $return .= '<strong>' . _('resp_traite_demandes_aucune_demande') . '</strong>';
        } else {
            $return .= '<hr/>';
            $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        }

        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de traitement des congés des utilisateurs sous la responsabilité du responsable
     *
     * @return void
     * @access public
     * @static
     */
    public static function traitementDemandesModule($tab_type_cong)
    {
        //var pour resp_traite_demande_all.php
        $tab_bt_radio   = getpost_variable('tab_bt_radio');
        $tab_text_refus = getpost_variable('tab_text_refus');
        $return = '';

        // titre
        $return .= '<h1>' . _('resp_traite_demandes_titre') . '</h1>';


        // si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
        if($tab_bt_radio=="") {
            $return .= \responsable\Fonctions::affiche_all_demandes_en_cours($tab_type_cong);
        } else {
            $return .= \responsable\Fonctions::traite_all_demande_en_cours( $tab_bt_radio, $tab_text_refus);
        }
        return $return;
    }
}
