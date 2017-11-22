<?php
namespace admin;

/**
* Regroupement des fonctions liées à l'admin
*/
class Fonctions
{
// recup des data d'une table sous forme de INSERT ...
    public static function get_table_data($table)
    {

        $chaine_data="";

        // suppression des donnéées de la table :
        $chaine_delete='DELETE FROM `'. \includes\SQL::quote($table).'` ;'."\n";
        $chaine_data=$chaine_data.$chaine_delete ;

        // recup des donnéées de la table :
        $sql_data='SELECT * FROM '. \includes\SQL::quote($table);
        $ReqLog_data = \includes\SQL::query($sql_data);

        while ($resultat_data = $ReqLog_data->fetch_array())
        {
            $count_fields=count($resultat_data)/2;   // on divise par 2 car c'est un tableau indexé (donc compte key+valeur)
            $chaine_insert = "INSERT INTO `$table` VALUES ( ";
            for($i=0; $i<$count_fields; $i++)
            {
                if(isset($resultat_data[$i]))
                    $chaine_insert = $chaine_insert."'".addslashes($resultat_data[$i])."'";
                else
                    $chaine_insert = $chaine_insert."NULL";

                if($i!=$count_fields-1)
                    $chaine_insert = $chaine_insert.", ";
            }
            $chaine_insert = $chaine_insert." );\n";

            $chaine_data=$chaine_data.$chaine_insert;
        }

        return $chaine_data;
    }

    // recup de la structure d'une table sous forme de CREATE ...
    public static function get_table_structure($table)
    {
        $drop = "DROP TABLE IF EXISTS  `$table` ;\n";

        // description des champs :
        $req = 'SHOW CREATE TABLE '. \includes\SQL::quote($table);
        $descriptor = \includes\SQL::query($req) ;
        $resultDescriptor = $descriptor->fetch_array();
        return $drop . $resultDescriptor['Create Table'] . ";\n#\n";
    }

