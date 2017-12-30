<?php
namespace config;

/**
 * Regroupement des fonctions liées à la configuration
 *
 */
class Fonctions
{
    public static function commit_vider_table_logs()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $sql_delete="TRUNCATE TABLE conges_logs ";
        $ReqLog_delete = \includes\SQL::query($sql_delete);

        // ecriture de cette action dans les logs
        $comment_log = "effacement des logs de php_conges ";
        log_action(0, "", "", $comment_log);

        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';
        redirect( ROOT_PATH . 'config/index.php?onglet=logs');
    }

    public static function confirmer_vider_table_logs()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $return .= '<center>';
        $return .= '<br><h2>' . _('confirm_vider_logs') . '</h2><br>';
        $return .= '<form action="' . $PHP_SELF . '?onglet=logs" method="POST">';
        $return .= '<input type="hidden" name="action" value="commit_suppr_logs">';
        $return .= '<input type="submit" value="' . _('form_delete_logs') . '">';
        $return .= '</form>';
        $return .= '<form action="' . $PHP_SELF . '?onglet=logs" method="POST">';
        $return .= '<input type="submit" value="' . _('form_cancel') . '"">';
        $return .= '</form></center>';
        return $return;
    }

    public static function affichage($login_par)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        //requête qui récupère les logs
        $sql1 = "SELECT log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date FROM conges_logs ";
        if($login_par!="") {
            $sql1 = $sql1." WHERE log_user_login_par = '$login_par' ";
        }
        $sql1 = $sql1." ORDER BY log_date";

        $ReqLog1 = \includes\SQL::query($sql1);

        if($ReqLog1->num_rows !=0) {
            $return .= '<br>';
            $table = new \App\Libraries\Structure\Table();
            $table->addClasses([
                'table',
                'table-hover',
                'table-striped',
                'table-condensed'
            ]);

            $childTable = '<tr><td class="histo" colspan="5">' . _('voir_les_logs_par') . '</td>';
            if($login_par!="") {
                $childTable .= '<tr><td class="histo" colspan="5">' . _('voir_tous_les_logs') . '<a href="' . $PHP_SELF . '?onglet=logs">' . _('voir_tous_les_logs') . '</a></td>';
            }
            $childTable .= '<tr><td class="histo" colspan="5">&nbsp;</td>';

            // titres
            $childTable .= '<tr>';
            $childTable .= '<td>' . _('divers_date_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_fait_par_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_pour_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_comment_maj_1') . '</td>';
            $childTable .= '<td>' . _('divers_etat_maj_1') . '</td>';
            $childTable .= '</tr>';

            // affichage des logs
            while ($data = $ReqLog1->fetch_array()) {
                $log_login_par = $data['log_user_login_par'];
                $log_login_pour = $data['log_user_login_pour'];
                $log_log_etat = $data['log_etat'];
                $log_log_comment = $data['log_comment'];
                $log_log_date = $data['log_date'];

                $childTable .= '<tr>';
                $childTable .= '<td>' . $log_log_date . '</td>';
                $childTable .= '<td><a href="' . $PHP_SELF . '?onglet=logs&login_par=' . $log_login_par . '"><b>' . $log_login_par . '</b></a></td>';
                $childTable .= '<td>' . $log_login_pour . '</td>';
                $childTable .= '<td>' . $log_log_comment . '</td>';
                $childTable .= '<td>' . $log_log_etat . '</td>';
                $childTable .= '</tr>';
            }

            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '<form action="' . $PHP_SELF . '?onglet=logs" method="POST">';

            // affichage du bouton pour vider les logs
            $return .= '<input type="hidden" name="action" value="suppr_logs">';
            $return .= '<input class="btn btn-danger" type="submit"  value="' . _('form_delete_logs') . '"><br>';
            $return .= '</form>';
        } else {
            $return .= _('no_logs_in_db') . '<br>';
        }
        return $return;
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @return void
     * @access public
     * @static
     */
    public static function logModule()
    {
        // verif des droits du user à afficher la page
        verif_droits_user("is_admin");
        $return = '<h1>Journaux</h1>';

        /*** initialisation des variables ***/
        /************************************/

        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $action         = htmlentities(getpost_variable('action', ""), ENT_QUOTES | ENT_HTML401);
        $login_par      = htmlentities(getpost_variable('login_par', ""), ENT_QUOTES | ENT_HTML401);

        /*************************************/

        // header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);


        if($action=="suppr_logs") {
            $return .= \config\Fonctions::confirmer_vider_table_logs();
        } elseif($action=="commit_suppr_logs") {
            \config\Fonctions::commit_vider_table_logs();
        } else {
            $return .= \config\Fonctions::affichage($login_par);
        }
        // bottom();
        return $return;
    }

    public static function commit_modif($tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=mail";


        // update de la table
        foreach($tab_new_values as $nom_mail => $tab_mail) {
            $subject = htmlspecialchars(addslashes($tab_mail['subject']));
            $body = htmlspecialchars(addslashes($tab_mail['body']));
            $req_update='UPDATE conges_mail SET mail_subject=\''.$subject.'\', mail_body=\''.$body.'\' WHERE mail_nom="'. \includes\SQL::quote($nom_mail).'" ';
            $result1 = \includes\SQL::query($req_update);
        }
        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';

        $comment_log = "configuration des mails d\'alerte";
        log_action(0, "", "", $comment_log);
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';

        return $return;
    }

    public static function test_config($tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=mail";

        // update de la table
        $mail_array             = find_email_adress_for_user($_SESSION['userlogin']);
        $mail_sender_name       = $mail_array[0];
        $mail_sender_addr       = $mail_array[1];
        constuct_and_send_mail("valid_conges", "Test email", $mail_sender_addr, $mail_sender_name, $mail_sender_addr, "test");
        //  echo "<p>Mail sent</p>"; exit(0);

        $return .= '<span class="messages">' . _('Mail_test_ok') . '</span><br>';

        $comment_log = "test d\'envoi mail d\'alerte";
        log_action(0, "", "", $comment_log);
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';

        return $return;
    }

    public static function affichage_config_mail($tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=mail";

        /**************************************/
        // affichage du titre
        $return .= '<div class="alert alert-info">' . _('config_mail_alerte_config') . '</div>';
        /**************************************/

        // affichage de la liste des type d'absence existants

        //requête qui récupère les informations de la table conges_type_absence
        $sql1 = "SELECT * FROM conges_mail ";
        $ReqLog1 = \includes\SQL::query($sql1);

        $return .= '<form method="POST" action="' . $URL . '">';
        $return .= '<input type="hidden" name="action" value="test" />';
        $return .= '<input class="btn btn-success" type="submit"  value="' . _('test_mail_config') . '"><br>';
        $return .= _('test_mail_comment');
        $return .= '</form>';

        $return .= '<form action="' . $URL . '" method="POST">';
        while ($data = $ReqLog1->fetch_array()) {
            $mail_nom = stripslashes($data['mail_nom']);
            $mail_subject = stripslashes($data['mail_subject']);
            $mail_body = stripslashes($data['mail_body']);

            $legend =$mail_nom ;
            $key = $mail_nom."_comment";
            $comment =  _($key)  ;

            $return .= '<br>';
            $table = new \App\Libraries\Structure\Table();
            $table->addChild('<tr><td><fieldset class="cal_saisie"><legend class="boxlogin">' . $legend . '</legend><i>' . $comment . '</i><br><br>');
            $subTable = new \App\Libraries\Structure\Table();
            $table->addChild($subTable);
            $childSubTable = '<tr>';
            $childSubTable .= '<td class="config" valign="top"><b>' .  _('config_mail_subject') . '</b></td>';
            $childSubTable .= '<td class="config"><input class="form-control" type="text" size="80" name="tab_new_values[' . $mail_nom . '][subject]" value="' . $mail_subject . '"></td>';
            $childSubTable .= '</tr>';
            $childSubTable .= '<tr>';
            $childSubTable .= '<td class="config" valign="top"><b>' . _('config_mail_body') . '</b></td>';
            $childSubTable .= '<td class="config"><textarea class="form-control" rows="6" cols="80" name="tab_new_values[' . $mail_nom . '][body]" value="' . $mail_body . '">' . $mail_body . '</textarea></td>';
            $childSubTable .= '</tr><tr>';
            $childSubTable .= '<td class="config">&nbsp;</td>';
            $childSubTable .= '<td class="config">';
            $childSubTable .= '<i>' . _('mail_remplace_url_accueil_comment') . '<br>';
            $childSubTable .= _('mail_remplace_sender_name_comment') . '<br>';
            $childSubTable .= _('mail_remplace_destination_name_comment') . '<br>';
            $childSubTable .= _('mail_remplace_nb_jours') . '<br>';
            $childSubTable .= _('mail_remplace_date_debut') . '<br>';
            $childSubTable .= _('mail_remplace_date_fin') . '<br>';
            $childSubTable .= _('mail_remplace_commentaire') . '<br>';
            $childSubTable .= _('mail_remplace_type_absence') . '<br>';
            $childSubTable .= _('mail_remplace_retour_ligne_comment') . '</i>';
            $childSubTable .= '</td></tr>';
            $subTable->addChild($childSubTable);
            $table->addChild('</fieldset></td></tr>');

            ob_start();
            $table->render();
            $return .= ob_get_clean();
        }

        $return .= '<input type="hidden" name="action" value="modif">';
        $return .= '<hr/>';
        $return .= '<input class="btn btn-success" type="submit"  value="' . _('form_save_modif') . '"><br>';
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @return void
     * @access public
     * @static
     */
    public static function mailModule()
    {
        // verif des droits du user à afficher la page
        verif_droits_user("is_admin");
        $return = '<h1>Mails</h1>';


        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $action = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');
        /*********************************/

        if($action=="modif") {
            $return .= \config\Fonctions::commit_modif($tab_new_values);
        }
        if($action=="test") {
            $return .= \config\Fonctions::test_config($tab_new_values);
        }

        $return .= \config\Fonctions::affichage_config_mail($tab_new_values);

        return $return;
    }

    // recup l'id de la derniere absence (le max puisque c'est un auto incrément)
    public static function get_last_absence_id()
    {
        $req_1="SELECT MAX(ta_id) FROM conges_type_absence ";
        $res_1 = \includes\SQL::query($req_1);
        $row_1 = $res_1->fetch_row();
        if(!$row_1) {
            return 0;     // si la table est vide, on renvoit 0
        } else {
            return $row_1[0];
        }
    }

    //
    // cree un tableau à partir des valeurs du enum(...) d'un champ mysql (cf structure des tables)
    //    $table         = nom de la table sql
    //    $column        = nom du champ sql
    public static function get_tab_from_mysql_enum_field($table, $column)
    {

        $tab=array();
        $req_enum = "DESCRIBE $table $column";
        $res_enum = \includes\SQL::query($req_enum);

        while ($row_enum = $res_enum->fetch_array()) {
            $sql_type=$row_enum['Type'];
            // exemple : enum('autre','labo','fonction','personne','web', ....
            $liste_enum = strstr($sql_type, '(');
            $liste_enum = substr($liste_enum, 1);    // on vire le premier caractere
            $liste_enum = substr($liste_enum, 0, strlen($liste_enum)-1);    // on vire le dernier caractere
            $option = strtok($liste_enum,"','");
            while ($option) {
                $tab[]=$option;
                $option = strtok("','");
            }
        }

        return $tab;
    }

    public static function commit_ajout(&$tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=type_absence";
        $tab_new_values['libelle'] = htmlentities($tab_new_values['libelle'], ENT_QUOTES | ENT_HTML401);
        $tab_new_values['short_libelle'] = htmlentities($tab_new_values['short_libelle']);
        $tab_new_values['type'] = htmlentities($tab_new_values['type'], ENT_QUOTES | ENT_HTML401);

        // verif de la saisie
        $erreur=FALSE ;
        // verif si pas de " ' , . ; % ?
        if( (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['libelle'])) || (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['short_libelle'])) ) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('config_abs_bad_caracteres') . ' " \' , . ; % ? <br>';
            $erreur=TRUE;
        }
        // verif si les champs sont vides
        if( (strlen($tab_new_values['libelle'])==0) || (strlen($tab_new_values['short_libelle'])==0) || (strlen($tab_new_values['type'])==0) ) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('config_abs_champs_vides') . '<br>';
            $erreur=TRUE;
        }


        if($erreur) {
            $return .= '<br>';
            $return .= '<form action="' . $URL . '" method="POST">';
            $return .= '<input type="hidden" name="id_to_update" value="' . $id_to_update . '">';
            $return .= '<input type="hidden" name="tab_new_values[libelle]" value="' . $tab_new_values['libelle']. '">';
            $return .= '<input type="hidden" name="tab_new_values[short_libelle]" value="' . $tab_new_values['short_libelle'] . '">';
            $return .= '<input type="hidden" name="tab_new_values[type]" value="' . $tab_new_values['type'] . '">';
            $return .= '<input type="submit" value="' . _('form_redo') . '" >';
            $return .= '</form>';
            $return .= '<br><br>';
        } else {
            // ajout dans la table conges_type_absence
            $req_insert1="INSERT INTO conges_type_absence (ta_libelle, ta_short_libelle, ta_type) " .
                "VALUES ('".$tab_new_values['libelle']."', '".$tab_new_values['short_libelle']."', '".$tab_new_values['type']."') ";
            $result1 = \includes\SQL::query($req_insert1);

            // on recup l'id de l'absence qu'on vient de créer
            $new_abs_id = \config\Fonctions::get_last_absence_id();

            if($new_abs_id!=0) {
                // ajout dans la table conges_solde_user (pour chaque user !!)(si c'est un conges, pas si c'est une absence)
                if( ($tab_new_values['type']=="conges") || ($tab_new_values['type']=="conges_exceptionnels") ) {
                    // recup de users :
                    $sql_users="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' " ;

                    $ReqLog_users = \includes\SQL::query($sql_users);

                    while ($resultat1 = $ReqLog_users->fetch_array()) {
                        $current_login=$resultat1["u_login"];

                        $req_insert2="INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) " .
                            "VALUES ('$current_login', $new_abs_id, 0, 0, 0) ";
                        $result2 = \includes\SQL::query($req_insert2);
                    }
                }
                $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';
            }

            $comment_log = "config : ajout_type_absence : ".$tab_new_values['libelle']."  (".$tab_new_values['short_libelle'].") (type : ".$tab_new_values['type'].") ";
            log_action(0, "", "", $comment_log);
            $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';
        }
        return $return;
    }

    public static function commit_suppr($id_to_update)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=type_absence";

        // delete dans la table conges_type_absence
        $req_delete1='DELETE FROM conges_type_absence WHERE ta_id='. \includes\SQL::quote($id_to_update);
        $result1 = \includes\SQL::query($req_delete1);

        // delete dans la table conges_solde_user
        $req_delete2='DELETE FROM conges_solde_user WHERE su_abs_id='.\includes\SQL::quote($id_to_update);
        $result2 = \includes\SQL::query($req_delete2);

        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';

        $comment_log = "config : supprime_type_absence ($id_to_update) ";
        log_action(0, "", "", $comment_log);
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';

        return $return;
    }

    public static function supprimer($id_to_update)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=type_absence";


        // verif si pas de periode de ce type de conges !!!
        //requête qui récupère les informations de la table conges_periode
        $sql1 = 'SELECT p_num FROM conges_periode WHERE p_type="'. \includes\SQL::quote($id_to_update).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        $count= ($ReqLog1->num_rows) ;

        if( $count!=0 ) {
            $return .= '<center>';
            $return .= '<br>' . _('config_abs_suppr_impossible') . '<br>' . _('config_abs_already_used') . '<br>';

            $return .= '<br>';
            $return .= '<form action="' . $URL . '" method="POST">';
            $return .= '<input type="submit" value="' . _('form_redo') . '" >';
            $return .= '</form>';
            $return .= '<br><br>';
            $return .= '</center>';
        } else {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_type_abs = recup_tableau_tout_types_abs();

            $return .= '<center>';
            $return .= '<br>';
            $return .= _('config_abs_confirm_suppr_of') . '<b>' . $tab_type_abs[$id_to_update]['libelle'] . '</b>';
            $return .= '<br>';
            $return .= '<form action="' . $URL . '" method="POST">';
            $return .= '<input type="hidden" name="action" value="commit_suppr">';
            $return .= '<input type="hidden" name="id_to_update" value="' . $id_to_update . '">';
            $return .= '<input type="submit"  value="' . _('form_supprim') . '">';
            $return .= '</form>';

            $return .= '<br>';
            $return .= '<form action="' . $URL . '" method="POST">';
            $return .= '<input type="submit" value="' . _('form_annul') . '" >';
            $return .= '</form>';
            $return .= '<br><br>';
            $return .= '</center>';
        }
        return $return;
    }

    public static function commit_modif_absence(&$tab_new_values, $id_to_update)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF";


        // verif de la saisie
        $erreur=FALSE ;
        // verif si pas de " ' , . ; % ?
        if( (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['libelle'])) || (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['short_libelle'])) ) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('config_abs_bad_caracteres') . ' " \' , . ; % ? <br>';
            $erreur=TRUE;
        }
        // verif si les champs sont vides
        if( (strlen($tab_new_values['libelle'])==0) || (strlen($tab_new_values['short_libelle'])==0) ) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('config_abs_champs_vides') . '<br>';
            $erreur=TRUE;
        }

        if($erreur) {
            $return .= '<br>';
            $return .= '<form action="' . $PHP_SELF . '?onglet=type_absence" method="POST">';
            $return .= '<input type="hidden" name="action" value="modif">';
            $return .= '<input type="hidden" name="id_to_update" value="' . $id_to_update .'">';
            $return .= '<input type="hidden" name="tab_new_values[libelle]" value="' . $tab_new_values['libelle'] . '">';
            $return .= '<input type="hidden" name="tab_new_values[short_libelle]" value="' . $tab_new_values['short_libelle'] . '">';
            $return .= '<input type="submit" value="' . _('form_redo') . '" >';
            $return .= '</form>';
            $return .= '<br><br>';
        } else {
            // update de la table
            $req_update='UPDATE conges_type_absence SET ta_libelle=\''.$tab_new_values['libelle'].'\', ta_short_libelle=\''.$tab_new_values['short_libelle'].'\' WHERE ta_id="'. \includes\SQL::quote($id_to_update).'" ';
            $result1 = \includes\SQL::query($req_update);

            $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';

            $comment_log = "config : modif_type_absence ($id_to_update): ".$tab_new_values['libelle']."  (".$tab_new_values['short_libelle'].") ";
            log_action(0, "", "", $comment_log);
            $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';
        }
        return $return;
    }

    public static function modifier(&$tab_new_values, $id_to_update)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=type_absence";

        /**************************************/
        // affichage du titre
        $return .= '<br><center><H1>' . _('config_abs_titre') . '</H1></center>';
        $return .= '<br>';

        // recup des infos du type de conges / absences
        $sql_cong='SELECT ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence WHERE ta_id = '. \includes\SQL::quote($id_to_update);

        $ReqLog_cong = \includes\SQL::query($sql_cong);

        if($resultat_cong = $ReqLog_cong->fetch_array()) {
            $sql_type=$resultat_cong['ta_type'];
            $sql_libelle= $resultat_cong['ta_libelle'];
            $sql_short_libelle= $resultat_cong['ta_short_libelle'];
        }

        // mise en place du formulaire
        $return .= '<form action="' . $URL . '" method="POST">';

        $text_libelle ="<input class=\"form-control\" type=\"text\" name=\"tab_new_values[libelle]\" size=\"20\" maxlength=\"20\" value=\"$sql_libelle\" >";
        $text_short_libelle ="<input class=\"form-control\" type=\"text\" name=\"tab_new_values[short_libelle]\" size=\"3\" maxlength=\"3\" value=\"$sql_short_libelle\" >";

        // affichage
        $table = new \App\Libraries\Structure\Table();
        $table->addClass('table');
        $table->addAttribute('cellpadding', 2);
        $childTable = '<tr>';
        $childTable .= '<td><b><u>' . _('config_abs_libelle') . '</b></u></td>';
        $childTable .= '<td><b><u>' . _('config_abs_libelle_short') . '</b></u></td>';
        $childTable .= '<td>' . _('divers_type') . '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr><td><b>' . $sql_libelle . '</b></td><td>' . $sql_short_libelle . '</td><td>' . $sql_type . '</td></tr>';
        $childTable .= '<tr><td><b>' . $text_libelle . '</b></td><td>' . $text_short_libelle . '</td><td></td></tr>';
        $table->addChild($childTable);

        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';
        $return .= '<input type="hidden" name="action" value="commit_modif">';
        $return .= '<input type="hidden" name="id_to_update" value="' . $id_to_update . '">';
        $return .= '<input type="submit" value="' . _('form_modif') . '">';
        $return .= '</form>';

        $return .= '<br>';
        $return .= '<form action="' . $URL . '" method="POST">';
        $return .= '<input type="submit" value="' . _('form_annul') . '">';
        $return .= '</form>';
        $return .= '<br><br>';
        return $return;
    }

    public static function affichage_absence($tab_new_values)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF?onglet=type_absence";

        /**************************************/
        // affichage du titre
        $return .= '<h1>' . _('config_abs_titre') . '</h1>';
        /**************************************/

        // affichage de la liste des type d'absence existants

        $tab_enum = \config\Fonctions::get_tab_from_mysql_enum_field("conges_type_absence", "ta_type");

        foreach($tab_enum as $ta_type) {
            if( ($ta_type=="conges_exceptionnels") &&  (!$config->isCongesExceptionnelsActive())) {
            } else {
                $divers_maj_1 = 'divers_' . $ta_type . '_maj_1';
                $config_abs_comment = 'config_abs_comment_' . $ta_type;

                $legend= _($divers_maj_1)  ;
                $comment= _($config_abs_comment)  ;

                $return .= '<h2>' . $legend . '</h2>';
                $return .= '<p>' . $comment . '</p>';

                //requête qui récupère les informations de la table conges_type_absence
                $sql1 = 'SELECT * FROM conges_type_absence WHERE ta_type = "'. \includes\SQL::quote($ta_type).'"';
                $ReqLog1 = \includes\SQL::query($sql1);

                if($ReqLog1->num_rows !=0) {
                    $table = new \App\Libraries\Structure\Table();
                    $table->addClasses([
                        'table',
                        'table-hover',
                        'table-responsive',
                        'table-condensed',
                        'table-striped'
                    ]);
                    $childTable = '<tr>';
                    $childTable .= '<th>' . _('config_abs_libelle') . '</th>';
                    $childTable .= '<th>' . _('config_abs_libelle_short') . '</th>';
                    $childTable .= '<th></th>';
                    $childTable .= '</tr>';

                    while ($data = $ReqLog1->fetch_array()) {
                        $ta_id = $data['ta_id'];
                        $ta_libelle = $data['ta_libelle'];
                        $ta_short_libelle = $data['ta_short_libelle'];

                        $text_modif="<a href=\"$PHP_SELF?action=modif&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>";
                        $text_suppr="<a href=\"$PHP_SELF?action=suppr&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>";

                        $childTable .= '<tr><td><strong>' . $ta_libelle . '</strong></td><td>' . $ta_short_libelle . '</td><td class="action">' . $text_modif . '&nbsp;' . $text_suppr . '</td></tr>';
                    }
                    $table->addChild($childTable);
                    ob_start();
                    $table->render();
                    $return .= ob_get_clean();
                    $return .= '<hr/>';
                }
            }
        }

        /**************************************/
        // saisie de nouveaux type d'absence
        $return .= '<h2>' . _('config_abs_add_type_abs') . '</h2>';
        $return .= '<p>' . _('config_abs_add_type_abs_comment') . '</p>';
        $return .= '<form action="' . $URL . '" method="POST">';
        $tableAjout = new \App\Libraries\Structure\Table();
        $tableAjout->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped'
        ]);

        $childTableAjout = '<tr>';
        $childTableAjout .= '<th>' . _('config_abs_libelle') . '</th>';
        $childTableAjout .= '<th>' . _('config_abs_libelle_short') . '</th>';
        $childTableAjout .= '<th>' . _('divers_type') . '</th>';
        $childTableAjout .= '</tr>';
        $childTableAjout .= '<tr>';
        $new_libelle = ( isset($tab_new_values['libelle']) ? $tab_new_values['libelle'] : "" );
        $new_short_libelle = ( isset($tab_new_values['short_libelle']) ? $tab_new_values['short_libelle'] : "" ) ;
        $new_type = ( isset($tab_new_values['type']) ? $tab_new_values['type'] : "" ) ;
        $childTableAjout .= '<td><input class="form-control" type="text" name="tab_new_values[libelle]" size="20" maxlength="20" value="' . $new_libelle . '"></td>';
        $childTableAjout .= '<td><input class="form-control" type="text" name="tab_new_values[short_libelle]" size="3" maxlength="3" value="' . $new_short_libelle . '" ></td>';
        $childTableAjout .= '<td>';

        $childTableAjout .= '<select class="form-control" name=tab_new_values[type]>';

        foreach($tab_enum as $option) {
            if( ($option=="conges_exceptionnels") &&  (!$config->isCongesExceptionnelsActive())) {
            } else {
                if($option==$new_type) {
                    $childTableAjout .= '<option selected>' . $option . '</option>';
                } else {
                    $childTableAjout .= '<option>' . $option . '</option>';
                }
            }
        }

        $childTableAjout .= '</select>';
        $childTableAjout .= '</td></tr>';
        $tableAjout->addChild($childTableAjout);
        ob_start();
        $tableAjout->render();
        $return .= ob_get_clean();
        $return .= '<input type="hidden" name="action" value="new">';
        $return .= '<hr/>';
        $return .= '<input type="submit" class="btn btn-success" value="' . _('form_ajout') . '"><br>';
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de configuration des types de congés
     *
     *
     * @return void
     * @access public
     * @static
     */
    public static function typeAbsenceModule()
    {
        $return = '';

        if (file_exists(CONFIG_PATH .'config_ldap.php')) {
            include_once CONFIG_PATH .'config_ldap.php';
        }

        // include_once ROOT_PATH .'fonctions_conges.php' ;
        // include_once INCLUDE_PATH .'fonction.php';
        if(!isset($_SESSION['config'])) {
            $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
        }
        include_once INCLUDE_PATH .'session.php';

        // verif des droits du user à afficher la page
        verif_droits_user( "is_admin");



        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $action         = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');
        $id_to_update   = htmlentities(getpost_variable('id_to_update'), ENT_QUOTES | ENT_HTML401);

        /*********************************/

        if($action=="new") {
            $return .= \config\Fonctions::commit_ajout($tab_new_values);
        } elseif($action=="modif") {
            $return .= \config\Fonctions::modifier($tab_new_values, $id_to_update);
        } elseif($action=="commit_modif") {
            $return .= \config\Fonctions::commit_modif_absence($tab_new_values, $id_to_update);
        } elseif($action=="suppr") {
            $return .= \config\Fonctions::supprimer($id_to_update);
        } elseif($action=="commit_suppr") {
            $return .= \config\Fonctions::commit_suppr($id_to_update);
        } else {
            $return .= \config\Fonctions::affichage_absence($tab_new_values);
        }

        return $return;
    }

    public static function commit_saisie(&$tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF";

        $timeout=2 ;  // temps d'attente pour rafraichir l'écran après l'update !

        foreach($tab_new_values as $key => $value ) {
            $value = htmlentities($value, ENT_QUOTES | ENT_HTML401);
            /* Contrôle de cohérence entre config. */
            if ('user_ch_passwd' === $key && 'dbconges' !== $tab_new_values['how_to_connect_user'] ) {
                $value = 'FALSE';
            }

            // CONTROLE gestion_conges_exceptionnels
            // si désactivation les conges exceptionnels, on verif s'il y a des conges exceptionnels enregistres ! si oui : changement impossible !
            if(($key=="gestion_conges_exceptionnels") && ($value=="FALSE") ) {
                $sql_abs="SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type='conges_exceptionnels' ";
                $ReqLog_abs = \includes\SQL::query($sql_abs);

                if($ReqLog_abs->num_rows !=0) {
                    $return .= '<b>' . _('config_abs_desactive_cong_excep_impossible') . '</b><br>';
                    $value = "TRUE" ;
                    $timeout=5 ;
                }
            }

            // CONTROLE jour_mois_limite_reliquats
            // si modif de jour_mois_limite_reliquats, on verifie le format ( 0 ou jj-mm) , sinon : changement impossible !
            if( ($key=="jour_mois_limite_reliquats") && ($value!= "0") ) {
                $t=explode("-", $value);
                if(checkdate($t[1], $t[0], date("Y"))==FALSE) {
                    $return .= '<b>' . _('config_jour_mois_limite_reliquats_modif_impossible') . '</b><br>';
                    $sql_date="SELECT conf_valeur FROM conges_config WHERE conf_nom='jour_mois_limite_reliquats' ";
                    $ReqLog_date = \includes\SQL::query($sql_date);
                    $data = $ReqLog_date->fetch_row();
                    $value = $data[0] ;
                    $timeout=5 ;
                }
            }

            if(preg_match("/_installed$/",$key) && ($value=="1")) {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                install_plugin($plugin);
            } elseif(preg_match("/_installed$/",$key) && ($value=="0")) {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                uninstall_plugin($plugin);
            }
            if(preg_match("/_activated$/",$key) && ($value=="1")) {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                activate_plugin($plugin);
            } elseif(preg_match("/_activated$/",$key) && ($value=="0")) {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                disable_plugin($plugin);
            }

            // Mise à jour
            $sql2 = 'UPDATE conges_config SET conf_valeur = \''.addslashes($value).'\' WHERE conf_nom ="'. \includes\SQL::quote($key).'" ';
            $ReqLog2 = \includes\SQL::query($sql2);
        }

        $_SESSION['config']=init_config_tab();      // on re-initialise le tableau des variables de config

        // enregistrement dans les logs
        $comment_log = "nouvelle configuration de php_conges ";
        log_action(0, "", "", $comment_log);

        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="' . $timeout . '; URL=' . $URL . '">';

        return $return;
    }

    public static function affichage_configuration()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // affichage de la liste des variables

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $return .= '<input type="hidden" name="action" value="commit">';

        //requête qui récupère les informations de config
        $sql1 = "SELECT * FROM conges_config ORDER BY conf_groupe ASC";
        $ReqLog1 = \includes\SQL::query($sql1);

        $old_groupe="";
        $config = [];
        while ($data = $ReqLog1->fetch_array()) {
            $config[$data['conf_groupe']][] = $data;
        }

        foreach ($config as $groupName => $group) {
            $return .= '<br>';
            $table = new \App\Libraries\Structure\Table();
            $table->addAttribute('width', '100%');
            $childTable = '<tr><td>';
            $childTable .= '<fieldset class="cal_saisie '. $groupName . '">';
            $childTable .= '<legend class="boxlogin">' . _($groupName) . '</legend>';

            foreach ($group as $data) {
                $conf_nom = $data['conf_nom'];
                $conf_valeur = $data['conf_valeur'];
                $conf_groupe = $data['conf_groupe'];
                $conf_type = strtolower($data['conf_type']);
                $conf_commentaire = strtolower($data['conf_commentaire']);

                if($conf_nom=="lang") {
                    $childTable .= '<b>Langue installée &nbsp;&nbsp;=&nbsp;&nbsp;' . $conf_valeur . '</b><br>';
                } else {
                    // affichage commentaire
                    $childTable .= '<br><i>' . _($conf_commentaire) . '</i><br>';

                    // affichage saisie variable
                    if($conf_nom=="installed_version") {
                        $childTable .= '<b>' . $conf_nom . '&nbsp;&nbsp;=&nbsp;&nbsp;' . $conf_valeur . '</b><br>';
                    } elseif( ($conf_type=="texte") || ($conf_type=="path") ) {
                        $childTable .= '<b>' . $conf_nom . '</b>&nbsp;=&nbsp;<input type="text" class="form-control" size="50" maxlength="200" name="tab_new_values[' . $conf_nom . ']" value="' . $conf_valeur . '"><br>';
                    } elseif($conf_type=="boolean") {
                        $childTable .= '<b>' . $conf_nom . '</b>&nbsp;=&nbsp;<select class="form-control" name="tab_new_values[' . $conf_nom . ']">';
                        $childTable .= '<option value="TRUE"';
                        if($conf_valeur=="TRUE") {
                            $childTable .= ' selected';
                        }
                        $childTable .= '>TRUE</option>';
                        $childTable .= '<option value="FALSE"';
                        if($conf_valeur=="FALSE") {
                            $childTable .= ' selected';
                        }
                        $childTable .= '>FALSE</option>';
                        $childTable .= '</select><br>';
                    } elseif(substr($conf_type,0,4)=="enum") {
                        $childTable .= '<b>' . $conf_nom . '</b>&nbsp;=&nbsp;<select class="form-control" name="tab_new_values[' . $conf_nom . ']">';
                        $options=explode("/", substr(strstr($conf_type, '='),1));
                        for($i=0; $i<count($options); $i++) {
                            $childTable .= '<option value="' . $options[$i] . '"';
                            if($conf_valeur==$options[$i]) {
                                $childTable .= ' selected';
                            }
                            $childTable .= '>' . $options[$i] . '</option>';
                        }
                        $childTable .= '</select><br>';
                    }
                    $childTable .= '<br>';
                }
            }

            $childTable .= '</td></tr>';
            $childTable .= '<tr><td align="right">';
            $childTable .= '<input type="submit" class="btn"  value="' . _('form_save_modif') . '"><br>';
            $childTable .= '</td></tr>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
        }


        /******************* GESTION DES PLUGINS V1.7 *************************/

        //rajout du formulaire de gestion des plugins : à partir de la version 1.7
        // - On détecte les plugins puis on propose de les installer
        // L'installation du plugin va lancer include/plugins/[nom_du_plugins]/plugin_install.php
        // plugin_install.php lance la création des tables supplémentaires;
        // normalement le format de nommage des tables est conges_plugin_[nom_du_plugin]. Exemple de table : conges_plugin_cet
        // il vaut mieux éviter de surcharger les tables existantes pour éviter les nombreux problèmes de compatibilité
        // lors d'un changement de version.
        // - Lorsqu'un plugin est installé, l'administrateur ou la personne autorisée pourra activer le plugin.
        // Le status qui s'affichera deviendra "activated"
        // Soit 4 statuts disponibles : not installed, installed, disable, activated
        // Correspondants à 4 fichiers dans le dossier du plugin : plugin_install.php, plugin_uninstall.php, plugin_active.php, plugin_inactive.php
        //Les statuts sont retrouvés par la table conges_plugins
        //Ensuite, les fichiers à inclure doivent être listés dans include/plugins/[nom_du_plugins]/allfilestoinclude.php
        // Ces fichiers à inclure contiennent le coeur de votre plugin.

        $my_plugins = scandir(PLUGINS_DIR);
        $plug_count = 0;
        $tableAddon = new \App\Libraries\Structure\Table();
        $tableAddon->addAttribute('width', '100%');
        $childTableAddon = '<tr><td>';
        $childTableAddon .= '<fieldset class="cal_saisie plugins">';
        $childTableAddon .= '<legend class="boxlogin">Plugins</legend>';
        foreach($my_plugins as $my_plugin) {
            if(is_dir(PLUGINS_DIR."/$my_plugin") && !preg_match("/^\./",$my_plugin)) {
                $childTableAddon .= _('plugin_detect').'<br>';
                $childTableAddon .= '<b>' . $my_plugin . ' : </b>'._('plugin_install').'
                    <select class="form-control" name=tab_new_values[' . $my_plugin . '_installed]>';

                $sql_plug="SELECT p_is_active, p_is_install FROM conges_plugins WHERE p_name = '".$my_plugin."';";
                $ReqLog_plug = \includes\SQL::query($sql_plug);
                if($ReqLog_plug->num_rows !=0) {
                    while($plug = $ReqLog_plug->fetch_array()){
                        $p_install = $plug["p_is_install"];
                        if ($p_install == '1') {
                            $childTableAddon .= '<option selected="selected" value="1">Y</option><option value="0">N</option>';
                        } else {
                            $childTableAddon .= '<option value="1">Y</option><option selected="selected" value="0">N</option>';
                        }
                        $childTableAddon .= '</select>';
                        $childTableAddon .= _('plugin_active').' <select class="form-control" name=tab_new_values[' . $my_plugin . '_activated]>';
                        $p_active = $plug["p_is_active"];
                        if ($p_active == '1') {
                            $childTableAddon .= '<option selected="selected" value="1">Y</option><option value="0">N</option>';
                        } else {
                            $childTableAddon .= '<option value="1">Y</option><option selected="selected" value="0">N</option>';
                        }
                    }
                } else {
                    $childTableAddon .= '<option value="1">Y</option><option selected="selected" value="0">N</option>';
                    $childTableAddon .= '</select>';
                    $childTableAddon .= _('plugin_active').' <select class="form-control" name=tab_new_values[' . $my_plugin . '_activated]>';
                    $childTableAddon .= '<option value="1">Y</option><option selected="selected" value="0">N</option>';
                }
                $childTableAddon .= '</select>';
                $childTableAddon .= '<br />';
                $plug_count++;
            }
        }
        if($plug_count == 0){
            $childTableAddon .= _('no_plugin');
        }
        $childTableAddon .= '</td></tr>';
        $childTableAddon .= '<tr><td align="right">';
        $childTableAddon .= '<input type="submit" class="btn"  value="' . _('form_save_modif') . '"><br>';
        $childTableAddon .= '</td></tr>';
        /**********************************************************************/

        $tableAddon->addChild($childTableAddon);
        ob_start();
        $tableAddon->render();
        $return .= ob_get_clean();
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @return void
     * @access public
     * @static
     */
    public static function configurationModule()
    {
        // verif des droits du user à afficher la page
        verif_droits_user("is_admin");
        $return = '<h1>Configuration générale</h1>';

        /*** initialisation des variables ***/
        $action="";
        $tab_new_values=array();
        /************************************/

        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $action         = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');

        /*************************************/

        if($action=="commit") {
            $return .= \config\Fonctions::commit_saisie($tab_new_values);
        } else {
            $return .= '<div class="wrapper configure">';
            $return .= \config\Fonctions::affichage_configuration();
            $return .= '<div>';
        }
        return $return;
    }
}
