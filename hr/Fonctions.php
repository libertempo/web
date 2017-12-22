<?php

namespace hr;

/**
 * Regroupement des fonctions liées au haut responsable
 */
class Fonctions
{
    public static function traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

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
            $return .= $numero . '---' . $user_login . '---' . $user_nb_jours_pris . '---' . $reponse . '<br>';

            /* Modification de la table conges_periode */
            if (strcmp($reponse, "OK")==0) {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat=\'ok\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );' ;
                /* On valide l'UPDATE dans la table "conges_periode" ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {
                    // Log de l'action
                    log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse");

                    /* UPDATE table "conges_solde_user" (jours restants) */
                    soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin);

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if ($config->isSendMailValidationUtilisateur())
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges");
                }
            } elseif (strcmp($reponse, "not_OK")==0) {
                // recup du motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql1 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';

                /* On valide l'UPDATE dans la table ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;
                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : refus");


                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if ($config->isSendMailRefusUtilisateur())
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges");
                }
            }
        }

        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?onglet=traitement_demandes">';
        return $return;
    }

    public static function affiche_all_demandes_en_cours($tab_type_conges)
    {
        $return = '';
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $count1=0;
        $count2=0;

        $sql = \includes\SQL::singleton();
        $typeAbsence = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
        if ($config->isCongesExceptionnelsActive()) {
            $typeAbsence = array_merge($typeAbsence, \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels'));
        }

        /*********************************/
        // Récupération des informations
        /*********************************/

        // Récup dans un tableau de tableau des informations de tous les users
        $tab_all_users=recup_infos_all_users();

        // si tableau des users du resp n'est pas vide
        if ( count($tab_all_users)!=0 ) {
            // constitution de la liste (séparé par des virgules) des logins ...
            $list_users="";
            foreach($tab_all_users as $current_login => $tab_current_user) {
                if ($list_users=="") {
                    $list_users= "'$current_login'" ;
                } else {
                    $list_users=$list_users.", '$current_login'" ;
                }
            }
        }

        /*********************************/

        $return .= '<form action="' . $PHP_SELF . '?onglet=traitement_demandes" method="POST">';

        /*********************************/
        /* TABLEAU DES DEMANDES DES USERS*/
        /*********************************/

        // si tableau des users n'est pas vide :)
        if ( count($tab_all_users)!=0 ) {

            // Récup des demandes en cours pour les users :
            $sql1 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
            $sql1=$sql1." WHERE p_etat =\"demande\" ";
            $sql1=$sql1." AND p_login IN ($list_users) ";
            $sql1=$sql1." ORDER BY p_num";

            $ReqLog1 = \includes\SQL::query($sql1) ;

            $count1 = $ReqLog1->num_rows;
            if ($count1!=0) {
                // AFFICHAGE TABLEAU DES DEMANDES EN COURS
                //$return .= '<h3>' . _('resp_traite_demandes_titre') . '</h3>';
                $return .= '<table cellpadding="2" class="table table-hover table-responsive table-condensed table-striped">';
                $return .= '<thead>';
                $return .= '<tr>';
                $return .= '<th>' . _('divers_nom_maj_1') . '<br>' . _('divers_prenom_maj_1') . '</th>';
                $return .= '<th>' . _('divers_quotite_maj_1') . '</th>';
                $return .= '<th>' . _('divers_type_maj_1') . '</th>';
                $return .= '<th>' . _('divers_debut_maj_1') .'</th>';
                $return .= '<th>' . _('divers_fin_maj_1') . '</th>';
                $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
                $return .= '<th>' . _('resp_traite_demandes_nb_jours') . '</th>';
                $return .= '<th>' . _('divers_solde') . '</th>';
                $return .= '<th>'. _('divers_accepter_maj_1') .'</th>' ;
                $return .= '<th>'. _('divers_refuser_maj_1') .'</th>' ;
                $return .= '<th>' . _('resp_traite_demandes_attente') . '</th>';
                $return .= '<th>'. _('resp_traite_demandes_motif_refus') . '</th>';
                if ($config->canAfficheDateTraitement()) {
                    $return .= '<th>' . _('divers_date_traitement') . '</th>';
                }
                $return .= '</tr>';
                $return .= '</thead>' ;
                $return .= '<tbody>' ;
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

                    if ($sql_p_demi_jour_deb=="am") {
                        $demi_j_deb="mat";
                    } else {
                        $demi_j_deb="aprm";
                    }

                    if ($sql_p_demi_jour_fin=="am") {
                        $demi_j_fin="mat";
                    } else {
                        $demi_j_fin="aprm";
                    }

                    // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                    $chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";
                    $boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
                    $boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
                    $boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";
                    $text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

                    $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                    $return .= '<td><b>' . $tab_all_users[$sql_p_login]['nom'] . '</b><br>' . $tab_all_users[$sql_p_login]['prenom'] . '</td><td>' . $tab_all_users[$sql_p_login]['quotite'] . '%</td>';
                    $return .= '<td>' . $typeAbsence[$sql_p_type]['libelle'] . '</td>';
                    $return .= '<td>' . $sql_p_date_deb_fr . '<span class="demi">' . $demi_j_deb . '</span></td><td>' . $sql_p_date_fin_fr . '<span class="demi">' . $demi_j_fin . '</span></td><td>' . $sql_p_commentaire . '</td><td><b>' . $sql_p_nb_jours . '</b></td>';
                    $tab_conges=$tab_all_users[$sql_p_login]['conges'];
                    $return .= '<td>' . $tab_conges[$typeAbsence[$sql_p_type]['libelle']]['solde'] . '</td>';
                    $return .= '<td>' . $boutonradio1 . '</td><td>' . $boutonradio2 . '</td><td>' . $boutonradio3 . '</td><td>' . $text_refus . '</td>';
                    if ($config->canAfficheDateTraitement()) {
                        if ($sql_p_date_demande == NULL) {
                            $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                        } else {
                            $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                        }
                    }
                    $return .= '</tr>' ;
                    $i = !$i;
                } // while
                $return .= '</tbody>' ;
                $return .= '</table>' ;
            } //if ($count1!=0)
        } //if ( count($tab_all_users)!=0 )

        $return .= '<br>';

        if (($count1==0) && ($count2==0)) {
            $return .= '<strong>' . _('aucune_demande') . '</strong>';
        } else {
            $return .= '<hr/>';
            $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        }
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de traitement des demandes
     *
     * @param array  $tab_type_cong
     * @param string $onglet
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraitementDemandeModule(array $tab_type_cong, $onglet)
    {
        $return = '';

        //var pour resp_traite_demande_all.php
        $tab_bt_radio   = getpost_variable('tab_bt_radio');
        $tab_text_refus = getpost_variable('tab_text_refus');

        // titre
        $return .= '<h1>'. _('resp_traite_demandes_titre') .'</h1>';

        // si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
        if ( $tab_bt_radio == '' ) {
            $return .= \hr\Fonctions::affiche_all_demandes_en_cours($tab_type_cong);
        } else {
            $return .= \hr\Fonctions::traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus);
            echo $return;
            redirect( ROOT_PATH .'hr/hr_index.php?onglet='.$onglet, false);
            exit;
        }
        return $return;
    }

    public static function new_conges($user_login, $numero_int, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $new_debut = convert_date($new_debut);
        $new_fin = convert_date($new_fin);

        // verif validité des valeurs saisies
        $valid = verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $user_login);

        if ($valid) {
            $return .= $user_login . '---' . $new_debut . '_' . $new_demi_jour_deb . '---' . $new_fin . '_' . $new_demi_jour_fin . '---' . $new_nb_jours . '---' . $new_comment . '---' . $new_type_id . '<br>';

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
            if ($tab_tout_type_abs[$new_type_id]['type']=="conges" || $tab_tout_type_abs[$user_type_abs_id]['type']=="conges_exceptionnels") {
                $user_nb_jours_pris_float=(float) $new_nb_jours ;
                soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $new_type_id, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin);
            }

            $comment_log = "saisie conges par le responsable pour $user_login ($new_nb_jours jour(s)) type_conges = $new_type_id ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
            log_action(0, "", $user_login, $comment_log);

            if ($result) {
                $return .= _('form_modif_ok') . '<br><br>';
            } else {
                $return .= _('form_modif_not_ok') . '<br><br>';
            }
        } else {
            $return .= _('resp_traite_user_valeurs_not_ok') . '<br><br>';
        }

        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="' . $PHP_SELF . '?onglet=traite_user&user_login=' . $user_login . '" method="POST">';
        $return .= '<input type="submit" value="' . _('form_retour') . '">';
        $return .= '</form>';
        return $return;
    }

    public static function traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
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

            if ($reponse == "ACCEPTE") { // acceptation definitive d'un conges
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat=\'ok\', p_date_traitement=NOW() WHERE p_num='.\includes\SQL::quote($numero_int).' AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';
                $ReqLog1 = \includes\SQL::query($sql1);

                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    /* UPDATE table "conges_solde_user" (jours restants) */
                    // on retranche les jours seulement pour des conges pris (pas pour les absences)
                    // donc seulement si le type de l'absence qu'on annule est un "conges"
                    if (($tab_tout_type_abs[$value_type_abs_id]['type']=="conges")||($tab_tout_type_abs[$value_type_abs_id]['type']=="conges_exceptionnels")) {
                        soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $value_type_abs_id, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin);
                    }

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if ($config->isSendMailValidationUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges");
                    }
                }
            } elseif ($reponse == "VALID") // première validation dans le cas d'une double validation
            {
                /* UPDATE table "conges_periode" */
                $sql1 = 'UPDATE conges_periode SET p_etat=\'valid\', p_date_traitement=NOW() WHERE p_num='.\includes\SQL::quote($numero_int).' AND p_etat=\'demande\';' ;
                $ReqLog1 = \includes\SQL::query($sql1);

                if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if ($config->isSendMailValidationUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges");
                    }
                }
            } elseif ($reponse == "REFUSE") // refus d'un conges
            {
                // recup di motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql3 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus="'.\includes\SQL::quote($motif_refus).'", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';

                $ReqLog3 = \includes\SQL::query($sql3);

                if ($ReqLog3 && \includes\SQL::getVar('affected_rows')) {
                    // Log de l'action
                    log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $date_deb");

                    //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                    if ($config->isSendMailRefusUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges");
                    }
                }
            }
        }
        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?user_login=' . $user_login . '">';
        return $return;
    }

    public static function annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
        $return = '';

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs();

        while($elem_tableau = each($tab_checkbox_annule)) {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $VerifDec=verif_saisie_decimal($user_nb_jours_pris) ;
            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            $user_type_abs_id=$champs[2];

            $motif_annul=addslashes($tab_text_annul[$numero_int]);

            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="annul", p_motif_refus="'.\includes\SQL::quote($motif_annul).'", p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" AND p_etat="ok";';
            $ReqLog1 = \includes\SQL::query($sql1);

            if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
                // Log de l'action
                log_action($numero_int,"annul", $user_login, "annulation conges $numero ($user_login) ($user_nb_jours_pris jours)");

                /* UPDATE table "conges_solde_user" (jours restants) */
                // on re-crédite les jours seulement pour des conges pris (pas pour les absences)
                // donc seulement si le type de l'absence qu'on annule est un "conges"
                if (in_array($tab_tout_type_abs[$user_type_abs_id]['type'],["conges","conges_exceptionnels"])) {
                    $sql2 = 'UPDATE conges_solde_user SET su_solde = su_solde+"'. \includes\SQL::quote($user_nb_jours_pris).'" WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_id="'. \includes\SQL::quote($user_type_abs_id).'";';
                    $ReqLog2 = \includes\SQL::query($sql2);
                }

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if ($config->isSendMailAnnulationCongesUtilisateur()) {
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "annul_conges");
                }
            }
        }

        $return .= _('form_modif_ok') . '<br><br>';
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $PHP_SELF . '?user_login=' . $user_login . '">';
        return $return;
    }

    //affiche l'état des conges du user (avec le formulaire pour le responsable)
    public static function affiche_etat_conges_user_for_resp($user_login, $year_affichage, $tri_date)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
        $return = '';

        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;

        $return .= '<b>';
        $return .= '<a href="' . $PHP_SELF . '?onglet=traite_user&user_login=' . $user_login . '&year_affichage=' . $year_affichage_prec . '"><<</a>';
        $return .= '&nbsp&nbsp&nbsp ' . $year_affichage . '&nbsp&nbsp&nbsp';
        $return .= '<a href="' . $PHP_SELF . '?onglet=traite_user&user_login=' . $user_login . '&year_affichage=' . $year_affichage_suiv . '">>></a>';
        $return .= '</b><br><br>';


        // Récupération des informations de speriodes de conges/absences
        $sql3 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num FROM conges_periode " .
                "WHERE p_login = '$user_login' " .
                "AND p_etat !='demande' " .
                "AND p_etat !='valid' " .
                "AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";
        if ($tri_date=="descendant")
            $sql3=$sql3." ORDER BY p_date_deb DESC ";
        else
            $sql3=$sql3." ORDER BY p_date_deb ASC ";

        $ReqLog3 = \includes\SQL::query($sql3);

        $count3=$ReqLog3->num_rows;
        if ($count3==0) {
            $return .= '<b>' . _('resp_traite_user_aucun_conges') . '</b><br><br>';
        } else {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_types_abs = recup_tableau_tout_types_abs() ;

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?onglet=traite_user" method="POST">';
            $return .= '<table cellpadding="2" class="tablo">';
            $return .= '<thead>';
            $return .= '<tr>';
            $return .= '<th>';
            $return .= '<a href="' . $PHP_SELF . '?onglet=traite_user&user_login=' . $user_login . '&tri_date=descendant"><img src="' . IMG_PATH . '1downarrow-16x16.png" width="16" height="16" border="0" title="trier"></a>';
            $return .= _('divers_debut_maj_1');
            $return .= '<a href="' . $PHP_SELF . '?onglet=traite_user&user_login=' . $user_login . '&tri_date=ascendant"><img src="' . IMG_PATH . '1uparrow-16x16.png" width="16" height="16" border="0" title="trier"></a>';
            $return .= '</th>';
            $return .= '<th>' . _('divers_fin_maj_1') . '</th>';
            $return .= '<th>' . _('divers_nb_jours_pris_maj_1') . '</th>';
            $return .= '<th>' . _('divers_comment_maj_1') . '<br><i>' . _('resp_traite_user_motif_possible') . '</i></th>';
            $return .= '<th>' . _('divers_type_maj_1') . '</th>';
            $return .= ' <th>'. _('divers_etat_maj_1') .'</th>';
            $return .= ' <th>'. _('resp_traite_user_annul') .'</th>';
            $return .= ' <th>'. _('resp_traite_user_motif_annul') .'</th>';
            if ($config->canAfficheDateTraitement()) {
                $return .= '<th>'. _('divers_date_traitement') .'</th>' ;
            }
            $return .= '</tr>';
            $return .= '</thead>';
            $return .= '<tbody>';

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

                if ($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }

                if ($sql_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }

                if (($sql_etat=="annul") || ($sql_etat=="refus") || ($sql_etat=="ajout")) {
                    $casecocher1="";
                    if ($sql_etat=="refus") {
                        if ($sql_motif_refus=="")
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                    } elseif ($sql_etat=="annul") {
                        if ($sql_motif_refus=="")
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                    } elseif ($sql_etat=="ajout") {
                        $text_annul="&nbsp;";
                    }
                } else {
                    $casecocher1=sprintf("<input type=\"checkbox\" name=\"tab_checkbox_annule[$sql_num]\" value=\"$sql_login--$sql_nb_jours--$sql_type--ANNULE\">");
                    $text_annul="<input type=\"text\" name=\"tab_text_annul[$sql_num]\" size=\"20\" max=\"100\">";
                }

                $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $return .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td>';
                $return .= '<td>' . $sql_date_fin . '_' . $demi_j_fin . '</td>';
                $return .= '<td>' . $sql_nb_jours . '</td>';
                $return .= '<td>' . $sql_commentaire . '</td>';
                $return .= '<td>' . $tab_types_abs[$sql_type]['libelle'] . '</td>';
                $return .= '<td>';
                if ($sql_etat=="refus") {
                    $return .= _('divers_refuse') ;
                } elseif ($sql_etat=="annul") {
                    $return .= _('divers_annule') ;
                } else {
                    $return .= $sql_etat;
                }
                $return .= '</td>';
                $return .= '<td>' . $casecocher1 . '</td>';
                $return .= '<td>' . $text_annul . '</td>';

                if ($config->canAfficheDateTraitement()) {
                    if (empty($sql_p_date_demande)) {
                        $return .= '<td class="histo-left">' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    } else {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    }
                }
                $return .= '</tr>';
                $i = !$i;
            }
            $return .= '</tbody>';
            $return .= '</table>';

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<br><input type="submit" value="' . _('form_submit') . '">';
            $return .= '</form>';
        }
        return $return;
    }

    //affiche l'état des demande en attente de 2ieme validation du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_2_valid_user_for_resp($user_login)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
        $return = '';

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='valid' ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if ($count2==0) {
            $return .= '<b>' . _('resp_traite_user_aucune_demande') . '</b><br><br>';
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?onglet=traite_user" method="POST">';
            $return .= '<table cellpadding="2" class="tablo">';
            $return .= '<thead>';
            $return .= '<tr>';
            $return .= '<th>'. _('divers_debut_maj_1') .'</th>';
            $return .= '<th>'. _('divers_fin_maj_1') .'</th>';
            $return .= '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
            $return .= '<th>'. _('divers_comment_maj_1') .'</th>';
            $return .= '<th>'. _('divers_type_maj_1') .'</th>';
            $return .= '<th>'. _('divers_accepter_maj_1') .'</th>';
            $return .= '<th>'. _('divers_refuser_maj_1') .'</th>';
            $return .= '<th>'. _('resp_traite_user_motif_refus') .'</th>';
            if ($config->canAfficheDateTraitement()) {
                $return .= '<th>'. _('divers_date_traitement') .'</th>' ;
            }
            $return .= '</tr>';
            $return .= '</thead>';
            $return .= '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array() ) {
                $sql_date_deb = $resultat2["p_date_deb"];
                $sql_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                if ($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                $sql_date_fin = $resultat2["p_date_fin"];
                $sql_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                if ($sql_demi_jour_fin=="am") {
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

                $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $return .= '<td>' . $sql_date_deb_fr . '_' . $demi_j_deb . '</td>';
                $return .= '<td>' . $sql_date_fin_fr . '_' . $demi_j_fin . '</td>';
                $return .= '<td>' . $sql_nb_jours . '</td>';
                $return .= '<td>' . $sql_commentaire . '</td>';
                $return .= '<td>' . $tab_type_all_abs[$sql_type]['libelle'] . '</td>';
                $return .= '<td>' . $casecocher1 . '</td>';
                $return .= '<td>' . $casecocher2 . '</td>';
                $return .= '<td>' . $text_refus . '</td>';
                if ($config->canAfficheDateTraitement()) {
                    $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_date_traitement . '</td>';
                }

                $return .= '</tr>';
                $i = !$i;
            }
            $return .= '</tbody>';
            $return .= '</table>';

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<br><input class="btn btn-success" type="submit" value="' . _('form_submit') . '">  &nbsp;&nbsp;&nbsp;&nbsp; <input type="reset" value="' . _('form_cancel') . '">';
            $return .= '<a class="btn" href="' . $PHP_SELF . '">' . _('form_cancel') . '</a>';
            $return .= '</form>';
        }
        return $return;
    }

    //affiche l'état des demande du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
        $return = '';

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='demande' ".
                "ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if ($count2==0) {
            $return .= '<b>' . _('resp_traite_user_aucune_demande') . '</b><br><br>';
        } else {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            $return .= '<form action="' . $PHP_SELF . '?onglet=traite_user" method="POST">';
            $return .= '<table cellpadding="2" class="tablo">';
            $return .= '<thead>';
            $return .= '<tr>';
            $return .= '<th>'. _('divers_debut_maj_1') .'</th>';
            $return .= '<th>'. _('divers_fin_maj_1') .'</th>';
            $return .= '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
            $return .= '<th>'. _('divers_comment_maj_1') .'</th>';
            $return .= '<th>'. _('divers_type_maj_1') .'</th>';
            $return .= '<th>'. _('divers_accepter_maj_1') .'</th>';
            $return .= '<th>'. _('divers_refuser_maj_1') .'</th>';
            $return .= '<th>'. _('resp_traite_user_motif_refus') .'</th>';
            if ($config->canAfficheDateTraitement()) {
                $return .= '<th>'. _('divers_date_traitement') .'</th>' ;
            }
            $return .= '</tr>';
            $return .= '</thead>';
            $return .= '<tbody>';

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


                if ($sql_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                if ($sql_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";

                // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
                if ($tab_user['double_valid'] == "Y") {
                    /*******************************/
                    /* verif si le resp est grand_responsable pour ce user*/
                    if (in_array($_SESSION['userlogin'], $tab_grd_resp)) { // si resp_login est dans le tableau
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--VALID\">";
                    } else {
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                    }
                } else {
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                }

                $boutonradio2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";

                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $return .= '<td>' . $sql_date_deb_fr . '_' . $demi_j_deb . '</td>';
                $return .= '<td>' . $sql_date_fin_fr . '_'  . $demi_j_fin . '</td>';
                $return .= '<td>' . $sql_nb_jours . '</td>';
                $return .= '<td>' . $sql_commentaire . '</td>';
                $return .= '<td>' . $tab_type_all_abs[$sql_type]['libelle'] . '</td>';
                $return .= '<td>' . $boutonradio1 . '</td>';
                $return .= '<td>' . $boutonradio2 . '</td>';
                $return .= '<td>' . $text_refus . '</td>';
                if ($config->canAfficheDateTraitement()) {
                    if ($sql_date_traitement==NULL) {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                    } else {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_date_traitement . '</td>';
                    }
                }

                $return .= '</tr>';
                $i = !$i;
            }
            $return .= '</tbody>';
            $return .= '</table>';

            $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
            $return .= '<br><input class="btn btn-success" type="submit" value="' . _('form_submit') . '">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type="reset" value="' . _('form_cancel') . '">';
            $return .= '<a class="btn" href="' . $PHP_SELF . '">' . _('form_cancel') . '</a>';
            $return .= '</form>';
        }
        return $return;
    }

    public static function affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL); ;
        $return = '';

        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        if (!isset($_SESSION["tab_j_feries"])) {
            init_tab_jours_feries();
        }

        /********************/
        /* Récupération des informations sur le user : */
        /********************/
        $list_group_dbl_valid_du_resp = get_list_groupes_double_valid_du_resp($_SESSION['userlogin']);
        $tab_user=array();
        $tab_user = recup_infos_du_user($user_login, $list_group_dbl_valid_du_resp);
        $list_all_users_du_hr=\hr\Fonctions::get_list_all_users_du_hr($_SESSION['userlogin']);
        // recup des grd resp du user
        $tab_grd_resp=array();
        if ($config->isDoubleValidationActive()) {
            get_tab_grd_resp_du_user($user_login, $tab_grd_resp);
        }

        /********************/
        /* Titre */
        /********************/
        $return .= '<h3>'. _('resp_traite_user_titre') . ' ' . $tab_user['prenom'] . ' ' . $tab_user['nom'] . '.</h3>';


        /********************/
        /* Bilan des Conges */
        /********************/
        // AFFICHAGE TABLEAU
        // affichage du tableau récapitulatif des solde de congés d'un user
        $return .= affiche_tableau_bilan_conges_user($user_login);
        $return .= '<br><br>';

        /*************************/
        /* SAISIE NOUVEAU CONGES */
        /*************************/
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

        if (is_array($_SESSION["tab_j_fermeture"])) {
            foreach ($_SESSION["tab_j_fermeture"] as $date) {
                $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }
        $startDate =  '';

        $datePickerOpts = [
            'daysOfWeekDisabled' => $daysOfWeekDisabled,
            'datesDisabled'      => $datesDisabled,
            'startDate'          => $startDate,
        ];
        $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ');</script>';

        // si les mois et année ne sont pas renseignés, on prend ceux du jour
        if ($year_calendrier_saisie_debut==0) {
            $year_calendrier_saisie_debut=date("Y");
        }
        if ($mois_calendrier_saisie_debut==0) {
            $mois_calendrier_saisie_debut=date("m");
        }
        if ($year_calendrier_saisie_fin==0) {
            $year_calendrier_saisie_fin=date("Y");
        }
        if ($mois_calendrier_saisie_fin==0) {
            $mois_calendrier_saisie_fin=date("m");
        }
        $return .= '<h1>' . _('resp_traite_user_new_conges') . '</h1>';

        $return .= saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);

        $return .= '<hr align="center" size="2" width="90%">';

        /*********************/
        /* Etat des Demandes */
        /*********************/
        if ($config->canUserSaisieDemande()) {
            //verif si le user est bien un user du resp (et pas seulement du grad resp)
            if (strstr($list_all_users_du_hr, "'$user_login'")!=FALSE) {
                $return .= '<h3>' . _('resp_traite_user_etat_demandes') . '</h3>';

                //affiche l'état des demande du user (avec le formulaire pour le responsable)
                $return .= \hr\Fonctions::affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp);

                $return .= '<hr align="center" size="2" width="90%">';
            }
        }

        /*********************/
        /* Etat des Demandes en attente de 2ieme validation */
        /*********************/
        if ($config->isDoubleValidationActive()) {
            /*******************************/
            /* verif si le resp est grand_responsable pour ce user*/

            if (in_array($_SESSION['userlogin'], $tab_grd_resp)) // si resp_login est dans le tableau
            {
                $return .= '<h3>' . _('resp_traite_user_etat_demandes_2_valid') . '</h3>';

                //affiche l'état des demande en attente de 2ieme valid du user (avec le formulaire pour le responsable)
                $return .= self::affiche_etat_demande_2_valid_user_for_resp($user_login);

                $return .= '<hr align="center" size="2" width="90%">';
            }
        }

        /*******************/
        /* Etat des Conges */
        /*******************/
        $return .= '<h3>' . _('resp_traite_user_etat_conges') . '</h3>';

        //affiche l'état des conges du user (avec le formulaire pour le responsable)
        $return .= \hr\Fonctions::affiche_etat_conges_user_for_resp($user_login,  $year_affichage, $tri_date);

        //echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";

        $return .= '<td valign="middle">';
        $return .= '</td></tr></table>';
        $return .= '<center>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de traitement des utilisateurs
     *
     * @param string $onglet
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraiteUserModule($onglet)
    {
        //var pour hr_traite_user.php
        $user_login                 = htmlentities(getpost_variable('user_login'), ENT_QUOTES | ENT_HTML401);
        $tab_checkbox_annule        = getpost_variable('tab_checkbox_annule') ;
        $tab_radio_traite_demande   = getpost_variable('tab_radio_traite_demande') ;
        $new_demande_conges         = getpost_variable('new_demande_conges', 0) ;
        $return = '';

        // si une annulation de conges a été selectionée :
        if ( $tab_checkbox_annule != '' ) {
            $tab_text_annul         = getpost_variable('tab_text_annul') ;
            $return .= \hr\Fonctions::annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul);
        }
        // si le traitement des demandes a été selectionée :
        elseif ( $tab_radio_traite_demande != '' ) {
            $tab_text_refus         = getpost_variable('tab_text_refus') ;
            $return .= \hr\Fonctions::traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus);
        }
        // si un nouveau conges ou absence a été saisi pour un user :
        elseif ( $new_demande_conges == 1 ) {
            $new_debut          = getpost_variable('new_debut') ;
            $new_demi_jour_deb  = getpost_variable('new_demi_jour_deb') ;
            $new_fin            = getpost_variable('new_fin') ;
            $new_demi_jour_fin  = getpost_variable('new_demi_jour_fin') ;
            $new_comment        = getpost_variable('new_comment') ;
            $new_type           = getpost_variable('new_type') ;

            $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $comment);

            $return .= \hr\Fonctions::new_conges($user_login, "", $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type);
        } else {
            $year_calendrier_saisie_debut   = getpost_variable('year_calendrier_saisie_debut', 0) ;
            $mois_calendrier_saisie_debut   = getpost_variable('mois_calendrier_saisie_debut', 0) ;
            $year_calendrier_saisie_fin     = getpost_variable('year_calendrier_saisie_fin', 0) ;
            $mois_calendrier_saisie_fin     = getpost_variable('mois_calendrier_saisie_fin', 0) ;
            $tri_date                       = getpost_variable('tri_date', "ascendant") ;
            $year_affichage                 = (int) getpost_variable('year_affichage' , date("Y") );

            $return .= \hr\Fonctions::affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet);
        }
        return $return;
    }

    // recup de la liste de tous les groupes pour le mode RH
    public static function get_list_groupes_pour_rh($user_login)
    {
        $list_group="";

        $sql1="SELECT DISTINCT gu_gid FROM conges_groupe_users ORDER BY gu_gid"; // Le but est de sélectionner tous les groupes ayant des utilisateurs
        $ReqLog1 = \includes\SQL::query($sql1);

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

    // on insert l'ajout de conges dans la table periode
    public static function insert_ajout_dans_periode($login, $nb_jours, $id_type_abs, $commentaire)
    {
        $date_today=date("Y-m-d");

        $result=insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0);
    }

    public static function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        // recup de la liste des users d'un groupe donné
        $list_users = get_list_users_du_groupe($choix_groupe);
        if (empty($list_users)) {
            return;
        }
        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if ($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) AND u_is_active='Y' ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while ($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                        $nb_conges=$nb_jours;
                    } else {
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
                    }

                    $valid=verif_saisie_decimal($nb_conges);
                    if ($valid) {
                        // 1 : on update conges_solde_user
                        $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde+ '.$nb_conges.'
                                WHERE  su_login = "'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.intval($id_conges).';';
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges dans la table periode
                        // recup du nom du groupe
                        $groupename= get_group_name_from_id($choix_groupe);
                        $commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";

                        // ajout conges
                        \hr\Fonctions::insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
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

    public static function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $list_users_du_resp = \hr\Fonctions::get_list_all_users_du_hr($_SESSION['userlogin']);

        foreach($tab_new_nb_conges_all as $id_conges => $nb_jours) {
            if ($nb_jours!=0) {
                $comment = $tab_new_comment_all[$id_conges];

                $sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
                $ReqLog1 = \includes\SQL::query($sql1);

                while($resultat1 = $ReqLog1->fetch_array()) {
                    $current_login  =$resultat1["u_login"];
                    $current_quotite=$resultat1["u_quotite"];

                    if ( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) ) {
                        $nb_conges=$nb_jours;
                    } else {
                        // pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
                        $nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
                    }
                    $valid=verif_saisie_decimal($nb_conges);
                    if ($valid) {
                        // 1 : update de la table conges_solde_user
                        $req_update = 'UPDATE conges_solde_user SET su_solde = su_solde + '.$nb_conges.'
                                WHERE  su_login = "'. \includes\SQL::quote($current_login).'"  AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
                        $ReqLog_update = \includes\SQL::query($req_update);

                        // 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
                        $commentaire =  _('resp_ajout_conges_comment_periode_all') ;
                        // ajout conges
                        \hr\Fonctions::insert_ajout_dans_periode($current_login, $nb_conges, $id_conges, $commentaire);
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


    public static function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
        {
          foreach($tab_conges as $id_conges => $user_nb_jours_ajout) {

            $valid=verif_saisie_decimal($user_nb_jours_ajout);   //verif la bonne saisie du nombre décimal
            if ($valid) {
              if ($user_nb_jours_ajout!=0) {
                /* Modification de la table conges_users */
                $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde+'.$user_nb_jours_ajout.' WHERE su_login="'. \includes\SQL::quote($user_name).'" AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
                /* On valide l'UPDATE dans la table ! */
                $ReqLog1 = \includes\SQL::query($sql1) ;

                // on insert l'ajout de conges dans la table periode
                $commentaire =  _('resp_ajout_conges_comment_periode_user') ;
                \hr\Fonctions::insert_ajout_dans_periode($user_name, $user_nb_jours_ajout, $id_conges, $commentaire);
              }
            }
          }
        }
    }

    public static function affichage_saisie_globale_groupe($tab_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs */

        // on établi la liste complète des groupes pour le mode RH
        $list_group = \hr\Fonctions::get_list_groupes_pour_rh($_SESSION['userlogin']);

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
            $ReqLog_group = \includes\SQL::query($sql_group) ;

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

    public static function affichage_saisie_globale_pour_tous($tab_type_conges)
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

    public static function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp)
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

    // renvoit un tableau de tableau contenant les informations de tous les users dont $login est HR responsable
    public static function recup_infos_all_users_du_hr($login)
    {
        $tab=array();
        $list_groupes_double_validation=get_list_groupes_double_valid();

        $sql1 = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom";
        $ReqLog = \includes\SQL::query($sql1) ;

        while ($resultat = $ReqLog->fetch_array())
        {
            $tab_user=array();
            $sql_login=$resultat["u_login"];
            $tab[$sql_login] = recup_infos_du_user($sql_login, $list_groupes_double_validation);
        }
        return $tab ;
    }

    // recup de la liste de TOUS les users pour le responsable RH
    // renvoit une liste de login entre quotes et séparés par des virgules
    public static function get_list_all_users_du_hr($resp_login)
    {
        $list_users="";

        $sql1="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' AND u_is_active='Y' ORDER BY u_nom  ";
        $ReqLog1 = \includes\SQL::query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array())
        {
            $current_login=$resultat1["u_login"];
                if ($list_users=="") {
                    $list_users="'$current_login'";
                } else {
                    $list_users=$list_users.", '$current_login'";
                }
        }
        return $list_users;
    }

    public static function saisie_ajout( $tab_type_conges)
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
            $return .= \hr\Fonctions::affichage_saisie_globale_pour_tous($tab_type_conges);
            $return .= '<br>';

            /***********************************************************************/
            /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
            $return .= \hr\Fonctions::affichage_saisie_globale_groupe($tab_type_conges);
            $return .= '<br>';

            /************************************************************/
            /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
            $return .= \hr\Fonctions::affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp);
            $return .= '<br>';

        } else {
            $return .= _('resp_etat_aucun_user') . '<br>';
        }
        return $return;
    }

    /**
     * Encapsule le comportement du module d'ajout de congés
     *
     * @param array  $tab_type_cong
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageAjoutCongesModule($tab_type_cong)
    {
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

            $return .= \hr\Fonctions::ajout_conges($tab_champ_saisie, $tab_commentaire_saisie);
            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } elseif ( $ajout_global == "TRUE" ) {

            $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all         = getpost_variable('tab_new_comment_all');

            $return .= \hr\Fonctions::ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } elseif ( $ajout_groupe == "TRUE" ) {

            $tab_new_nb_conges_all       = getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel    = getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all         = getpost_variable('tab_new_comment_all');
            $choix_groupe                = getpost_variable('choix_groupe');

            \hr\Fonctions::ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all);
            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } else {
            $return .= \hr\Fonctions::saisie_ajout($tab_type_cong);
        }

        return $return;
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
    public static function get_tableau_jour_feries($year, &$tab_year)
    {

        $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'-%" ;';
        $res_select = \includes\SQL::query($sql_select);
        $num_select = $res_select->num_rows;

        if ($num_select!=0) {
            while($result_select = $res_select->fetch_array()) {
                $tab_year[]=$result_select["jf_date"];
            }
        }
    }

    public static function verif_year_deja_saisie($tab_checkbox_j_chome) {
        $date_1=key($tab_checkbox_j_chome);
        $year=substr($date_1, 0, 4);
        $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'%" ;';
        $relog = \includes\SQL::query($sql_select);
        return($relog->num_rows != 0);
    }

    public static function delete_year($tab_checkbox_j_chome) {
        $date_1=key($tab_checkbox_j_chome);
        $year=substr($date_1, 0, 4);
        $sql_delete='DELETE FROM conges_jours_feries WHERE jf_date LIKE "'. \includes\SQL::quote($year).'%" ;';
        $result = \includes\SQL::query($sql_delete);

        return true;
    }

    public static function insert_year($tab_checkbox_j_chome) {
        foreach($tab_checkbox_j_chome as $key => $value)
            $result = \includes\SQL::query('INSERT INTO conges_jours_feries SET jf_date="'. \includes\SQL::quote($key).'";');
        return true;
    }

    public static function commit_saisie($tab_checkbox_j_chome)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
        if (\hr\Fonctions::verif_year_deja_saisie($tab_checkbox_j_chome)) {
            $result = \hr\Fonctions::delete_year($tab_checkbox_j_chome);
        }


        // on insert les nouvelles dates saisies
        $result = \hr\Fonctions::insert_year($tab_checkbox_j_chome);

        // on recharge les jours feries dans les variables de session
        init_tab_jours_feries();

        if ($result) {
            $return .= '<div class="alert alert-success">' . _('form_modif_ok') . '</div>';
        } else {
            $return .= '<div class="alert alert-danger">' . _('form_modif_not_ok') . '</div>';
        }

        $date_1=key($tab_checkbox_j_chome);
        $tab_date = explode('-', $date_1);
        $comment_log = "saisie des jours chomés pour ".$tab_date[0] ;
        log_action(0, "", "", $comment_log);
        return $return;
    }

    public static function confirm_saisie($tab_checkbox_j_chome)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        header_popup();

        $return .= '<h1>' . _('admin_jours_chomes_titre') . '</h1>';
        $return .= '<form action="' . $PHP_SELF . '?onglet=jours_chomes" method="POST">';
        $return .= '<table>';
        $return .= '<tr>';
        $return .= '<td align="center">';

        foreach($tab_checkbox_j_chome as $key => $value) {
            $date_affiche=eng_date_to_fr($key);
            $return .= $date_affiche . '<br>';
            $return .= '<input type="hidden" name="tab_checkbox_j_chome[' . $key . ']" value="' . $value . '">';
        }
        $return .= '<input type="hidden" name="choix_action" value="commit">';
        $return .= '<input type="submit" value="' . _('admin_jours_chomes_confirm') . '">';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= '<td align="center">';
        $return .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';
        $return .= '</form>';

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
    public static function affiche_calendrier_saisie_jours_chomes($year, $mois, $tab_year)
    {
        $jour_today=date("j");
        $jour_today_name=date("D");
        $return = '';

        $first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
        $mois_name=date_fr("F", $first_jour_mois_timestamp);
        $first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        if ($first_jour_mois_rang==0) {
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
        }

        $return .= '<table>';
        /* affichage  2 premieres lignes */
        $return .= '<thead>';
        $return .= '<tr align="center"><th colspan=7 class="titre">' . $mois_name . ' ' . $year . '</th></tr>';
        $return .= '<tr>';
        $return .= '<th class="cal-saisie2">' . _('lundi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('mardi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('mercredi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('jeudi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('vendredi_1c') . '</th>';
        $return .= '<th class="cal-saisie2 weekend">' . _('samedi_1c') . '</th>';
        $return .= '<th class="cal-saisie2 weekend">' . _('dimanche_1c') . '</th>';
        $return .= '</tr>';
        $return .= '</thead>';

        /* affichage ligne 1 du mois*/
        $return .= '<tr>';
        // affichage des cellules vides jusqu'au 1 du mois ...
        for($i=1; $i<$first_jour_mois_rang; $i++) {
            $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        // affichage des cellules cochables du 1 du mois à la fin de la ligne ...
        for($i=$first_jour_mois_rang; $i<8; $i++) {
            $j=$i-$first_jour_mois_rang+1;
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$j,$year,$tab_year);
        }
        $return .= '</tr>';

        /* affichage ligne 2 du mois*/
        $return .= '<tr>';
        for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        $return .= '</tr>';

        /* affichage ligne 3 du mois*/
        $return .= '<tr>';
        for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++) {
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        $return .= '</tr>';

        /* affichage ligne 4 du mois*/
        $return .= '<tr>';
        for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }
        $return .= '</tr>';

        /* affichage ligne 5 du mois (peut etre la derniere ligne) */
        $return .= '<tr>';
        for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }

        for($i; $i<36-$first_jour_mois_rang+1; $i++) {
            $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        $return .= '</tr>';

        /* affichage ligne 6 du mois (derniere ligne)*/
        $return .= '<tr>';
        for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
            $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$tab_year);
        }

        for($i; $i<43-$first_jour_mois_rang+1; $i++) {
            $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$tab_year);
        }
        $return .= '</tr></table>';

        return $return;
    }

    public static function saisie($year_calendrier_saisie)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration();
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // si l'année n'est pas renseignée, on prend celle du jour
        if ($year_calendrier_saisie==0) {
            $year_calendrier_saisie = date("Y");
        }

        // on construit le tableau des jours feries de l'année considérée
        $tab_year = [];
        \hr\Fonctions::get_tableau_jour_feries($year_calendrier_saisie, $tab_year);

        //calcul automatique des jours feries
        if ($config->isJoursFeriesFrance()) {
            $tableau_jour_feries = \hr\Fonctions::fcListJourFeries($year_calendrier_saisie) ;
            foreach ($tableau_jour_feries as $i => $value) {
                if (!in_array ("$value", $tab_year))
                    $tab_year[] = $value;
            }
        }
        $return .= '<form action="' . $PHP_SELF . '?onglet=jours_chomes&year_calendrier_saisie=' . $year_calendrier_saisie . '" method="POST">';
        $return .= '<div class="calendar">';
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        $i = 0;
        foreach ($months as $month) {
            if ($i%4 == 0) {
                $return .= '<div class="row">';
            }
            $return .= '<div class="month">';
            $return .= '<div class="wrapper">';
            $return .= \hr\Fonctions::affiche_calendrier_saisie_jours_chomes($year_calendrier_saisie, $month, $tab_year);
            $return .= '</div>';
            $return .= '</div>';
            if ($i%4 == 3) {
                $return .= '</div>';
            }
            $i++;
        }
        $return .= '</div>';
        $return .= '</div>';
        $return .= '<div class="actions">';
        $return .= '<input type="hidden" name="choix_action" value="commit">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '</div>';
        $return .= '</form>';

        return $return;
    }

    /**
     * Encapsule le comportement du module des jours chomés
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageJoursChomesModule()
    {
        // verif des droits du user à afficher la page
        verif_droits_user( "is_hr");
        $return = '';
        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $choix_action                 = getpost_variable('choix_action');
        $year_calendrier_saisie        = getpost_variable('year_calendrier_saisie', 0);
        $checkbox = getpost_variable('tab_checkbox_j_chome');
        $tab_checkbox_j_chome = (!is_array($checkbox) || empty($checkbox)) ? [] : $checkbox;
        /*************************************/

        // si l'année n'est pas renseignée, on prend celle du jour
        if ($year_calendrier_saisie==0) {
            $year_calendrier_saisie = date("Y");
        }

        $add_css = '<style>#onglet_menu .onglet{ width: 50% ;}</style>';

        //    header_menu('hr', NULL, $add_css);
        $return .= '<h1>'. _('admin_button_jours_chomes_1') . '</h1>';
        $return .= '<div class="pager">';
        $return .= '<div class="onglet calendar-nav">';
        // navigation
        $prev_link = "$PHP_SELF?onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie - 1);
        $next_link = "$PHP_SELF?onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie + 1);
        $return .= '<ul>';
        $return .= '<li><a href="' . $prev_link . '" class="calendar-prev"><i class="fa fa-chevron-left"></i><span>année précédente</span></a></li>';
        $return .= '&nbsp;<li class="current-year">' . $year_calendrier_saisie . '</li>';
        $return .= '&nbsp;<li><a href="' . $next_link . '" class="calendar-next"><i class="fa fa-chevron-right"></i><span>année suivante</span></a></li>';
        $return .= '</ul>';
        $return .= '</div>';
        $return .= '</div>';
        if ($choix_action=="commit") {
            $return .= \hr\Fonctions::commit_saisie($tab_checkbox_j_chome);
        }
        $return .= '<div class="wrapper">';
        $return .= \hr\Fonctions::saisie($year_calendrier_saisie);
        $return .= '</div>';
        return $return;
    }

    // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée) et stockage dans la table
    public static function set_nouvelle_date_limite_reliquat()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        //si on autorise les reliquats
        if ($config->isReliquatsAutorise()) {
            // s'il y a une date limite d'utilisationdes reliquats (au format jj-mm)
            if ($config->getDateLimiteReliquats() != 0) {
                // nouvelle date limite au format aaa-mm-jj
                $t=explode("-", $config->getDateLimiteReliquats());
                $new_date_limite = date("Y")."-".$t[1]."-".$t[0];

                //si la date limite n'a pas encore été updatée
                if ($_SESSION['config']['date_limite_reliquats'] < $new_date_limite) {
                    /* Modification de la table conges_appli */
                    $sql_update= 'UPDATE conges_appli SET appli_valeur = \''.$new_date_limite.'\' WHERE appli_variable=\'date_limite_reliquats\';';
                    $ReqLog_update = \includes\SQL::query($sql_update) ;

                }
            }
        }
    }

    // cloture / debut d'exercice pour TOUS les users d'un groupe'
    public static function cloture_globale_groupe($group_id, $tab_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup de la liste de TOUS les users du groupe
        $tab_all_users_du_groupe=recup_infos_all_users_du_groupe($group_id);
        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if (count($tab_all_users_du_groupe)!=0) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_groupe as $current_login => $tab_current_user) {
                $return .= \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture);
            }
        }
        return $return;
    }

    // cloture / debut d'exercice pour TOUS les users du resp (ou grand resp)
    public static function cloture_globale($tab_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=\hr\Fonctions::recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

        if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                $return .= \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture);
            }
        }
        return $return;
    }

    // verifie si tous les users on été basculés de l'exercice précédent vers le suivant.
    // si oui : on incrémente le num_exercice de l'application
    public static function update_appli_num_exercice()
    {
        // verif
        $appli_num_exercice = $_SESSION['config']['num_exercice'] ;
        $sql_verif = "SELECT u_login FROM conges_users WHERE u_login != 'admin' AND u_login != 'conges' AND u_num_exercice != $appli_num_exercice "  ;
        $ReqLog_verif = \includes\SQL::query($sql_verif) ;

        if ($ReqLog_verif->num_rows == 0) {
            /* Modification de la table conges_appli */
            $sql_update= "UPDATE conges_appli SET appli_valeur = appli_valeur+1 WHERE appli_variable='num_exercice' ";
            $ReqLog_update = \includes\SQL::query($sql_update) ;

            // ecriture dans les logs
            $new_appli_num_exercice = $appli_num_exercice+1 ;
            log_action(0, "", "", "fin/debut exercice (appli_num_exercice : $appli_num_exercice -> $new_appli_num_exercice)");
        }
    }

    public static function cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire)
    {
        $return = '';
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on le bascule d'exercice
        if ($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice']) {
            // calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée)
            \hr\Fonctions::set_nouvelle_date_limite_reliquat();

            //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
            $tab_conges_current_user=$tab_current_user['conges'];
            foreach($tab_type_conges as $id_conges => $libelle) {
                $user_nb_jours_ajout_an = $tab_conges_current_user[$libelle]['nb_an'];
                $user_solde_actuel= $tab_conges_current_user[$libelle]['solde'];
                $user_reliquat_actuel= $tab_conges_current_user[$libelle]['reliquat'];

                /**********************************************/
                /* Modification de la table conges_solde_user */

                if ($config->isReliquatsAutorise()) {
                    // ATTENTION : si le solde du user est négatif, on ne compte pas de reliquat et le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if ($user_solde_actuel>0) {
                        //calcul du reliquat pour l'exercice suivant
                        if ($config->getReliquatsMax() != 0) {
                            if ($user_solde_actuel <= $config->getReliquatsMax()) {
                                $new_reliquat = $user_solde_actuel ;
                            } else {
                                $new_reliquat = $config->getReliquatsMax();
                            }
                        } else {
                            $new_reliquat = $user_reliquat_actuel + $user_solde_actuel ;
                        }

                        $VerifDec = verif_saisie_decimal($new_reliquat);
                        //
                        // update D'ABORD du reliquat
                        $sql_reliquat = 'UPDATE conges_solde_user SET su_reliquat = '.$new_reliquat.' WHERE su_login="'. \includes\SQL::quote($current_login).'"  AND su_abs_id = '.$id_conges;
                        $ReqLog_reliquat = \includes\SQL::query($sql_reliquat) ;
                    } else {
                        $new_reliquat = $user_solde_actuel ; // qui est nul ou negatif
                    }

                    $new_solde = $user_nb_jours_ajout_an + $new_reliquat  ;
                    $VerifDec = verif_saisie_decimal($new_solde);

                    // update du solde
                    $sql_solde = 'UPDATE conges_solde_user SET su_solde = '.$new_solde.' WHERE su_login="'. \includes\SQL::quote($current_login).'"  AND su_abs_id = '.intval($id_conges).';';
                    $ReqLog_solde = \includes\SQL::query($sql_solde) ;
                } else {
                    // ATTENTION : meme si on accepte pas les reliquats, si le solde du user est négatif, il faut le reporter: le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
                    if ($user_solde_actuel < 0) {
                        $new_solde = $user_nb_jours_ajout_an + $user_solde_actuel ; // qui est nul ou negatif
                    } else {
                        $new_solde = $user_nb_jours_ajout_an ;
                    }

                    $VerifDec = verif_saisie_decimal($new_solde);
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
                insert_dans_periode($current_login, $date_today, "am", $date_today, "am", $user_nb_jours_ajout_an, $commentaire, $id_conges, "ajout", 0);
            }

            // on incrémente le num_exercice de l'application si tous les users ont été basculés.
            \hr\Fonctions::update_appli_num_exercice();
        }
        return $return;
    }

    // cloture / debut d'exercice user par user pour les users du resp (ou grand resp)
    public static function cloture_users($tab_type_conges, $tab_cloture_users, $tab_commentaire_saisie)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=\hr\Fonctions::recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            // traitement des users dont on est responsable :
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                // tab_cloture_users[$current_login]=TRUE si checkbox "cloturer" est cochée
                if ( (isset($tab_cloture_users[$current_login])) && ($tab_cloture_users[$current_login]=TRUE) ) {
                    $commentaire = $tab_commentaire_saisie[$current_login];
                    $return .= \hr\Fonctions::cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire);
                }
            }
        }
        return $return;
    }

    public static function affichage_cloture_globale_groupe($tab_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /***********************************************************************/
        /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */

        // on établi la liste complète des groupes dont on est le resp (ou le grd resp)
        $list_group=get_list_groupes_du_resp($_SESSION['userlogin']);

        if ($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
        {
            $return .= '<form action="' . $PHP_SELF . '" method="POST">';
            $return .= '<table>';
            $return .= '<tr><td align="center">';
            $return .= '<fieldset class="cal_saisie">';
            $return .= '<legend class="boxlogin">' . _('resp_cloture_exercice_groupe') . '</legend>';

            $return .= '<table>';
            $return .= '<tr>';

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

            $return .= '<td class="big">' . _('resp_ajout_conges_choix_groupe') . ' : ' . $text_choix_group . '</td>';
            $return .= '</tr>';
            $return .= '<tr>';
            $return .= '<td class="big">' . _('resp_cloture_exercice_for_groupe_text_confirmer') . '</td>';
            $return .= '</tr>';
            $return .= '<tr>';
            $return .= '<td align="center"><input type="submit" value="' . _('form_valid_cloture_group') . '"></td>';
            $return .= '</tr>';
            $return .= '</table>';

            $return .= '</fieldset>';
            $return .= '</td></tr>';
            $return .= '</table>';

            $return .= '<input type="hidden" name="onglet" value="cloture_exercice">';
            $return .= '<input type="hidden" name="cloture_groupe" value="TRUE">';
            $return .= '</form>';
        }

        return $return;
    }

    public static function affichage_cloture_globale_pour_tous($tab_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /************************************************************/
        /* CLOTURE EXERCICE GLOBALE pour tous les utilisateurs du responsable */

        $return .= '<form action="' . $PHP_SELF . '?onglet=cloture_year" method="POST">';
        $return .= '<table>';
        $return .= '<tr><td align="center">';
        $return .= '<fieldset class="cal_saisie">';
        $return .= '<legend class="boxlogin">' . _('resp_cloture_exercice_all') . '</legend>';
        $return .= '<table>';
        $return .= '<tr>';
        $return .= '<td class="big">&nbsp;&nbsp;&nbsp;' . _('resp_cloture_exercice_for_all_text_confirmer') . '&nbsp;&nbsp;&nbsp;</td>';
        $return .= '</tr>';
        // bouton valider
        $return .= '<tr>';
        $return .= '<td colspan="5" align="center"><input type="submit" value="' . _('form_valid_cloture_global') . '"></td>';
        $return .= '</tr>';
        $return .= '</table>';
        $return .= '</fieldset>';
        $return .= '</td></tr>';
        $return .= '</table>';
        $return .= '<input type="hidden" name="cloture_globale" value="TRUE">';
        $return .= '</form>';
        return $return;
    }

    public static function affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i = true)
    {
        $return = '';
        $return .= '<tr class="' . ($i ? 'i' : 'p') . '">';
        //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
        $tab_conges=$tab_current_user['conges'];

        /** sur la ligne ,   **/
        $return .= '<td>' . $tab_current_user['nom'] . '</td>';
        $return .= '<td>' . $tab_current_user['prenom'] . '</td>';
        $return .= '<td>' . $tab_current_user['quotite'] . '%</td>';

        foreach($tab_type_conges as $id_conges => $libelle) {
            if (isset($tab_conges[$libelle])) {
                $return .= '<td>' . $tab_conges[$libelle]['nb_an'] . ' <i>(' . $tab_conges[$libelle]['solde'] . ')</i></td>';
            } else {
                $return .= '<td></td>';
            }
        }

        // si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on peut le cocher
        if ($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice']) {
            $return .= '<td align="center" class="histo"><input type="checkbox" name="tab_cloture_users[' . $current_login . ']" value="TRUE" checked></td>';
        } else {
            $return .= '<td align="center" class="histo"><img src="' . IMG_PATH . 'stop.png" width="16" height="16" border="0" ></td>';
        }

        $comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");
        $return .= '<td align="center" class="histo"><input type="text" name="tab_commentaire_saisie[' . $current_login . ']" size="20" maxlength="200" value="' . $comment_cloture . '"></td>';
        $return .= '</tr>';

        return $return;
    }

    public static function affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /************************************************************/
        /* CLOTURE EXERCICE USER PAR USER pour tous les utilisateurs du responsable */

        if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            $return .= '<form action="' . $PHP_SELF . '?onglet=cloture_year" method="POST">';
            $return .= '<table>';
            $return .= '<tr>';
            $return .= '<td align="center">';
            $return .= '<fieldset class="cal_saisie">';
            $return .= '<legend class="boxlogin">' . _('resp_cloture_exercice_users') . '</legend>';
            $return .= '<table>';
            $return .= '<tr>';
            $return .= '<td align="center">';

            // AFFICHAGE TITRES TABLEAU
            $return .= '<table cellpadding="2" class="tablo">';
            $return .= '<thead>';
            $return .= '<tr>';
            $return .= '<th>' . _('divers_nom_maj_1') . '</th>';
            $return .= '<th>' . _('divers_prenom_maj_1') . '</th>';
            $return .= '<th>' . _('divers_quotite_maj_1') . '</th>';
            foreach($tab_type_conges as $id_conges => $libelle) {
                $return .= '<th>' . $libelle . '<br><i>(' . _('divers_solde') . ')</i></th>';
            }
            $return .= '<th>' . _('divers_cloturer_maj_1') . '<br></th>';
            $return .= '<th>' . _('divers_comment_maj_1') . '<br></th>';
            $return .= '</tr>';
            $return .= '</thead>';
            $return .= '<tbody>';

            // AFFICHAGE LIGNES TABLEAU

            // affichage des users dont on est responsable :
            $i = true;
            foreach($tab_all_users_du_hr as $current_login => $tab_current_user) {
                $return .= \hr\Fonctions::affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i);
                $i = !$i;
            }

            $return .= '</tbody>';
            $return .= '</table>';
            $return .= '</td>';
            $return .= '</tr>';
            $return .= '<tr>';
            $return .= '<td align="center">';
            $return .= '<input type="submit" value="' . _('form_submit') . '">';
            $return .= '</td>';
            $return .= '</tr>';
            $return .= '</table>';
            $return .= '</fieldset>';
            $return .= '</td></tr>';
            $return .= '</table>';
            $return .= '<input type="hidden" name="cloture_users" value="TRUE">';
            $return .= '</form>';
        }

        return $return;
    }

    public static function saisie_cloture( $tab_type_conges)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup de la liste de TOUS les users dont $resp_login est responsable
        // (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
        // renvoit une liste de login entre quotes et séparés par des virgules
        $tab_all_users_du_hr=\hr\Fonctions::recup_infos_all_users_du_hr($_SESSION['userlogin']);
        $tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
        if ( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) ) {
            /************************************************************/
            /* SAISIE GLOBALE pour tous les utilisateurs du responsable */
            $return .= \hr\Fonctions::affichage_cloture_globale_pour_tous($tab_type_conges);
            $return .= '<br>';

            /***********************************************************************/
            /* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
            $return .= \hr\Fonctions::affichage_cloture_globale_groupe($tab_type_conges);
            $return .= '<br>';

            /************************************************************/
            /* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
            $return .= \hr\Fonctions::affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp);
            $return .= '<br>';

        } else {
            $return .= _('resp_etat_aucun_user') . '<br>';
        }
        return $return;
    }

    /**
     * Encapsule le comportement du module de cloture d'exercice
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageClotureYearModule()
    {
        /*************************************/
        // recup des parametres reçus :

        $cloture_users   = getpost_variable('cloture_users');
        $cloture_globale = getpost_variable('cloture_globale');
        $cloture_groupe  = getpost_variable('cloture_groupe');
        $return = '';
        /*************************************/

        /** initialisation des tableaux des types de conges/absences  **/
        // recup du tableau des types de conges (conges et congesexceptionnels)
        // on concatene les 2 tableaux
        $tab_type_cong = ( recup_tableau_types_conges() + recup_tableau_types_conges_exceptionnels()  );

        // titre
        $return .= '<h1>'. _('resp_cloture_exercice_titre') . '</h1>';

        if ($cloture_users=="TRUE") {
            $tab_cloture_users       = getpost_variable('tab_cloture_users');
            $tab_commentaire_saisie       = getpost_variable('tab_commentaire_saisie'); //a vérifier
            $return .= \hr\Fonctions::cloture_users($tab_type_cong, $tab_cloture_users, $tab_commentaire_saisie);

            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } elseif ($cloture_globale=="TRUE") {
            \hr\Fonctions::cloture_globale($tab_type_cong);

            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } elseif ($cloture_groupe=="TRUE") {
            $choix_groupe            = getpost_variable('choix_groupe');
            $return .= \hr\Fonctions::cloture_globale_groupe($choix_groupe, $tab_type_cong);

            redirect( ROOT_PATH .'hr/hr_index.php', false);
            exit;
        } else {
            $return .= \hr\Fonctions::saisie_cloture($tab_type_cong);
        }
        return $return;
    }

    public static function affiche_calendrier_fermeture_mois($year, $mois, $tab_year)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $jour_today=date("j");
        $jour_today_name=date("D");
        $return = '';

        $first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
        $mois_name=date_fr("F", $first_jour_mois_timestamp);
        $first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
        if ($first_jour_mois_rang==0) {
            $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
        }

        $return .= '<table>';
        /* affichage  2 premieres lignes */
        $return .= '<thead>';
        $return .= '<tr><th colspan=7 class="titre">' . $mois_name . ' ' . $year . '</th></tr>';
        $return .= '<tr>';
        $return .= '<th class="cal-saisie2">' . _('lundi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('mardi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('mercredi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('jeudi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('vendredi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('samedi_1c') . '</th>';
        $return .= '<th class="cal-saisie2">' . _('dimanche_1c') . '</th>';
        $return .= '</tr>' ;
        $return .= '</thead>';

        /* affichage ligne 1 du mois*/
        $return .= '<tr>';
        // affichage des cellules vides jusqu'au 1 du mois ...
        for($i=1; $i<$first_jour_mois_rang; $i++) {
            if ( (($i==6)&&(!$config->isSamediOuvrable())) || (($i==7)&&(!$config->isDimancheOuvrable())) ) {
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            } else {
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            }
            $return .= '<td class="month-out cal-saisie2">&nbsp;</td>';
        }
        // affichage des cellules du 1 du mois à la fin de la ligne ...
        for($i=$first_jour_mois_rang; $i<8; $i++) {
            $j=$i-$first_jour_mois_rang+1 ;
            $j_timestamp=mktime (0,0,0,$mois,$j,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td  class="cal-saisie ' . $td_second_class . '">' . $j_day . '</td>';
        }
        $return .= '</tr>';

        /* affichage ligne 2 du mois*/
        $return .= '<tr>';
        for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td class="cal-saisie ' . $td_second_class . '">' . $j_day . '</td>';
        }
        $return .= '</tr>';

        /* affichage ligne 3 du mois*/
        $return .= '<tr>';
        for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td class="cal-saisie ' . $td_second_class . '">' . $j_day .  '</td>';
        }
        $return .= '</tr>';

        /* affichage ligne 4 du mois*/
        $return .= '<tr>';
        for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td class="cal-saisie ' . $td_second_class . '">' . $j_day . '</td>';
        }
        $return .= '</tr>';

        /* affichage ligne 5 du mois (peut etre la derniere ligne) */
        $return .= '<tr>';
        for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td  class="cal-saisie ' . $td_second_class . '">' . $j_day . '</td>';
        }
        for($i; $i<36-$first_jour_mois_rang+1; $i++) {
            if ((($i==35-$first_jour_mois_rang)&&(!$config->isSamediOuvrable())) || (($i==36-$first_jour_mois_rang)&&(!$config->isDimancheOuvrable()))) {
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            } else {
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            }
            $return .= '<td class="cal-saisie2 month-out">&nbsp;</td>';
        }
        $return .= '</tr>';

        /* affichage ligne 6 du mois (derniere ligne)*/
        $return .= '<tr>';
        for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
            $j_timestamp=mktime (0,0,0,$mois,$i,$year);
            $j_date=date("Y-m-d", $j_timestamp);
            $j_day=date("d", $j_timestamp);
            $td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

            if (in_array ("$j_date", $tab_year)) {
                $td_second_class="fermeture";
            }

            $return .= '<td  class="cal-saisie ' . $td_second_class . '">' . $j_day . '</td>';
        }
        for($i; $i<43-$first_jour_mois_rang+1; $i++) {
            if ( (($i==42-$first_jour_mois_rang)&&(!$config->isSamediOuvrable())) || (($i==43-$first_jour_mois_rang)&&(!$config->isDimancheOuvrable()))) {
                $bgcolor=$_SESSION['config']['week_end_bgcolor'];
            } else {
                $bgcolor=$_SESSION['config']['semaine_bgcolor'];
            }
            $return .= '<td class="month-out cal-saisie2">&nbsp;</td>';
        }
        $return .= '</tr></table>';
        return $return;
    }

    //calendrier des fermeture
    public static function affiche_calendrier_fermeture($year, $groupe_id = 0) {

        // on construit le tableau de l'année considérée
        $tab_year=array();
        \hr\Fonctions::get_tableau_jour_fermeture($year, $tab_year,  $groupe_id);
        // navigation
        $onglet = htmlentities(getpost_variable('onglet'), ENT_QUOTES | ENT_HTML401);
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '<div class="btn-group pull-right">';
        $prev_link = "$PHP_SELF?onglet=$onglet&year=". ($year - 1) . "&groupe_id=$groupe_id";
        $return .= '<a href="' . $prev_link . '" class="btn btn-default"><i class="fa fa-chevron-left"></i></a>';
        $currentLink = "$PHP_SELF?onglet=$onglet&year=". date('Y') . "&groupe_id=$groupe_id";
        $return .= '<a href="' . $currentLink . '" class="btn btn-default"><i class="fa fa-home" title="Retourner à l\'année courante"></i></a>';
        $next_link = "$PHP_SELF?onglet=$onglet&year=". ($year + 1) . "&groupe_id=$groupe_id";
        $return .= '<a href="' . $next_link . '" class="btn btn-default"><i class="fa fa-chevron-right"></i></a>';
        $return .= '</div>';
        $return .= '<a href="' . $PHP_SELF . '?onglet=saisie" class="btn btn-success pull-right" style="margin-right:15px">' . _('admin_jours_fermeture_titre') . '</a>';
        $return .= '<h1>Calendrier des fermetures <span class="current-year">' .  $year . '</span></h1>';

        $return .= '<div class="calendar calendar-year">';
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        foreach ($months as $month) {
            $return .= '<div class="month">';
            $return .= '<div class="wrapper">';
            $return .= \hr\Fonctions::affiche_calendrier_fermeture_mois($year, $month, $tab_year);
            $return .= '</div>';
            $return .= '</div>';
        }
        $return .= '</div>';
        return $return;
    }

    //insertion des nouvelles dates de fermeture
    public static function insert_year_fermeture($fermeture_id, $tab_j_ferme, $groupe_id)
    {
        $sql_insert="";
        foreach($tab_j_ferme as $jf_date ) {
            $sql_insert="INSERT INTO conges_jours_fermeture (jf_id, jf_gid, jf_date) VALUES ($fermeture_id, $groupe_id, '$jf_date') ;";
            $result_insert = \includes\SQL::query($sql_insert);
        }
        return TRUE;
    }

    // supprime une fermeture
    public static function delete_year_fermeture($fermeture_id, $groupe_id)
    {

        $sql_delete="DELETE FROM conges_jours_fermeture WHERE jf_id = '$fermeture_id' AND jf_gid= '$groupe_id' ;";
        $result = \includes\SQL::query($sql_delete);
        return TRUE;
    }

    // recup l'id de la derniere fermeture (le max)
    public static function get_last_fermeture_id()
    {
        $req_1="SELECT MAX(jf_id) FROM conges_jours_fermeture ";
        $res_1 = \includes\SQL::query($req_1);
        $row_1 = $res_1->fetch_array();
        if (!$row_1)
            return 0;     // si la table est vide, on renvoit 0
        else
            return $row_1[0];
    }

    // verifie si la periode donnee chevauche une periode de conges d'un des user du groupe ..
    // retourne TRUE si chevauchement et FALSE sinon !
    public static function verif_periode_chevauche_periode_groupe($date_debut, $date_fin, $num_current_periode='', $tab_periode_calcul, $groupe_id)
    {
        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if ($groupe_id==0)  // fermeture pour tous !
            $list_users = get_list_all_users();
        else
            $list_users = get_list_users_du_groupe($groupe_id);

        $tab_users = explode(",", $list_users);

        foreach($tab_users as $current_login) {
            $current_login = trim($current_login);
            // on enleve les quotes qui ont été ajoutées lors de la creation de la liste
            $current_login = trim($current_login, "\'");
            $comment="";
            if (verif_periode_chevauche_periode_user($date_debut, $date_fin, $current_login, $num_current_periode, $tab_periode_calcul, $comment))
                return TRUE;
        }
    }

    public static function commit_annul_fermeture($fermeture_id, $groupe_id)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if ($groupe_id==0) { // fermeture pour tous !
            $list_users = get_list_all_users();
        } else {
            $list_users = get_list_users_du_groupe($groupe_id);
        }

        $tab_users = explode(",", $list_users);

        /***********************************************/
        /** suppression des jours de fermetures   **/
        // on suprimme les dates de cette fermeture dans conges_jours_fermeture
        $result = \hr\Fonctions::delete_year_fermeture($fermeture_id, $groupe_id);


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

            $sql1 = 'UPDATE conges_periode SET p_etat = "'.\includes\SQL::quote($etat).'" WHERE p_num="'.\includes\SQL::quote($sql_num_periode).'" AND p_etat=\'ok\';';
            $ReqLog = \includes\SQL::query($sql1);

            if ($ReqLog && \includes\SQL::getVar('affected_rows')) {
                // mise à jour du solde de jours de conges pour l'utilisateur $current_login
                if ($sql_nb_jours_a_crediter != 0) {
                    $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde + '.\includes\SQL::quote($sql_nb_jours_a_crediter).' WHERE su_login="'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.\includes\SQL::quote($sql_type_abs) ;
                    $ReqLog = \includes\SQL::query($sql1);
                }
            }
        }

        $return .= '<div class="wrapper">';
        if ($result) {
            $return .= '<br>' . _('form_modif_ok') . '<br><br>';
        } else {
            $return .= '<br>' . _('form_modif_not_ok') . ' !<br><br>';
        }

        // on enregistre cette action dan les logs
        if ($groupe_id==0) { // fermeture pour tous !
            $comment_log = "annulation fermeture $fermeture_id (pour tous) " ;
        } else {
            $comment_log = "annulation fermeture $fermeture_id (pour le groupe $groupe_id)" ;
        }
        log_action(0, "", "", $comment_log);

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_ok') . '">';
        $return .= '</form>';
        $return .= '</div>';
        return $return;
    }

    public static function commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // on transforme les formats des dates
        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $date_debut=$tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0];
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $date_fin=$tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0];

        /*****************************/
        // on construit le tableau des users affectés par les fermetures saisies :
        if ($groupe_id==0) { // fermeture pour tous !
            $list_users = get_list_all_users();
        } else {
            $list_users = get_list_users_du_groupe($groupe_id);
        }

        $tab_users = explode(",", $list_users);
        //******************************
        // !!!!
            // type d'absence à modifier ....
        //    $id_type_conges = 1 ; //"cp" : conges payes

        //calcul de l'ID de de la fermeture (en fait l'ID de la saisie de fermeture)
        $new_fermeture_id = \hr\Fonctions::get_last_fermeture_id() + 1;

        /***********************************************/
        /** enregistrement des jours de fermetures   **/
        $tab_fermeture=array();
        for($current_date=$date_debut; $current_date <= $date_fin; $current_date=jour_suivant($current_date)) {
            $tab_fermeture[] = $current_date;
        }

        // on insere les nouvelles dates saisies dans conges_jours_fermeture
        $result = \hr\Fonctions::insert_year_fermeture($new_fermeture_id, $tab_fermeture, $groupe_id);

        $opt_debut='am';
        $opt_fin='pm';

        /*********************************************************/
        /** insersion des jours de fermetures pour chaque user  **/
        foreach($tab_users as $current_login) {
            $current_login = trim($current_login);
            // on enleve les quotes qui ont été ajoutées lors de la creation de la liste
            $current_login = trim($current_login, "\'");

            if (is_active($current_login)) {
                // on compte le nb de jour à enlever au user (par periode et au total)
                // on ne met à jour la table conges_periode
                $nb_jours = 0;
                $comment="" ;

                $nb_jours = compter($current_login, "", $date_debut, $date_fin, $opt_debut, $opt_fin, $comment);

                // on ne met à jour la table conges_periode .
                $commentaire =  _('divers_fermeture') ;
                $etat = "ok" ;
                $num_periode = insert_dans_periode($current_login, $date_debut, $opt_debut, $date_fin, $opt_fin, $nb_jours, $commentaire, $id_type_conges, $etat, $new_fermeture_id) ;

                // mise à jour du solde de jours de conges pour l'utilisateur $current_login
                if ($nb_jours != 0) {
                    soustrait_solde_et_reliquat_user($current_login, "", $nb_jours, $id_type_conges, $date_debut, $opt_debut, $date_fin, $opt_fin);
                }
            }
        }

        // on recharge les jours fermés dans les variables de session
        init_tab_jours_fermeture($_SESSION['userlogin']);

        $return .= '<div class="wrapper">';

        if ($result) {
            $return .= '<br>' . _('form_modif_ok') . '<br><br>';
        } else {
            $return .= '<br>' . _('form_modif_not_ok') . ' !<br><br>';
        }

        $comment_log = "saisie des jours de fermeture de $date_debut a $date_fin" ;
        log_action(0, "", "", $comment_log);
        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_ok') . '">';
        $return .= '</form>';
        $return .= '</div>';
        return $return;
    }

    public static function confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $return .= '<div class="wrapper">';
        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= _('divers_fermeture_du') . '<b>' . $fermeture_date_debut . '</b>' . _('divers_au') . '<b>' . $fermeture_date_fin . '</b>.';
        $return .= '<b>' . _('admin_annul_fermeture_confirm') . '</b>.<br>';
        $return .= '<input type="hidden" name="fermeture_id" value="' . $fermeture_id . '">';
        $return .= '<input type="hidden" name="fermeture_date_debut" value="' . $fermeture_date_debut . '">';
        $return .= '<input type="hidden" name="fermeture_date_fin" value="' . $fermeture_date_fin . '">';
        $return .= '<input type="hidden" name="groupe_id" value="' . $groupe_id . '">';
        $return .= '<input type="hidden" name="choix_action" value="commit_annul_fermeture">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_continuer') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '">' . _('form_cancel') . '</a>';
        $return .= '</form>';
        $return .= '</div>';
        return $return;
    }

    // retourne un tableau des periodes de fermeture (pour un groupe donné (gid=0 pour tout le monde))
    public static function get_tableau_periodes_fermeture(&$tab_periodes_fermeture)
    {
        $req_1="SELECT DISTINCT conges_periode.p_date_deb, conges_periode.p_date_fin, conges_periode.p_fermeture_id, conges_jours_fermeture.jf_gid, conges_groupe.g_groupename FROM conges_periode, conges_jours_fermeture LEFT JOIN conges_groupe ON conges_jours_fermeture.jf_gid=conges_groupe.g_gid WHERE conges_periode.p_fermeture_id = conges_jours_fermeture.jf_id AND conges_periode.p_etat='ok' ORDER BY conges_periode.p_date_deb DESC  ";
        $res_1 = \includes\SQL::query($req_1);

        $num_select = $res_1->num_rows;
        if ($num_select!=0) {
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
    public static function affiche_select_conges_id()
    {
        $tab_conges=recup_tableau_types_conges();
        $tab_conges_except=recup_tableau_types_conges_exceptionnels();
        $return = '';

        foreach($tab_conges as $id => $libelle) {
            if ($libelle == 1) {
                $return .= '<option value="' . $id . '" selected>' . $libelle . '</option>';
            } else {
                $return .= '<option value="' . $id  . '">' . $libelle . '</option>';
            }
        }
        if (count($tab_conges_except)!=0) {
            foreach($tab_conges_except as $id => $libelle) {
                if ($libelle == 1) {
                    $return .= '<option value="' . $id . '" selected>' . $libelle . '</option>';
                } else {
                    $return .= '<option value="' . $id . '">' . $libelle . '</option>';
                }
            }
        }
        return $return;
    }

    //renvoi un tableau des jours de fermeture
    public static function get_tableau_jour_fermeture($year, &$tab_year,  $groupe_id)
    {
        $sql_select = " SELECT jf_date FROM conges_jours_fermeture WHERE DATE_FORMAT(jf_date, '%Y-%m-%d') LIKE '$year%'  ";
        // on recup les fermeture du groupe + les fermetures de tous !
        if ($groupe_id==0)
            $sql_select = $sql_select."AND jf_gid = 0";
        else
            $sql_select = $sql_select."AND  (jf_gid = $groupe_id OR jf_gid =0 ) ";
        $res_select = \includes\SQL::query($sql_select);
        $num_select =$res_select->num_rows;

        if ($num_select!=0) {
            while($result_select = $res_select->fetch_array()) {
                $tab_year[]=$result_select["jf_date"];
            }
        }
    }

    public static function saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
        $date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
        $date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
        $timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

        // on construit le tableau de l'année considérée
        $tab_year=array();
        \hr\Fonctions::get_tableau_jour_fermeture($year, $tab_year,  $groupe_id);

        $return .= '<form id="form-fermeture" class="form-inline" role="form" action="' . $PHP_SELF . '?year=' . $year . '" method="POST">';
        $return .= '<div class="form-group">';
        $return .= '<label for="new_date_debut">' . _('divers_date_debut') . '</label><input type="text" class="form-control date" name="new_date_debut" value="' . $new_date_debut . '">';
        $return .= '</div>';
        $return .= '<div class="form-group">';
        $return .= '<label for="new date_fin">' . _('divers_date_fin') . '</label><input type="text" class="form-control date" name="new_date_fin" value="' . $new_date_fin . '">';
        $return .= '</div>';
        $return .= '<div class="form-group">';
        $return .= '<label for="id_type_conges">' . _('admin_jours_fermeture_affect_type_conges') . '</label>';
        $return .= '<select name="id_type_conges" class="form-control">';
        $return .= \hr\Fonctions::affiche_select_conges_id();
        $return .= '</select>';
        $return .= '</div>';
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="groupe_id" value="' . $groupe_id . '">';
        $return .= '<input type="hidden" name="choix_action" value="commit_new_fermeture">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '</form>';
        return $return;
    }

    public static function saisie_groupe_fermeture()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '<h1>Nouvelle fermeture</h1>';
        $return .= '<a href="' . ROOT_PATH . 'hr/hr_jours_fermeture.php" class="admin-back"><i class="fa fa-arrow-circle-o-left"></i>Retour calendrier des fermetures</a>';


        $return .= '<div class="row">';
        $return .= '<div class="col-md-6">';
        /********************/
        /* Choix Tous       */
        /********************/

        // AFFICHAGE TABLEAU
        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= '<input type="hidden" name="groupe_id" value="0">';
        $return .= '<input type="hidden" name="choix_action" value="saisie_dates">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('admin_jours_fermeture_fermeture_pour_tous') . ' !">';
        $return .= '</form>';
        $return .= '</div>';

        if ($config->canFermetureParGroupe()) {
            /********************/
            /* Choix Groupe     */
            /********************/
            // Récuperation des informations :
            $sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

            // AFFICHAGE TABLEAU
            $return .= '<div class="col-md-6">';
            $return .= '<form action="' . $PHP_SELF . '" class="form-inline" method="POST">';
            $return .= '<div class="form-group" style="margin-right: 10px;">';
            $ReqLog_gr = \includes\SQL::query($sql_gr);
            $return .= '<select class="form-control" name="groupe_id">';
            while ($resultat_gr = $ReqLog_gr->fetch_array()) {
                $sql_gid=$resultat_gr["g_gid"] ;
                $sql_group=$resultat_gr["g_groupename"] ;
                $sql_comment=$resultat_gr["g_comment"] ;

                $return .= '<option value="' . $sql_gid . '">' . $sql_group;
            }
            $return .= '</select>';
            $return .= '<input type="hidden" name="choix_action" value="saisie_dates">';
            $return .= '</div>';
            $return .= '<input class="btn btn-success" type="submit" value="' . _('admin_jours_fermeture_fermeture_par_groupe') . '">';
            $return .= '</form>';
            $return .= '</div>';
        }

        /************************************************/
        // HISTORIQUE DES FERMETURES

        $tab_periodes_fermeture = array();
        \hr\Fonctions::get_tableau_periodes_fermeture($tab_periodes_fermeture);
        if (count($tab_periodes_fermeture)!=0) {
            $return .= '<table class="table">';
            $return .= '<thead>';
            $return .= '<tr>';
            $return .= '<th colspan="2">Fermetures</th>';
            $return .= '</tr>';
            $return .= '</thead>';
            foreach($tab_periodes_fermeture as $tab_periode) {
                $date_affiche_1=eng_date_to_fr($tab_periode['date_deb']);
                $date_affiche_2=eng_date_to_fr($tab_periode['date_fin']);
                $fermeture_id =($tab_periode['fermeture_id']);
                $groupe_id =($tab_periode['groupe_id']);
                $groupe_name =($tab_periode['groupe_name']);

                if ($groupe_id==0) {
                    $groupe_name = 'Tous';
                } else {
                    $groupe_name = $groupe_name;
                }

                $return .= '<tr>';
                $return .= '<td>';
                $return .= _('divers_du') . ' <b>'. $date_affiche_1 . '</b> ' . _('divers_au') . ' <b>' . $date_affiche_2 . '</b>  (id ' . $fermeture_id . ')</b> ' . $groupe_name;
                $return .= '</td>';
                $return .= '<td>';
                $return .= '<a href="' . $PHP_SELF . '?choix_action=annul_fermeture&fermeture_id=' . $fermeture_id . '&groupe_id=' . $groupe_id . '&fermeture_date_debut=' . $date_affiche_1 . '&fermeture_date_fin=' . $date_affiche_2 . '">' . _('admin_annuler_fermeture') . '</a>';
                $return .= '</td>';
                $return .= '</tr>';
            }
            $return .= '</table>';
        }
        $return .= '</div>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de jours de fermeture
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageJoursFermetureModule()
    {
        // verif des droits du user à afficher la page
        verif_droits_user("is_hr");
        $return = '';

        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        // GET / POST
        $choix_action         = getpost_variable('choix_action');
        $year                 = getpost_variable('year', 0);
        $groupe_id            = htmlentities(getpost_variable('groupe_id'), ENT_QUOTES | ENT_HTML401);
        $id_type_conges       = getpost_variable('id_type_conges');
        $new_date_debut       = getpost_variable('new_date_debut'); // valeur par dédaut = aujourd'hui
        $new_date_fin         = getpost_variable('new_date_fin');   // valeur par dédaut = aujourd'hui
        $fermeture_id         = getpost_variable('fermeture_id', 0);
        $fermeture_date_debut = getpost_variable('fermeture_date_debut');
        $fermeture_date_fin   = getpost_variable('fermeture_date_fin');
        $code_erreur          = getpost_variable('code_erreur', 0);

        // si les dates de début ou de fin ne sont pas passé par get/post alors date du jour.
        if ($new_date_debut=="") {
            if ($year==0) {
                $new_date_debut=date("d/m/Y") ;
            } else {
                $new_date_debut=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
            }
        }
        if ($new_date_fin=="") {
            if ($year==0) {
                $new_date_fin=date("d/m/Y") ;
            } else {
                $new_date_fin=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
            }
        }

        if ($year ==0) {
            $year= date("Y");
        }

        /*************************************/

        /***********************************/
        /*  VERIF DES DATES RECUES   */
        $tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
        $timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
        $date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
        $tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
        $timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
        $date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
        $timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

        /*********************************/
        /*   COMPOSITION DES ONGLETS...  */
        /*********************************/

        $onglet = htmlentities(getpost_variable('onglet'), ENT_QUOTES | ENT_HTML401);

        if (!$onglet) {
            $onglet = 'calendar';
        }

        //initialisation de l'action par défaut
        if ($choix_action=="") {
            $choix_action="saisie_groupe";
        }

        /***********************************/
        // AFFICHAGE DE LA PAGE
        header_menu('', 'Libertempo : '._('divers_fermeture'));

        $return .= '<div class="main-content">';

        // vérifie si les jours fériés sont saisie pour l'année en cours
        if ( (verif_jours_feries_saisis($date_debut_yyyy_mm_dd)==FALSE) && (verif_jours_feries_saisis($date_fin_yyyy_mm_dd)==FALSE) ) {
                $code_erreur=1 ;  // code erreur : jour feriés non saisis
                $onglet="calendar";
        }

        //initialisation de l'action demandée : saisie_dates, commit_new_fermeture pour enregistrer une fermeture, annul_fermeture pour confirmer une annulation, commit_annul_fermeture pour annuler une fermeture

        //en cas de confirmation d'une fermeture :
        if ($choix_action == "commit_new_fermeture") {
            // on verifie que $new_date_debut est anterieure a $new_date_fin
            if ($timestamp_date_debut > $timestamp_date_fin) {
                $code_erreur=2 ;  // code erreur : $new_date_debut est posterieure a $new_date_fin
                $choix_action="saisie_dates";
            }
            // on verifie que ce ne sont pas des dates passées
            elseif ($timestamp_date_debut < $timestamp_today) {
                $code_erreur=3 ;  // code erreur : saisie de date passée
                $choix_action="saisie_dates";
            }
            // on ne verifie QUE si date_debut ou date_fin sont !=  d'aujourd'hui
            // (car aujourd'hui est la valeur par dédaut des dates, et on ne peut saisir aujourd'hui puisque c'est fermé !)
            elseif ( ($timestamp_date_debut==$timestamp_today) || ($timestamp_date_fin==$timestamp_today) ) {
                $code_erreur=4 ;  // code erreur : saisie de aujourd'hui
                $choix_action="saisie_dates";
            } else {
                // fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
                $tab_periode_calcul = make_tab_demi_jours_periode($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, "am", "pm");
                // on verifie si la periode saisie ne chevauche pas une periode existante
                if (\hr\Fonctions::verif_periode_chevauche_periode_groupe($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, '', $tab_periode_calcul, $groupe_id) ) {
                    $code_erreur=5 ;  // code erreur : fermeture chevauche une periode deja saisie
                    $choix_action="saisie_dates";
                }
            }
        }

        if ($onglet == 'calendar') {
            // les jours fériés de l'annee de la periode saisie ne sont pas enregistrés
            if ($code_erreur==1) {
                $return .= '<div class="alert alert-danger">' . _('admin_jours_fermeture_annee_non_saisie') . '</div>';
            }

                   /************************************************/
            // CALENDRIER DES FERMETURES
            $return .= \hr\Fonctions::affiche_calendrier_fermeture($year);
        } elseif ($choix_action=="saisie_dates") {
            if ($groupe_id=="") {
                $groupe_id=0;
            }

            // $new_date_debut est anterieure a $new_date_fin
            if ($code_erreur==2) {
                $return .= '<div class="alert alert-danger">' . _('admin_jours_fermeture_dates_incompatibles') . '</div>';
            }

            // ce ne sont des dates passées
            if ($code_erreur==3) {
                $return .= '<div class="alert alert-danger">' . _('admin_jours_fermeture_date_passee_error') . '</div>';
            }

            // fermeture le jour même impossible
            if ($code_erreur==4) {
                $return .= '<div class="alert alert-danger">' . _('admin_jours_fermeture_fermeture_aujourd_hui') . '</div>';
            }

            // la periode saisie chevauche une periode existante
            if ($code_erreur==5) {
                $return .= '<div class="alert alert-danger">' . _('admin_jours_fermeture_chevauche_periode') . '</div>';
            }

            $return .= '<div class="wrapper">';
            if ($onglet == 'saisie') {
                $return .= \hr\Fonctions::saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur);
            }
        } elseif ($choix_action=="saisie_groupe") {
            $return .= '<div class="wrapper">';
            $return .= \hr\Fonctions::saisie_groupe_fermeture();
            $return .= '</div>';
        } elseif ($choix_action=="commit_new_fermeture") {
            $return .= $title;
            $return .= \hr\Fonctions::commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges);
        } elseif ($choix_action=="annul_fermeture") {
            $return .= $title;
            $return .= \hr\Fonctions::confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin);
        } elseif ($choix_action=="commit_annul_fermeture") {
            $return .= $title;
            $return .= \hr\Fonctions::commit_annul_fermeture($fermeture_id, $groupe_id);
        }
        $return .= '</div>';

        return $return;
    }
}
