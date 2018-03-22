<?php
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

        if ($resultat_abs = $ReqLog_abs->fetch_array())
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        //conversion des dates
        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);
        $return = '';

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        // verif validité des valeurs saisies
        $valid = verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $_SESSION['userlogin']);

        // verifie que le solde de conges sera encore positif après validation
        if ($config->canSoldeNegatif()) {
            $valid = $valid && \utilisateur\Fonctions::verif_solde_user($_SESSION['userlogin'], $new_type, $new_nb_jours);
        }

        if ( $valid ) {
            if ( in_array(\utilisateur\Fonctions::get_type_abs($new_type) , array('conges','conges_exceptionnels') ) ) {
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

            $periode_num = insert_dans_periode($_SESSION['userlogin'], $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $new_etat, 0);

            if ( $periode_num != 0 ) {
                $return .= schars( _('form_modif_ok') ) . '<br><br>';
                //envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)
                if ($config->isSendMailDemandeResponsable()) {
                    if (in_array(\utilisateur\Fonctions::get_type_abs($new_type), array('absences'))) {
                        alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_absence_conges");
                    } else {
                        alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_demande");
                    }
                }
            }
            else {
                $return .= schars( _('form_modif_not_ok') ) . '<br><br>';
            }
        }
        else {
            $return .= schars( _('resp_traite_user_valeurs_not_ok') ) . '<br><br>';
        }

        $return .= '<a class="btn" href="' . $PHP_SELF . '">' . _('form_retour') . '</a>';

        return $return;
    }

    /**
     * Retourne les options de select des années
     *
     * @return array
     */
    public static function getOptionsAnnees()
    {
        $current = date('Y');

        return [
            $current => $current,
            $current - 1 => $current - 1,
            $current - 2 => $current - 2,
        ];
    }

    /**
     * Retourne le timestamp du dernier jour de l'année
     *
     * @param string $annee
     *
     * @return string
     */
    public static function getTimestampDernierJourAnnee($annee)
    {
        return mktime(23, 59, 59, 12, 31, $annee);
    }

    /**
     * Retourne le timestamp du premier jour de l'année
     *
     * @param string $annee
     *
     * @return string
     */
    public static function getTimestampPremierJourAnnee($annee)
    {
        return mktime(0, 0, 0, 1, 1, $annee);
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        init_tab_jours_feries();
        $return = '';

        // si le user peut saisir ses demandes et qu'il vient d'en saisir une ...

        $new_demande_conges = getpost_variable('new_demande_conges', 0);

        if ( $new_demande_conges == 1 && $config->canUserSaisieDemande()) {
            $new_debut        = htmlentities(getpost_variable('new_debut'), ENT_QUOTES | ENT_HTML401);
            $new_demi_jour_deb  = htmlentities(getpost_variable('new_demi_jour_deb'), ENT_QUOTES | ENT_HTML401);
            $new_fin        = htmlentities(getpost_variable('new_fin'), ENT_QUOTES | ENT_HTML401);
            $new_demi_jour_fin  = htmlentities(getpost_variable('new_demi_jour_fin'), ENT_QUOTES | ENT_HTML401);
            $new_comment        = htmlentities(getpost_variable('new_comment'), ENT_QUOTES | ENT_HTML401);
            $new_type        = htmlentities(getpost_variable('new_type'), ENT_QUOTES | ENT_HTML401);

            $user_login        = $_SESSION['userlogin'];

            $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);

            $return .= \utilisateur\Fonctions::new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type);
        } else {
            $year_calendrier_saisie_debut   = (int) getpost_variable('year_calendrier_saisie_debut'   , date('Y'));
            $mois_calendrier_saisie_debut   = (int) getpost_variable('mois_calendrier_saisie_debut'   , date('m'));
            $year_calendrier_saisie_fin     = (int) getpost_variable('year_calendrier_saisie_fin'     , date('Y'));
            $mois_calendrier_saisie_fin     = (int) getpost_variable('mois_calendrier_saisie_fin'     , date('m'));

            /**************************/
            /* Nouvelle Demande */
            /**************************/
            /* Génération du datePicker et de ses options */
            $daysOfWeekDisabled = [];
            $datesDisabled      = [];
            if ((!$config->isDimancheOuvrable())
                && (!$config->isSamediOuvrable())
            ) {
                $daysOfWeekDisabled = [0,6];
            } else {
                if (!$config->isDimancheOuvrable()) {
                    $daysOfWeekDisabled = [0];
                }
                if (!$config->isSamediOuvrable()) {
                    $daysOfWeekDisabled = [6];
                }
            }

            if (is_array($_SESSION["tab_j_feries"])) {
                foreach ($_SESSION["tab_j_feries"] as $date) {
                    $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
                }
            }

            if (isset($_SESSION["tab_j_fermeture"]) && is_array($_SESSION["tab_j_fermeture"])) {
                foreach ($_SESSION["tab_j_fermeture"] as $date) {
                    $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
                }
            }
            $startDate = ($config->canUserSaisieDemandePasse()) ? 'd' : '';

            $datePickerOpts = [
                'daysOfWeekDisabled' => $daysOfWeekDisabled,
                'datesDisabled'      => $datesDisabled,
                'startDate'          => $startDate,
            ];
            $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ');</script>';
            $return .= '<h1>' . _('resp_traite_user_new_conges') . '</h1>';

            //affiche le formulaire de saisie d'une nouvelle demande de conges
            $return .= saisie_nouveau_conges2($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
        }
        return $return;
    }

    public static function modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';
        $VerifNb = verif_saisie_decimal($new_nb_jours);

        $sql1 = "UPDATE conges_periode
            SET p_date_deb='$new_debut', p_demi_jour_deb='$new_demi_jour_deb', p_date_fin='$new_fin', p_demi_jour_fin='$new_demi_jour_fin', p_nb_jours='$new_nb_jours', p_commentaire='". \includes\SQL::quote($new_comment)  ."', ";
        if ($p_etat=="demande")
            $sql1 = $sql1." p_date_demande=NOW() ";
        else
            $sql1 = $sql1." p_date_traitement=NOW() ";
        $sql1 = $sql1."    WHERE p_num='$p_num_to_update' AND p_login='".$_SESSION['userlogin']."' ;" ;

        $result = \includes\SQL::query($sql1) ;

        if ($config->isSendMailAnnulationCongesUtilisateur()) {
            alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_update, "modif_demande_conges");
        }
        $comment_log = "modification de demande num $p_num_to_update ($new_nb_jours jour(s)) ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
        log_action($p_num_to_update, "$p_etat", $_SESSION['userlogin'], $comment_log);


        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="'.ROOT_PATH .'utilisateur/user_index.php?onglet=liste_conge" method="POST">';
        $return .= '<input class="btn" type="submit" value="'. _('form_submit') .'">';
        $return .= '</form>';

        return $return;

    }

    public static function confirmer($p_num, $onglet)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // Récupération des informations
        $sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_etat, p_num FROM conges_periode where p_num = "'. \includes\SQL::quote($p_num).'"';
        $ReqLog1 = \includes\SQL::query($sql1) ;

        /* Génération du datePicker et de ses options */
        $daysOfWeekDisabled = [];
        $datesDisabled      = [];
        if ((false == $config->isDimancheOuvrable())
            && (!$config->isSamediOuvrable())
        ) {
            $daysOfWeekDisabled = [0,6];
        } else {
            if (!$config->isDimancheOuvrable()) {
                $daysOfWeekDisabled = [0];
            }
            if (!$config->isSamediOuvrable()) {
                $daysOfWeekDisabled = [6];
            }
        }

        if (is_array($_SESSION["tab_j_feries"])) {
            foreach ($_SESSION["tab_j_feries"] as $date) {
                $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        if (isset($_SESSION["tab_j_fermeture"]) && is_array($_SESSION["tab_j_fermeture"])) {
            foreach ($_SESSION["tab_j_fermeture"] as $date) {
                $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }
        $startDate = ($config->canUserSaisieDemandePasse()) ? 'd' : '';

        $datePickerOpts = [
            'daysOfWeekDisabled' => $daysOfWeekDisabled,
            'datesDisabled'      => $datesDisabled,
            'startDate'          => $startDate,
        ];
        $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ');</script>';

        // AFFICHAGE TABLEAU

        $return .= '<form NAME="dem_conges" action="' . $PHP_SELF . '" method="POST">' ;
        $return .= '<table class="table table-responsive">';
        $return .= '<thead>';
        // affichage première ligne : titres
        $return .= '<tr>';
        $return .= '<td>' . _('divers_debut_maj_1') . '</td>';
        $return .= '<td>' . _('divers_fin_maj_1') . '</td>';
        $return .= '<td>' . _('divers_nb_jours_maj_1') . '</td>';
        $return .= '<td>' . _('divers_comment_maj_1') . '</td>';
        $return .= '</tr>';
        $return .= '</thead>';
        $return .= '<tbody>';
        // affichage 2ieme ligne : valeurs actuelles
        $return .= '<tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);

            $sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
            if ($sql_demi_jour_deb=="am")
                $demi_j_deb= _('divers_am_short') ;
            else
                $demi_j_deb= _('divers_pm_short') ;
            $sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
            $sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
            if ($sql_demi_jour_fin=="am")
                $demi_j_fin= _('divers_am_short') ;
            else
                $demi_j_fin= _('divers_pm_short') ;
            $sql_nb_jours=$resultat1["p_nb_jours"];
            $aff_nb_jours=affiche_decimal($sql_nb_jours);
            $sql_commentaire=$resultat1["p_commentaire"];
            $sql_etat=$resultat1["p_etat"];

            $return .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td><td>' . $sql_date_fin  . '_' . $demi_j_fin . '</td><td>' . $aff_nb_jours . '</td><td>' . $sql_commentaire . '</td>';

            if ( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) ) {
                $compte = 'onClick="compter_jours();return true;" ' ;
            } else {
                $compte = 'onChange="compter_jours();return false;" ' ;
            }

            $text_debut="<input class=\"form-control date\" type=\"text\" name=\"new_debut\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_deb) . "\">" ;
            if ($sql_demi_jour_deb=="am") {
                $radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\" checked>&nbsp;". _('form_debut_am') ;
                $radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\">&nbsp;". _('form_debut_pm') ;
            } else {
                $radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\">". _('form_debut_am') ;
                $radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\" checked>". _('form_debut_pm') ;
            }
            $text_fin="<input class=\"form-control date\" type=\"text\" name=\"new_fin\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_fin) . "\">" ;
            if ($sql_demi_jour_fin=="am") {
                $radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\" checked>". _('form_fin_am') ;
                $radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\">". _('form_fin_pm') ;
            } else {
                $radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\">". _('form_fin_am') ;
                $radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\" checked>". _('form_fin_pm') ;
            }
            $text_nb_jours = "<span id='new_nb_jours'>$sql_nb_jours</span>";

            $text_commentaire="<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"15\" maxlength=\"30\" value=\"$sql_commentaire\"><br><br>" ;
        }
        $return .= '</tr>';

        // affichage 3ieme ligne : saisie des nouvelles valeurs
        $return .= '<tr>';
        $return .= '<td>' . $text_debut . '<br>' . $radio_deb_am . '/' . $radio_deb_pm . '</td><td>' . $text_fin . '<br>' . $radio_fin_am . '/' .  $radio_fin_pm . '</td><td>' . $text_nb_jours . '</td><td>' . $text_commentaire . '</td>';
        $return .= '</tr>';

        $return .= '</tbody>';
        $return .= '</table>';
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="p_num_to_update" value="' . $p_num . '">';
        $return .= '<input type="hidden" name="p_etat" value="' . $sql_etat . '">';
        $return .= '<input type="hidden" name="user_login" value="'.$_SESSION['userlogin'].'">';
        $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
        $return .= '<p id="comment_nbj" style="color:red">&nbsp;</p>';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?onglet=liste_conge">' . _('form_cancel') . '</a>';
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $user_login        = $_SESSION['userlogin'];
        $p_num             = getpost_variable('p_num');
        $onglet            = getpost_variable('onglet');
        $p_num_to_update   = getpost_variable('p_num_to_update');
        $p_etat               = getpost_variable('p_etat');
        $new_debut         = getpost_variable('new_debut');
        $new_demi_jour_deb = getpost_variable('new_demi_jour_deb');
        $new_fin           = getpost_variable('new_fin');
        $new_demi_jour_fin = getpost_variable('new_demi_jour_fin');
        $new_comment       = htmlentities(getpost_variable('new_comment'), ENT_QUOTES | ENT_HTML401);

        $return            = '';
        // lors de la modification  p_num n'existe plus et est remplacé par p_num_update
        if ($p_num != '') {
            $id = $p_num;
        } else {
            $id = $p_num_to_update;
        }
        $isAllowed = self::canUserManipulateConge($id, $_SESSION['userlogin']);
        if (!$isAllowed || !$config->canUserModifieDemande()) {
            redirect(ROOT_PATH . 'utilisateur/user_index.php');
        }

        //conversion des dates
        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);
        $new_nb_jours = compter($user_login, $p_num_to_update, $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);


        /*************************************/

        // TITRE
        $return .= '<h1>'. _('user_modif_demande_titre') .'</h1>';

        if ($p_num!="") {
            $return .= \utilisateur\Fonctions::confirmer($p_num, $onglet);
        } else {
            if ($p_num_to_update != "") {
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
        if ($resultat_abs = $ReqLog_abs->fetch_array())
            return $resultat_abs['ta_libelle'];
        else
            return "" ;
    }

    public static function suppression($p_num_to_delete, $onglet)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        if ($config->isSendMailSupprimeDemandeResponsable()) {
            alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_delete, "supp_demande_conges");
        }

        $sql_delete = 'DELETE FROM conges_periode WHERE p_num = '.\includes\SQL::quote($p_num_to_delete).' AND p_login="'.\includes\SQL::quote($_SESSION['userlogin']).'";';
        $result_delete = \includes\SQL::query($sql_delete);

        $comment_log = "suppression de demande num $p_num_to_delete";
        log_action($p_num_to_delete, "", $_SESSION['userlogin'], $comment_log);

        if ($result_delete)
            $return .= _('form_modif_ok') ."<br><br> \n";
        else
            $return .= _('form_modif_not_ok') ."<br><br> \n";

        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="'.ROOT_PATH .'utilisateur/user_index.php?onglet=liste_conge" method="POST">';
        $return .= '<input class="btn" type="submit" value="'. _('form_submit') .'">';
        $return .= '</form>';
        $return .= '<a href="">';

        return $return;
    }

    public static function confirmerSuppression($p_num, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // Récupération des informations
        $sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_num FROM conges_periode WHERE p_num = "'.\includes\SQL::quote($p_num).'"';
        //printf("sql1 = %s<br>\n", $sql1);
        $ReqLog1 = \includes\SQL::query($sql1) ;

        // AFFICHAGE TABLEAU
        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= '<table class="table table-responsive table-condensed">';
        $return .= '<thead>';
        $return .= '<tr>';
        $return .= '<th>' . _('divers_debut_maj_1') . '</th>';
        $return .= '<th>' . _('divers_fin_maj_1') . '</th>';
        $return .= '<th>' . _('divers_nb_jours_maj_1') . '</th>';
        $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $return .= '<th>' . _('divers_type_maj_1') . '</th>';
        $return .= '</tr>';
        $return .= '</thead>';
        $return .= '<tbody>';
        $return .= '<tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);
            $sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
            if ($sql_demi_jour_deb=="am")
                $demi_j_deb= _('divers_am_short') ;
            else
                $demi_j_deb= _('divers_pm_short') ;
            $sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
            $sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
            if ($sql_demi_jour_fin=="am")
                $demi_j_fin= _('divers_am_short') ;
            else
                $demi_j_fin= _('divers_pm_short') ;
            $sql_nb_jours=affiche_decimal($resultat1["p_nb_jours"]);
            //$sql_type=$resultat1["p_type"];
            $sql_type= \utilisateur\Fonctions::get_libelle_abs($resultat1["p_type"]);
            $sql_comment = $resultat1["p_commentaire"];

            $return .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td>';
            $return .= '<td>' . $sql_date_fin . '_' . $demi_j_fin . '</td>';
            $return .= '<td>' . $sql_nb_jours . '</td>';
            $return .= '<td>' . $sql_comment . '</td>';
            $return .= '<td>' . $sql_type . '</td>';
        }
        $return .= '</tr>';
        $return .= '</tbody>';
        $return .= '</table>';
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="p_num_to_delete" value="' . $p_num . '">';
        $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?onglet=liste_conge">' . _('form_cancel') . '</a>';
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
        // lors de la modification  p_num n'existe plus et est remplacé par p_num_update
        if ($p_num != '') {
            $id = $p_num;
        } else {
            $id = $p_num_to_delete;
        }
        $isAllowed = self::canUserManipulateConge($id, $_SESSION['userlogin']);
        if (!$isAllowed) {
            redirect(ROOT_PATH . 'utilisateur/user_index.php');
        }

        // TITRE
        $return .= '<h1>'. _('user_suppr_demande_titre') .'</h1>';
        $return .= '<br>';

        if ($p_num!="") {
            $return .= \utilisateur\Fonctions::confirmerSuppression($p_num, $onglet);
        } else {
            if ($p_num_to_delete!="") {
                $return .= \utilisateur\Fonctions::suppression($p_num_to_delete, $onglet);
            } else {
                // renvoit sur la page principale .
                redirect( ROOT_PATH .'utilisateur/user_index.php', false );
            }
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

        if ( $first_jour_mois_rang == 0 )
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        if ( $last_jour_mois_rang == 0 )
            $last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        $return .= '<table class="table calendrier_saisie_date">';
        $return .= '<thead>
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
        $return .= '<tbody>';

        $start_nb_day_before = $first_jour_mois_rang -1;
        $stop_nb_day_before = 7 - $last_jour_mois_rang ;
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user_login);


        for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
            if ( ($i + $start_nb_day_before ) % 7 == 0)
                $return .= '<tr>';


            $j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
            $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

            if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
                $return .= '<td class="'.$td_second_class.'">-</td>';
            }
            else {
                $val_matin='';
                $val_aprem='';
                recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $planningUser);
                $return .= \utilisateur\Fonctions::affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1);
            }

            if ( ($i + $start_nb_day_before ) % 7 == 6)
                $return .= '<tr>';
        }

        $return .= '</tbody>';
        $return .= '</table>';

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

        if ( $first_jour_mois_rang == 0 )
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        if ( $last_jour_mois_rang == 0 )
            $last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

        $return .= '<table class="table calendrier_saisie_date">';
        $return .= '<thead><tr><th colspan="7" class="titre"> '.$mois_name.' '.$year.' </th></tr><tr><th class="cal-saisie2">'. _('lundi_1c') .'</th><th class="cal-saisie2">'. _('mardi_1c') .'</th><th class="cal-saisie2">'. _('mercredi_1c') .'</th><th class="cal-saisie2">'. _('jeudi_1c') .'</th><th class="cal-saisie2">'. _('vendredi_1c') .'</th><th class="cal-saisie2">'. _('samedi_1c') .'</th><th class="cal-saisie2">'. _('dimanche_1c') .'</th></tr></thead>';
        $return .= '<tbody>';

        $start_nb_day_before = $first_jour_mois_rang -1;
        $stop_nb_day_before = 7 - $last_jour_mois_rang ;
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user_login);


        for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
            if ( ($i + $start_nb_day_before ) % 7 == 0)
                $return .= '<tr>';

            $j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
            $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

            if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
                $return .= '<td class="'.$td_second_class.'">-</td>';
            }
            else {
                $val_matin='';
                $val_aprem='';
                recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $planningUser);
                $return .= \utilisateur\Fonctions::affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1);
            }

            if ( ($i + $start_nb_day_before ) % 7 == 6)
                $return .= '<tr>';
        }

        $return .= '</tbody>';
        $return .= '</table>';

        return $return;
    }

    public static function affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j)
    {
        $return = '';
        $bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
        if ( $val_matin == 'Y' && $val_aprem == 'Y')
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-j"></td>';
        elseif ( $val_matin == 'Y' && $val_aprem == 'N' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-a"></td>';
        elseif ( $val_matin == 'N' && $val_aprem == 'Y' )
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
        if ( $val_matin == 'Y' && $val_aprem == 'Y' )  // rtt le matin et l'apres midi !
            $return .= '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'</td>';
        elseif ( $val_matin == 'Y' && $val_aprem == 'N' )
            $return .= '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-p"></td>';
        elseif ( $val_matin == 'N' && $val_aprem == 'Y' )
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

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        $duree_demande_1="";
        $duree_demande_2="";
        $valid=TRUE;

        // verif si les dates sont renseignées  (si ce n'est pas le cas, on ne verifie meme pas la suite !)
        // $new_debut et $new_fin sont des string au format : $year-$mois-$jour-X  (avec X = j pour "jour entier", a pour "a" (matin), et p pour "pm" (apres midi) )
        if ( ($new_debut_string=="")||($new_fin_string=="") )
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
            if ($demi_jour_debut=="j") // on est absent la journee
            {
                if ($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="jour";
                }
                elseif ($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    $nouvelle_presence_date_1="M";
                    $nouvelle_absence_date_1="A";
                    $duree_demande_1="demi";
                }
                elseif ($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    $nouvelle_presence_date_1="A";
                    $nouvelle_absence_date_1="M";
                    $duree_demande_1="demi";
                }
            }
            elseif ($demi_jour_debut=="a") // on est absent le matin
            {
                if ($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="demi";
                }
                elseif ($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    if ($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
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
                elseif ($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    $valid=FALSE;
                }
            }
            elseif ($demi_jour_debut=="p") // on est absent l'aprem
            {
                if ($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
                {
                    $nouvelle_presence_date_1="J";
                    $nouvelle_absence_date_1="N";
                    $duree_demande_1="demi";
                }
                elseif ($moment_absence_ordinaire=="a") // on demande à etre present le matin
                {
                    $valid=FALSE;
                }
                elseif ($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
                {
                    if ($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
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
            if ($demi_jour_fin=="j") // on est present la journee
            {
                if ($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
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
                if ($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
                {
                    $nouvelle_presence_date_2="N";
                    $nouvelle_absence_date_2="J";
                    $duree_demande_2="demi";
                }
                elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
                {
                    if ($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
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
                if ($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
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
                    if ($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
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
            if ( ($duree_demande_1=="") || ($duree_demande_2=="") || ($duree_demande_1!=$duree_demande_2) )
                $valid=FALSE;
        }



        if ($valid) {
            $return .= schars($_SESSION['userlogin']) . ' --- ' . schars($new_debut) . ' --- ' . schars($new_fin) . ' --- ' . schars($new_comment) . '<br>';

            // insert du jour d'absence ordinaire (qui n'en sera plus un ou qu'a moitie ...)
            // e_presence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
            // verif si le couple user/date1 existe dans conges_echange_rtt ...
            $sql_verif_echange1='SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login="'. \includes\SQL::quote($_SESSION['userlogin']).'" AND e_date_jour="'. \includes\SQL::quote($new_debut).'";';
            $result_verif_echange1 = \includes\SQL::query($sql_verif_echange1) ;

            $count_verif_echange1=$result_verif_echange1->num_rows;

            // si le couple user/date1 existe dans conges_echange_rtt : on update
            if ($count_verif_echange1!=0) {
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
            if ($count_verif_echange2!=0) {
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


            if (($result1)&&($result2))
                $return .= 'Changements pris en compte avec succes !<br><br>';
            else
                $return .= 'ERREUR ! Une erreur s\'est produite : contactez votre responsable !<br><br>';

        } else {
            $return .= 'ERREUR ! Les valeurs saisies sont invalides ou manquantes  !!!<br><br>';
        }

        /* RETOUR PAGE PRINCIPALE */
        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet . '" method="POST">';
        $return .= '<input type="submit" value="Retour">';
        $return .= '</form>';

        return $return;
    }

    //affiche le formulaire d'échange d'un jour de rtt-temps partiel / jour travaillé
    public static function saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet)
    {
        $return = '';
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
        $mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
        $mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
        $mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

        $return .= '<form action="'.$PHP_SELF.'?onglet='.$onglet.'" method="POST">' ;

        $return .= '<table class="table table condensed">';
        $return .= '<tr align="center">';

        // cellule 1 : calendrier de saisie du jour d'absence
        $return .= '<td class="cell-top">';
        $return .= '<table class="table table-bordered table-calendar">';
        $return .= '<tr>';
        init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
                $mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
                $mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
                $mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
                $mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
                $mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

        // affichage des boutons de défilement
        // recul du mois saisie debut
        $return .= '<td align="center">';
        $return .= '<a href="' . $PHP_SELF . '?year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_prec . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_prec . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' .$onglet . '">';
        $return .= '<i class="fa fa-chevron-circle-left"></i>';
        $return .= '</a>';
        $return .= '</td>';

        // titre du calendrier de saisie du jour d'absence
        $return .= '<td align="center">'. _('saisie_echange_titre_calendrier_1') . '</td>';

        // affichage des boutons de défilement
        // avance du mois saisie debut
        $return .= '<td align="center">';
        $return .= '<a href="' . $PHP_SELF . '?year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_suiv . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_suiv . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
        $return .= '<i class="fa fa-chevron-circle-right"></i>';
        $return .= '</a>';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= '<td colspan="3">';
        //*** calendrier saisie date debut ***/
        $return .= \utilisateur\Fonctions::affiche_calendrier_saisie_jour_absence($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut);
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';
        $return .= '</td>';

        // cellule 2 : boutons radio 1/2 journée ou jour complet
        $return .= '<td class="day-period">';
        $return .= '<div><input type="radio" name="moment_absence_ordinaire" value="a"><label>'. _('form_am') .'</label><input type="radio" name="moment_absence_souhaitee" value="a"></div>';
        $return .= '<input type="radio" name="moment_absence_ordinaire" value="p"><label>'. _('form_pm') .'</label><input type="radio" name="moment_absence_souhaitee" value="p"></div>';
        $return .= '<div><input type="radio" name="moment_absence_ordinaire" value="j" checked><label>'. _('form_day') .'</label><input type="radio" name="moment_absence_souhaitee" value="j" checked></div>';
        $return .= '</td>';

        // cellule 3 : calendrier de saisie du jour d'absence
        $return .= '<td class="cell-top">';
        $return .= '<table class="table table-bordered table-calendar">';
        $return .= '<tr>';
        $mois_calendrier_saisie_fin_prec = $mois_calendrier_saisie_fin==1 ? 12 : $mois_calendrier_saisie_fin-1 ;
        $mois_calendrier_saisie_fin_suiv = $mois_calendrier_saisie_fin==12 ? 1 : $mois_calendrier_saisie_fin+1 ;

        // affichage des boutons de défilement
        // recul du mois saisie fin
        $return .= '<td align="center">';
        $return .= '<a href="'.$PHP_SELF.'?year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_prec.'&user_login='.$user_login.'&onglet='.$onglet.'">';
        $return .= '<i class="fa fa-chevron-circle-left"></i>';
        $return .= '</a>';
        $return .= '</td>';

        // titre du ecalendrier de saisie du jour d'absence
        $return .= '<td align="center">' . _('saisie_echange_titre_calendrier_2') . '</td>';

        // affichage des boutons de défilement
        // avance du mois saisie fin
        $return .= '<td align="center">';
        $return .= '<a href="'.$PHP_SELF.'?year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_suiv.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_suiv.'&user_login='.$user_login.'&onglet='.$onglet.'">';
        $return .= '<i class="fa fa-chevron-circle-right"></i>';
        $return .= '</a>';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= '<td colspan="3">';
        //*** calendrier saisie date fin ***/
        $return .= \utilisateur\Fonctions::affiche_calendrier_saisie_jour_presence($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin);
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';
        $return .= "<hr/>\n";
        // cellule 1 : champs texte et boutons (valider/cancel)
        $return .= '<label>'. _('divers_comment_maj_1') .'</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';
        $return .= "<hr/>\n";
        $return .= '<input type="hidden" name="user_login" value="'.schars($user_login).'">';
        $return .= '<input type="hidden" name="new_echange_rtt" value=1>';
        $return .= '<input class="btn btn-success" type="submit" value="'. _('form_submit') .'">';
        $return .= "<a class=\"btn\" href=\"$PHP_SELF\">". _('form_cancel') ."</a>\n";
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $return = '';
        init_tab_jours_feries();

        $new_echange_rtt    = getpost_variable('new_echange_rtt', 0);

        if ($new_echange_rtt == 1 && $config->canUserEchangeRTT()) {

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
            if (!\utilisateur\Fonctions::hasUserPlanning($_SESSION['userlogin'])) {
                $return .= '<div class="alert alert-danger">' . _('aucun_planning_associe_utilisateur') . '</div>';

            } else {
                //affiche le formulaire de saisie d'une nouvelle demande de conges
                $return .= \utilisateur\Fonctions::saisie_echange_rtt($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
            }
        }

        return $return;
    }

    /**
     * Retourne vrai si l'utilisateur a un planning associé
     *
     * @param string $user
     *
     * @return bool
     */
    public static function hasUserPlanning($user)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
            SELECT planning_id
            FROM conges_users
                INNER JOIN planning USING (planning_id)
            WHERE u_login ="' . $sql->quote($user) . '"
            AND planning.status = ' . \App\Models\Planning::STATUS_ACTIVE . '
        )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Retourne le planning de l'utilisateur organisé selon la hiérarchie habituelle
     * @example planningId[typeSemaine][jourId][typePeriode][creneaux]
     *
     * @param string $user
     *
     * @return ?array
     * @TODO $dataPlanning peut être nullable (php7.1)
     */
    public static function getUserPlanning($user)
    {
        $dataPlanning = null;
        $sql = \includes\SQL::singleton();
        $reqUser = 'SELECT planning.*
            FROM conges_users
                INNER JOIN planning USING (planning_id)
            WHERE u_login = "' . $sql->quote($user) . '"
                AND planning.status = ' . \App\Models\Planning::STATUS_ACTIVE;
        $queryUser = $sql->query($reqUser);
        $planning = $queryUser->fetch_array();
        if (!empty($planning)) {
            $dataPlanning = [];
            $reqCreneau = 'SELECT *
                FROM planning_creneau
                WHERE planning_id = ' . $planning['planning_id'];
            $queryCreneau = $sql->query($reqCreneau);

            while ($data = $queryCreneau->fetch_array()) {
                $dataPlanning[$data['type_semaine']][$data['jour_id']][$data['type_periode']][] = [
                    \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT => $data['debut'],
                    \App\Models\Planning\Creneau::TYPE_HEURE_FIN   => $data['fin'],
                ];
            }
        }

        return $dataPlanning;
    }

    /**
     * Retourne le type de semaine applicable pour un planning et un numéro de semaine donnés
     *
     * @param array $planningUser
     * @param int   $weekOfDay
     *
     * @return int
     */
    public static function getRealWeekType(array $planningUser, $weekOfDay)
    {
        $typeSemaineDuJour = ($weekOfDay & 1)
            ? \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE
            : \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE;
        if (isset($planningUser[$typeSemaineDuJour])) {
            return $typeSemaineDuJour;
        } elseif (isset($planningUser[\App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE])) {
            return \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE;
        } else {
            return NIL_INT;
        }
    }

    /**
     * Vérifie que le jour est travaillé selon le planning
     *
     * @param array $planningWeek
     * @param int   $jourId
     */
    public static function isWorkingDay(array $planningWeek, $jourId)
    {
        return isset($planningWeek[$jourId]);
    }

    /**
    * Vérifie qu'une matinée est travaillée pour un jour de planning donné
     *
     * @param array $planningDay
     *
     * @return bool
     */
    public static function isWorkingMorning(array $planningDay)
    {
        return \utilisateur\Fonctions::isWorkingPeriodType($planningDay, \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN);
    }

    /**
     * Vérifie qu'une après midi est travaillée pour un jour de planning donné
     *
     * @param array $planningDay
     *
     * @return bool
     */
    public static function isWorkingAfternoon(array $planningDay)
    {
        return \utilisateur\Fonctions::isWorkingPeriodType($planningDay, \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI);
    }

    /**
     * Vérifie qu'un type de période est travaillé pour un jour de planning donné
     *
     * @param array $planningDay
     * @param int   $periodType
     *
     * @return bool
     */
    private static function isWorkingPeriodType(array $planningDay, $periodType)
    {
        return isset($planningDay[$periodType]);
    }

    /**
     * Retourne les jours de la semaine à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerDaysOfWeekDisabled()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $daysOfWeekDisabled = [];

        if (!$config->isDimancheOuvrable()) {
            $daysOfWeekDisabled[] = 0;
        }
        if (!$config->isSamediOuvrable()) {
            $daysOfWeekDisabled[] = 6;
        }
        return $daysOfWeekDisabled;
    }

    /**
     * Retourne les jours fériés à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerJoursFeries()
    {
        $Jferies      = [];

        if (is_array($_SESSION["tab_j_feries"])) {
            foreach ($_SESSION["tab_j_feries"] as $date) {
                $Jferies[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        return $Jferies;
    }

    /**
     * Retourne les jours de fermeture à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerFermeture()
    {
        $Fermeture      = [];

        if (isset($_SESSION["tab_j_fermeture"]) && is_array($_SESSION["tab_j_fermeture"])) {
            foreach ($_SESSION["tab_j_fermeture"] as $date) {
                $Fermeture[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        return $Fermeture;
    }

    /**
     * Retourne le jour de début du calendrier dans datepicker
     *
     * @return string
     * @access public
     * @static
     */
    public static function getDatePickerStartDate()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        return ($config->canUserSaisieDemandePasse()) ? 'd' : '';
    }


    // --------------------------------------

    public static function getOptionsTypeConges()
    {
        $options = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT ta_libelle, ta_short_libelle
                FROM conges_type_absence';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
            $options[$data['ta_short_libelle']] = $data['ta_libelle'];
        }

        return $options;
    }

    public static function canUserManipulateConge($idConge, $user) {
        if (empty($idConge) || empty($user)) {
            return false;
        }
        $conge = \App\ProtoControllers\Conge::getConge($idConge);
        if (($conge["p_etat"]  == \App\Models\Conge::STATUT_DEMANDE) && ($conge['p_login'] == $user)) {
            return true;
        }
        return false;
    }
}
