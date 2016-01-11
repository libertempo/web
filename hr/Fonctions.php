<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
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
namespace hr;

/**
 * Regroupement des fonctions liées au haut responsable
 */
class Fonctions
{
    /**
     * Encapsule le comportement du module de page principale
     *
     * @param array  $tab_type_cong
     * @param array  $tab_type_conges_exceptionnels
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pagePrincipaleModule(array $tab_type_cong, array $tab_type_conges_exceptionnels, $session, $DEBUG = false)
    {
        /***********************************/
        // AFFICHAGE ETAT CONGES TOUS USERS
        /***********************************/
        // AFFICHAGE TABLEAU (premiere ligne)
        echo '<h2>'. _('hr_traite_user_etat_conges') ."</H2>\n\n";
        echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
        echo '<thead>';
        echo '<tr>';
        echo '<th>'. _('divers_nom_maj') .'</th>';
        echo '<th>'. _('divers_prenom_maj') .'</th>';
        echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
        $nb_colonnes = 3;
        foreach($tab_type_cong as $id_conges => $libelle) {
            // cas d'une absence ou d'un congé
            echo "<th> $libelle"." / ". _('divers_an_maj') .'</th>';
            echo '<th>'. _('divers_solde_maj') ." ".$libelle .'</th>';
            $nb_colonnes += 2;
        }
        // conges exceptionnels
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
                echo '<th>'. _('divers_solde_maj') ." $libelle</th>\n";
                $nb_colonnes += 1;
            }
        }
        echo '<th></th>';
        $nb_colonnes += 1;
        if($_SESSION['config']['editions_papier']) {
            echo '<th></th>';
            $nb_colonnes += 1;
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        /***********************************/
        // AFFICHAGE USERS
        /***********************************/
        // AFFICHAGE DE USERS DIRECTS DU RESP

        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
        $tab_all_users=recup_infos_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
        if( $DEBUG ) {echo "tab_all_users :<br>\n";  print_r($tab_all_users); echo "<br>\n"; }

        if(count($tab_all_users)==0) // si le tableau est vide (resp sans user !!) on affiche une alerte !
            echo "<tr><td class=\"histo\" colspan=\"".$nb_colonnes."\">". _('resp_etat_aucun_user') ."</td></tr>\n" ;
        else {
            //$i = true;
            foreach($tab_all_users as $current_login => $tab_current_user) {
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];
                $text_affich_user="<a href=\"hr_index.php?session=$session&onglet=traite_user&user_login=$current_login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a>" ;
                $text_edit_papier="<a href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
                if($tab_current_user['is_active'] == "Y" || $_SESSION['config']['print_disable_users'] == 'TRUE')
                    { echo '<tr>'; }
                else
                    { echo '<tr class="hidden">'; }
                echo '<td>'.$tab_current_user['nom']."</td><td>".$tab_current_user['prenom']."</td><td>".$tab_current_user['quotite']."%</td>";
                foreach($tab_type_cong as $id_conges => $libelle) {
                    $nbAn = isset($tab_conges[$libelle]['nb_an'])
                        ? $tab_conges[$libelle]['nb_an']
                        : 0;
                    $solde = isset($tab_conges[$libelle]['solde'])
                        ? $tab_conges[$libelle]['solde']
                        : 0;
                    echo '<td>'.$nbAn.'</td>';
                    echo '<td>'. $solde .'</td>';
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                    foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
                    {
                        $solde = isset($tab_conges[$libelle]['solde'])
                            ? $tab_conges[$libelle]['solde']
                            : 0;
                        echo '<td>' . $solde .'</td>';
                    }
                }
                echo "<td>$text_affich_user</td>\n";
                if($_SESSION['config']['editions_papier'])
                echo "<td>$text_edit_papier</td>";
                echo '</tr>';
                //$i = !$i;
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '<script>
        $(document).ready(function()
            {
            $("tr:not(.hidden):odd").css("background-color", "#F4F4F4");
            $("#display_hidden").click(function () {
                $(".hidden").slideToggle();
                });
            });
        </script>';
    }

    public static function traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        while($elem_tableau = each($tab_bt_radio)) {
            $champs             = explode("--", $elem_tableau['value']);
            $user_login         = $champs[0];
            $user_nb_jours_pris = $champs[1];
            $type_abs           = $champs[2];   // id du type de conges demandé
            $date_deb           = $champs[3];
            $demi_jour_deb      = $champs[4];
            $date_fin           = $champs[5];
            $demi_jour_fin      = $champs[6];
            $reponse            = $champs[7];

            $numero             = $elem_tableau['key'];
            $numero_int         = (int) $numero;
            echo "$numero---$user_login---$user_nb_jours_pris---$reponse<br>\n";

            /* Modification de la table conges_periode */
            if(strcmp($reponse, "OK")==0) {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat=\'ok\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );' ;
                /* On valide l'UPDATE dans la table "conges_periode" ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {
                    // Log de l'action
                    log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse",  $DEBUG);

                    /* UPDATE table "conges_solde_user" (jours restants) */
                    soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_valid_conges_alerte_user'])
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges",  $DEBUG);
                }
            } elseif(strcmp($reponse, "not_OK")==0) {
                // recup du motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql1 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';

                /* On valide l'UPDATE dans la table ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : refus",  $DEBUG);


                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if($_SESSION['config']['mail_refus_conges_alerte_user'])
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges",  $DEBUG);
                }
            }
        }

        if( $DEBUG ) {
            echo "<form action=\"$PHP_SELF?sesssion=$session&onglet=traitement_demande\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
            echo "</form>\n" ;
        } else {
            echo  _('form_modif_ok') ."<br><br> \n";
            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&onglet=traitement_demandes\">";
        }
                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_refus_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges", $DEBUG);

    }

    public static function affiche_all_demandes_en_cours($tab_type_conges, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $count1=0;
        $count2=0;

        $tab_type_all_abs = recup_tableau_tout_types_abs();

        // recup du tableau des types de conges (seulement les conges exceptionnels)
        $tab_type_conges_exceptionnels=array();
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

        /*********************************/
        // Récupération des informations
        /*********************************/

        // Récup dans un tableau de tableau des informations de tous les users
        $tab_all_users=recup_infos_all_users($DEBUG);
        if( $DEBUG ) { echo "tab_all_users :<br>\n"; print_r($tab_all_users); echo "<br><br>\n";}

        // si tableau des users du resp n'est pas vide
        if( count($tab_all_users)!=0 ) {
            // constitution de la liste (séparé par des virgules) des logins ...
            $list_users="";
            foreach($tab_all_users as $current_login => $tab_current_user) {
                if($list_users=="")
                    $list_users= "'$current_login'" ;
                else
                    $list_users=$list_users.", '$current_login'" ;
            }
        }

        /*********************************/




        echo " <form action=\"$PHP_SELF?session=$session&onglet=traitement_demandes\" method=\"POST\"> \n" ;

        /*********************************/
        /* TABLEAU DES DEMANDES DES USERS*/
        /*********************************/

        // si tableau des users n'est pas vide :)
        if( count($tab_all_users)!=0 ) {

            // Récup des demandes en cours pour les users :
            $sql1 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
            $sql1=$sql1." WHERE p_etat =\"demande\" ";
            $sql1=$sql1." AND p_login IN ($list_users) ";
            $sql1=$sql1." ORDER BY p_num";

            $ReqLog1 = \includes\SQL::query($sql1) ;

            $count1 = $ReqLog1->num_rows;
            if($count1!=0) {
                // AFFICHAGE TABLEAU DES DEMANDES EN COURS

                echo "<h3>". _('resp_traite_demandes_titre_tableau_1') ."</h3>\n" ;
                echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\">\n" ;
                echo '<thead>' ;
                echo '<tr>' ;
                echo '<th>'. _('divers_nom_maj_1') ."<br>". _('divers_prenom_maj_1') .'</th>' ;
                echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
                echo "<th>". _('divers_type_maj_1') ."</th>\n" ;
                echo '<th>'. _('divers_debut_maj_1') .'</th>' ;
                echo '<th>'. _('divers_fin_maj_1') .'</th>' ;
                echo '<th>'. _('divers_comment_maj_1') .'</th>' ;
                echo '<th>'. _('resp_traite_demandes_nb_jours') .'</th>';
                echo "<th>". _('divers_solde') ."</th>\n" ;
                echo '<th>'. _('divers_accepter_maj_1') .'</th>' ;
                echo '<th>'. _('divers_refuser_maj_1') .'</th>' ;
                echo '<th>'. _('resp_traite_demandes_attente') .'</th>' ;
                echo '<th>'. _('resp_traite_demandes_motif_refus') .'</th>' ;
                if( $_SESSION['config']['affiche_date_traitement'] )
                echo '<th>'. _('divers_date_traitement') .'</th>' ;
                echo '</tr>';
                echo '</thead>' ;
                echo '<tbody>' ;
                $i = true;
                $tab_bt_radio=array();
                while ($resultat1 = $ReqLog1->fetch_array()) {
                    /** sur la ligne ,   **/
                    /** le 1er bouton radio est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--OK"> */
                    /**  et le 2ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--not_OK"> */
                    /**  et le 3ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--RIEN"> */

                    $sql_p_date_deb         = $resultat1["p_date_deb"];
                    $sql_p_date_fin         = $resultat1["p_date_fin"];
                    $sql_p_date_deb_fr      = eng_date_to_fr($resultat1["p_date_deb"]);
                    $sql_p_date_fin_fr      = eng_date_to_fr($resultat1["p_date_fin"]);
                    $sql_p_demi_jour_deb    = $resultat1["p_demi_jour_deb"] ;
                    $sql_p_demi_jour_fin    = $resultat1["p_demi_jour_fin"] ;
                    $sql_p_commentaire      = $resultat1["p_commentaire"];
                    $sql_p_num              = $resultat1["p_num"];
                    $sql_p_login            = $resultat1["p_login"];
                    $sql_p_nb_jours         = affiche_decimal($resultat1["p_nb_jours"]);
                    $sql_p_type             = $resultat1["p_type"];
                    $sql_p_date_demande     = $resultat1["p_date_demande"];
                    $sql_p_date_traitement  = $resultat1["p_date_traitement"];

                    if($sql_p_demi_jour_deb=="am")
                        $demi_j_deb="mat";
                    else
                        $demi_j_deb="aprm";

                    if($sql_p_demi_jour_fin=="am")
                        $demi_j_fin="mat";
                    else
                        $demi_j_fin="aprm";

                    // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                    $chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";
                    $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
                    $boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
                    $boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";
                    $text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

                    echo '<tr class="'.($i?'i':'p').'">';
                    echo "<td><b>".$tab_all_users[$sql_p_login]['nom']."</b><br>".$tab_all_users[$sql_p_login]['prenom']."</td><td>".$tab_all_users[$sql_p_login]['quotite']."%</td>";
                    echo "<td>".$tab_type_all_abs[$sql_p_type]['libelle']."</td>\n";
                    echo "<td>$sql_p_date_deb_fr <span class=\"demi\">$demi_j_deb</span></td><td>$sql_p_date_fin_fr <span class=\"demi\">$demi_j_fin</span></td><td>$sql_p_commentaire</td><td><b>$sql_p_nb_jours</b></td>";
                    $tab_conges=$tab_all_users[$sql_p_login]['conges'];
                    echo "<td>".$tab_conges[$tab_type_all_abs[$sql_p_type]['libelle']]['solde']."</td>";
                    // foreach($tab_type_conges as $id_conges => $libelle)
                    // {
                    //     echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
                    // }

                    // if ($_SESSION['config']['gestion_conges_exceptionnels'])
                    //     foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
                    //     {
                    //         echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
                    //     }
                    // echo '<td>'.$tab_type_all_abs[$sql_p_type]['libelle'].'</td>';
                    echo "<td>$boutonradio1</td><td>$boutonradio2</td><td>$boutonradio3</td><td>$text_refus</td>\n";
                    if($_SESSION['config']['affiche_date_traitement']) {
                        if($sql_p_date_demande == NULL)
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                        else
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                    }
                    echo '</tr>' ;
                    $i = !$i;
                } // while
                echo '</tbody>' ;
                echo '</table>' ;
            } //if($count1!=0)
        } //if( count($tab_all_users)!=0 )

        echo "<br>\n";

        if(($count1==0) && ($count2==0))
            echo "<strong>". _('resp_traite_demandes_aucune_demande') ."</strong>\n";
        else {
            echo "<hr/>\n";
            echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n" ;
        }
        echo " </form> \n" ;
    }

    /**
     * Encapsule le comportement du module de traitement des demandes
     *
     * @param array  $tab_type_cong
     * @param string $onglet
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraitementDemandeModule(array $tab_type_cong, $onglet, $session, $DEBUG = false)
    {


        //var pour resp_traite_demande_all.php
        $tab_bt_radio   = getpost_variable('tab_bt_radio');
        $tab_text_refus = getpost_variable('tab_text_refus');

        // titre
        echo '<h2>'. _('resp_traite_demandes_titre') .'</h2>';

        // si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
        if( $tab_bt_radio == '' ) {
            \hr\Fonctions::affiche_all_demandes_en_cours($tab_type_cong, $DEBUG);
        } else {
            \hr\Fonctions::traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session.'&onglet='.$onglet, false);
            exit;
        }
    }

    public static function new_conges($user_login, $numero_int, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);

        // verif validité des valeurs saisies
        $valid=verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

        if($valid) {
            echo "$user_login---$new_debut _ $new_demi_jour_deb---$new_fin _ $new_demi_jour_fin---$new_nb_jours---$new_comment---$new_type_id<br>\n";

            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

            /**********************************/
            /* insert dans conges_periode     */
            /**********************************/
            $new_etat="ok";
            $result=insert_dans_periode($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $new_etat, 0,$DEBUG);

            /************************************************/
            /* UPDATE table "conges_solde_user" (jours restants) */
            // on retranche les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on annule est un "conges"
            if($tab_tout_type_abs[$new_type_id]['type']=="conges") {
                $user_nb_jours_pris_float=(float) $new_nb_jours ;
                soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $new_type_id, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin , $DEBUG);
            }

            $comment_log = "saisie conges par le responsable pour $user_login ($new_nb_jours jour(s)) type_conges = $new_type_id ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
            log_action(0, "", $user_login, $comment_log, $DEBUG);

            if($result)
                echo  _('form_modif_ok') ."<br><br> \n";
            else
                echo  _('form_modif_not_ok') ."<br><br> \n";
        } else {
                echo  _('resp_traite_user_valeurs_not_ok') ."<br><br> \n";
        }

        /* APPEL D'UNE AUTRE PAGE */
        echo "<form action=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo "</form> \n";

    }

    public static function traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

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
            $value_traite=$champs[3];

            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            if( $DEBUG ) { echo "<br><br>conges numero :$numero --- User_login : $user_login --- nb de jours : $user_nb_jours_pris --->$value_traite<br>" ; }

            if($reponse == "ACCEPTE") // acceptation definitive d'un conges
            {
                /* UPDATE table "conges_periode" */
                $sql1 = "UPDATE conges_periode SET p_etat=\"ok\", p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog1 = \includes\SQL::query($sql1);

                // Log de l'action
                log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                /* UPDATE table "conges_solde_user" (jours restants) */
                // on retranche les jours seulement pour des conges pris (pas pour les absences)
                // donc seulement si le type de l'absence qu'on annule est un "conges"
                if( $DEBUG ) { echo "type_abs = ".$tab_tout_type_abs[$value_type_abs_id]['type']."<br>\n" ; }
                if(($tab_tout_type_abs[$value_type_abs_id]['type']=="conges")||($tab_tout_type_abs[$value_type_abs_id]['type']=="conges_exceptionnels"))
                {
                    soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $value_type_abs_id, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);
                }

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_valid_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges", $DEBUG);
            }
            elseif($reponse == "VALID") // première validation dans le cas d'une double validation
            {
                /* UPDATE table "conges_periode" */
                $sql1 = "UPDATE conges_periode SET p_etat=\"valid\", p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog1 = \includes\SQL::query($sql1);

                // Log de l'action
                log_action($numero_int,"valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_valid_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges", $DEBUG);
            }
            elseif($reponse == "REFUSE") // refus d'un conges
            {
                // recup di motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql3 = "UPDATE conges_periode SET p_etat=\"refus\", p_motif_refus='$motif_refus', p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog3 = \includes\SQL::query($sql3);

                // Log de l'action
                log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_refus_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges", $DEBUG);
            }
        }

        if( $DEBUG ) {
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
            echo "</form>\n" ;
        } else {
            echo  _('form_modif_ok') ."<br><br> \n";
            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
        }
    }

    public static function annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

        while($elem_tableau = each($tab_checkbox_annule)) {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $user_nb_jours_pris_float=(float) $user_nb_jours_pris ;
            $user_nb_jours_pris_float=number_format($user_nb_jours_pris_float, 1, '.', '');
            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            $user_type_abs_id=$champs[2];

            $motif_annul=addslashes($tab_text_annul[$numero_int]);

            if( $DEBUG ) { echo "<br><br>conges numero :$numero ---> login : $user_login --- nb de jours : $user_nb_jours_pris_float --- type : $user_type_abs_id ---> ANNULER <br>"; }

            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="annul", p_motif_refus="'.\includes\SQL::quote($motif_annul).'", p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" ';
            $ReqLog1 = \includes\SQL::query($sql1);

            // Log de l'action
            log_action($numero_int,"annul", $user_login, "annulation conges $numero ($user_login) ($user_nb_jours_pris jours)", $DEBUG);

            /* UPDATE table "conges_solde_user" (jours restants) */
            // on re-crédite les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on annule est un "conges"
            if($tab_tout_type_abs[$user_type_abs_id]['type']=="conges") {
                $sql2 = 'UPDATE conges_solde_user SET su_solde = su_solde+"'. \includes\SQL::quote($user_nb_jours_pris_float).'" WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_id="'. \includes\SQL::quote($user_type_abs_id).'";';
                $ReqLog2 = \includes\SQL::query($sql2);
            }

            //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
            if($_SESSION['config']['mail_annul_conges_alerte_user'])
                alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "annul_conges", $DEBUG);
        }

        if( $DEBUG ) {
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
            echo "</form>\n" ;
        } else {
            echo  _('form_modif_ok') ."<br><br> \n";
            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
        }
    }

    //affiche l'état des conges du user (avec le formulaire pour le responsable)
    public static function affiche_etat_conges_user_for_resp($user_login, $year_affichage, $tri_date, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;

        echo "<b>";
        echo "<a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_prec\"><<</a>";
        echo "&nbsp&nbsp&nbsp  $year_affichage &nbsp&nbsp&nbsp";
        echo "<a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_suiv\">>></a>";
        echo "</b><br><br>\n";


        // Récupération des informations de speriodes de conges/absences
        $sql3 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num FROM conges_periode " .
                "WHERE p_login = '$user_login' " .
                "AND p_etat !='demande' " .
                "AND p_etat !='valid' " .
                "AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";
        if($tri_date=="descendant")
            $sql3=$sql3." ORDER BY p_date_deb DESC ";
        else
            $sql3=$sql3." ORDER BY p_date_deb ASC ";

        $ReqLog3 = \includes\SQL::query($sql3);

        $count3=$ReqLog3->num_rows;
        if($count3==0) {
            echo "<b>". _('resp_traite_user_aucun_conges') ."</b><br><br>\n";
        } else {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_types_abs = recup_tableau_tout_types_abs($DEBUG) ;

            // AFFICHAGE TABLEAU
            echo "<form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo '<thead>';
                echo '<tr>';
                    echo " <th>\n";
                    echo " <a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&tri_date=descendant\"><img src=\"". TEMPLATE_PATH ."img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
                    echo " ". _('divers_debut_maj_1') ." \n";
                    echo " <a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&tri_date=ascendant\"><img src=\"". TEMPLATE_PATH ."img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
                    echo " </th>\n";
                    echo " <th>". _('divers_fin_maj_1') .'</th>';
                    echo " <th>". _('divers_nb_jours_pris_maj_1') .'</th>';
                    echo " <th>". _('divers_comment_maj_1') ."<br><i>". _('resp_traite_user_motif_possible') ."</i></th>\n";
                    echo " <th>". _('divers_type_maj_1') .'</th>';
                    echo " <th>". _('divers_etat_maj_1') .'</th>';
                    echo " <th>". _('resp_traite_user_annul') .'</th>';
                    echo " <th>". _('resp_traite_user_motif_annul') .'</th>';
                    if( $_SESSION['config']['affiche_date_traitement'] )
                        echo '<th>'. _('divers_date_traitement') .'</th>' ;
                echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat3 = $ReqLog3->fetch_array() ) {
                    $sql_date_deb           = eng_date_to_fr($resultat3["p_date_deb"]) ;
                    $sql_date_fin           = eng_date_to_fr($resultat3["p_date_fin"]) ;
                    $sql_demi_jour_deb      = $resultat3["p_demi_jour_deb"] ;
                    $sql_demi_jour_fin      = $resultat3["p_demi_jour_fin"] ;

                    $sql_login              = $resultat3["p_login"] ;
                    $sql_nb_jours           = affiche_decimal($resultat3["p_nb_jours"]) ;
                    $sql_commentaire        = $resultat3["p_commentaire"] ;
                    $sql_type               = $resultat3["p_type"] ;
                    $sql_etat               = $resultat3["p_etat"] ;
                    $sql_motif_refus        = $resultat3["p_motif_refus"] ;
                    $sql_p_date_demande     = $resultat3["p_date_demande"];
                    $sql_p_date_traitement  = $resultat3["p_date_traitement"];
                    $sql_num                = $resultat3["p_num"] ;

                    if($sql_demi_jour_deb=="am")
                        $demi_j_deb =  _('divers_am_short') ;
                    else
                        $demi_j_deb =  _('divers_pm_short') ;

                    if($sql_demi_jour_fin=="am")
                        $demi_j_fin =  _('divers_am_short') ;
                    else
                        $demi_j_fin =  _('divers_pm_short') ;

                    if(($sql_etat=="annul") || ($sql_etat=="refus") || ($sql_etat=="ajout")) {
                        $casecocher1="";
                        if($sql_etat=="refus") {
                            if($sql_motif_refus=="")
                                $sql_motif_refus =  _('divers_inconnu')  ;
                            $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                        } elseif($sql_etat=="annul") {
                            if($sql_motif_refus=="")
                                $sql_motif_refus =  _('divers_inconnu')  ;
                            $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                        } elseif($sql_etat=="ajout") {
                            $text_annul="&nbsp;";
                        }
                    } else {
                        $casecocher1=sprintf("<input type=\"checkbox\" name=\"tab_checkbox_annule[$sql_num]\" value=\"$sql_login--$sql_nb_jours--$sql_type--ANNULE\">");
                        $text_annul="<input type=\"text\" name=\"tab_text_annul[$sql_num]\" size=\"20\" max=\"100\">";
                    }

                    echo '<tr class="'.($i?'i':'p').'">';
                        echo "<td>$sql_date_deb _ $demi_j_deb</td>\n";
                        echo "<td>$sql_date_fin _ $demi_j_fin</td>\n";
                        echo "<td>$sql_nb_jours</td>\n";
                        echo "<td>$sql_commentaire</td>\n";
                        echo '<td>'.$tab_types_abs[$sql_type]['libelle'].'</td>';
                        echo '<td>';
                        if($sql_etat=="refus")
                            echo  _('divers_refuse') ;
                        elseif($sql_etat=="annul")
                            echo  _('divers_annule') ;
                        else
                            echo "$sql_etat";
                        echo '</td>';
                        echo "<td>$casecocher1</td>\n";
                        echo "<td>$text_annul</td>\n";

                        if($_SESSION['config']['affiche_date_traitement']) {
                            if(empty($sql_p_date_demande))
                             echo "<td class=\"histo-left\">". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                            else
                                echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                        }
                        echo '</tr>';
                        $i = !$i;
                }
            echo '</tbody>';
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">\n";
            echo " </form> \n";
        }
    }

    //affiche l'état des demande en attente de 2ieme validation du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_2_valid_user_for_resp($user_login, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='valid' ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0) {
            echo "<b>". _('resp_traite_user_aucune_demande') ."</b><br><br>\n";
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            echo " <form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo "<thead>\n";
            echo '<tr>';
            echo '<th>'. _('divers_debut_maj_1') .'</th>';
            echo '<th>'. _('divers_fin_maj_1') .'</th>';
            echo '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
            echo '<th>'. _('divers_comment_maj_1') .'</th>';
            echo '<th>'. _('divers_type_maj_1') .'</th>';
            echo '<th>'. _('divers_accepter_maj_1') .'</th>';
            echo '<th>'. _('divers_refuser_maj_1') .'</th>';
            echo '<th>'. _('resp_traite_user_motif_refus') .'</th>';
            if($_SESSION['config']['affiche_date_traitement']) {
                echo '<th>'. _('divers_date_traitement') .'</th>' ;
            }
            echo '</tr>';
            echo "</thead>\n";
            echo "<tbody>\n";

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array() ) {
                $sql_date_deb = $resultat2["p_date_deb"];
                $sql_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am")
                    $demi_j_deb =  _('divers_am_short') ;
                else
                    $demi_j_deb =  _('divers_pm_short') ;
                $sql_date_fin = $resultat2["p_date_fin"];
                $sql_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am")
                    $demi_j_fin =  _('divers_am_short') ;
                else
                    $demi_j_fin =  _('divers_pm_short') ;
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

                echo '<tr class="'.($i?'i':'p').'">';
                echo "<td>$sql_date_deb_fr _ $demi_j_deb</td>\n";
                echo "<td>$sql_date_fin_fr _ $demi_j_fin</td>\n";
                echo "<td>$sql_nb_jours</td>\n";
                echo "<td>$sql_commentaire</td>\n";
                echo '<td>'.$tab_type_all_abs[$sql_type]['libelle'].'</td>';
                echo "<td>$casecocher1</td>\n";
                echo "<td>$casecocher2</td>\n";
                echo "<td>$text_refus</td>\n";
                if($_SESSION['config']['affiche_date_traitement']) {
                    echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
                }

                echo '</tr>';
                $i = !$i;
            }
            echo "</tbody>\n";
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
            echo " </form> \n";
        }
    }

    //affiche l'état des demande du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='demande' ".
                "ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0) {
            echo "<b>". _('resp_traite_user_aucune_demande') ."</b><br><br>\n";
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            echo " <form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo '<thead>';
                echo '<tr>';
                    echo '<th>'. _('divers_debut_maj_1') .'</th>';
                    echo '<th>'. _('divers_fin_maj_1') .'</th>';
                    echo '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
                    echo '<th>'. _('divers_comment_maj_1') .'</th>';
                    echo '<th>'. _('divers_type_maj_1') .'</th>';
                    echo '<th>'. _('divers_accepter_maj_1') .'</th>';
                    echo '<th>'. _('divers_refuser_maj_1') .'</th>';
                    echo '<th>'. _('resp_traite_user_motif_refus') .'</th>';
                    if( $_SESSION['config']['affiche_date_traitement'] )
                        echo '<th>'. _('divers_date_traitement') .'</th>' ;
                echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_date_deb       = $resultat2["p_date_deb"];
                $sql_date_fin       = $resultat2["p_date_fin"];
                $sql_date_deb_fr    = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_date_fin_fr    = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_deb  = $resultat2["p_demi_jour_deb"] ;
                $sql_demi_jour_fin  = $resultat2["p_demi_jour_fin"] ;

                $sql_nb_jours       = affiche_decimal($resultat2["p_nb_jours"]) ;
                $sql_commentaire    = $resultat2["p_commentaire"] ;
                $sql_type           = $resultat2["p_type"] ;
                $sql_date_demande   = $resultat2["p_date_demande"];
                $sql_date_traitement= $resultat2["p_date_traitement"];
                $sql_num            = $resultat2["p_num"] ;


                if($sql_demi_jour_deb=="am")
                    $demi_j_deb =  _('divers_am_short') ;
                else
                    $demi_j_deb =  _('divers_pm_short') ;
                if($sql_demi_jour_fin=="am")
                    $demi_j_fin =  _('divers_am_short') ;
                else
                    $demi_j_fin =  _('divers_pm_short') ;

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";

                // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
                if($tab_user['double_valid'] == "Y") {
                    /*******************************/
                    /* verif si le resp est grand_responsable pour ce user*/
                    if(in_array($_SESSION['userlogin'], $tab_grd_resp)) // si resp_login est dans le tableau
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--VALID\">";
                    else
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                }
                else
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";

                $boutonradio2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";

                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                echo '<tr class="'.($i?'i':'p').'">';
                    echo "<td>$sql_date_deb_fr _ $demi_j_deb</td>\n";
                    echo "<td>$sql_date_fin_fr _ $demi_j_fin</td>\n";
                    echo "<td>$sql_nb_jours</td>\n";
                    echo "<td>$sql_commentaire</td>\n";
                    echo '<td>'.$tab_type_all_abs[$sql_type]['libelle'].'</td>';
                    echo "<td>$boutonradio1</td>\n";
                    echo "<td>$boutonradio2</td>\n";
                    echo "<td>$text_refus</td>\n";
                    if( $_SESSION['config']['affiche_date_traitement'] )
                    {
                        if($sql_date_traitement==NULL)
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                        else
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
                    }

                echo '</tr>';
                $i = !$i;
            }
            echo '</tbody>';
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
            echo " </form> \n";
        }
    }

    public static function affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet, $DEBUG)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();

        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        if(!isset($_SESSION["tab_j_feries"])) {
            init_tab_jours_feries();
        }

        /********************/
        /* Récupération des informations sur le user : */
        /********************/
        $list_group_dbl_valid_du_resp = get_list_groupes_double_valid_du_resp($_SESSION['userlogin'], $DEBUG);
        $tab_user=array();
        $tab_user = recup_infos_du_user($user_login, $list_group_dbl_valid_du_resp, $DEBUG);
        if( $DEBUG ) { echo"tab_user =<br>\n"; print_r($tab_user); echo "<br>\n"; }

        $list_all_users_du_hr=get_list_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
        if( $DEBUG ) { echo"list_all_users_du_hr = $list_all_users_du_hr<br>\n"; }

        // recup des grd resp du user
        $tab_grd_resp=array();
        if($_SESSION['config']['double_validation_conges']) {
            get_tab_grd_resp_du_user($user_login, $tab_grd_resp, $DEBUG);
            if( $DEBUG ) { echo"tab_grd_resp =<br>\n"; print_r($tab_grd_resp); echo "<br>\n"; }
        }

        include ROOT_PATH .'fonctions_javascript.php' ;
        /********************/
        /* Titre */
        /********************/
        echo '<h2>'. _('resp_traite_user_titre') ." ".$tab_user['prenom']." ".$tab_user['nom'].".</H2>\n\n";


        /********************/
        /* Bilan des Conges */
        /********************/
        // AFFICHAGE TABLEAU
        // affichage du tableau récapitulatif des solde de congés d'un user
        affiche_tableau_bilan_conges_user($user_login);
        echo "<br><br>\n";

        /*************************/
        /* SAISIE NOUVEAU CONGES */
        /*************************/
        // dans le cas ou les users ne peuvent pas saisir de demande, le responsable saisi les congès :
        if(($_SESSION['config']['user_saisie_demande']==FALSE)||($_SESSION['config']['resp_saisie_mission'])) {

            // si les mois et année ne sont pas renseignés, on prend ceux du jour
            if($year_calendrier_saisie_debut==0)
                $year_calendrier_saisie_debut=date("Y");
            if($mois_calendrier_saisie_debut==0)
                $mois_calendrier_saisie_debut=date("m");
            if($year_calendrier_saisie_fin==0)
                $year_calendrier_saisie_fin=date("Y");
            if($mois_calendrier_saisie_fin==0)
                $mois_calendrier_saisie_fin=date("m");
            if( $DEBUG ) { echo "$mois_calendrier_saisie_debut  $year_calendrier_saisie_debut  -  $mois_calendrier_saisie_fin  $year_calendrier_saisie_fin<br>\n"; }

            echo "<H3>". _('resp_traite_user_new_conges') ."</H3>\n\n";

            saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);

            echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
        }

        /*********************/
        /* Etat des Demandes */
        /*********************/
        if($_SESSION['config']['user_saisie_demande']) {
            //verif si le user est bien un user du resp (et pas seulement du grad resp)
            if(strstr($list_all_users_du_hr, "'$user_login'")!=FALSE) {
                echo "<h3>". _('resp_traite_user_etat_demandes') ."</h3>\n";

                //affiche l'état des demande du user (avec le formulaire pour le responsable)
                \hr\Fonctions::affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp, $DEBUG);

                echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
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
                echo "<h3>". _('resp_traite_user_etat_demandes_2_valid') ."</h3>\n";

                //affiche l'état des demande en attente de 2ieme valid du user (avec le formulaire pour le responsable)
                affiche_etat_demande_2_valid_user_for_resp($user_login, $DEBUG);

                echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
            }
        }

        /*******************/
        /* Etat des Conges */
        /*******************/
        echo "<h3>". _('resp_traite_user_etat_conges') ."</h3>\n";

        //affiche l'état des conges du user (avec le formulaire pour le responsable)
        \hr\Fonctions::affiche_etat_conges_user_for_resp($user_login,  $year_affichage, $tri_date, $DEBUG);

        //echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";


        echo "<td valign=\"middle\">\n";
        echo "</td></tr></table>\n";
        echo "<center>\n";
    }

    /**
     * Encapsule le comportement du module de traitement des utilisateurs
     *
     * @param string $onglet
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraiteUserModule($onglet, $DEBUG = false)
    {
        //var pour hr_traite_user.php
        $user_login                 = getpost_variable('user_login') ;
        $tab_checkbox_annule        = getpost_variable('tab_checkbox_annule') ;
        $tab_radio_traite_demande   = getpost_variable('tab_radio_traite_demande') ;
        $new_demande_conges         = getpost_variable('new_demande_conges', 0) ;

        // si une annulation de conges a été selectionée :
        if( $tab_checkbox_annule != '' ) {
            $tab_text_annul         = getpost_variable('tab_text_annul') ;
            \hr\Fonctions::annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul, $DEBUG);
        }
        // si le traitement des demandes a été selectionée :
        elseif( $tab_radio_traite_demande != '' ) {
            $tab_text_refus         = getpost_variable('tab_text_refus') ;
            \hr\Fonctions::traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus, $DEBUG);
        }
        // si un nouveau conges ou absence a été saisi pour un user :
        elseif( $new_demande_conges == 1 ) {
            $new_debut          = getpost_variable('new_debut') ;
            $new_demi_jour_deb  = getpost_variable('new_demi_jour_deb') ;
            $new_fin            = getpost_variable('new_fin') ;
            $new_demi_jour_fin  = getpost_variable('new_demi_jour_fin') ;
            $new_comment        = getpost_variable('new_comment') ;
            $new_type           = getpost_variable('new_type') ;

            if( $_SESSION['config']['disable_saise_champ_nb_jours_pris'] ) {
                $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $comment,  $DEBUG);
                if ($new_nb_jours <= 0 )
                    $new_nb_jours      = getpost_variable('new_nb_jours');
            } else {
                $new_nb_jours   = getpost_variable('new_nb_jours') ;
            }

            \hr\Fonctions::new_conges($user_login, $numero_int, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $DEBUG);
        } else {
            $year_calendrier_saisie_debut   = getpost_variable('year_calendrier_saisie_debut', 0) ;
            $mois_calendrier_saisie_debut   = getpost_variable('mois_calendrier_saisie_debut', 0) ;
            $year_calendrier_saisie_fin     = getpost_variable('year_calendrier_saisie_fin', 0) ;
            $mois_calendrier_saisie_fin     = getpost_variable('mois_calendrier_saisie_fin', 0) ;
            $tri_date                       = getpost_variable('tri_date', "ascendant") ;
            $year_affichage                 = getpost_variable('year_affichage' , date("Y") );

            \hr\Fonctions::affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet, $DEBUG);
        }
    }

    // recup de la liste de tous les groupes pour le mode RH
    public static function get_list_groupes_pour_rh($user_login, $DEBUG=FALSE)
    {
        $list_group="";

        $sql1="SELECT g_gid FROM conges_groupe ORDER BY g_gid";
        $ReqLog1 = \includes\SQL::query($sql1);

        if($ReqLog1->num_rows != 0) {
            while ($resultat1 = $ReqLog1->fetch_array()) {
                $current_group=$resultat1["g_gid"];
                if($list_group=="")
                    $list_group="$current_group";
                else
                    $list_group=$list_group.", $current_group";
            }
        }
        if( $DEBUG ) { echo "list_group = $list_group<br>\n" ;}

        return $list_group;
    }

    // on insert l'ajout de conges dans la table periode
    public static function insert_ajout_dans_periode($DEBUG, $login, $nb_jours, $id_type_abs, $commentaire)
    {
        $date_today=date("Y-m-d");

        $result=insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0, $DEBUG);
    }

    public static function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        // recup de la liste des users d'un groupe donné
        $list_users = get_list_users_du_groupe($choix_groupe, $DEBUG);


        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while ($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
                        $nb_conges=$nb_jours;
                    else
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;

                    $valid=verif_saisie_decimal($nb_conges, $DEBUG);
                    if($valid){
                        // 1 : on update conges_solde_user
                        $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde+ '.intval($nb_conges).'
                                WHERE  su_login = "'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.intval($id_conges).';';
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges dans la table periode
                        // recup du nom du groupe
                        $groupename= get_group_name_from_id($choix_groupe, $DEBUG);
                        $commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";

                        // ajout conges
                        \hr\Fonctions::insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
                    }
                }

                $group_name = get_group_name_from_id($choix_groupe, $DEBUG);
                if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
                    $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
                else
                    $comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
                log_action(0, "ajout", "groupe", $comment_log, $DEBUG);
            }
        }
    }

    public static function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $list_users_du_resp = get_list_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
        if( $DEBUG ) { echo "list_all_users_du_hr = $list_users_du_resp<br>\n";}

        if( $DEBUG ) { echo "tab_new_nb_conges_all = <br>"; print_r($tab_new_nb_conges_all); echo "<br>\n" ;}
        if( $DEBUG ) { echo "tab_calcul_proportionnel = <br>"; print_r($tab_calcul_proportionnel); echo "<br>\n" ;}

        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
                        $nb_conges=$nb_jours;
                    else
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
                       $valid=verif_saisie_decimal($nb_conges, $DEBUG);
                    if($valid) {
                        // 1 : update de la table conges_solde_user
                        $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde + '.floatval($nb_conges).'
                                WHERE  su_login = "'. \includes\SQL::quote($current_login).'"  AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
                        $commentaire =  _('resp_ajout_conges_comment_periode_all') ;
                        // ajout conges
                        \hr\Fonctions::insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
                    }
                }

                if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
                    $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
                else
                    $comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
                log_action(0, "ajout", "tous", $comment_log, $DEBUG);
            }
        }
    }


    public static function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
        {
          foreach($tab_conges as $id_conges => $user_nb_jours_ajout) {
            $user_nb_jours_ajout_float =(float) $user_nb_jours_ajout ;
            $valid=verif_saisie_decimal($user_nb_jours_ajout_float, $DEBUG);   //verif la bonne saisie du nombre décimal
            if($valid) {
              if( $DEBUG ) {echo "$user_name --- $id_conges --- $user_nb_jours_ajout_float<br>\n";}

              if($user_nb_jours_ajout_float!=0) {
                /* Modification de la table conges_users */
                $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde+'.floatval($user_nb_jours_ajout_float).' WHERE su_login="'. \includes\SQL::quote($user_name).'" AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
                /* On valide l'UPDATE dans la table ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;

                // on insert l'ajout de conges dans la table periode
                $commentaire =  _('resp_ajout_conges_comment_periode_user') ;
                \hr\Fonctions::insert_ajout_dans_periode($DEBUG, $user_name, $user_nb_jours_ajout_float, $id_conges, $commentaire);
              }
            }
          }
        }
    }

    public static function affichage_saisie_globale_groupe($tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs */

        // on établi la liste complète des groupes pour le mode RH
        $list_group = \hr\Fonctions::get_list_groupes_pour_rh($_SESSION['userlogin']);

        if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
        {
            echo "<h2>". _('resp_ajout_conges_ajout_groupe') ."</h2>\n";
            echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
            echo "    <fieldset class=\"cal_saisie\">\n";
            echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
            echo "    <tr>\n";
            echo "        <td class=\"big\">". _('resp_ajout_conges_choix_groupe') ." : </td>\n";
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

            echo "        <td colspan=\"3\">$text_choix_group</td>\n";
            echo "    </tr>\n";
        echo "<tr>\n";
        echo "<th colspan=\"2\">" . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . "</th>\n";
        echo "<th>" ._('resp_ajout_conges_calcul_prop') . "</th>\n";
        echo "<th>" . _('divers_comment_maj_1') . "</th>\n";
        echo "</tr>\n";
            foreach($tab_type_conges as $id_conges => $libelle) {
                echo "    <tr>\n";
                echo "        <td><strong>$libelle<strong></td>\n";
                echo "        <td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
                echo "        <td>". _('resp_ajout_conges_oui') ." <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked></td>\n";
                echo "        <td><input class=\"form-control\" type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
                echo "    </tr>\n";
            }
            echo "    </table></div>\n";
            echo "<p>" . _('resp_ajout_conges_calcul_prop_arondi') . "! </p>\n";
            echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_groupe') ."\">\n";
            echo "    </fieldset>\n";
            echo "<input type=\"hidden\" name=\"ajout_groupe\" value=\"TRUE\">\n";
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "</form> \n";
        }
    }

    public static function affichage_saisie_globale_pour_tous($tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /************************************************************/
        /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
        echo "<h2>". _('resp_ajout_conges_ajout_all') ."</h2>\n";
        echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
        echo "    <fieldset class=\"cal_saisie\">\n";
        echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th colspan=\"2\">" . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . "</th>\n";
        echo "<th>" ._('resp_ajout_conges_calcul_prop') . "</th>\n";
        echo "<th>" . _('divers_comment_maj_1') . "</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        foreach($tab_type_conges as $id_conges => $libelle) {
            echo "    <tr>\n";
            echo "        <td><strong>$libelle<strong></td>\n";
            echo "        <td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
            echo "        <td>". _('resp_ajout_conges_oui') ." <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked></td>\n";
            echo "        <td><input class=\"form-control\" type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
            echo "    </tr>\n";
        }
        echo "</table></div>\n";
        // texte sur l'arrondi du calcul proportionnel
        echo "<p>" . _('resp_ajout_conges_calcul_prop_arondi') . "!</p>\n";
        // bouton valider
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_global') ."\">\n";
        echo "</fieldset>\n";
        echo "<input type=\"hidden\" name=\"ajout_global\" value=\"TRUE\">\n";
        echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
        echo "</form> \n";
    }

    public static function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /************************************************************/
        /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
        echo "<h2>Ajout par utilisateur</h2>\n";
        echo " <form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // AFFICHAGE TITRES TABLEAU
            echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
            echo '<thead>';
                echo '<tr align="center">';
                    echo '<th>'. _('divers_nom_maj_1') .'</th>';
                    echo '<th>'. _('divers_prenom_maj_1') .'</th>';
                    echo '<th>'. _('divers_quotite_maj_1') .'</th>';
                    foreach($tab_type_conges as $id_conges => $libelle) {
                        echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
                        echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') .'</th>' ;
                    }
                    if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                        foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                            echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
                            echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') .'</th>' ;
                        }
                    }
                    echo '<th>'. _('divers_comment_maj_1') ."<br></th>\n" ;
                echo"</tr>\n";
            echo '</thead>';
            echo '<tbody>';

            // AFFICHAGE LIGNES TABLEAU
            $cpt_lignes=0 ;
            $tab_champ_saisie_conges=array();

            $i = true;
            // affichage des users dont on est responsable :
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                echo '<tr class="'.($i?'i':'p').'">';
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];

                /** sur la ligne ,   **/
                echo '<td>'.$tab_current_user['nom'].'</td>';
                echo '<td>'.$tab_current_user['prenom'].'</td>';
                echo '<td>'.$tab_current_user['quotite']."%</td>\n";

                foreach($tab_type_conges as $id_conges => $libelle) {
                    /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                    $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                    echo '<td>'.$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
                    echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                    foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                        /** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
                        $champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
                        echo "<td><i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
                        echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
                    }
                }
                echo "<td align=\"center\" class=\"histo\"><input class=\"form-control\" type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
                echo '</tr>';
                $cpt_lignes++ ;
                $i = !$i;
            }

            echo '</tbody>';
            echo '</table>';

            echo "<input type=\"hidden\" name=\"ajout_conges\" value=\"TRUE\">\n";
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
            echo " </form> \n";
        }
    }

    public static function saisie_ajout( $tab_type_conges, $DEBUG)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        // recup du tableau des types de conges (seulement les congesexceptionnels )
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
          $tab_type_conges_exceptionnels = recup_tableau_types_conges_exceptionnels();
          if( $DEBUG ) { echo "tab_type_conges_exceptionnels = "; print_r($tab_type_conges_exceptionnels); echo "<br><br>\n";}
        }
        else
          $tab_type_conges_exceptionnels = array();

        // recup de la liste de TOUS les users pour le RH
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            /************************************************************/
            /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
            \hr\Fonctions::affichage_saisie_globale_pour_tous($tab_type_conges, $DEBUG);
            echo "<br>\n";

            /***********************************************************************/
            /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
            if( $_SESSION['config']['gestion_groupes'] )
                \hr\Fonctions::affichage_saisie_globale_groupe($tab_type_conges, $DEBUG);
            echo "<br>\n";

            /************************************************************/
            /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
            \hr\Fonctions::affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG);
            echo "<br>\n";

        }
        else
            echo  _('resp_etat_aucun_user') ."<br>\n";

    }

    /**
     * Encapsule le comportement du module d'ajout de congés
     *
     * @param array  $tab_type_cong
     * @param string $session
     * @param bool   $DEBUG          Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageAjoutCongesModule($tab_type_cong, $session, $DEBUG = false)
    {
        //var pour resp_ajout_conges_all.php
        $ajout_conges            = getpost_variable('ajout_conges');
        $ajout_global            = getpost_variable('ajout_global');
        $ajout_groupe            = getpost_variable('ajout_groupe');
        $choix_groupe            = getpost_variable('choix_groupe');

        // titre
        echo '<h1>'. _('resp_ajout_conges_titre') ."</h1>\n\n";

        if( $ajout_conges == "TRUE" ) {
            $tab_champ_saisie            = getpost_variable('tab_champ_saisie');
            $tab_commentaire_saisie        = getpost_variable('tab_commentaire_saisie');

            \hr\Fonctions::ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } elseif( $ajout_global == "TRUE" ) {

            $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all         = getpost_variable('tab_new_comment_all');

            \hr\Fonctions::ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } elseif( $ajout_groupe == "TRUE" ) {

            $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all         = getpost_variable('tab_new_comment_all');
            $choix_groupe                = getpost_variable('choix_groupe');

            \hr\Fonctions::ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } else {
            \hr\Fonctions::saisie_ajout($tab_type_cong,$DEBUG);
        }
    }

    //fonction de recherche des jours fériés de l'année demandée
    // trouvée sur http://www.phpcs.com/codes/LISTE-JOURS-FERIES-ANNEE_32791.aspx
    public static function fcListJourFeries($iAnnee = 2000)
    {

        //Initialisation de variables
        $iCstJour = 3600*24;
        $tbJourFerie=array();

        // Détermination des dates toujours fixes
        $tbJourFerie["Jour de l an"]     = $iAnnee . "-01-01";
        $tbJourFerie["Armistice 39-45"]  = $iAnnee . "-05-08";
        $tbJourFerie["Toussaint"]        = $iAnnee . "-11-01";
        $tbJourFerie["Armistice 14-18"]  = $iAnnee . "-11-11";
        $tbJourFerie["Assomption"]       = $iAnnee . "-08-15";
        $tbJourFerie["Fete du travail"]  = $iAnnee . "-05-01";
        $tbJourFerie["Fete nationale"]   = $iAnnee . "-07-14";
        $tbJourFerie["Noel"]    = $iAnnee . "-12-25";

        // Récupération des fêtes mobiles
             $tbJourFerie["Lundi de Paques"]   = $iAnnee . date( "-m-d", easter_date($iAnnee) + 1*$iCstJour );
             $tbJourFerie["Jeudi de l ascension"] = $iAnnee . date( "-m-d", easter_date($iAnnee) + 39*$iCstJour );

        // Retour du tableau des jours fériés pour l'année demandée
        return $tbJourFerie;
    }

    // retourne un tableau des jours feriés de l'année dans un tables passé par référence
    public static function get_tableau_jour_feries($year, &$tab_year,  $DEBUG=FALSE)
    {

        $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'-%" ;';
        $res_select = \includes\SQL::query($sql_select);
        $num_select = $res_select->num_rows;

        if($num_select!=0) {
            while($result_select = $res_select->fetch_array()) {
                $tab_year[]=$result_select["jf_date"];
            }
        }
    }

    public static function verif_year_deja_saisie($tab_checkbox_j_chome, $DEBUG=FALSE) {
        $date_1=key($tab_checkbox_j_chome);
        $year=substr($date_1, 0, 4);
        $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'%" ;';
        $relog = \includes\SQL::query($sql_select);
        return($relog->num_rows != 0);
    }

    public static function delete_year($tab_checkbox_j_chome, $DEBUG=FALSE) {
        $date_1=key($tab_checkbox_j_chome);
        $year=substr($date_1, 0, 4);
        $sql_delete='DELETE FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'%" ;';
        $result = \includes\SQL::query($sql_delete);

        return true;
    }

    public static function insert_year($tab_checkbox_j_chome, $DEBUG=FALSE) {
        foreach($tab_checkbox_j_chome as $key => $value)
            $result = \includes\SQL::query('INSERT INTO conges_jours_feries SET jf_date="'. \includes\SQL::quote($key).'";');
        return true;
    }

    public static function commit_saisie($tab_checkbox_j_chome,$DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        if( $DEBUG ) { echo "tab_checkbox_j_chome : <br>\n"; print_r($tab_checkbox_j_chome); echo "<br>\n"; }

        // si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
        if(\hr\Fonctions::verif_year_deja_saisie($tab_checkbox_j_chome, $DEBUG))
            $result = \hr\Fonctions::delete_year($tab_checkbox_j_chome, $DEBUG);


        // on insert les nouvelles dates saisies
        $result = \hr\Fonctions::insert_year($tab_checkbox_j_chome, $DEBUG);

        // on recharge les jours feries dans les variables de session
        init_tab_jours_feries($DEBUG);

        if($result)
            echo "<div class=\"alert alert-success\">" . _('form_modif_ok') . "</div>\n";
        else
            echo "<div class=\"alert alert-danger\">". _('form_modif_not_ok') . "</div>\n";

        $date_1=key($tab_checkbox_j_chome);
        $tab_date = explode('-', $date_1);
        $comment_log = "saisie des jours chomés pour ".$tab_date[0] ;
        log_action(0, "", "", $comment_log, $DEBUG);
    }

    public static function confirm_saisie($tab_checkbox_j_chome, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        header_popup();

        echo "<h1>". _('admin_jours_chomes_titre') ."</h1>\n";
        echo "<form action=\"$PHP_SELF?session=$session&onglet=jours_chomes\" method=\"POST\">\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<td align=\"center\">\n";

            foreach($tab_checkbox_j_chome as $key => $value) {
                $date_affiche=eng_date_to_fr($key);
                echo "$date_affiche<br>\n";
                echo "<input type=\"hidden\" name=\"tab_checkbox_j_chome[$key]\" value=\"$value\">\n";
            }
            echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit\">\n";
            echo "<input type=\"submit\" value=\"". _('admin_jours_chomes_confirm') ."\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\">\n";
        echo "    <input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";

        bottom();
    }

    public static function affiche_jour_hors_mois($mois,$i,$year,$tab_year) {
        $j_timestamp=mktime (0,0,0,$mois,$i,$year);
        $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
        return "<td class=\"cal-saisie2 month-out $td_second_class\">&nbsp;</td>\n";
    }

    public static function affiche_jour_checkbox($mois,$i,$year,$tab_year) {
        $j_timestamp=mktime (0,0,0,$mois,$i,$year);
        $j_date=date("Y-m-d", $j_timestamp);
        $j_day=date("d", $j_timestamp);
        $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
        $checked = in_array ("$j_date", $tab_year);

        return "<td  class=\"cal-saisie $td_second_class" . (($checked) ? ' fermeture' : '') . "\">$j_day<input type=\"checkbox\" name=\"tab_checkbox_j_chome[$j_date]\" value=\"Y\"" . (($checked) ? ' checked' : '') . "></td>\n";
    }

    // affichage du calendrier du mois avec les case à cocher
    // on lui passe en parametre le tableau des jour chomé de l'année (pour pré-cocher certaines cases)
    public static function affiche_calendrier_saisie_jours_chomes($year, $mois, $tab_year, $DEBUG=FALSE)
    {
        $jour_today=date("j");
        $jour_today_name=date("D");

        $first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
        $mois_name=date_fr("F", $first_jour_mois_timestamp);
        $first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        if($first_jour_mois_rang==0)
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)


        echo "<table>\n";
        /* affichage  2 premieres lignes */
        echo "<thead>\n";
        echo "    <tr align=\"center\" bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\"><th colspan=7 class=\"titre\"> $mois_name $year </th></tr>\n" ;
        echo "    <tr>\n";
        echo "        <th class=\"cal-saisie2\">". _('lundi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('mardi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('mercredi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('jeudi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('vendredi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2 weekend\">". _('samedi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2 weekend\">". _('dimanche_1c') ."</th>\n";
        echo "    </tr>\n" ;
        echo "</thead>\n";

        /* affichage ligne 1 du mois*/
        echo "<tr>\n";
        // affichage des cellules vides jusqu'au 1 du mois ...
        for($i=1; $i<$first_jour_mois_rang; $i++) {
            echo \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        // affichage des cellules cochables du 1 du mois à la fin de la ligne ...
        for($i=$first_jour_mois_rang; $i<8; $i++) {
            $j=$i-$first_jour_mois_rang+1;
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$j,$year,$tab_year);
        }
        echo "</tr>\n";

        /* affichage ligne 2 du mois*/
        echo "<tr>\n";
        for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        echo "</tr>\n";

        /* affichage ligne 3 du mois*/
        echo "<tr>\n";
        for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++){
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        echo "</tr>\n";

        /* affichage ligne 4 du mois*/
        echo "<tr>\n";
        for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        echo "</tr>\n";

        /* affichage ligne 5 du mois (peut etre la derniere ligne) */
        echo "<tr>\n";
        for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }

        for($i; $i<36-$first_jour_mois_rang+1; $i++) {
            echo \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        echo "</tr>\n";

        /* affichage ligne 6 du mois (derniere ligne)*/
        echo "<tr>\n";
        for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
            echo \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }

        for($i; $i<43-$first_jour_mois_rang+1; $i++) {
            echo \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        echo "</tr>\n";

        echo "</table>\n";
    }

    public static function saisie($year_calendrier_saisie, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // si l'année n'est pas renseignée, on prend celle du jour
        if($year_calendrier_saisie==0)
            $year_calendrier_saisie = date("Y");

        // on construit le tableau des jours feries de l'année considérée
        $tab_year=array();
        \hr\Fonctions::get_tableau_jour_feries($year_calendrier_saisie, $tab_year,$DEBUG);
        if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

        //calcul automatique des jours feries
        if($_SESSION['config']['calcul_auto_jours_feries_france']) {
            $tableau_jour_feries = \hr\Fonctions::fcListJourFeries($year_calendrier_saisie) ;
            if( $DEBUG ) { echo "tableau_jour_feries = "; print_r($tableau_jour_feries); echo "<br>\n"; }
            foreach ($tableau_jour_feries as $i => $value)
            {
                if(!in_array ("$value", $tab_year))
                    $tab_year[]=$value;
            }
        }
        if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

        echo "<form action=\"$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=$year_calendrier_saisie\" method=\"POST\">\n" ;
        echo "<div class=\"calendar\">\n";
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        foreach ($months as $month) {
            echo "<div class=\"month\">\n";
            echo "<div class=\"wrapper\">\n";
            echo \hr\Fonctions::affiche_calendrier_saisie_jours_chomes($year_calendrier_saisie, $month, $tab_year);
            echo "</div>\n";
            echo "</div>";
        }
        echo "</div>";
        echo "<div class=\"actions\">";
        echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit\">\n";
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">  \n";
        echo "</div>";
        echo "</form>\n" ;
    }

    /**
     * Encapsule le comportement du module des jours chomés
     *
     * @param string $session
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageJoursChomesModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_hr", $DEBUG);
        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $choix_action                 = getpost_variable('choix_action');
        $year_calendrier_saisie        = getpost_variable('year_calendrier_saisie', 0);
        $tab_checkbox_j_chome        = getpost_variable('tab_checkbox_j_chome');
        /*************************************/

        if( $DEBUG ) { echo "choix_action = $choix_action # year_calendrier_saisie = $year_calendrier_saisie<br>\n"; print_r($year_calendrier_saisie) ; echo "<br>\n"; }

        // si l'année n'est pas renseignée, on prend celle du jour
        if($year_calendrier_saisie==0)
            $year_calendrier_saisie = date("Y");

        $add_css = '<style>#onglet_menu .onglet{ width: 50% ;}</style>';

        //    header_menu('hr', NULL, $add_css);

        echo "<div class=\"pager\">\n";
        echo "<div class=\"onglet calendar-nav\">\n";
        // navigation
        $prev_link = "$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie - 1);
        $next_link = "$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie + 1);
        echo "<ul>\n";
        echo "<li><a href=\"$prev_link\" class=\"calendar-prev\"><i class=\"fa fa-chevron-left\"></i><span>année précédente</span></a></li>\n";
        echo "<li class=\"current-year\">$year_calendrier_saisie</li>\n";
        echo "<li><a href=\"$next_link\" class=\"calendar-next\"><i class=\"fa fa-chevron-right\"></i><span>année suivante</span></a></li>\n";
        echo "</ul>\n";
        echo "</div>\n";
        echo "</div>\n";
        if($choix_action=="commit")
            \hr\Fonctions::commit_saisie($tab_checkbox_j_chome, $DEBUG);
        echo "<div class=\"wrapper\">\n";
        \hr\Fonctions::saisie($year_calendrier_saisie, $DEBUG);
        echo "</div>\n";
    }

    // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée) et stockage dans la table
    public static function set_nouvelle_date_limite_reliquat($DEBUG=FALSE)
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
                    $sql_update= 'UPDATE conges_appli SET appli_valeur = \''.$new_date_limite.'\' WHERE appli_variable=\'date_limite_reliquats\';';
                    $ReqLog_update = \includes\SQL::query($sql_update) ;

                }
            }
        }
    }

    // cloture / debut d'exercice pour TOUS les users d'un groupe'
    public static function cloture_globale_groupe($group_id, $tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // recup de la liste de TOUS les users du groupe
        $tab_all_users_du_groupe=recup_infos_all_users_du_groupe($group_id, $DEBUG);
        if( $DEBUG ) { echo "tab_all_users_du_groupe =<br>\n"; print_r($tab_all_users_du_groupe); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if(count($tab_all_users_du_groupe)!=0) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_groupe as $current_login => $tab_current_user) {
                \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture, $DEBUG);
            }
        }
    }

    // cloture / debut d'exercice pour TOUS les users du resp (ou grand resp)
    public static function cloture_globale($tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture, $DEBUG);
            }
        }
    }

    // verifie si tous les users on été basculés de l'exercice précédent vers le suivant.
    // si oui : on incrémente le num_exercice de l'application
    public static function update_appli_num_exercice($DEBUG=FALSE)
    {
        // verif
        $appli_num_exercice = $_SESSION['config']['num_exercice'] ;
        $sql_verif = "SELECT u_login FROM conges_users WHERE u_login != 'admin' AND u_login != 'conges' AND u_num_exercice != $appli_num_exercice "  ;
        $ReqLog_verif = \includes\SQL::query($sql_verif) ;

        if($ReqLog_verif->num_rows == 0) {
            /* Modification de la table conges_appli */
            $sql_update= "UPDATE conges_appli SET appli_valeur = appli_valeur+1 WHERE appli_variable='num_exercice' ";
            $ReqLog_update = \includes\SQL::query($sql_update) ;

            // ecriture dans les logs
            $new_appli_num_exercice = $appli_num_exercice+1 ;
            log_action(0, "", "", "fin/debut exercice (appli_num_exercice : $appli_num_exercice -> $new_appli_num_exercice)", $DEBUG);
        }
    }

    public static function cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire, $DEBUG=FALSE)
    {
        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on le bascule d'exercice
        if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice']) {
            // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée)
            \hr\Fonctions::set_nouvelle_date_limite_reliquat($DEBUG);

            //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
            $tab_conges_current_user=$tab_current_user['conges'];
            foreach($tab_type_conges as $id_conges => $libelle) {
                $user_nb_jours_ajout_an = $tab_conges_current_user[$libelle]['nb_an'];
                $user_solde_actuel= $tab_conges_current_user[$libelle]['solde'];
                $user_reliquat_actuel= $tab_conges_current_user[$libelle]['reliquat'];

                if( $DEBUG ) {echo "$current_login --- $id_conges --- $user_nb_jours_ajout_an<br>\n";}

                /**********************************************/
                /* Modification de la table conges_solde_user */

                if($_SESSION['config']['autorise_reliquats_exercice']) {
                    // ATTENTION : si le solde du user est négatif, on ne compte pas de reliquat et le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if($user_solde_actuel>0) {
                        //calcul du reliquat pour l'exercice suivant
                        if($_SESSION['config']['nb_maxi_jours_reliquats']!=0) {
                            if($user_solde_actuel <= $_SESSION['config']['nb_maxi_jours_reliquats'])
                                $new_reliquat = $user_solde_actuel ;
                            else
                                $new_reliquat = $_SESSION['config']['nb_maxi_jours_reliquats'] ;
                        }
                        else
                            $new_reliquat = $user_reliquat_actuel + $user_solde_actuel ;

                        $new_reliquat = str_replace(',', '.', $new_reliquat);
                        //
                        // update D'ABORD du reliquat
                        $sql_reliquat = 'UPDATE conges_solde_user SET su_reliquat = '.$new_reliquat.' WHERE su_login="'. \includes\SQL::quote($current_login).'"  AND su_abs_id = '.$id_conges;
                        $ReqLog_reliquat = \includes\SQL::query($sql_reliquat) ;
                    }
                    else
                        $new_reliquat = $user_solde_actuel ; // qui est nul ou negatif

                    $new_solde = $user_nb_jours_ajout_an + $new_reliquat  ;
                    $new_solde = str_replace(',', '.', $new_solde);

                    // update du solde
                    $sql_solde = 'UPDATE conges_solde_user SET su_solde = '.$new_solde.' WHERE su_login="'. \includes\SQL::quote($current_login).'"  AND su_abs_id = '.intval($id_conges).';';
                    $ReqLog_solde = \includes\SQL::query($sql_solde) ;
                } else {
                    // ATTENTION : meme si on accepte pas les reliquats, si le solde du user est négatif, il faut le reporter: le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if($user_solde_actuel < 0)
                        $new_solde = $user_nb_jours_ajout_an + $user_solde_actuel ; // qui est nul ou negatif
                    else
                        $new_solde = $user_nb_jours_ajout_an ;

                    $new_solde = str_replace(',', '.', $new_solde);
                    $sql_solde = 'UPDATE conges_solde_user SET su_solde = '.$new_solde.' WHERE su_login="'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.intval($id_conges).';';
                    $ReqLog_solde = \includes\SQL::query($sql_solde) ;
                }

                /* Modification de la table conges_users */
                // ATTENTION : ne pas faire "SET u_num_exercice = u_num_exercice+1" dans la requete SQL car on incrémenterait pour chaque type d'absence !
                $new_num_exercice=$_SESSION['config']['num_exercice'] ;
                $sql2 = 'UPDATE conges_users SET u_num_exercice = '.$new_num_exercice.' WHERE u_login="'. \includes\SQL::quote($current_login).'"  ';
                $ReqLog2 = \includes\SQL::query($sql2) ;

                // on insert l'ajout de conges dans la table periode (avec le commentaire)
                $date_today=date("Y-m-d");
                insert_dans_periode($current_login, $date_today, "am", $date_today, "am", $user_nb_jours_ajout_an, $commentaire, $id_conges, "ajout", 0, $DEBUG);
            }

            // on incrémente le num_exercice de l'application si tous les users on été basculés.
            \hr\Fonctions::update_appli_num_exercice($DEBUG);
        }
    }

    // cloture / debut d'exercice user par user pour les users du resp (ou grand resp)
    public static function cloture_users($tab_type_conges, $tab_cloture_users, $tab_commentaire_saisie, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_cloture_users =<br>\n"; print_r($tab_cloture_users); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_commentaire_saisie =<br>\n"; print_r($tab_commentaire_saisie); echo "<br>\n"; }

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                // tab_cloture_users[$current_login]=TRUE si checkbox "cloturer" est cochée
                if( (isset($tab_cloture_users[$current_login])) && ($tab_cloture_users[$current_login]=TRUE) ) {
                    $commentaire = $tab_commentaire_saisie[$current_login];
                    \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire, $DEBUG);
                }
            }
        }
    }

    public static function affichage_cloture_globale_groupe($tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */

        // on établi la liste complète des groupes dont on est le resp (ou le grd resp)
        $list_group=get_list_groupes_du_resp($_SESSION['userlogin']);

        if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
        {
            echo "<form action=\"$PHP_SELF\" method=\"POST\"> \n";
            echo "<table>\n";
            echo "<tr><td align=\"center\">\n";
            echo "    <fieldset class=\"cal_saisie\">\n";
            echo "    <legend class=\"boxlogin\">". _('resp_cloture_exercice_groupe') ."</legend>\n";

            echo "    <table>\n";
            echo "    <tr>\n";

                // création du select pour le choix du groupe
                $text_choix_group="<select name=\"choix_groupe\" >";
                $sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
                $ReqLog_group = \includes\SQL::query($sql_group) ;

                while ($resultat_group = $ReqLog_group->fetch_array())
                {
                    $current_group_id=$resultat_group["g_gid"];
                    $current_group_name=$resultat_group["g_groupename"];
                    $text_choix_group=$text_choix_group."<option value=\"$current_group_id\" >$current_group_name</option>";
                }
                $text_choix_group=$text_choix_group."</select>" ;

            echo "        <td class=\"big\">". _('resp_ajout_conges_choix_groupe') ." : $text_choix_group</td>\n";

            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "        <td class=\"big\">". _('resp_cloture_exercice_for_groupe_text_confirmer') ." </td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "        <td align=\"center\"><input type=\"submit\" value=\"". _('form_valid_cloture_group') ."\"></td>\n";
            echo "    </tr>\n";
            echo "    </table>\n";

            echo "    </fieldset>\n";
            echo "</td></tr>\n";
            echo "</table>\n";

            echo "<input type=\"hidden\" name=\"onglet\" value=\"cloture_exercice\">\n";
            echo "<input type=\"hidden\" name=\"cloture_groupe\" value=\"TRUE\">\n";
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "</form> \n";
        }
    }

    public static function affichage_cloture_globale_pour_tous($tab_type_conges, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /************************************************************/
        /* CLOTURE EXERCICE GLOBALE pour tous les utilisateurs du responsable */

        echo "<form action=\"$PHP_SELF?session=$session&onglet=cloture_year\" method=\"POST\"> \n";
        echo "<table>\n";
        echo "<tr><td align=\"center\">\n";
        echo "    <fieldset class=\"cal_saisie\">\n";
        echo "    <legend class=\"boxlogin\">". _('resp_cloture_exercice_all') ."</legend>\n";
        echo "    <table>\n";
        echo "    <tr>\n";
        echo "        <td class=\"big\">&nbsp;&nbsp;&nbsp;". _('resp_cloture_exercice_for_all_text_confirmer') ." &nbsp;&nbsp;&nbsp;</td>\n";
        echo "    </tr>\n";
        // bouton valider
        echo "    <tr>\n";
        echo "        <td colspan=\"5\" align=\"center\"><input type=\"submit\" value=\"". _('form_valid_cloture_global') ."\"></td>\n";
        echo "    </tr>\n";
        echo "    </table>\n";
        echo "    </fieldset>\n";
        echo "</td></tr>\n";
        echo "</table>\n";
        echo "<input type=\"hidden\" name=\"cloture_globale\" value=\"TRUE\">\n";
        echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
        echo "</form> \n";
    }

    public static function affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i = true)
    {
        echo '<tr class="'.($i?'i':'p').'">';
        //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
        $tab_conges=$tab_current_user['conges'];

        /** sur la ligne ,   **/
        echo " <td>".$tab_current_user['nom'].'</td>';
        echo " <td>".$tab_current_user['prenom'].'</td>';
        echo " <td>".$tab_current_user['quotite']."%</td>\n";

        foreach($tab_type_conges as $id_conges => $libelle) {
            if (isset($tab_conges[$libelle])) {
                echo " <td>".$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
            } else {
                echo "<td></td>";
            }
        }

        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on peut le cocher
        if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice'])
            echo "    <td align=\"center\" class=\"histo\"><input type=\"checkbox\" name=\"tab_cloture_users[$current_login]\" value=\"TRUE\" checked></td>\n";
        else
            echo "    <td align=\"center\" class=\"histo\"><img src=\"". TEMPLATE_PATH ."img/stop.png\" width=\"16\" height=\"16\" border=\"0\" ></td>\n";

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");
        echo "    <td align=\"center\" class=\"histo\"><input type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"20\" maxlength=\"200\" value=\"$comment_cloture\"></td>\n";
        echo "     </tr>\n";
    }

    public static function affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        /************************************************************/
        /* CLOTURE EXERCICE USER PAR USER pour tous les utilisateurs du responsable */

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            echo "<form action=\"$PHP_SELF?session=$session&onglet=cloture_year\" method=\"POST\"> \n";
            echo "<table>\n";
            echo '<tr>';
            echo "<td align=\"center\">\n";
            echo "<fieldset class=\"cal_saisie\">\n";
            echo "<legend class=\"boxlogin\">". _('resp_cloture_exercice_users') ."</legend>\n";
            echo "    <table>\n";
            echo "    <tr>\n";
            echo "    <td align=\"center\">\n";

            // AFFICHAGE TITRES TABLEAU
            echo "    <table cellpadding=\"2\" class=\"tablo\">\n";
            echo "  <thead>\n";
            echo "    <tr>\n";
            echo "    <th>". _('divers_nom_maj_1') .'</th>';
            echo "    <th>". _('divers_prenom_maj_1') .'</th>';
            echo "    <th>". _('divers_quotite_maj_1') .'</th>';
            foreach($tab_type_conges as $id_conges => $libelle) {
                echo "    <th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
            }
            echo "    <th>". _('divers_cloturer_maj_1') ."<br></th>\n" ;
            echo "    <th>". _('divers_comment_maj_1') ."<br></th>\n" ;
            echo "    </tr>\n";
            echo "  </thead>\n";
            echo "  <tbody>\n";

            // AFFICHAGE LIGNES TABLEAU

            // affichage des users dont on est responsable :
            $i = true;
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                \hr\Fonctions::affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i);
                $i = !$i;
            }

            echo "    </tbody>\n\n";
            echo "    </table>\n\n";

            echo "    </td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "    <td align=\"center\">\n";
            echo "    <input type=\"submit\" value=\"". _('form_submit') ."\">\n";
            echo "    </td>\n";
            echo "    </tr>\n";
            echo "    </table>\n";

            echo "</fieldset>\n";
            echo "</td></tr>\n";
            echo "</table>\n";
            echo "<input type=\"hidden\" name=\"cloture_users\" value=\"TRUE\">\n";
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "</form> \n";
        }
    }

    public static function saisie_cloture( $tab_type_conges, $DEBUG)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
        if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }

        if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            /************************************************************/
            /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
            \hr\Fonctions::affichage_cloture_globale_pour_tous($tab_type_conges, $DEBUG);
            echo "<br>\n";

            /***********************************************************************/
            /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
            if( $_SESSION['config']['gestion_groupes'] )
            {
                \hr\Fonctions::affichage_cloture_globale_groupe($tab_type_conges, $DEBUG);
            }
            echo "<br>\n";

            /************************************************************/
            /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
            \hr\Fonctions::affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG);
            echo "<br>\n";

        }
        else
         echo  _('resp_etat_aucun_user') ."<br>\n";
    }

    /**
     * Encapsule le comportement du module de cloture d'exercice
     *
     * @param string $session
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageClotureYearModule($session, $DEBUG = false)
    {
        /*************************************/
        // recup des parametres reçus :

        $cloture_users           = getpost_variable('cloture_users');
        $cloture_globale         = getpost_variable('cloture_globale');
        $cloture_groupe          = getpost_variable('cloture_groupe');
        /*************************************/


        /** initialisation des tableaux des types de conges/absences  **/
        // recup du tableau des types de conges (conges et congesexceptionnels)
        // on concatene les 2 tableaux
        $tab_type_cong = ( recup_tableau_types_conges($DEBUG) + recup_tableau_types_conges_exceptionnels($DEBUG)  );

        // titre
        echo '<h2>'. _('resp_cloture_exercice_titre') ."</H2>\n\n";

        if($cloture_users=="TRUE") {
            $tab_cloture_users       = getpost_variable('tab_cloture_users');
            \hr\Fonctions::cloture_users($tab_type_cong, $tab_cloture_users, $tab_commentaire_saisie, $DEBUG);

            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } elseif($cloture_globale=="TRUE") {
            \hr\Fonctions::cloture_globale($tab_type_cong, $DEBUG);

            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } elseif($cloture_groupe=="TRUE") {
            $choix_groupe            = getpost_variable('choix_groupe');
            \hr\Fonctions::cloture_globale_groupe($choix_groupe, $tab_type_cong, $DEBUG);

            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        } else {
            \hr\Fonctions::saisie_cloture($tab_type_cong,$DEBUG);
        }
    }

    public static function affiche_calendrier_fermeture_mois($year, $mois, $tab_year, $DEBUG=FALSE)
    {
        $jour_today=date("j");
        $jour_today_name=date("D");

        $first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
        $mois_name=date_fr("F", $first_jour_mois_timestamp);
        $first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        if($first_jour_mois_rang==0)
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        echo "<table>\n";
        /* affichage  2 premieres lignes */
        echo "    <thead>\n";
        echo "    <tr><th colspan=7 class=\"titre\"> $mois_name $year </th></tr>\n" ;
        echo "    <tr>\n";
        echo "        <th class=\"cal-saisie2\">". _('lundi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('mardi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('mercredi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('jeudi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('vendredi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('samedi_1c') ."</th>\n";
        echo "        <th class=\"cal-saisie2\">". _('dimanche_1c') ."</th>\n";
        echo "    </tr>\n" ;
        echo "    </thead>\n" ;

        /* affichage ligne 1 du mois*/
        echo "<tr>\n";
        // affichage des cellules vides jusqu'au 1 du mois ...
        for($i=1; $i<$first_jour_mois_rang; $i++) {
            if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            else
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            echo "<td class=\"month-out cal-saisie2\">&nbsp;</td>";
        }
        // affichage des cellules du 1 du mois à la fin de la ligne ...
        for($i=$first_jour_mois_rang; $i<8; $i++) {
            $j=$i-$first_jour_mois_rang+1 ;
            $j_timestamp=mktime (0,0,0,$mois,$j,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        echo "</tr>\n";

        /* affichage ligne 2 du mois*/
        echo "<tr>\n";
        for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        echo "</tr>\n";

        /* affichage ligne 3 du mois*/
        echo "<tr>\n";
        for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        echo "</tr>\n";

        /* affichage ligne 4 du mois*/
        echo "<tr>\n";
        for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        echo "</tr>\n";

        /* affichage ligne 5 du mois (peut etre la derniere ligne) */
        echo "<tr>\n";
        for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        for($i; $i<36-$first_jour_mois_rang+1; $i++) {
            if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            else
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            echo "<td class=\"cal-saisie2 month-out\">&nbsp;</td>";
        }
        echo "</tr>\n";

        /* affichage ligne 6 du mois (derniere ligne)*/
        echo "<tr>\n";
        for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if(in_array ("$j_date", $tab_year))
                $td_second_class="fermeture";

            echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
        }
        for($i; $i<43-$first_jour_mois_rang+1; $i++) {
            if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)))
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            else
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            echo "<td class=\"month-out cal-saisie2\">&nbsp;</td>";
        }
        echo "</tr>\n";

        echo "</table>\n";
    }

    //calendrier des fermeture
    public static function affiche_calendrier_fermeture($year, $groupe_id = 0, $DEBUG=FALSE) {

        // on construit le tableau de l'année considérée
        $tab_year=array();
        \hr\Fonctions::get_tableau_jour_fermeture($year, $tab_year,  $groupe_id,  $DEBUG);

        echo "<div class=\"calendar calendar-year\">\n";
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        foreach ($months as $month) {
            echo "<div class=\"month\">\n";
            echo "<div class=\"wrapper\">\n";
            echo \hr\Fonctions::affiche_calendrier_fermeture_mois($year, $month, $tab_year);
            echo "</div>\n";
            echo "</div>";
        }
        echo "</div>";
    }

    //insertion des nouvelles dates de fermeture
    public static function insert_year_fermeture($fermeture_id, $tab_j_ferme, $groupe_id,  $DEBUG=FALSE)
    {
        $sql_insert="";
        foreach($tab_j_ferme as $jf_date ) {
            $sql_insert="INSERT INTO conges_jours_fermeture (jf_id, jf_gid, jf_date) VALUES ($fermeture_id, $groupe_id, '$jf_date') ;";
            $result_insert = \includes\SQL::query($sql_insert);
        }
        return TRUE;
    }

    // supprime une fermeture
    public static function delete_year_fermeture($fermeture_id, $groupe_id,  $DEBUG=FALSE)
    {

        $sql_delete="DELETE FROM conges_jours_fermeture WHERE jf_id = '$fermeture_id' AND jf_gid= '$groupe_id' ;";
        $result = \includes\SQL::query($sql_delete);
        return TRUE;
    }

    // recup l'id de la derniere fermeture (le max)
    public static function get_last_fermeture_id( $DEBUG=FALSE)
    {
        $req_1="SELECT MAX(jf_id) FROM conges_jours_fermeture ";
        $res_1 = \includes\SQL::query($req_1);
        $row_1 = $res_1->fetch_array();
        if(!$row_1)
            return 0;     // si la table est vide, on renvoit 0
        else
            return $row_1[0];
    }

    // verifie si la periode donnee chevauche une periode de conges d'un des user du groupe ..
    // retourne TRUE si chevauchement et FALSE sinon !
    public static function verif_periode_chevauche_periode_groupe($date_debut, $date_fin, $num_current_periode='', $tab_periode_calcul, $groupe_id,  $DEBUG=FALSE)
    {
        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if($groupe_id==0)  // fermeture pour tous !
            $list_users = get_list_all_users( $DEBUG);
        else
            $list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

        $tab_users = explode(",", $list_users);
        if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

        foreach($tab_users as $current_login) {
            $current_login = trim($current_login);
            // on enleve les quotes qui ont été ajoutées lors de la creation de la liste
            $current_login = trim($current_login, "\'");
            $comment="";
            if(verif_periode_chevauche_periode_user($date_debut, $date_fin, $current_login, $num_current_periode, $tab_periode_calcul, $comment, $DEBUG))
                return TRUE;
        }
    }

    public static function commit_annul_fermeture($fermeture_id, $groupe_id,  $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        if( $DEBUG ) { echo "fermeture_id = $fermeture_id <br>\n"; }


        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if($groupe_id==0)  // fermeture pour tous !
            $list_users = get_list_all_users( $DEBUG);
        else
            $list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

        $tab_users = explode(",", $list_users);
        if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

        /***********************************************/
        /** suppression des jours de fermetures   **/
        // on suprimme les dates de cette fermeture dans conges_jours_fermeture
        $result = \hr\Fonctions::delete_year_fermeture($fermeture_id, $groupe_id,  $DEBUG);


        // on va traiter user par user pour annuler sa periode de conges correspondant et lui re-crediter son solde
        foreach($tab_users as $current_login) {
            $current_login = trim($current_login);
            // on enleve les quotes qui ont été ajoutées lors de la creation de la liste
            $current_login = trim($current_login, "\'");

            // on recupère les infos de la periode ....
            $sql_credit='SELECT p_num, p_nb_jours, p_type FROM conges_periode WHERE p_login="'. \includes\SQL::quote($current_login).'" AND p_fermeture_id="' . \includes\SQL::quote($fermeture_id) .'" AND p_etat=\'ok\'';
            $result_credit = \includes\SQL::query($sql_credit);
            $row_credit = $result_credit->fetch_array();
            $sql_num_periode=$row_credit['p_num'];
            $sql_nb_jours_a_crediter=$row_credit['p_nb_jours'];
            $sql_type_abs=$row_credit['p_type'];


            // on met à jour la table conges_periode .
            $etat = "annul" ;
             $sql1 = 'UPDATE conges_periode SET p_etat = "'.\includes\SQL::quote($etat).'" WHERE p_num='.\includes\SQL::quote($sql_num_periode) ;
            $ReqLog = \includes\SQL::query($sql1);

            // mise à jour du solde de jours de conges pour l'utilisateur $current_login
            if ($sql_nb_jours_a_crediter != 0) {
                    $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde + '.\includes\SQL::quote($sql_nb_jours_a_crediter).' WHERE su_login="'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.\includes\SQL::quote($sql_type_abs) ;
                    $ReqLog = \includes\SQL::query($sql1);
            }
        }

        echo '<div class="wrapper">';
        if($result)
            echo "<br>". _('form_modif_ok') ."<br><br>\n";
        else
            echo "<br>". _('form_modif_not_ok') ." !<br><br>\n";

        // on enregistre cette action dan les logs
        if($groupe_id==0)  // fermeture pour tous !
            $comment_log = "annulation fermeture $fermeture_id (pour tous) " ;
        else
            $comment_log = "annulation fermeture $fermeture_id (pour le groupe $groupe_id)" ;
        log_action(0, "", "", $comment_log,  $DEBUG);

        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
        echo "</form>\n";
        echo '</div>';
    }

    public static function commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        // on transforme les formats des dates
        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $date_debut=$tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0];
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $date_fin=$tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0];
        if( $DEBUG ) { echo "date_debut = $date_debut  // date_fin = $date_fin<br>\n"; }


        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if($groupe_id==0)  // fermeture pour tous !
            $list_users = get_list_all_users( $DEBUG);
        else
            $list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

        $tab_users = explode(",", $list_users);
        if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

        //******************************
        // !!!!
            // type d'absence à modifier ....
        //    $id_type_conges = 1 ; //"cp" : conges payes

        //calcul de l'ID de de la fermeture (en fait l'ID de la saisie de fermeture)
        $new_fermeture_id = \hr\Fonctions::get_last_fermeture_id( $DEBUG) + 1;

        /***********************************************/
        /** enregistrement des jours de fermetures   **/
        $tab_fermeture=array();
        for($current_date=$date_debut; $current_date <= $date_fin; $current_date=jour_suivant($current_date)) {
            $tab_fermeture[] = $current_date;
        }
        if( $DEBUG ) { echo "tab_fermeture =<br>\n"; print_r($tab_fermeture) ; echo "<br>\n"; }
        // on insere les nouvelles dates saisies dans conges_jours_fermeture
        $result = \hr\Fonctions::insert_year_fermeture($new_fermeture_id, $tab_fermeture, $groupe_id,  $DEBUG);

        $opt_debut='am';
        $opt_fin='pm';

        /*********************************************************/
        /** insersion des jours de fermetures pour chaque user  **/
        foreach($tab_users as $current_login) {
            $current_login = trim($current_login);
            // on enleve les quotes qui ont été ajoutées lors de la creation de la liste
            $current_login = trim($current_login, "\'");

            if (is_active($current_login,  $DEBUG)) {
                // on compte le nb de jour à enlever au user (par periode et au total)
                // on ne met à jour la table conges_periode
                $nb_jours = 0;
                $comment="" ;

                // $nb_jours = compter($current_login, $date_debut, $date_fin, $opt_debut, $opt_fin, $comment,  $DEBUG);
                $nb_jours = compter($current_login, "", $date_debut, $date_fin, $opt_debut, $opt_fin, $comment, $DEBUG);

                if ($DEBUG) echo "<br>user_login : " . $current_login . " nbjours : " . $nb_jours . "<br>\n";

                // on ne met à jour la table conges_periode .
                $commentaire =  _('divers_fermeture') ;
                $etat = "ok" ;
                $num_periode = insert_dans_periode($current_login, $date_debut, $opt_debut, $date_fin, $opt_fin, $nb_jours, $commentaire, $id_type_conges, $etat, $new_fermeture_id, $DEBUG) ;

                // mise à jour du solde de jours de conges pour l'utilisateur $current_login
                if ($nb_jours != 0) {
                    soustrait_solde_et_reliquat_user($current_login, "", $nb_jours, $id_type_conges, $date_debut, $opt_debut, $date_fin, $opt_fin, $DEBUG);
                }
            }
        }

        // on recharge les jours fermés dans les variables de session
        init_tab_jours_fermeture($_SESSION['userlogin'],  $DEBUG);

        echo '<div class="wrapper">';

        if($result)
            echo "<br>". _('form_modif_ok') ."<br><br>\n";
        else
            echo "<br>". _('form_modif_not_ok') ." !<br><br>\n";

        $comment_log = "saisie des jours de fermeture de $date_debut a $date_fin" ;
        log_action(0, "", "", $comment_log,  $DEBUG);
        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
        echo "</form>\n";
        echo '</div>';
    }

    //function confirm_saisie_fermeture($tab_checkbox_j_ferme, $year_calendrier_saisie, $groupe_id, $DEBUG=FALSE)
    public static function confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo '<div class="wrapper">';
        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo  _('divers_fermeture_du') ."  <b>$fermeture_date_debut</b> ". _('divers_au') ." <b>$fermeture_date_fin</b>. \n";
        echo "<b>". _('admin_annul_fermeture_confirm') .".</b><br>\n";
        echo "<input type=\"hidden\" name=\"fermeture_id\" value=\"$fermeture_id\">\n";
        echo "<input type=\"hidden\" name=\"fermeture_date_debut\" value=\"$fermeture_date_debut\">\n";
        echo "<input type=\"hidden\" name=\"fermeture_date_fin\" value=\"$fermeture_date_fin\">\n";
        echo "<input type=\"hidden\" name=\"groupe_id\" value=\"$groupe_id\">\n";
        echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_annul_fermeture\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_continuer') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>";
        echo "</form>\n";
        echo "</div>\n";
    }

    // retourne un tableau des periodes de fermeture (pour un groupe donné (gid=0 pour tout le monde))
    public static function get_tableau_periodes_fermeture(&$tab_periodes_fermeture, $DEBUG=FALSE)
    {
        $req_1="SELECT DISTINCT conges_periode.p_date_deb, conges_periode.p_date_fin, conges_periode.p_fermeture_id, conges_jours_fermeture.jf_gid, conges_groupe.g_groupename FROM conges_periode, conges_jours_fermeture LEFT JOIN conges_groupe ON conges_jours_fermeture.jf_gid=conges_groupe.g_gid WHERE conges_periode.p_fermeture_id = conges_jours_fermeture.jf_id AND conges_periode.p_etat='ok' ORDER BY conges_periode.p_date_deb DESC  ";
        $res_1 = \includes\SQL::query($req_1);

        $num_select = $res_1->num_rows;
        if($num_select!=0) {
            while($result_select = $res_1->fetch_array()) {
                $tab_periode=array();
                $tab_periode['date_deb']=$result_select["p_date_deb"];
                $tab_periode['date_fin']=$result_select["p_date_fin"];
                $tab_periode['fermeture_id']=$result_select["p_fermeture_id"];
                $tab_periode['groupe_id']=$result_select["jf_gid"];
                $tab_periode['groupe_name']=$result_select["g_groupename"];
                $tab_periodes_fermeture[]=$tab_periode;
            }
        }
    }

    // Affichage d'un SELECT de formulaire pour choix d'un type d'absence
    public static function affiche_select_conges_id($DEBUG=FALSE)
    {
        $tab_conges=recup_tableau_types_conges( $DEBUG);
        $tab_conges_except=recup_tableau_types_conges_exceptionnels( $DEBUG);

        foreach($tab_conges as $id => $libelle) {
            if($libelle == 1)
                echo "<option value=\"$id\" selected>$libelle</option>\n";
            else
                echo "<option value=\"$id\">$libelle</option>\n";
        }
        if(count($tab_conges_except)!=0) {
            foreach($tab_conges_except as $id => $libelle) {
                if($libelle == 1)
                    echo "<option value=\"$id\" selected>$libelle</option>\n";
                else
                    echo "<option value=\"$id\">$libelle</option>\n";
            }
        }
    }

    //renvoi un tableau des jours de fermeture
    public static function get_tableau_jour_fermeture($year, &$tab_year,  $groupe_id,  $DEBUG=FALSE)
    {
        $sql_select = " SELECT jf_date FROM conges_jours_fermeture WHERE DATE_FORMAT(jf_date, '%Y-%m-%d') LIKE '$year%'  ";
        // on recup les fermeture du groupe + les fermetures de tous !
        if($groupe_id==0)
            $sql_select = $sql_select."AND jf_gid = 0";
        else
            $sql_select = $sql_select."AND  (jf_gid = $groupe_id OR jf_gid =0 ) ";
        $res_select = \includes\SQL::query($sql_select);
        $num_select =$res_select->num_rows;

        if($num_select!=0) {
            while($result_select = $res_select->fetch_array()) {
                $tab_year[]=$result_select["jf_date"];
            }
        }
    }

    public static function saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
        $date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
        $date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
        $timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

        // on construit le tableau de l'année considérée
        $tab_year=array();
        \hr\Fonctions::get_tableau_jour_fermeture($year, $tab_year,  $groupe_id,  $DEBUG);
        if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

        echo "<form id=\"form-fermeture\" class=\"form-inline\" role=\"form\" action=\"$PHP_SELF?session=$session&year=$year\" method=\"POST\">\n";
          echo "<div class=\"form-group\">\n";
        echo "<label for=\"new_date_debut\">" . _('divers_date_debut') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_date_debut\" value=\"$new_date_debut\">\n";
          echo "</div>";
          echo "<div class=\"form-group\">\n";
          echo "<label for=\"new date_fin\">" . _('divers_date_fin') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_date_fin\" value=\"$new_date_fin\">\n";
          echo "</div>";
          echo "<div class=\"form-group\">\n";
        echo "<label for=\"id_type_conges\">" . _('admin_jours_fermeture_affect_type_conges') . "</label>\n";
        echo "<select name=\"id_type_conges\" class=\"form-control\">\n";
           echo \hr\Fonctions::affiche_select_conges_id($DEBUG);
           echo "</select>\n";
           echo "</div>\n";
           echo "<hr/>\n";
         echo "<input type=\"hidden\" name=\"groupe_id\" value=\"$groupe_id\">\n";
        echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_new_fermeture\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "</form>\n";
    }

    public static function saisie_groupe_fermeture( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        echo "<h3>fermeture pour tous ou pour un groupe ?</h3>\n";
        echo '<div class="row">';
            echo '<div class="col-md-6">';
                /********************/
                /* Choix Tous       */
                /********************/

                // AFFICHAGE TABLEAU
                echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
                    echo "<input type=\"hidden\" name=\"groupe_id\" value=\"0\">\n";
                    echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
                    echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('admin_jours_fermeture_fermeture_pour_tous') ." !\">  \n";
                echo "</form>\n" ;
            echo '</div>';
            echo '<div class="col-md-6">';
                /********************/
                /* Choix Groupe     */
                /********************/
                // Récuperation des informations :
                $sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

                // AFFICHAGE TABLEAU

                echo "<form action=\"$PHP_SELF?session=$session\" class=\"form-inline\" method=\"POST\">\n" ;
                    echo '<div class="form-group" style="margin-right: 10px;">';
                        $ReqLog_gr = \includes\SQL::query($sql_gr);
                        echo "<select  class=\"form-control\" name=\"groupe_id\">";
                        while ($resultat_gr = $ReqLog_gr->fetch_array()) {
                            $sql_gid=$resultat_gr["g_gid"] ;
                            $sql_group=$resultat_gr["g_groupename"] ;
                            $sql_comment=$resultat_gr["g_comment"] ;

                            echo "<option value=\"$sql_gid\">$sql_group";
                        }
                        echo "</select>";
                        echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
                    echo '</div>';
                    echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('admin_jours_fermeture_fermeture_par_groupe') ."\">  \n";
                echo "</form>\n" ;
            echo '</div>';

        /************************************************/
        // HISTORIQUE DES FERMETURES

        $tab_periodes_fermeture = array();
        \hr\Fonctions::get_tableau_periodes_fermeture($tab_periodes_fermeture, $DEBUG);
        if(count($tab_periodes_fermeture)!=0) {
            echo "<table class=\"table\">\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th colspan=\"2\">Fermetures</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            foreach($tab_periodes_fermeture as $tab_periode) {
                $date_affiche_1=eng_date_to_fr($tab_periode['date_deb']);
                $date_affiche_2=eng_date_to_fr($tab_periode['date_fin']);
                $fermeture_id =($tab_periode['fermeture_id']);
                $groupe_id =($tab_periode['groupe_id']);
                $groupe_name =($tab_periode['groupe_name']);

                if($groupe_id==0)
                    $groupe_name = 'Tous';
                else
                    $groupe_name = $groupe_name;

                echo "<tr>\n";
                echo "<td>\n";
                echo  _('divers_du') ." <b>$date_affiche_1</b> ". _('divers_au') ." <b>$date_affiche_2</b>  (id $fermeture_id)</b>  ".$groupe_name."\n";
                echo "</td>\n";
                echo "<td>\n";
                echo "<a href=\"$PHP_SELF?session=$session&choix_action=annul_fermeture&fermeture_id=$fermeture_id&groupe_id=$groupe_id&fermeture_date_debut=$date_affiche_1&fermeture_date_fin=$date_affiche_2\">". _('admin_annuler_fermeture') ."</a>\n";
                echo "</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }


        echo '</div>';
        echo "<hr>\n" ;
        echo "<a class=\"btn\" href=\"/admin/admin_index.php?session=$session\">". _('form_cancel') ."</a>\n";
    }

    /**
     * Encapsule le comportement du module de jours de fermeture
     *
     * @param string $session
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageJoursFermetureModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_hr", $DEBUG);

        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $choix_action                 = getpost_variable('choix_action');
        $year                        = getpost_variable('year', 0);
        $groupe_id                    = getpost_variable('groupe_id');
        $id_type_conges                = getpost_variable('id_type_conges');
        $new_date_debut                = getpost_variable('new_date_debut'); // valeur par dédaut = aujourd'hui
        $new_date_fin                  = getpost_variable('new_date_fin');   // valeur par dédaut = aujourd'hui
        $fermeture_id                  = getpost_variable('fermeture_id', 0);
        $fermeture_date_debut        = getpost_variable('fermeture_date_debut');
        $fermeture_date_fin            = getpost_variable('fermeture_date_fin');
        $code_erreur                = getpost_variable('code_erreur', 0);

        // si les dates de début ou de fin ne sont pas passé par get/post alors date du jours.
        if($new_date_debut=="") {
            if($year==0)
                $new_date_debut=date("d/m/Y") ;
            else
                $new_date_debut=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
        }
        if($new_date_fin=="") {
            if($year==0)
                $new_date_fin=date("d/m/Y") ;
            else
                $new_date_fin=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
        }

        if($year ==0)
            $year= date("Y");

        /*************************************/

        //debugage
        if( $DEBUG ) { echo "choix_action = $choix_action // year = $year // groupe_id = $groupe_id<br>\n"; }
        if( $DEBUG ) { echo "new_date_debut = $new_date_debut // new_date_fin = $new_date_fin<br>\n"; }
        if( $DEBUG ) { echo "fermeture_id = $fermeture_id // fermeture_date_debut = $fermeture_date_debut // fermeture_date_fin = $fermeture_date_fin<br>\n"; }

        /***********************************/
        /*  VERIF DES DATES RECUES   */
        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
        $date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
        $date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
        $timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

        if( $DEBUG ) { echo "timestamp_date_debut = $timestamp_date_debut // timestamp_date_fin = $timestamp_date_fin // timestamp_today = $timestamp_today<br>\n"; }

        /*********************************/
        /*   COMPOSITION DES ONGLETS...  */
        /*********************************/

        $onglet = getpost_variable('onglet');

        if(!$onglet)
            $onglet = 'saisie';

        $onglets = array();
        $onglets['saisie'] = _('admin_jours_fermeture_titre') . " " . "<span class=\"current-year\">$year</span>";
        $onglets['calendar'] = 'Calendrier des fermetures' . " " . "<span class=\"current-year\">$year</span>";
        $onglets['year_nav'] = NULL;

        //initialisation de l'action par défaut : saisie_dates pour tous, saisie_groupe en cas de gestion et fermeture par groupe autorisée
        if($choix_action=="") {
            // si pas de gestion par groupe
            if($_SESSION['config']['gestion_groupes']==FALSE)
                 $choix_action="saisie_dates";
            // si gestion par groupe et fermeture_par_groupe
            elseif(($_SESSION['config']['fermeture_par_groupe']) && ($groupe_id=="") )
                 $choix_action="saisie_groupe";
            else
                 $choix_action="saisie_dates";
        }

        /*********************************/
        /*   COMPOSITION DU HEADER...    */
        /*********************************/

        $add_css = '<style>#onglet_menu .onglet{ width: '. (str_replace(',', '.', 100 / count($onglets) )).'% ;}</style>';

        /***********************************/
        // AFFICHAGE DE LA PAGE
        header_menu('', 'Libertempo : '._('divers_fermeture'), $add_css);
        include ROOT_PATH .'fonctions_javascript.php' ;

        /*********************************/
        /*   AFFICHAGE DES ONGLETS...  */
        /*********************************/
        echo '<div id="onglet_menu">';
        foreach($onglets as $key => $title) {
            if($key == 'year_nav')
            {
                // navigation
                $prev_link = "$PHP_SELF?session=$session&onglet=$onglet&year=". ($year - 1) . "&groupe_id=$groupe_id";
                $next_link = "$PHP_SELF?session=$session&onglet=$onglet&year=". ($year + 1) . "&groupe_id=$groupe_id";
                echo "<div class=\"onglet calendar-nav\">\n";
                echo "<ul>\n";
                echo "<li><a href=\"$prev_link\" class=\"calendar-prev\"><i class=\"fa fa-chevron-left\"></i><span>année précédente</span></a></li>\n";
                echo "<li class=\"current-year\">$year</li>\n";
                echo "<li><a href=\"$next_link\" class=\"calendar-next\"><i class=\"fa fa-chevron-right\"></i><span>année suivante</span></a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
            } else {
                echo '<div class="onglet '.($onglet == $key ? ' active': '').'" ><a href="' . $PHP_SELF . '?session=' . $session . "&year=$year&onglet=" . $key . '">' . $title . '</a></div>';
            }
        }
        echo '</div>';

        // vérifie si les jours fériés sont saisie pour l'année en cours
        if( (verif_jours_feries_saisis($date_debut_yyyy_mm_dd, $DEBUG)==FALSE) && (verif_jours_feries_saisis($date_fin_yyyy_mm_dd, $DEBUG)==FALSE) ) {
                $code_erreur=1 ;  // code erreur : jour feriés non saisis
                $onglet="calendar";
        }

        //initialisation de l'action demandée : saisie_dates, commit_new_fermeture pour enregistrer une fermeture, annul_fermeture pour confirmer une annulation, commit_annul_fermeture pour annuler une fermeture

        //en cas de confirmation d'une fermeture :
        if($choix_action == "commit_new_fermeture") {
            // on verifie que $new_date_debut est anterieure a $new_date_fin
            if($timestamp_date_debut > $timestamp_date_fin)
            {
                $code_erreur=2 ;  // code erreur : $new_date_debut est posterieure a $new_date_fin
                $choix_action="saisie_dates";
            }
            // on verifie que ce ne sont pas des dates passées
            elseif($timestamp_date_debut < $timestamp_today) {
                $code_erreur=3 ;  // code erreur : saisie de date passée
                $choix_action="saisie_dates";
            }
            // on ne verifie QUE si date_debut ou date_fin sont !=  d'aujourd'hui
            // (car aujourd'hui est la valeur par dédaut des dates, et on ne peut saisir aujourd'hui puisque c'est fermé !)
            elseif( ($timestamp_date_debut==$timestamp_today) || ($timestamp_date_fin==$timestamp_today) ) {
                $code_erreur=4 ;  // code erreur : saisie de aujourd'hui
                $choix_action="saisie_dates";
            } else {
                // fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
                $tab_periode_calcul = make_tab_demi_jours_periode($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, "am", "pm", $DEBUG);
                // on verifie si la periode saisie ne chevauche pas une periode existante
                if(\hr\Fonctions::verif_periode_chevauche_periode_groupe($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, '', $tab_periode_calcul, $groupe_id, $DEBUG) )
                {
                    $code_erreur=5 ;  // code erreur : fermeture chevauche une periode deja saisie
                    $choix_action="saisie_dates";
                }
            }
        }

        if($onglet == 'calendar') {

            // les jours fériés de l'annee de la periode saisie ne sont pas enregistrés
            if($code_erreur==1)
                echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_annee_non_saisie') . "</div>\n";

                   /************************************************/
            // CALENDRIER DES FERMETURES
            \hr\Fonctions::affiche_calendrier_fermeture($year, $DEBUG);
        } elseif($choix_action=="saisie_dates") {
            if($groupe_id=="") // choix du groupe n'a pas été fait ($_SESSION['config']['fermeture_par_groupe']==FALSE)
                $groupe_id=0;

            // $new_date_debut est anterieure a $new_date_fin
            if($code_erreur==2)
                echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_dates_incompatibles') . "</div>\n";

            // ce ne sont des dates passées
            if($code_erreur==3)
                echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_date_passee_error') . "</div>\n";

            // fermeture le jour même impossible
            if($code_erreur==4)
                echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_fermeture_aujourd_hui') . "</div>\n";

            // la periode saisie chevauche une periode existante
            if($code_erreur==5)
                echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_chevauche_periode') . "</div>\n";

            echo "<div class=\"wrapper\">";
            echo '<a href="' . ROOT_PATH . "hr/hr_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode rh</a>\n";
            if($onglet == 'saisie')
                    \hr\Fonctions::saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur, $DEBUG);
        } elseif($choix_action=="saisie_groupe") {
            echo '<div class="wrapper">';
            echo '<a href="' . ROOT_PATH . "hr/hr_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode rh</a>\n";
                   \hr\Fonctions::saisie_groupe_fermeture($DEBUG);
                echo '</div>';
        } elseif($choix_action=="commit_new_fermeture") {
            echo $title;
            \hr\Fonctions::commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges, $DEBUG);
        } elseif($choix_action=="annul_fermeture") {
            echo $title;
            \hr\Fonctions::confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG);
        } elseif($choix_action=="commit_annul_fermeture") {
            echo $title;
            \hr\Fonctions::commit_annul_fermeture($fermeture_id, $groupe_id, $DEBUG);
        }

        bottom();
    }
}
