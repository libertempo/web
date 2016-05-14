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
namespace utilisateur;

/**
* Regroupement des fonctions liées à l'utilisateur
*/
class Fonctions
{
    // renvoit le type d'absence (conges ou absence) d'une absence
    public static function get_type_abs($_type_abs_id)
    {
        $sql_abs='SELECT ta_type FROM conges_type_absence WHERE ta_id="'. \includes\SQL::quote($_type_abs_id).'"';
        $ReqLog_abs = \includes\SQL::query($sql_abs);

        if($resultat_abs = $ReqLog_abs->fetch_array())
            return $resultat_abs["ta_type"];
        else
            return "" ;
    }

    public static function verif_solde_user($user_login, $type_conges, $nb_jours)
    {
        $verif = TRUE;
        // on ne tient compte du solde que pour les absences de type conges (conges avec solde annuel)
        if (\utilisateur\Fonctions::get_type_abs($type_conges)=="conges")
        {
            // recup du solde de conges de type $type_conges pour le user de login $user_login
            $select_solde='SELECT su_solde FROM conges_solde_user WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_id='. \includes\SQL::quote($type_conges);
            $ReqLog_solde_conges = \includes\SQL::query($select_solde);
            $resultat_solde = $ReqLog_solde_conges->fetch_array();
            $sql_solde_user = $resultat_solde["su_solde"];

            // recup du nombre de jours de conges de type $type_conges pour le user de login $user_login qui sont à valider par son resp ou le grd resp
            $select_solde_a_valider='SELECT SUM(p_nb_jours) FROM conges_periode WHERE p_login="'. \includes\SQL::quote($user_login).'" AND p_type='. \includes\SQL::quote($type_conges).' AND (p_etat=\'demande\' OR p_etat=\'valid\') ';
            $ReqLog_solde_conges_a_valider = \includes\SQL::query($select_solde_a_valider);
            $resultat_solde_a_valider = $ReqLog_solde_conges_a_valider->fetch_array();
            $sql_solde_user_a_valider = $resultat_solde_a_valider["SUM(p_nb_jours)"];
            if ($sql_solde_user_a_valider == NULL )
                $sql_solde_user_a_valider = 0;

            // vérification du solde de jours de type $type_conges
            if ($sql_solde_user < $nb_jours+$sql_solde_user_a_valider)
            {
                echo '<p class="bg-danger">'.schars( _('verif_solde_erreur_part_1') ).' ('.(float)schars($nb_jours).') '.schars( _('verif_solde_erreur_part_2') ).' ('.(float)schars($sql_solde_user).') '.schars( _('verif_solde_erreur_part_3') ).' ('.(float)schars($sql_solde_user_a_valider).')</p>'."\n";
                $verif = FALSE;
            }
        }
        return $verif;
    }