    public static function restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        header_popup();

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        if( ($fichier_restaure_error!=0)||($fichier_restaure_size==0) ) // s'il y a eu une erreur dans le telechargement OU taille==0
            //(cf code erreur dans fichier features.file-upload.errors.html de la doc php)
        {
            //message d'erreur et renvoit sur la page précédente (choix fichier)
            $return.= '<form action="' . $PHP_SELF . '" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $childTable = '<tr>';
            $childTable .= '<th>' . _('admin_sauve_db_bad_file') . ' : <br>' . $fichier_restaure_name .'</th>';
            $childTable .= '</tr><tr>';
            $childTable .= '<td align="center">';
            $childTable .= '<input type="hidden" name="choix_action" value="restaure">';
            $childTable .= '<input type="submit" value="' . _('form_redo') . '">';
            $childTable .= '</td>';
            $childTable .= '</tr><tr>';
            $childTable .= '<td align="center">';
            $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
            $childTable .= '</td>';
            $childTable .= '</tr>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '</form>';
        } else {
            $result = execute_sql_file($fichier_restaure_tmpname);

            $return .= '<form action="" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $childTable = '<tr>';
            $childTable .= '<th>' . _('admin_sauve_db_restaure_ok') . ' !</th>';
            $childTable .= '</tr>';
            $childTable .= '<tr>';
            $childTable .= '<td align="center">&nbsp;</td>';
            $childTable .= '</tr>';
            $childTable .= '<tr>';
            $childTable .= '<td>';
            $childTable .= '</td>';
            $childTable .= '</tr>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '</form>';
        }
        echo $return;
        bottom();
    }

    // RESTAURATION
    public static function choix_restaure()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';
        $return .= '<form enctype="multipart/form-data" action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th>' . _('admin_sauve_db_restaure') . '<br>' . _('admin_sauve_db_file_to_restore') . ' :</th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center"><input type="file" name="fichier_restaure"></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">&nbsp;</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center"><font color="red">' . _('admin_sauve_db_warning') . ' !</font></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">&nbsp;</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">';
        $childTable .= '<input type="hidden" name="choix_action" value="restaure">';
        $childTable .= '<input type="submit" value="' . _('admin_sauve_db_do_restaure') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;
        bottom();
    }

    public static function commit_sauvegarde($type_sauvegarde)
    {
        header("Pragma: no-cache");
        header("Content-Type: text/x-delimtext; name=\"php_conges_".$type_sauvegarde.".sql\"");
        header("Content-disposition: attachment; filename=php_conges_".$type_sauvegarde.".sql");

        echo static::getDataFile($type_sauvegarde);
    }

    /**
     * Écrit un fichier de sauvegarde de version dans le répertoire de backup
     *
     * @param string $previousVersion Version de départ (courante)
     * @param string $newVersion Version visée
     *
     * @throws \Exception en cas d'échec d'écriture de fichier
     */
    public static function sauvegardeAsFile($previousVersion, $newVersion)
    {
        $typeSauvegarde = 'all';
        $contentFile = static::getDataFile($typeSauvegarde);
        $filename = BACKUP_PATH . 'libertempo_' . $typeSauvegarde .'_' . $previousVersion . '__' . $newVersion . '.sql'; // nom de migration

        if (false === file_put_contents($filename, $contentFile)) {
            throw new \Exception('Échec de l\'écriture de la sauvegarde');
        }
    }

    /**
     * Retourne les données de sauvegarde
     *
     * @param string $typeSauvegarde Si on veut sauvegarder la structure seule ou les données
     *
     * @return string
     */
    public static function getDataFile($typeSauvegarde)
    {
        $content = "#\n";
        $content .= "# Libertempo\n";
        $content .= "#\n# DATE : " . date("d-m-Y H:i:s") . "\n";
        $content .= "#\n";

        //recup de la liste des tables
        $ReqLog = \includes\SQL::query('SHOW TABLES');
        while ($resultat = $ReqLog->fetch_array()) {
            $table = $resultat[0];
            $content .= "#\n#\n# TABLE: $table \n#\n";
            if(($typeSauvegarde=="all") || ($typeSauvegarde=="structure") ) {
                $content .= "# Struture : \n#\n";
                $content .= static::get_table_structure($table);
            }
            if(($typeSauvegarde=="all") || ($typeSauvegarde=="data") ) {
                $content .= "# Data : \n#\n";
                $content .= static::get_table_data($table);
            }
        }

        return $content;
    }

    public static function sauve($type_sauvegarde)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        redirect(ROOT_PATH .'admin/admin_db_sauve.php?choix_action=sauvegarde&type_sauvegarde='.$type_sauvegarde.'&commit=ok', false);

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_save_ok') . ' ...</th>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;
        bottom();
    }

    // SAUVEGARDE
    public static function choix_sauvegarde()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_options') . '</th>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td><input type="radio" name="type_sauvegarde" value="all" checked></td>';
        $childTable .= '<td>' . _('admin_sauve_db_complete') . '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td><input type="radio" name="type_sauvegarde" value="data"></td>';
        $childTable .= '<td>' . _('admin_sauve_db_data_only') . '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '&nbsp;';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="hidden" name="choix_action" value="sauvegarde">';
        $childTable .= '<input type="submit" value="' . _('admin_sauve_db_do_sauve') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;

        bottom();
    }

    // CHOIX
    public static function choix_save_restore()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_choisissez') . ' :</th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td><input type="radio" name="choix_action" value="sauvegarde" checked></td>';
        $childTable .= '<td><b>' . _('admin_sauve_db_sauve') . '</b></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td><input type="radio" name="choix_action" value="restaure" /></td>';
        $childTable .= '<td><b>' . _('admin_sauve_db_restaure') . '</b></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '&nbsp;';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan=2" align="center">';
        $childTable .= '<input type="submit" value="' . _('form_submit') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td></tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';
        echo $return;
        bottom();
    }

    /**
     * Encapsule le comportement du module de sauvegarde / restauration de bdd
     *
     * @return void
     * @access public
     * @static
     */
    public static function saveRestoreModule()
    {
        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $choix_action    = getpost_variable('choix_action');
        $type_sauvegarde = getpost_variable('type_sauvegarde');
        $commit          = getpost_variable('commit');

        $fichier_restaure_name="";
        $fichier_restaure_tmpname="";
        $fichier_restaure_size=0;
        $fichier_restaure_error=4;
        if(isset($_FILES['fichier_restaure'])) {
            $fichier_restaure_name=$_FILES['fichier_restaure']['name'];
            $fichier_restaure_size=$_FILES['fichier_restaure']['size'];
            $fichier_restaure_tmpname=$_FILES['fichier_restaure']['tmp_name'];
            $fichier_restaure_error=$_FILES['fichier_restaure']['error'];
        }
        /*************************************/
        if($choix_action=="") {
            \admin\Fonctions::choix_save_restore();
        } elseif($choix_action=="sauvegarde") {
            if(!isset($type_sauvegarde) || $type_sauvegarde=="") {
                \admin\Fonctions::choix_sauvegarde();
            } else {
                if( (!isset($commit)) || ($commit=="")) {
                    \admin\Fonctions::sauve($type_sauvegarde);
                } else {
                    \admin\Fonctions::commit_sauvegarde($type_sauvegarde);
                }
            }
        } elseif($choix_action=="restaure") {
            if( (!isset($fichier_restaure_name)) || ($fichier_restaure_name=="")||(!isset($fichier_restaure_tmpname)) || ($fichier_restaure_tmpname=="") )
                \admin\Fonctions::choix_restaure();
            else
                \admin\Fonctions::restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error);
        } else {
            /* APPEL D'UNE AUTRE PAGE immediat */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=admin_index.php?onglet=admin-users\">";
        }
    }

    public static function suppression($u_login_to_delete)
    {
        $sql1 = 'DELETE FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result = \includes\SQL::query($sql1);

        $sql2 = 'DELETE FROM conges_periode WHERE p_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result2 = \includes\SQL::query($sql2);

        $sql4 = 'DELETE FROM conges_echange_rtt WHERE e_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result4 = \includes\SQL::query($sql4);

        $sql5 = 'DELETE FROM conges_groupe_resp WHERE gr_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result5 = \includes\SQL::query($sql5);

        $sql6 = 'DELETE FROM conges_groupe_users WHERE gu_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result6 = \includes\SQL::query($sql6);

        $sql7 = 'DELETE FROM conges_solde_user WHERE su_login = "'.\includes\SQL::quote($u_login_to_delete).'"';
        $result7 = \includes\SQL::query($sql7);


        $comment_log = "suppression_user ($u_login_to_delete)";
        log_action(0, "", $u_login_to_delete, $comment_log);

        if($result) {
            $return .= _('form_modif_ok') . ' !<br><br>';
        } else {
            $return .= _('form_modif_not_ok') . ' !<br><br>';
        }
        return $return;
    }

    public static function confirmer_suppression($u_login, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        /*****************************/
        /* Etat Utilisateur en cours */
        /*****************************/
        // AFFICHAGE TABLEAU
        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet . '&u_login_to_delete=' . $u_login . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th>' . _('divers_login_maj_1') . '</th>';
        $childTable .= '<th>'. _('divers_nom_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        // Récupération des informations
        $sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        $return .= '<tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $childTable .= '<td>' . $resultat1["u_login"] . '</td>';
            $childTable .= '<td>' . $resultat1["u_nom"] . '</td>';
            $childTable .= '<td>' . $resultat1["u_prenom"] . '</td>';
        }
        $childTable .= '</tr>';
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="admin_index.php?onglet=admin-users">' . _('form_cancel') . '</a>';
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de suppression des utilisateurs
     *
     * @param string $onglet
     *
     * @return string
     * @access public
     * @static
     */
    public static function supprimerUtilisateurModule($onglet)
    {
        $return = '';
        /*************************************/
        // recup des parametres reçus :

        $u_login = getpost_variable('u_login') ;
        $u_login_to_delete = getpost_variable('u_login_to_delete') ;
        /*************************************/

        // TITRE
        if($u_login!="") {
            $login_titre = $u_login;
        } elseif($u_login_to_delete!="") {
            $login_titre = $u_login_to_delete;
        }

        $return .= '<h1>' . _('admin_suppr_user_titre') . ' : <strong>' . $login_titre . '</strong></h1>';


        if($u_login!="") {
            $return .= \admin\Fonctions::confirmer_suppression($u_login, $onglet);
        } elseif($u_login_to_delete!="") {
            echo \admin\Fonctions::suppression($u_login_to_delete);
            redirect( ROOT_PATH .'admin/admin_index.php?onglet=admin-users', false);
            exit;
        } else {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?onglet=admin-users', false);
            exit;
        }
        return $return;
    }

    public static function recup_users_from_ldap(&$tab_ldap, &$tab_login)
    {
        // cnx à l'annuaire ldap :
        $ds = \ldap_connect($_SESSION['config']['ldap_server']);
        if($_SESSION['config']['ldap_protocol_version'] != 0) {
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
			// Support Active Directory
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        }
        if ($_SESSION['config']['ldap_user'] == "") {
            $bound = ldap_bind($ds);  // connexion anonyme au serveur
        } else {
            $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);
        }

        // recherche des entrées :
        if ($_SESSION['config']['ldap_filtre_complet'] != "") {
            $filter = $_SESSION['config']['ldap_filtre_complet'];
        } else {
            $filter = "(&(".$_SESSION['config']['ldap_nomaff']."=*)(".$_SESSION['config']['ldap_filtre']."=".$_SESSION['config']['ldap_filrech']."))";
        }

        $sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
        $data = ldap_get_entries($ds,$sr);

        foreach ($data as $info) {
            $ldap_libelle_login=$_SESSION['config']['ldap_login'];
            $ldap_libelle_nom=$_SESSION['config']['ldap_nom'];
            $ldap_libelle_prenom=$_SESSION['config']['ldap_prenom'];
            $login = $info[$ldap_libelle_login][0];
            // concaténation NOM Prénom
            // utf8_decode permet de supprimer les caractères accentués mal interprêtés...
            $nom = ( isset($info[$ldap_libelle_nom]) ? strtoupper($info[$ldap_libelle_nom][0]): '' )." ". (isset($info[$ldap_libelle_prenom])?$info[$ldap_libelle_prenom][0]:'');
            array_push($tab_ldap, $nom);
            array_push($tab_login, $login);
        }
    }

    public static function verif_new_param(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, &$return = null)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        foreach($tab_new_jours_an as $id_cong => $jours_an) {
            $valid=verif_saisie_decimal($tab_new_jours_an[$id_cong]);    //verif la bonne saisie du nombre décimal
            $valid=verif_saisie_decimal($tab_new_solde[$id_cong]);    //verif la bonne saisie du nombre décimal
        }

        // verif des parametres reçus :
        // si on travaille avec la base dbconges, on teste tout, mais si on travaille avec ldap, on ne teste pas les champs qui viennent de ldap ...
        if(!\admin\Fonctions::test_form_add_user($tab_new_user)) {
            $return .= '<h3><font color="red">' . _('admin_verif_param_invalides') . '</font></h3>';
            // affichage des param :
            $return .= htmlentities($tab_new_user['login']) . '---' . htmlentities($tab_new_user['nom']) . '---' . htmlentities($tab_new_user['prenom']) . '---' . htmlentities($tab_new_user['quotite']) . '---' . htmlentities($tab_new_user['is_resp']) . '<br>';
            foreach($tab_new_jours_an as $id_cong => $jours_an) {
                $return .= $tab_new_jours_an[$id_cong] . '---' . $tab_new_solde[$id_cong] . '<br>';
            }

            $return .= '<form action="' . $PHP_SELF . '?onglet=ajout-user" method="POST">';
            $return .= '<input type="hidden" name="new_login" value="' . $tab_new_user['login'] . '">';
            $return .= '<input type="hidden" name="new_nom" value="' . $tab_new_user['nom'] . '">';
            $return .= '<input type="hidden" name="new_prenom" value="' . $tab_new_user['prenom'] . '">';
            $return .= '<input type="hidden" name="new_is_resp" value="'  . $tab_new_user['is_resp'] . '">';
            $return .= '<input type="hidden" name="new_is_admin" value="'  . $tab_new_user['is_admin'] . '">';
            $return .= '<input type="hidden" name="new_is_hr" value="' . $tab_new_user['is_hr'] . '">';
            $return .= '<input type="hidden" name="new_quotite" value="' . $tab_new_user['quotite'] . '">';
            $return .= '<input type="hidden" name="new_heure_solde" value="' . $tab_new_user['solde_heure'] . '">';
            $return .= '<input type="hidden" name="new_email" value="' . $tab_new_user['email'] . '">';
            foreach($tab_new_jours_an as $id_cong => $jours_an) {
                $return .= '<input type="hidden" name="tab_new_jours_an[$id_cong]" value="' . $tab_new_jours_an[$id_cong] . '">';
                $return .= '<input type="hidden" name="tab_new_solde[' . $id_cong . ']" value="' . $tab_new_solde[$id_cong] . '">';
            }

            $return .= '<input type="hidden" name="saisie_user" value="faux">';
            $return .= '<input type="submit" value="' . _('form_redo') . '"">';
            $return .= '</form>';

            return true;
        } else {
            // verif si le login demandé n'existe pas déjà ....
            $sql_verif='SELECT u_login FROM conges_users WHERE u_login="'.\includes\SQL::quote($tab_new_user['login']).'"';
            $ReqLog_verif = \includes\SQL::query($sql_verif);

            $num_verif = $ReqLog_verif->num_rows;
            if ($num_verif!=0) {
                $return .= '<h3><font color="red">' . _('admin_verif_login_exist') . '</font></h3>';
                $return .= '<form action="' . $PHP_SELF . '?onglet=ajout-user" method="POST">';
                $return .= '<input type="hidden" name="new_login" value="' . $tab_new_user['login'] . '">';
                $return .= '<input type="hidden" name="new_nom" value="' . $tab_new_user['nom'] . '">';
                $return .= '<input type="hidden" name="new_prenom" value="' . $tab_new_user['prenom'] . '">';
                $return .= '<input type="hidden" name="new_is_resp" value="' . $tab_new_user['is_resp'] . '">';
                $return .= '<input type="hidden" name="new_is_admin" value="' . $tab_new_user['is_admin'] . '">';
                $return .= '<input type="hidden" name="new_is_hr" value="' . $tab_new_user['is_hr'] . '">';
                $return .= '<input type="hidden" name="new_quotite" value="' . $tab_new_user['quotite'] . '">';
                $return .= '<input type="hidden" name="new_heure_solde" value="' . $tab_new_user['solde_heure'] . '">';
                $return .= '<input type="hidden" name="new_email" value="' . $tab_new_user['email'] . '">';

                foreach($tab_new_jours_an as $id_cong => $jours_an) {
                    $return .= '<input type="hidden" name="tab_new_jours_an[' . $id_cong . ']" value="' . $tab_new_jours_an[$id_cong] . '">';
                    $return .= '<input type="hidden" name="tab_new_solde[' . $id_cong . ']" value="' . $tab_new_solde[$id_cong] . '">';
                }

                $return .= '<input type="hidden" name="saisie_user" value="faux">';
                $return .= '<input type="submit" value="' . _('form_redo') . '">';
                $return .= '</form>';

                return true;
            } elseif(!$config->getMailFromLdap() && strrchr($tab_new_user['email'], "@")==FALSE) {
                $return .= '<h3>' . _('admin_verif_bad_mail') . '</h3>';
                $return .= '<form action="' . $PHP_SELF . '?onglet=ajout-user" method="POST">';
                $return .= '<input type="hidden" name="new_login" value="' . $tab_new_user['login'] . '">';
                $return .= '<input type="hidden" name="new_nom" value="' . $tab_new_user['nom'] . '">';
                $return .= '<input type="hidden" name="new_prenom" value="' . $tab_new_user['prenom'] . '">';
                $return .= '<input type="hidden" name="new_is_resp" value="' . $tab_new_user['is_resp'] . '">';
                $return .= '<input type="hidden" name="new_is_admin" value="' . $tab_new_user['is_admin'] . '">';
                $return .= '<input type="hidden" name="new_is_hr" value="' . $tab_new_user['is_hr'] . '">';
                $return .= '<input type="hidden" name="new_quotite" value="' . $tab_new_user['quotite'] . '">';
                $return .= '<input type="hidden" name="new_heure_solde" value="' . $tab_new_user['solde_heure'] . '">';
                $return .= '<input type="hidden" name="new_email" value="' . $tab_new_user['email'] . '">';

                foreach($tab_new_jours_an as $id_cong => $jours_an) {
                    $return .= '<input type="hidden" name="tab_new_jours_an[' . $id_cong . ']" value="' . $tab_new_jours_an[$id_cong] . '">';
                    $return .= '<input type="hidden" name="tab_new_solde[' . $id_cong . ']" value="' . $tab_new_solde[$id_cong] . '">';
                }

                $return .= '<input type="hidden" name="saisie_user" value="faux">';
                $return .= '<input class="btn" type="submit" value="' . _('form_redo') . '">';
                $return .= '</form>';

                return true;
            } else {
                return false;
            }
        }
    }

    public static function test_form_add_user($tab_new_user) {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        if($config->isUsersExportFromLdap()) {
            return \admin\Fonctions::FormAddUserLoginOk($tab_new_user['login']) && \admin\Fonctions::FormAddUserQuotiteOk($tab_new_user['quotite']) && \admin\Fonctions::FormAddUserSoldeHeureOk($tab_new_user['solde_heure']);
        } else {
            return \admin\Fonctions::FormAddUserLoginOk($tab_new_user['login']) && \admin\Fonctions::FormAddUserQuotiteOk($tab_new_user['quotite'])  && \admin\Fonctions::FormAddUserSoldeHeureOk($tab_new_user['solde_heure']) && \admin\Fonctions::FormAddUserNameOk($tab_new_user['nom']) && \admin\Fonctions::FormAddUserNameOk($tab_new_user['prenom']) && \admin\Fonctions::FormAddUserpasswdOk($tab_new_user['password1'],$tab_new_user['password2']);
        }
    }

    public static function FormAddUserSoldeHeureOk($solde_heure){
        if (empty($solde_heure)) {
            return false;
        }
        return \App\Helpers\Formatter::isHourFormat($solde_heure);
    }

    public static function FormAddUserLoginOk($login) {
        return preg_match('/^[a-z.\d_-]{2,30}$/i', $login);
    }

    public static function FormAddUserQuotiteOk($quot) {
        return !(strlen($quot)==0 || $quot>100);
    }

    public static function FormAddUserNameOk($name) {
        return preg_match('/^[a-z\d\sàáâãäåçèéêëìíîïðòóôõöùúûüýÿ-]{2,20}$/i', $name);
    }

    public static function FormAddUserpasswdOk($password1,$password2) {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        if($config->getHowToConnectUser() == 'dbconges')
        {
            return !(strlen($password1)==0 || strlen($password2)==0 || strcmp($password1, $password2)!=0);
        } else {
            return (strlen($password1)==0 && strlen($password2)==0);
        }
    }
}
