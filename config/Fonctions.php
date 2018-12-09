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
        $return = '';

        $sql_delete="TRUNCATE TABLE conges_logs ";
        \includes\SQL::query($sql_delete);

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

        if ($ReqLog1->num_rows !=0) {
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
     * @return string
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


        if ($action=="suppr_logs") {
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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF";


        // update de la table
        foreach($tab_new_values as $nom_mail => $tab_mail) {
            $subject = htmlspecialchars(addslashes($tab_mail['subject']));
            $body = htmlspecialchars(addslashes($tab_mail['body']));
            $req_update='UPDATE conges_mail SET mail_subject=\''.$subject.'\', mail_body=\''.$body.'\' WHERE mail_nom="'. \includes\SQL::quote($nom_mail).'" ';
            \includes\SQL::query($req_update);
        }
        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';

        $comment_log = "configuration des mails d\'alerte";
        log_action(0, "", "", $comment_log);
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';

        return $return;
    }

    public static function test_config($tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF";

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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = "$PHP_SELF";

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
     * @return string
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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = $PHP_SELF;
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

        // vérif unicité du libellé court
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
        $absenceTypes = $api->get('absence/type', $_SESSION['token'])['data'];
        $absenceWithLibelle = array_filter($absenceTypes, function (array $type) use ($tab_new_values) {
            return $tab_new_values['short_libelle'] === $type['libelleCourt'];
        });
        if (!empty($absenceWithLibelle)) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('libelle_court_existant') . '<br>';
            $erreur = true;
        }

        if($erreur) {
            $return .= '<br>';
            $return .= '<form action="' . $URL . '" method="POST">';
            $return .= '<input type="hidden" name="tab_new_values[libelle]" value="' . $tab_new_values['libelle']. '">';
            $return .= '<input type="hidden" name="tab_new_values[short_libelle]" value="' . $tab_new_values['short_libelle'] . '">';
            $return .= '<input type="hidden" name="tab_new_values[type]" value="' . $tab_new_values['type'] . '">';
            $return .= '<input type="submit" value="' . _('form_redo') . '" >';
            $return .= '</form>';
            $return .= '<br><br>';
        } else {
            // ajout dans la table conges_type_absence
            $req_insert1="INSERT INTO conges_type_absence (ta_libelle, ta_short_libelle, ta_type, type_natif) " .
                "VALUES ('".$tab_new_values['libelle']."', '".$tab_new_values['short_libelle']."', '".$tab_new_values['type']."', 0) ";
            \includes\SQL::query($req_insert1);

            // on recup l'id de l'absence qu'on vient de créer
            $new_abs_id = \config\Fonctions::get_last_absence_id();

            if($new_abs_id!=0) {
                // ajout dans la table conges_solde_user (pour chaque user !!)(si c'est un conges, pas si c'est une absence)
                if ( ($tab_new_values['type']=="conges") || ($tab_new_values['type']=="conges_exceptionnels") ) {
                    // recup de users :
                    $sql_users="SELECT DISTINCT(u_login) FROM conges_users;" ;

                    $ReqLog_users = \includes\SQL::query($sql_users);

                    while ($resultat1 = $ReqLog_users->fetch_array()) {
                        $current_login=$resultat1["u_login"];

                        $req_insert2="INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) " .
                            "VALUES ('$current_login', $new_abs_id, 0, 0, 0) ";
                        \includes\SQL::query($req_insert2);
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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = $PHP_SELF;

        // delete dans la table conges_type_absence
        $req_delete1='DELETE FROM conges_type_absence WHERE ta_id='. \includes\SQL::quote($id_to_update);
        \includes\SQL::query($req_delete1);

        // delete dans la table conges_solde_user
        $req_delete2='DELETE FROM conges_solde_user WHERE su_abs_id='.\includes\SQL::quote($id_to_update);
        \includes\SQL::query($req_delete2);

        $return .= '<span class="messages">' . _('form_modif_ok') . '</span><br>';

        $comment_log = "config : supprime_type_absence ($id_to_update) ";
        log_action(0, "", "", $comment_log);
        $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=' . $URL . '">';

        return $return;
    }

    public static function supprimer($id_to_update)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = parse_url($PHP_SELF, PHP_URL_PATH);


        // verif si pas de periode de ce type de conges !!!
        //requête qui récupère les informations de la table conges_periode
        $sql1 = 'SELECT p_num FROM conges_periode WHERE p_type="'. \includes\SQL::quote($id_to_update).'"';
        $ReqLog1 = \includes\SQL::query($sql1);
        $withErreur = false;

        if (0 !== $ReqLog1->num_rows) {
            $raison = _('config_abs_already_used');
            $withErreur = true;
        }

        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
        $absenceType = $api->get('absence/type/' . $id_to_update, $_SESSION['token'])['data'];

        if ($absenceType['typeNatif']) {
            $raison = _('config_abs_type_natif');
            $withErreur = true;
        }

        if ($withErreur) {
            $return .= '<center>';
            $return .= '<br>' . _('config_abs_suppr_impossible') . '<br>' . $raison . '<br>';

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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = $PHP_SELF;


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

        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
        $absenceTypes = $api->get('absence/type/', $_SESSION['token'])['data'];

        $absence = [];
        $absenceWithLibelle = [];
        foreach ($absenceTypes as $at) {
            if ($at['id'] === $id_to_update) {
                $absence = $at;
            }
            if ($tab_new_values['short_libelle'] === $at['libelleCourt'] && $at['id'] != $id_to_update) {

                $absenceWithLibelle[] = $at;
            }
        }
        if (!empty($absenceWithLibelle)) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('libelle_court_existant') . '<br>';
            $erreur = true;
        }

        if (isset($absence['typeNatif']) && $absence['typeNatif']) {
            $return .= '<br>' . _('config_abs_saisie_not_ok') . ' : ' . _('config_abs_type_natif') . '<br>';
            $erreur = true;
        }

        if($erreur) {
            $return .= '<br>';
            $return .= '<form action="' . $PHP_SELF . '" method="POST">';
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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $return = '';

        $URL = parse_url($PHP_SELF, PHP_URL_PATH);

        /**************************************/
        // affichage du titre
        $return .= '<br><center><H1>' . _('config_abs_titre') . '</H1></center>';
        $return .= '<br>';

        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
        $absenceType = $api->get('absence/type/' . $id_to_update, $_SESSION['token'])['data'];

        // mise en place du formulaire
        $return .= '<form action="' . $URL . '" method="POST">';

        $text_libelle = '<input class="form-control" type="text" name="tab_new_values[libelle]" size="20" maxlength="20" value="' . $absenceType['libelle'] . '" >';
        $text_short_libelle = '<input class="form-control" type="text" name="tab_new_values[short_libelle]" size="3" maxlength="3" value="' . $absenceType['libelleCourt'] . '" >';

        // affichage
        $table = new \App\Libraries\Structure\Table();
        $table->addClass('table');
        $table->addAttribute('cellpadding', 2);
        $childTable = '<tr>';
        $childTable .= '<td><b><u>' . _('config_abs_libelle') . '</b></u></td>';
        $childTable .= '<td><b><u>' . _('config_abs_libelle_short') . '</b></u></td>';
        $childTable .= '<td>' . _('divers_type') . '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr><td><b>' . $absenceType['libelle'] . '</b></td><td>' . $absenceType['libelleCourt'] . '</td><td>' . $absenceType['type'] . '</td></tr>';
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

    public static function commit_saisie(&$tab_new_values)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
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

                if ($ReqLog_abs->num_rows !=0) {
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

            // Mise à jour
            $sql2 = 'UPDATE conges_config SET conf_valeur = \''.addslashes($value).'\' WHERE conf_nom ="'. \includes\SQL::quote($key).'" ';
            \includes\SQL::query($sql2);
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
        $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
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
                    if ($conf_nom=="installed_version") {
                        $childTable .= '<b>' . $conf_nom . '&nbsp;&nbsp;=&nbsp;&nbsp;' . $conf_valeur . '</b><br>';
                    } elseif( ($conf_type=="texte") || ($conf_type=="path") ) {
                        $childTable .= '<b>' . $conf_nom . '</b>&nbsp;=&nbsp;<input type="text" class="form-control" size="50" maxlength="200" name="tab_new_values[' . $conf_nom . ']" value="' . $conf_valeur . '"><br>';
                    } elseif ($conf_type=="boolean") {
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

        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @return string
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