    // verifie les parametre de la nouvelle demande :si ok : enregistre la demande dans table conges_periode
    public static function new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type)
    {
        //conversion des dates
        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);
        $return = '';

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // verif validité des valeurs saisies
        $valid = verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

        // verifie que le solde de conges sera encore positif après validation
        if( $_SESSION['config']['solde_toujours_positif'] ) {
            $valid = $valid && \utilisateur\Fonctions::verif_solde_user($_SESSION['userlogin'], $new_type, $new_nb_jours);
        }

        if( $valid ) {
            if( in_array(\utilisateur\Fonctions::get_type_abs($new_type) , array('conges','conges_exceptionnels') ) ) {
                $resp_du_user = get_tab_resp_du_user($_SESSION['userlogin']);
                if ((1 === count($resp_du_user) && isset($resp_du_user['conges']))||empty($resp_du_user)) {
                    $new_etat = 'ok' ;
                    soustrait_solde_et_reliquat_user($_SESSION['userlogin'], "", $new_nb_jours, $new_type, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin);
                } else {
                    $new_etat = 'demande' ;
                }
            } else {
                $new_etat = 'ok' ;
            }

            $new_comment = addslashes($new_comment);

            $periode_num = insert_dans_periode($_SESSION['userlogin'], $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $new_etat, 0);

            if ( $periode_num != 0 ) {
                $return .= schars( _('form_modif_ok') ) . ' !<br><br>.';
                //envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_new_demande_alerte_resp']){
                    if(in_array(\utilisateur\Fonctions::get_type_abs($new_type), array('absences'))) {
                        alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_absence_conges");
                    } else {
                        alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_demande");
                    }
                }
            }
            else {
                $return .= schars( _('form_modif_not_ok') ) . ' !<br><br>.';
            }
        }
        else {
            $return .= schars( _('resp_traite_user_valeurs_not_ok') ) . ' !<br><br>.';
        }

        $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '">' . _('form_retour') . '</a>';

        return $return;
    }

    /**
     * Encapsule le comportement du module de nouvelle absence
     *
     * @param string $onglet Nom de l'onglet à afficher
     *
     * @return void
     * @access public
     * @static
     */
    public static function nouvelleAbsenceModule($onglet)
    {
        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        init_tab_jours_feries();
        $return = '';

        // si le user peut saisir ses demandes et qu'il vient d'en saisir une ...

        $new_demande_conges = getpost_variable('new_demande_conges', 0);

        if( $new_demande_conges == 1 && $_SESSION['config']['user_saisie_demande'] ) {
            $new_debut        = getpost_variable('new_debut');
            $new_demi_jour_deb  = getpost_variable('new_demi_jour_deb');
            $new_fin        = getpost_variable('new_fin');
            $new_demi_jour_fin  = getpost_variable('new_demi_jour_fin');
            $new_comment        = getpost_variable('new_comment');
            $new_type        = getpost_variable('new_type');

            $user_login        = $_SESSION['userlogin'];

            if( $_SESSION['config']['disable_saise_champ_nb_jours_pris'] ) {
                $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);
            } else {
                $new_nb_jours = getpost_variable('new_nb_jours') ;
            }

            $return .= \utilisateur\Fonctions::new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type);
        } else {
            $year_calendrier_saisie_debut   = getpost_variable('year_calendrier_saisie_debut'   , date('Y'));
            $mois_calendrier_saisie_debut   = getpost_variable('mois_calendrier_saisie_debut'   , date('m'));
            $year_calendrier_saisie_fin     = getpost_variable('year_calendrier_saisie_fin'     , date('Y'));
            $mois_calendrier_saisie_fin     = getpost_variable('mois_calendrier_saisie_fin'     , date('m'));

            /**************************/
            /* Nouvelle Demande */
            /**************************/
            /* Génération du datePicker et de ses options */
            $daysOfWeekDisabled = [];
            $datesDisabled      = [];
            if ((false == $_SESSION['config']['dimanche_travail'])
                && (false == $_SESSION['config']['samedi_travail'])
            ) {
                $daysOfWeekDisabled = [0,6];
            } else {
                if (false == $_SESSION['config']['dimanche_travail']) {
                    $daysOfWeekDisabled = [0];
                }
                if (false == $_SESSION['config']['samedi_travail']) {
                    $daysOfWeekDisabled = [6];
                }
            }

            if (is_array($_SESSION["tab_j_feries"])) {
                foreach ($_SESSION["tab_j_feries"] as $date) {
                    $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
                }
            }

            if (is_array($_SESSION["tab_j_fermeture"])) {
                foreach ($_SESSION["tab_j_fermeture"] as $date) {
                    $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
                }
            }
            $startDate = ($_SESSION['config']['interdit_saisie_periode_date_passee']) ? 'd' : '';

            $datePickerOpts = [
                'daysOfWeekDisabled' => $daysOfWeekDisabled,
                'datesDisabled'      => $datesDisabled,
                'startDate'          => $startDate,
            ];
            $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ');</script>';
            $return .= '<h1>' . _('divers_nouvelle_absence') . '</h1>';

            //affiche le formulaire de saisie d'une nouvelle demande de conges
            $return .= saisie_nouveau_conges2($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
        }
        return $return;
    }

    public static function modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';
        $VerifNb = verif_saisie_decimal($new_nb_jours);
        $sql1 = "UPDATE conges_periode
            SET p_date_deb='$new_debut', p_demi_jour_deb='$new_demi_jour_deb', p_date_fin='$new_fin', p_demi_jour_fin='$new_demi_jour_fin', p_nb_jours='$new_nb_jours', p_commentaire='$new_comment', ";
        if($p_etat=="demande")
            $sql1 = $sql1." p_date_demande=NOW() ";
        else
            $sql1 = $sql1." p_date_traitement=NOW() ";
        $sql1 = $sql1."    WHERE p_num='$p_num_to_update' AND p_login='".$_SESSION['userlogin']."' ;" ;

        $result = \includes\SQL::query($sql1) ;

        if($_SESSION['config']['mail_modif_demande_alerte_resp']) {
            alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_update, "modif_demande_conges");
        }
        $comment_log = "modification de demande num $p_num_to_update ($new_nb_jours jour(s)) ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
        log_action($p_num_to_update, "$p_etat", $_SESSION['userlogin'], $comment_log);


        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="'.ROOT_PATH .'utilisateur/user_index.php?session='.$session.'&onglet=demandes_en_cours" method="POST">';
        $return .= '<input class="btn" type="submit" value="'. _('form_submit') .'">';
        $return .= '</form>';

        return $return;

    }

    public static function confirmer($p_num, $onglet)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        // Récupération des informations
        $sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_etat, p_num FROM conges_periode where p_num = "'. \includes\SQL::quote($p_num).'"';
        $ReqLog1 = \includes\SQL::query($sql1) ;

        // AFFICHAGE TABLEAU

        $return .= '<form NAME="dem_conges" action="' . $PHP_SELF . '" method="POST">' ;
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead>';
        // affichage première ligne : titres
        $childTable .= '<tr>';
        $childTable .= '<td>' . _('divers_debut_maj_1') . '</td>';
        $childTable .= '<td>' . _('divers_fin_maj_1') . '</td>';
        $childTable .= '<td>' . _('divers_nb_jours_maj_1') . '</td>';
        $childTable .= '<td>' . _('divers_comment_maj_1') . '</td>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        // affichage 2ieme ligne : valeurs actuelles
        $childTable .= '<tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);

            $sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
            if($sql_demi_jour_deb=="am")
                $demi_j_deb= _('divers_am_short') ;
            else
                $demi_j_deb= _('divers_pm_short') ;
            $sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
            $sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
            if($sql_demi_jour_fin=="am")
                $demi_j_fin= _('divers_am_short') ;
            else
                $demi_j_fin= _('divers_pm_short') ;
            $sql_nb_jours=$resultat1["p_nb_jours"];
            $aff_nb_jours=affiche_decimal($sql_nb_jours);
            $sql_commentaire=$resultat1["p_commentaire"];
            $sql_etat=$resultat1["p_etat"];

            $childTable .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td><td>' . $sql_date_fin  . '_' . $demi_j_fin . '</td><td>' . $aff_nb_jours . '</td><td>' . $sql_commentaire . '</td>';

            $compte ="";
            if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']) {
                $compte = 'onChange="compter_jours();return false;"';
            }

            $text_debut="<input class=\"form-control date\" type=\"text\" name=\"new_debut\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_deb) . "\">" ;
            if($sql_demi_jour_deb=="am") {
                $radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\" checked>&nbsp;". _('form_am') ;
                $radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\">&nbsp;". _('form_pm') ;
            } else {
                $radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\">". _('form_am') ;
                $radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\" checked>". _('form_pm') ;
            }
            $text_fin="<input class=\"form-control date\" type=\"text\" name=\"new_fin\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_fin) . "\">" ;
            if($sql_demi_jour_fin=="am") {
                $radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\" checked>". _('form_am') ;
                $radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\">". _('form_pm') ;
            } else {
                $radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\">". _('form_am') ;
                $radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\" checked>". _('form_pm') ;
            }
            if($_SESSION['config']['disable_saise_champ_nb_jours_pris'])
                $text_nb_jours="<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\">" ;
            else
                $text_nb_jours="<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\">" ;


            $text_commentaire="<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"15\" maxlength=\"30\" value=\"$sql_commentaire\">" ;
        }
        $childTable .= '</tr>';

        // affichage 3ieme ligne : saisie des nouvelles valeurs
        $childTable .= '<tr>';
        $childTable .= '<td>' . $text_debut . '<br>' . $radio_deb_am . '/' . $radio_deb_pm . '</td><td>' . $text_fin . '<br>' . $radio_fin_am . '/' .  $radio_fin_pm . '</td><td>' . $text_nb_jours . '</td><td>' . $text_commentaire . '</td>';
        $childTable .= '</tr>';

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="p_num_to_update" value="' . $p_num . '">';
        $return .= '<input type="hidden" name="p_etat" value="' . $sql_etat . '">';
        $return .= '<input type="hidden" name="session" value="' . $session . '">';
        $return .= '<input type="hidden" name="user_login" value="'.$_SESSION['userlogin'].'">';
        $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
        $return .= '<p id="comment_nbj" style="color:red">&nbsp;</p>';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '&onglet=demandes_en_cours">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    /**
     * Encapsule le comportement du module de modification d'absence
     *
     *
     * @return void
     * @access public
     * @static
     */
    public static function modificationAbsenceModule()
    {
        $user_login        = $_SESSION['userlogin'];
        $p_num             = getpost_variable('p_num');
        $onglet            = getpost_variable('onglet');
        $p_num_to_update   = getpost_variable('p_num_to_update');
        $p_etat               = getpost_variable('p_etat');
        $new_debut         = getpost_variable('new_debut');
        $new_demi_jour_deb = getpost_variable('new_demi_jour_deb');
        $new_fin           = getpost_variable('new_fin');
        $new_demi_jour_fin = getpost_variable('new_demi_jour_fin');
        $new_comment       = getpost_variable('new_comment');
        $return            = '';

        //conversion des dates
        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);

        if ($_SESSION['config']['disable_saise_champ_nb_jours_pris'])
            $new_nb_jours = compter($user_login, $p_num_to_update, $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);
        else
            $new_nb_jours = getpost_variable('new_nb_jours');

        /*************************************/

        // TITRE
        $return .= '<h1>'. _('user_modif_demande_titre') .'</h1>';

        if($p_num!="") {
            $return .= \utilisateur\Fonctions::confirmer($p_num, $onglet);
        } else {
            if($p_num_to_update != "") {
                $return .= \utilisateur\Fonctions::modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet);
            } else {
                // renvoit sur la page principale .
                redirect( ROOT_PATH .'utilisateur/user_index.php', false );
            }
        }

        return $return;
    }

    // renvoit le libelle d une absence (conges ou absence) d une absence
    public static function get_libelle_abs($_type_abs_id)
    {

        $sql_abs='SELECT ta_libelle FROM conges_type_absence WHERE ta_id="'. \includes\SQL::quote($_type_abs_id).'"';
        $ReqLog_abs = \includes\SQL::query($sql_abs);
        if($resultat_abs = $ReqLog_abs->fetch_array())
            return $resultat_abs['ta_libelle'];
        else
            return "" ;
    }

    public static function suppression($p_num_to_delete, $onglet)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        if($_SESSION['config']['mail_supp_demande_alerte_resp']) {
            alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_delete, "supp_demande_conges");
        }

        $sql_delete = 'DELETE FROM conges_periode WHERE p_num = '.\includes\SQL::quote($p_num_to_delete).' AND p_login="'.\includes\SQL::quote($_SESSION['userlogin']).'";';
        $result_delete = \includes\SQL::query($sql_delete);

        $comment_log = "suppression de demande num $p_num_to_delete";
        log_action($p_num_to_delete, "", $_SESSION['userlogin'], $comment_log);

        if($result_delete)
            $return .= _('form_modif_ok') ."<br><br> \n";
        else
            $return .= _('form_modif_not_ok') ."<br><br> \n";

        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="'.ROOT_PATH .'utilisateur/user_index.php?session='.$session.'&onglet=demandes_en_cours" method="POST">';
        $return .= '<input class="btn" type="submit" value="'. _('form_submit') .'">';
        $return .= '</form>';
        $return .= '<a href="">';

        return $return;
    }

    public static function confirmerSuppression($p_num, $onglet)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id() ;
        $return = '';

        // Récupération des informations
        $sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_num FROM conges_periode WHERE p_num = "'.\includes\SQL::quote($p_num).'"';
        //printf("sql1 = %s<br>\n", $sql1);
        $ReqLog1 = \includes\SQL::query($sql1) ;

        // AFFICHAGE TABLEAU
        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_nb_jours_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        $childTable .= '<tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);
            $sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
            if($sql_demi_jour_deb=="am")
                $demi_j_deb= _('divers_am_short') ;
            else
                $demi_j_deb= _('divers_pm_short') ;
            $sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
            $sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
            if($sql_demi_jour_fin=="am")
                $demi_j_fin= _('divers_am_short') ;
            else
                $demi_j_fin= _('divers_pm_short') ;
            $sql_nb_jours=affiche_decimal($resultat1["p_nb_jours"]);
            //$sql_type=$resultat1["p_type"];
            $sql_type= \utilisateur\Fonctions::get_libelle_abs($resultat1["p_type"]);
            $sql_comment=$resultat1["p_commentaire"];

            $childTable .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td>';
            $childTable .= '<td>' . $sql_date_fin . '_' . $demi_j_fin . '</td>';
            $childTable .= '<td>' . $sql_nb_jours . '</td>';
            $childTable .= '<td>' . $sql_comment . '</td>';
            $childTable .= '<td>' . $sql_type . '</td>';
        }
        $childTable .= '</tr>';
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="p_num_to_delete" value="' . $p_num . '">';
        $return .= '<input type="hidden" name="session" value="' . $session . '">';
        $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '&onglet=demandes_en_cours">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    /**
     * Encapsule le comportement du module de suppression d'absence
     *
     *
     * @return void
     * @access public
     * @static
     */
    public static function suppressionAbsenceModule()
    {
        $p_num           = getpost_variable('p_num');
        $onglet          = getpost_variable('onglet');
        $p_num_to_delete = getpost_variable('p_num_to_delete');
        $return          = '';
        /*************************************/

        // TITRE
        $return .= '<h1>'. _('user_suppr_demande_titre') .'</h1>';
        $return .= '<br>';

        if($p_num!="") {
            $return .= \utilisateur\Fonctions::confirmerSuppression($p_num, $onglet);
        } else {
            if($p_num_to_delete!="") {
                $return .= \utilisateur\Fonctions::suppression($p_num_to_delete, $onglet);
            } else {
                // renvoit sur la page principale .
                redirect( ROOT_PATH .'utilisateur/user_index.php', false );
            }
        }

        return $return;
    }

    public static function change_passwd( $new_passwd1, $new_passwd2)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $return = '';

        if((strlen($new_passwd1)==0) || (strlen($new_passwd2)==0) || ($new_passwd1!=$new_passwd2)) // si les 2 passwd sont vides ou differents
        {
            $return .= _('user_passwd_error') ."<br>\n" ;
        }
        else
        {
            $passwd_md5=md5($new_passwd1);
            $sql1 = 'UPDATE conges_users SET  u_passwd=\''.$passwd_md5.'\' WHERE u_login=\''.$_SESSION['userlogin'].'\' ';
            $result = \includes\SQL::query($sql1) ;

            if($result)
                $return .= _('form_modif_ok') ." <br><br> \n";
            else
                $return .= _('form_mofif_not_ok') ."<br><br> \n";
        }

        $comment_log = 'changement Password';
        log_action(0, '', $_SESSION['userlogin'], $comment_log);

        return $return;
    }

    /**
     * Encapsule le comportement du module de modification de mot de passe
     *
     * @param string $onglet Nom de l'onglet à afficher
     *
     * @return void
     * @access public
     * @static
     */
    public static function modificationMotDePasseModule($onglet)
    {
        $return = '';
        if($_SESSION['config']['where_to_find_user_email']=="ldap"){
            include_once CONFIG_PATH .'config_ldap.php';
        }

        $change_passwd = getpost_variable('change_passwd', 0);
        $new_passwd1 = getpost_variable('new_passwd1');
        $new_passwd2 = getpost_variable('new_passwd2');

        if($change_passwd==1) {
            $return .= \utilisateur\Fonctions::change_passwd($new_passwd1, $new_passwd2);
        } else {
            $PHP_SELF=$_SERVER['PHP_SELF'];
            $session=session_id();

            $return .= '<h1>' . _('user_change_password') . '</h1>';
            $return .= '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $childTable = '<thead>';
            $childTable .= '<tr>
                <th class="titre">'. _('user_passwd_saisie_1') .'</th>
                <th class="titre">'. _('user_passwd_saisie_2') .'</th>
                </tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            $text_passwd1    = '<input class="form-control" type="password" name="new_passwd1" size="10" maxlength="20" value="">';
            $text_passwd2    = '<input class="form-control" type="password" name="new_passwd2" size="10" maxlength="20" value="">';
            $childTable .= '<tr>';
            $childTable .= '<td>'.($text_passwd1).'</td><td>'.($text_passwd2).'</td>'."\n";
            $childTable .= '</tr>';

            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();

            $return .= '<hr/>';
            $return .= '<input type="hidden" name="change_passwd" value=1>';
            $return .= '<input class="btn btn-success" type="submit" value="'. _('form_submit') .'">';
            $return .= '</form>';
        }

        return $return;
    }

    /**
     * Encapsule le comportement du module de demande en cours
     *
     * @param string $session Clé de session

     *
     * @return void
     * @access public
     * @static
     */
    public static function demandeEnCoursModule($session)
    {
        $return = '';
        if($_SESSION['config']['where_to_find_user_email']=="ldap"){
            include_once CONFIG_PATH .'config_ldap.php';
        }


        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        init_tab_jours_feries();

        $return .= '<h1>' . _('user_etat_demandes') . '</h1>';

        $tri_date = getpost_variable('tri_date', "ascendant");


        // Récupération des informations
        // on ne recup QUE les periodes de type "conges"(cf table conges_type_absence) ET QUE les demandes
        $sql3 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num, ta_libelle
            FROM conges_periode as a, conges_type_absence as b
            WHERE a.p_login = "'. \includes\SQL::quote($_SESSION['userlogin']).'"
            AND (a.p_type=b.ta_id)
            AND ( (b.ta_type=\'conges\') OR (b.ta_type=\'conges_exceptionnels\') )
            AND ((p_etat=\'demande\') OR (p_etat=\'valid\')) ';
        if($tri_date=='descendant')
            $sql3=$sql3.' ORDER BY p_date_deb DESC ;';
        else
            $sql3=$sql3.' ORDER BY p_date_deb ASC ;';
        $ReqLog3 = \includes\SQL::query($sql3) ;

        $count3=$ReqLog3->num_rows;
        if($count3==0) {
            $return .= '<b>'. _('user_demandes_aucune_demande') .'</b>';
        } else {
            // AFFICHAGE TABLEAU
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped',
            ]);
            $childTable = '<thead>';
            $childTable .= '<tr>';
            $childTable .= '<th>';
            $childTable .= _('divers_debut_maj_1')  ;
            $childTable .= '</th>';
            $childTable .= '<th>'. _('divers_fin_maj_1') .'</th>';
            $childTable .= '<th>'. _('divers_type_maj_1') .'</th>';
            $childTable .= '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
            $childTable .= '<th>'. _('divers_comment_maj_1') .'</th>';
            $childTable .= '<th></th><th></th>' ;
            if( $_SESSION['config']['affiche_date_traitement'] ) {
                $childTable .= '<th >'. _('divers_date_traitement') .'</th>';
            }
            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            $i = true;
            while ($resultat3 = $ReqLog3->fetch_array()) {
                $sql_p_date_deb                = eng_date_to_fr($resultat3["p_date_deb"]);
                $sql_p_date_fin                = eng_date_to_fr($resultat3["p_date_fin"]);
                $sql_p_demi_jour_deb        = $resultat3["p_demi_jour_deb"];
                $sql_p_demi_jour_fin        = $resultat3["p_demi_jour_fin"];

                if($sql_p_demi_jour_deb=="am")
                    $demi_j_deb="mat";
                else
                    $demi_j_deb="aprm";

                if($sql_p_demi_jour_fin=="am")
                    $demi_j_fin="mat";
                else
                    $demi_j_fin="aprm";

                $sql_p_nb_jours            = $resultat3["p_nb_jours"];
                $sql_p_commentaire        = $resultat3["p_commentaire"];
                $sql_p_type                = $resultat3["ta_libelle"];
                $sql_p_etat                = $resultat3["p_etat"];
                $sql_p_date_demande        = $resultat3["p_date_demande"];
                $sql_p_date_traitement    = $resultat3["p_date_traitement"];
                $sql_p_num                = $resultat3["p_num"];

                // si on peut modifier une demande :on defini le lien à afficher
                if( !$_SESSION['config']['interdit_modif_demande'] ) {
                    //on ne peut pas modifier une demande qui a déja été validé une fois (si on utilise la double validation)
                    if($sql_p_etat=="valid")
                        $user_modif_demande="&nbsp;";
                    else
                        $user_modif_demande="<a href=\"user_index.php?session=$session&p_num=$sql_p_num&onglet=modif_demande\">". _('form_modif') ."</a>" ;
                }
                $user_suppr_demande="<a href=\"user_index.php?session=$session&p_num=$sql_p_num&onglet=suppr_demande\">". _('form_supprim') ."</a>" ;
                $childTable .= '<tr class="'.($i?'i':'p').'">';
                $childTable .= '<td class="histo">'.schars($sql_p_date_deb).' _ '.schars($demi_j_deb).'</td>';
                $childTable .= '<td class="histo">'.schars($sql_p_date_fin).' _ '.schars($demi_j_fin).'</td>' ;
                $childTable .= '<td class="histo">'.schars($sql_p_type).'</td>' ;
                $childTable .= '<td class="histo">'.affiche_decimal($sql_p_nb_jours).'</td>' ;
                $childTable .= '<td class="histo">'.schars($sql_p_commentaire).'</td>' ;
                if( !$_SESSION['config']['interdit_modif_demande'] ) {
                    $childTable .= '<td class="histo">'.($user_modif_demande).'</td>' ;
                }
                $childTable .= '<td class="histo">'.($user_suppr_demande).'</td>'."\n" ;

                if( $_SESSION['config']['affiche_date_traitement'] ) {
                    if($sql_p_date_demande == NULL)
                        $childTable .= '<td class="histo-left">'. _('divers_demande') .' : '.$sql_p_date_demande.'<br>'. _('divers_traitement') .' : '.$sql_p_date_traitement.'</td>';
                    else
                        $childTable .= '<td class="histo-left">'. _('divers_demande') .' : '.$sql_p_date_demande.'<br>'. _('divers_traitement') .' : pas traité</td>';
                }
                $childTable .= '</tr>';
                $i = !$i;
            }
            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
        }

        return $return;
    }

    // affichage du calendrier du mois avec les case à cocher sur les jour de présence
    public static function  affiche_calendrier_saisie_jour_presence($user_login, $year, $mois)
    {
        $return = '';
        $jour_today                    = date('j');
        $jour_today_name            = date('D');

        $first_jour_mois_timestamp    = mktime(0,0,0,$mois,1,$year);
        $last_jour_mois_timestamp    = mktime(0,0,0,$mois +1 , 0,$year);

        $mois_name                    = date_fr('F', $first_jour_mois_timestamp);

        $first_jour_mois_rang        = date('w', $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        $last_jour_mois_rang        = date('w', $last_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        $nb_jours_mois                = ( $last_jour_mois_timestamp - $first_jour_mois_timestamp  + 60*60 *12 ) / (24 * 60 * 60);// + 60*60 *12 for fucking DST

        if( $first_jour_mois_rang == 0 )
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        if( $last_jour_mois_rang == 0 )
            $last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'calendrier_saisie_date',
        ]);
        $childTable = '<thead>
            <tr>
            <th colspan="7" class="titre"> '.$mois_name.' '.$year.' </th>
            </tr>
            <tr>
            <th class="cal-saisie2">'. _('lundi_1c') .'</th>
            <th class="cal-saisie2">'. _('mardi_1c') .'</th>
            <th class="cal-saisie2">'. _('mercredi_1c') .'</th>
            <th class="cal-saisie2">'. _('jeudi_1c') .'</th>
            <th class="cal-saisie2">'. _('vendredi_1c') .'</th>
            <th class="cal-saisie2">'. _('samedi_1c') .'</th>
            <th class="cal-saisie2">'. _('dimanche_1c') .'</th>
            </tr>
            </thead>';
        $childTable .= '<tbody>';

        $start_nb_day_before = $first_jour_mois_rang -1;
        $stop_nb_day_before = 7 - $last_jour_mois_rang ;


        for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
            if ( ($i + $start_nb_day_before ) % 7 == 0)
                $childTable .= '<tr>';


            $j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
            $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

            if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
                $childTable .= '<td class="'.$td_second_class.'">-</td>';
            }
            else {
                $val_matin='';
                $val_aprem='';
                recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem);
                $childTable .= \utilisateur\Fonctions::affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1);
            }

            if ( ($i + $start_nb_day_before ) % 7 == 6)
                $childTable .= '<tr>';
        }

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        return $return;
    }

    //affichage du calendrier du mois avec les case à cocher sur les jour d'absence
    public static function  affiche_calendrier_saisie_jour_absence($user_login, $year, $mois)
    {
        $return = '';
        $jour_today                    = date('j');
        $jour_today_name            = date('D');

        $first_jour_mois_timestamp    = mktime(0,0,0,$mois,1,$year);
        $last_jour_mois_timestamp    = mktime(0,0,0,$mois + 1, 0 ,$year);

        $mois_name                    = date_fr('F', $first_jour_mois_timestamp);

        $first_jour_mois_rang        = date('w', $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        $last_jour_mois_rang        = date('w', $last_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        $nb_jours_mois                = ( $last_jour_mois_timestamp - $first_jour_mois_timestamp  + 60*60 *12 ) / (24 * 60 * 60);// + 60*60 *12 for fucking DST

        if( $first_jour_mois_rang == 0 )
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        if( $last_jour_mois_rang == 0 )
            $last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'calendrier_saisie_date',
        ]);
        $childTable = '<thead><tr><th colspan="7" class="titre"> '.$mois_name.' '.$year.' </th></tr><tr><th class="cal-saisie2">'. _('lundi_1c') .'</th><th class="cal-saisie2">'. _('mardi_1c') .'</th><th class="cal-saisie2">'. _('mercredi_1c') .'</th><th class="cal-saisie2">'. _('jeudi_1c') .'</th><th class="cal-saisie2">'. _('vendredi_1c') .'</th><th class="cal-saisie2">'. _('samedi_1c') .'</th><th class="cal-saisie2">'. _('dimanche_1c') .'</th></tr></thead>';
        $childTable .= '<tbody>';

        $start_nb_day_before = $first_jour_mois_rang -1;
        $stop_nb_day_before = 7 - $last_jour_mois_rang ;


        for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
            if ( ($i + $start_nb_day_before ) % 7 == 0)
                $childTable .= '<tr>';

            $j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
            $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

            if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
                $childTable .= '<td class="'.$td_second_class.'">-</td>';
            }
            else {
                $val_matin='';
                $val_aprem='';
                recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem);
                $childTable .= \utilisateur\Fonctions::affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1);
            }

            if ( ($i + $start_nb_day_before ) % 7 == 6)
                $childTable .= '<tr>';
        }

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        return $return;
    }

    public static function affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j)
    {
        $return = '';
        $bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
        if( $val_matin == 'Y' && $val_aprem == 'Y')
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-j"></td>';
        elseif( $val_matin == 'Y' && $val_aprem == 'N' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-a"></td>';
        elseif( $val_matin == 'N' && $val_aprem == 'Y' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_travail_am_rtt_pm_w35">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-p"></td>';
        else {
            $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'</td>';
        }
        return $return;
    }

    public static function affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j)
    {
        $return = '';
        $bgcolor = $_SESSION['config']['temps_partiel_bgcolor'];
        if( $val_matin == 'Y' && $val_aprem == 'Y' )  // rtt le matin et l'apres midi !
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'</td>';
        elseif( $val_matin == 'Y' && $val_aprem == 'N' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-p"></td>';
        elseif( $val_matin == 'N' && $val_aprem == 'Y' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_travail_am_rtt_pm_w35">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-a"></td>';
        else
        {
            $bgcolor = $_SESSION['config']['semaine_bgcolor'];
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-j"></td>';
        }

        return $return;
    }

    public static function echange_absence_rtt($onglet, $new_debut_string, $new_fin_string, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee)
    {
        $return = '';

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $duree_demande_1="";
        $duree_demande_2="";
        $valid=TRUE;

        // verif si les dates sont renseignées  (si ce n'est pas le cas, on ne verifie meme pas la suite !)
        // $new_debut et $new_fin sont des string au format : $year-$mois-$jour-X  (avec X = j pour "jour entier", a pour "a" (matin), et p pour "pm" (apres midi) )
        if( ($new_debut_string=="")||($new_fin_string=="") )
            $valid=FALSE;
        else
        {
            $date_1=explode("-", $new_debut_string);
            $year_debut=$date_1[0];
            $mois_debut=$date_1[1];
            $jour_debut=$date_1[2];
            $demi_jour_debut=$date_1[3];

            $new_debut="$year_debut-$mois_debut-$jour_debut";

            $date_2=explode("-", $new_fin_string);
            $year_fin=$date_2[0];
            $mois_fin=$date_2[1];
            $jour_fin=$date_2[2];
            $demi_jour_fin=$date_2[3];

            $new_fin="$year_fin-$mois_fin-$jour_fin";


            /********************************************/
            // traitement du jour d'absence à remplacer

            // verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
            if($demi_jour_debut=="j") // on est absent la journee
            {
                if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="jour";
                }
                elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    $nouvelle_presence_date_1="M";
                    $nouvelle_absence_date_1="A";
                    $duree_demande_1="demi";
                }
                elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    $nouvelle_presence_date_1="A";
                    $nouvelle_absence_date_1="M";
                    $duree_demande_1="demi";
                }
            }
            elseif($demi_jour_debut=="a") // on est absent le matin
            {
                if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="demi";
                }
                elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                    {
                        $nouvelle_presence_date_1="M";
                        $nouvelle_absence_date_1="A";
                    }
                    else
                    {
                        $nouvelle_presence_date_1="J";
                        $nouvelle_absence_date_1="N";
                    }
                    $duree_demande_1="demi";
                }
                elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    $valid=FALSE;
                }
            }
            elseif($demi_jour_debut=="p") // on est absent l'aprem
            {
                if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="demi";
                }
                elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    $valid=FALSE;
                }
                elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                    {
                        $nouvelle_presence_date_1="A";
                        $nouvelle_absence_date_1="M";
                    }
                    else
                    {
                        $nouvelle_presence_date_1="J";
                        $nouvelle_absence_date_1="N";
                    }
                    $duree_demande_1="demi";
                }
            }
            else
                $valid=FALSE;


            /**********************************************/
            // traitement du jour de présence à remplacer

            // verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
            if($demi_jour_fin=="j") // on est present la journee
            {
                if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
                {
                    $nouvelle_presence_date_2="N";
                    $nouvelle_absence_date_2="J";
                    $duree_demande_2="jour";
                }
                elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
                {
                    $nouvelle_presence_date_2="A";
                    $nouvelle_absence_date_2="M";
                    $duree_demande_2="demi";
                }
                elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
                {
                    $nouvelle_presence_date_2="M";
                    $nouvelle_absence_date_2="A";
                    $duree_demande_2="demi";
                }
            }
            elseif($demi_jour_fin=="a") // on est present le matin
            {
                if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
                {
                    $nouvelle_presence_date_2="N";
                    $nouvelle_absence_date_2="J";
                    $duree_demande_2="demi";
                }
                elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
                {
                    if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                    {
                        $nouvelle_presence_date_2="A";
                        $nouvelle_absence_date_2="M";
                    }
                    else
                    {
                        $nouvelle_presence_date_2="N";
                        $nouvelle_absence_date_2="j";
                    }
                    $duree_demande_2="demi";
                }
                elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
                {
                    $valid=FALSE;
                }
            }
            elseif($demi_jour_fin=="p") // on est present l'aprem
            {
                if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
                {
                    $nouvelle_presence_date_2="N";
                    $nouvelle_absence_date_2="J";
                    $duree_demande_2="demi";
                }
                elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
                {
                    $valid=FALSE;
                }
                elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
                {
                    if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                    {
                        $nouvelle_presence_date_2="M";
                        $nouvelle_absence_date_2="A";
                    }
                    else
                    {
                        $nouvelle_presence_date_2="N";
                        $nouvelle_absence_date_2="J";
                    }
                    $duree_demande_2="demi";
                }
            }
            else
            {
                $valid=FALSE;
            }


            // verif de la concordance des durée (journée avec journée ou 1/2 journée avec1/2 journée)
            if( ($duree_demande_1=="") || ($duree_demande_2=="") || ($duree_demande_1!=$duree_demande_2) )
                $valid=FALSE;
        }



        if($valid) {
            $return .= schars($_SESSION['userlogin']) . ' --- ' . schars($new_debut) . ' --- ' . schars($new_fin) . ' --- ' . schars($new_comment) . '<br>';

            // insert du jour d'absence ordinaire (qui n'en sera plus un ou qu'a moitie ...)
            // e_presence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
            // verif si le couple user/date1 existe dans conges_echange_rtt ...
            $sql_verif_echange1='SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login="'. \includes\SQL::quote($_SESSION['userlogin']).'" AND e_date_jour="'. \includes\SQL::quote($new_debut).'";';
            $result_verif_echange1 = \includes\SQL::query($sql_verif_echange1) ;

            $count_verif_echange1=$result_verif_echange1->num_rows;

            // si le couple user/date1 existe dans conges_echange_rtt : on update
            if($count_verif_echange1!=0) {
                $new_comment=addslashes($new_comment);
                //$resultat1=$result_verif_echange1->fetch_array();
                //if($resultatverif_echange1['e_absence'] == 'N' )
                $sql1 = 'UPDATE conges_echange_rtt
                    SET e_absence=\''.$nouvelle_absence_date_1.'\', e_presence=\''.$nouvelle_presence_date_1.'\', e_comment=\''.$new_comment.'\'
                    WHERE e_login=\''.$_SESSION['userlogin'].'\' AND e_date_jour="'. \includes\SQL::quote($new_debut).'"  ';
            }
            else // sinon : on insert
            {
                $sql1 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
                    VALUES ('".$_SESSION['userlogin']."','$new_debut','$nouvelle_absence_date_1', '$nouvelle_presence_date_1', '$new_comment')" ;
            }
            $result1 = \includes\SQL::query($sql1);

            // insert du jour d'absence souhaité (qui en devient un)
            // e_absence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
            // verif si le couple user/date2 existe dans conges_echange_rtt ...
            $sql_verif_echange2='SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login="'.\includes\SQL::quote($_SESSION['userlogin']).'" AND e_date_jour="'. \includes\SQL::quote($new_fin).'";';
            $result_verif_echange2 = \includes\SQL::query($sql_verif_echange2);

            $count_verif_echange2=$result_verif_echange2->num_rows;

            // si le couple user/date2 existe dans conges_echange_rtt : on update
            if($count_verif_echange2!=0) {
                $sql2 = 'UPDATE conges_echange_rtt
                    SET e_absence=\''.$nouvelle_absence_date_2.'\', e_presence=\''.$nouvelle_presence_date_2.'\', e_comment=\''.$new_comment.'\'
                    WHERE e_login=\''.$_SESSION['userlogin'].'\' AND e_date_jour=\''.$new_fin.'\' ';
            }
            else // sinon: on insert
            {
                $sql2 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
                    VALUES ('".$_SESSION['userlogin']."','$new_fin','$nouvelle_absence_date_2', '$nouvelle_presence_date_2', '$new_comment')" ;
            }
            $result2 = \includes\SQL::query($sql2) ;

            $comment_log = "echange absence - rtt  ($new_debut_string / $new_fin_string)";
            log_action(0, "", $_SESSION['userlogin'], $comment_log);


            if(($result1)&&($result2))
                $return .= 'Changements pris en compte avec succes !<br><br>';
            else
                $return .= 'ERREUR ! Une erreur s\'est produite : contactez votre responsable !<br><br>';

        } else {
            $return .= 'ERREUR ! Les valeurs saisies sont invalides ou manquantes  !!!<br><br>';
        }

        /* RETOUR PAGE PRINCIPALE */
        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=' . $onglet . '" method="POST">';
        $return .= '<input type="submit" value="Retour">';
        $return .= '</form>';

        return $return;
    }

    //affiche le formulaire d'échange d'un jour de rtt-temps partiel / jour travaillé
    public static function saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet)
    {
        $return = '';
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();
        $mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
        $mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
        $mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
        $mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

        $return .= '<form action="'.$PHP_SELF.'?session='.$session.'&&onglet='.$onglet.'" method="POST">' ;

        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-responsive',
            'table-condensed',
        ]);
        $childTable = '<tr align="center">';

        // cellule 1 : calendrier de saisie du jour d'absence
        $childTable .= '<td class="cell-top">';
        $innerTableCell1 = new \App\Libraries\Structure\Table();
        $innerTableCell1->addClasses([
            'table',
            'table-responsive',
            'table-bordered',
            'table-calendar',
        ]);
        $childTableCell1 = '<tr>';
        init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
                $mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
                $mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
                $mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
                $mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
                $mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

        // affichage des boutons de défilement
        // recul du mois saisie debut
        $childTableCell1 .= '<td align="center">';
        $childTableCell1 .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_prec . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_prec . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' .$onglet . '">';
        $childTableCell1 .= '<i class="fa fa-chevron-circle-left"></i>';
        $childTableCell1 .= '</a>';
        $childTableCell1 .= '</td>';

        // titre du calendrier de saisie du jour d'absence
        $childTableCell1 .= '<td align="center">'. _('saisie_echange_titre_calendrier_1') . '</td>';

        // affichage des boutons de défilement
        // avance du mois saisie debut
        $childTableCell1 .= '<td align="center">';
        $childTableCell1 .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_suiv . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_suiv . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
        $childTableCell1 .= '<i class="fa fa-chevron-circle-right"></i>';
        $childTableCell1 .= '</a>';
        $childTableCell1 .= '</td>';
        $childTableCell1 .= '</tr>';
        $childTableCell1 .= '<tr>';
        $childTableCell1 .= '<td colspan="3">';
        //*** calendrier saisie date debut ***/
        $childTableCell1 .= \utilisateur\Fonctions::affiche_calendrier_saisie_jour_absence($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut);
        $childTableCell1 .= '</td>';
        $childTableCell1 .= '</tr>';
        $innerTableCell1->addChild($childTableCell1);
        ob_start();
        $innerTableCell1->render();
        $childTable .= ob_get_clean();
        $childTable .= '</td>';

        // cellule 2 : boutons radio 1/2 journée ou jour complet
        $childTable .= '<td class="day-period">';
        $childTable .= '<div><input type="radio" name="moment_absence_ordinaire" value="a"><label>'. _('form_am') .'</label><input type="radio" name="moment_absence_souhaitee" value="a"></div>';
        $childTable .= '<input type="radio" name="moment_absence_ordinaire" value="p"><label>'. _('form_pm') .'</label><input type="radio" name="moment_absence_souhaitee" value="p"></div>';
        $childTable .= '<div><input type="radio" name="moment_absence_ordinaire" value="j" checked><label>'. _('form_day') .'</label><input type="radio" name="moment_absence_souhaitee" value="j" checked></div>';
        $childTable .= '</td>';

        // cellule 3 : calendrier de saisie du jour d'absence
        $childTable .= '<td class="cell-top">';
        $innerTableCell3 = new \App\Libraries\Structure\Table();
        $innerTableCell3->addClasses([
            'table',
            'table-responsive',
            'table-bordered',
            'table-calendar',
        ]);
        $childTableCell3 = '<tr>';
        $mois_calendrier_saisie_fin_prec = $mois_calendrier_saisie_fin==1 ? 12 : $mois_calendrier_saisie_fin-1 ;
        $mois_calendrier_saisie_fin_suiv = $mois_calendrier_saisie_fin==12 ? 1 : $mois_calendrier_saisie_fin+1 ;

        // affichage des boutons de défilement
        // recul du mois saisie fin
        $childTableCell3 .= '<td align="center">';
        $childTableCell3 .= '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_prec.'&user_login='.$user_login.'&onglet='.$onglet.'">';
        $childTableCell3 .= '<i class="fa fa-chevron-circle-left"></i>';
        $childTableCell3 .= '</a>';
        $childTableCell3 .= '</td>';

        // titre du ecalendrier de saisie du jour d'absence
        $childTableCell3 .= '<td align="center">' . _('saisie_echange_titre_calendrier_2') . '</td>';

        // affichage des boutons de défilement
        // avance du mois saisie fin
        $childTableCell3 .= '<td align="center">';
        $childTableCell3 .= '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_suiv.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_suiv.'&user_login='.$user_login.'&onglet='.$onglet.'">';
        $childTableCell3 .= '<i class="fa fa-chevron-circle-right"></i>';
        $childTableCell3 .= '</a>';
        $childTableCell3 .= '</td>';
        $childTableCell3 .= '</tr>';
        $childTableCell3 .= '<tr>';
        $childTableCell3 .= '<td colspan="3">';
        //*** calendrier saisie date fin ***/
        $childTableCell3 .= \utilisateur\Fonctions::affiche_calendrier_saisie_jour_presence($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin);
        $childTableCell3 .= '</td>';
        $childTableCell3 .= '</tr>';
        $innerTableCell3->addChild($childTableCell3);
        ob_start();
        $innerTableCell3->render();
        $childTable .= ob_get_clean();
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= "<hr/>\n";
        // cellule 1 : champs texte et boutons (valider/cancel)
        $return .= '<label>'. _('divers_comment_maj_1') .'</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';
        $return .= "<hr/>\n";
        $return .= '<input type="hidden" name="user_login" value="'.schars($user_login).'">';
        $return .= '<input type="hidden" name="new_echange_rtt" value=1>';
        $return .= '<input class="btn btn-success" type="submit" value="'. _('form_submit') .'">';
        $return .= "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>\n";
        $return .= '</form>' ;

        return $return;
    }

    /**
     * Encapsule le comportement du module d'échange d'absence
     *
     * @param string $onglet Nom de l'onglet à afficher
     *
     * @return void
     * @access public
     * @static
     */
    public static function echangeJourAbsenceModule($onglet)
    {
        $return = '';
        init_tab_jours_feries();

        $new_echange_rtt    = getpost_variable('new_echange_rtt', 0);

        if( $new_echange_rtt == 1 && $_SESSION['config']['user_echange_rtt'] ) {

            $new_debut                = getpost_variable('new_debut');
            $new_fin                  = getpost_variable('new_fin');
            $new_comment              = getpost_variable('new_comment');
            $moment_absence_ordinaire = getpost_variable('moment_absence_ordinaire');
            $moment_absence_souhaitee = getpost_variable('moment_absence_souhaitee');

            $return .= \utilisateur\Fonctions::echange_absence_rtt($onglet, $new_debut, $new_fin, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee);
        } else {

            $year_calendrier_saisie_debut = getpost_variable('year_calendrier_saisie_debut', date('Y'));
            $mois_calendrier_saisie_debut = getpost_variable('mois_calendrier_saisie_debut', date('m'));
            $year_calendrier_saisie_fin   = getpost_variable('year_calendrier_saisie_fin', date('Y'));
            $mois_calendrier_saisie_fin   = getpost_variable('mois_calendrier_saisie_fin', date('m'));

            $return .= '<h1>'. _('user_echange_rtt') .'</h1>';

            //affiche le formulaire de saisie d'une nouvelle demande de conges
            $return .= \utilisateur\Fonctions::saisie_echange_rtt($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
        }

        return $return;
    }

    /**
     * Encapsule le comportement du module de l'historique des congés
     *
     * @param string $session  Clé de session
     * @param string $PHP_SELF
     *
     * @return void
     * @access public
     * @static
     */
    public static function historiqueCongesModule($session, $PHP_SELF)
    {
        $return = '';
        if($_SESSION['config']['where_to_find_user_email']=="ldap"){
            include_once CONFIG_PATH .'config_ldap.php';
        }

        $tri_date = getpost_variable('tri_date', "ascendant");
        $year_affichage = getpost_variable('year_affichage' , date("Y") );

        $return .= '<h1>' . _('user_historique_conges') . '</h1>';

        //affiche le tableau de l'hitorique des conges


        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;

        $return .= '<b>';
        $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&onglet=historique_conges&year_affichage=' . $year_affichage_prec . '"><<</a>';
        $return .= '&nbsp&nbsp&nbsp  ' . schars($year_affichage) . ' &nbsp&nbsp&nbsp';
        $return .= '<a href="' . schars($PHP_SELF) . '?session=' . schars($session) . '&onglet=historique_conges&year_affichage=' . schars($year_affichage_suiv) . '">>></a>';
        $return .= '</b><br><br>';


        // Récupération des informations
        // on ne recup QUE les periodes de type "conges"(cf table conges_type_absence) ET pas les demandes
        $sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, ta_libelle
            FROM conges_periode as a, conges_type_absence as b
            WHERE a.p_login = '".$_SESSION['userlogin']."'
            AND (a.p_type=b.ta_id)
            AND ( (b.ta_type='conges') OR (b.ta_type='conges_exceptionnels') )
            AND (p_etat='ok' OR  p_etat='refus' OR  p_etat='annul')
            AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";

        if($tri_date=="descendant")
            $sql2=$sql2." ORDER BY p_date_deb DESC ";
        else
            $sql2=$sql2." ORDER BY p_date_deb ASC ";

        $ReqLog2 = \includes\SQL::query($sql2) ;

        $count2=$ReqLog2->num_rows;
        if($count2==0) {
            $return .= '<b>' . _('user_conges_aucun_conges') . '</b><br>';
        } else {
            // AFFICHAGE TABLEAU
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped',
            ]);
            $childTable = '<thead>';
            $childTable .= '<tr>';
            $childTable .= '<th>';
            $childTable .= _('divers_debut_maj_1');
            $childTable .= '</th>';
            $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_nb_jours_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_etat_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_motif_refus') . '</th>';
            if($_SESSION['config']['affiche_date_traitement']) {
                $childTable .= '<td>' . _('divers_date_traitement') . '</td>';
            }

            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            $i = true;
            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
                $sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
                if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
                $sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
                $sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
                if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
                $sql_p_nb_jours = $resultat2["p_nb_jours"];
                $sql_p_commentaire = $resultat2["p_commentaire"];
                //$sql_p_type = $resultat2["p_type"];
                $sql_p_type = $resultat2["ta_libelle"];
                $sql_p_etat = $resultat2["p_etat"];
                $sql_p_motif_refus=$resultat2["p_motif_refus"] ;
                $sql_p_date_demande = $resultat2["p_date_demande"];
                $sql_p_date_traitement = $resultat2["p_date_traitement"];

                $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $childTable .= '<td class="histo">' . schars($sql_p_date_deb) . ' _ ' . schars($demi_j_deb) . '</td>';
                $childTable .= '<td class="histo">' . schars($sql_p_date_fin) . ' _ ' . schars($demi_j_fin) . '</td>' ;
                $childTable .= '<td class="histo">' . schars($sql_p_type) . '</td>';
                $childTable .= '<td class="histo">' . affiche_decimal($sql_p_nb_jours) . '</td>';
                $childTable .= '<td class="histo">' . schars($sql_p_commentaire) . '</td>';


                $childTable .= '<td>';
                if($sql_p_etat=="refus")
                    $childTable .= _('divers_refuse') ;
                elseif($sql_p_etat=="annul")
                    $childTable .= _('divers_annule') ;
                else
                    $childTable .= schars($sql_p_etat);
                $childTable .= '</td>';


                if($sql_p_etat=="refus") {
                    if($sql_p_motif_refus=="")
                        $sql_p_motif_refus= _('divers_inconnu') ;
                    $childTable .= '<td class="histo">' . schars($sql_p_motif_refus) . '</td>';
                } elseif($sql_p_etat=="annul") {
                    if($sql_p_motif_refus=="")
                        $sql_p_motif_refus= _('divers_inconnu') ;
                    $childTable .= '<td class="histo">' . schars($sql_p_motif_refus) . '</td>';
                } elseif($sql_p_etat=="ok") {
                    if($sql_p_motif_refus=="")
                        $sql_p_motif_refus=" ";
                    $childTable .= '<td class="histo">' . schars($sql_p_motif_refus) . '</td>';
                }
                $childTable .= '</td>';

                if($_SESSION['config']['affiche_date_traitement']) {
                    $childTable .= '<td class="histo-left">' . schars( _('divers_demande')) . ' : ' . schars($sql_p_date_demande) . '<br>';
                    $text_lang_a_afficher="divers_traitement_$sql_p_etat" ; // p_etat='ok' OR  p_etat='refus' OR  p_etat='annul' .....
                    $childTable .= schars( _($text_lang_a_afficher) ) . ' : ' . schars($sql_p_date_traitement).'</td>';
                }

                $childTable .= '</tr>';
                $i = !$i;
            }
            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
        }
        $return .= '<br><br>';

        return $return;
    }

    /**
     * Encapsule le comportement du module de l'historique des autres absences
     *
     * @param string $onglet Nom de l'onglet à afficher
     * @param string $session  Clé de session
     * @param string $PHP_SELF
     *
     * @return void
     * @access public
     * @static
     */
    public static function historiqueAutresAbsencesModule($onglet, $session, $PHP_SELF)
    {
        $return = '';
        if($_SESSION['config']['where_to_find_user_email']=="ldap"){
            include_once CONFIG_PATH .'config_ldap.php';
        }

        $tri_date = getpost_variable('tri_date', "ascendant");
        $year_affichage = getpost_variable('year_affichage' , date("Y") );

        $return = '<h1>'. _('user_historique_abs') .' :</h1>';

        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;

        $return .= '<b>';
        $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&onglet=historique_autres_absences&year_affichage=' . $year_affichage_prec . '"><<</a>';
        $return .= '&nbsp&nbsp&nbsp ' . $year_affichage . '&nbsp&nbsp&nbsp';
        $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&onglet=historique_autres_absences&year_affichage=' . $year_affichage_suiv . '">>></a>';
        $return .= '</b><br><br>';


        // Récupération des informations
        $sql4 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num, ta_libelle
            FROM conges_periode as a, conges_type_absence as b
            WHERE a.p_login = "'.\includes\SQL::quote($_SESSION['userlogin']).'"
            AND (a.p_type=b.ta_id)
            AND (b.ta_type=\'absences\')
            AND (p_date_deb LIKE \''.intval($year_affichage).'%\' OR p_date_fin LIKE \''.intval($year_affichage).'%\') ';

        if($tri_date=="descendant")
            $sql4=$sql4." ORDER BY p_date_deb DESC ";
        else
            $sql4=$sql4." ORDER BY p_date_deb ASC ";

        $ReqLog4 = \includes\SQL::query($sql4) ;

        $count4=$ReqLog4->num_rows;
        if($count4==0) {
            $return .= '<b>' . _('user_abs_aucune_abs') . '</b><br>';
        } else {
            // AFFICHAGE TABLEAU
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-responsive',
                'table-condensed',
                'table-striped',
            ]);
            $childTable = '<thead>';
            $childTable .= '<tr>';
            $childTable .= '<td>';
            $childTable .= '<a href="' . $PHP_SELF . '?session=' . $session . '&onglet=' . $onglet . '&tri_date=descendant"><img src="' . IMG_PATH . '1downarrow-16x16.png" width="16" height="16" border="0" title="trier"></a>';
            $childTable .= _('divers_debut_maj_1');
            $childTable .= '<a href="' . $PHP_SELF . '?session=' . $session . '&onglet=' . $onglet . '&tri_date=ascendant"><img src="' . IMG_PATH . '1uparrow-16x16.png" width="16" height="16" border="0" title="trier"></a>';
            $childTable .= '</td>';
            $childTable .= '<td>' . _('divers_fin_maj_1') . '</td>';
            $childTable .= '<td>' . _('user_abs_type') . '</td>';
            $childTable .= '<td>' . _('divers_nb_jours_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_comment_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_etat_maj_1') . '</td>';
            $childTable .= '<td></td><td></td>';
            if($_SESSION['config']['affiche_date_traitement']) {
                $childTable .= '<td>' . _('divers_date_traitement') . '</td>';
            }
            $childTable .= '</tr>';
            $childTable .= '</thead>';
            $childTable .= '<tbody>';

            $i = true;
            while ($resultat4 = $ReqLog4->fetch_array()) {
                $sql_login= $resultat4["p_login"];
                $sql_date_deb= eng_date_to_fr($resultat4["p_date_deb"]);
                $sql_p_demi_jour_deb = $resultat4["p_demi_jour_deb"];
                if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
                $sql_date_fin= eng_date_to_fr($resultat4["p_date_fin"]);
                $sql_p_demi_jour_fin = $resultat4["p_demi_jour_fin"];
                if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
                $sql_nb_jours= affiche_decimal($resultat4["p_nb_jours"]);
                $sql_commentaire= $resultat4["p_commentaire"];
                //$sql_type=$resultat4["p_type"];
                $sql_type=$resultat4["ta_libelle"];
                $sql_etat=$resultat4["p_etat"];
                $sql_motif_refus=$resultat4["p_motif_refus"] ;
                $sql_date_demande = $resultat4["p_date_demande"];
                $sql_date_traitement = $resultat4["p_date_traitement"];
                $sql_num= $resultat4["p_num"];

                // si le user a le droit de saisir lui meme ses absences et qu'elle n'est pas deja annulee, on propose de modifier ou de supprimer
                if(($sql_etat != "annul")&&($_SESSION['config']['user_saisie_mission'])) {
                    $user_modif_mission="<a href=\"user_index.php?session=$session&p_num=$sql_num&onglet=modif_demande\">". _('form_modif') ."</a>" ;
                    $user_suppr_mission="<a href=\"user_index.php?session=$session&p_num=$sql_num&onglet=suppr_demande\">". _('form_supprim') ."</a>" ;
                } else {
                    $user_modif_mission=" - " ;
                    $user_suppr_mission=" - " ;
                }

                $childTable .= '<tr class="'.($i ? 'i' : 'p') . '">';
                $childTable .= '<td class="histo">' . schars($sql_date_deb) . ' _ ' . schars($demi_j_deb) . '</td>';
                $childTable .= '<td class="histo">' . schars($sql_date_fin) . ' _ ' . schars($demi_j_fin) . '</td>' ;
                $childTable .= '<td class="histo">' . schars($sql_type) . '</td>';
                $childTable .= '<td class="histo">' . affiche_decimal($sql_nb_jours) . '</td>' ;
                $childTable .= '<td class="histo">' . schars($sql_commentaire) . '</td>';

                if($sql_etat=="refus") {
                    if($sql_motif_refus=="")
                        $sql_motif_refus= _('divers_inconnu') ;
                    $childTable .= '<br><i>".'.schars( _('divers_motif_refus') ).'." : '.schars($sql_motif_refus).'</i>';
                } elseif($sql_etat=="annul") {
                    if($sql_motif_refus=="")
                        $sql_motif_refus= _('divers_inconnu') ;
                    $childTable .= '<br><i>".'.schars( _('divers_motif_annul') ).'." : '.schars($sql_motif_refus).'</i>';
                }
                $childTable .= '</td>';
                $childTable .= '<td>';
                if($sql_etat=="refus")
                    $childTable .= _('divers_refuse') ;
                elseif($sql_etat=="annul")
                    $childTable .= _('divers_annule') ;
                else
                    $childTable .= schars($sql_etat);
                $childTable .= '</td>';
                $childTable .= '<td class="histo">' . ($user_modif_mission) . '</td>';
                $childTable .= '<td class="histo">'.($user_suppr_mission).'</td>'."\n";
                if($_SESSION['config']['affiche_date_traitement']) {
                    $childTable .= '<td class="histo-left">' . schars(_('divers_demande') ) . ' : ' . schars($sql_date_demande) . '<br>' . schars(_('divers_traitement') ) . ' : ' . schars($sql_date_traitement) . '</td>';
                }
                $childTable .= '</tr>';
                $i = !$i;
            }
            $childTable .= '</tbody>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
        }
        $return .= '<br><br>';

        return $return;
    }
}
