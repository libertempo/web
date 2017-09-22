<?php
namespace admin;

/**
* Regroupement des fonctions liées à l'admin
*/
class Fonctions
{
    public static function modif_user_groups($choix_user, &$checkbox_user_groups)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $result_insert= \admin\Fonctions::commit_modif_user_groups($choix_user, $checkbox_user_groups);

        if($result_insert) {
            $return .= _('form_modif_ok') . ' !<br><br>';
        } else {
            $return .= _('form_modif_not_ok') . ' !<br><br>';
        }
        $comment_log = "mofification_des groupes auxquels $choix_user appartient" ;
        log_action(0, "", $choix_user, $comment_log);

        /* APPEL D'UNE AUTRE PAGE */
        $return .= '<form action="' . $PHP_SELF . '?onglet=admin-group-users" method="POST">';
        $return .= '<input type="submit" value="' . _('form_retour') . '">';
        $return .= '</form>';

        return $return;
    }

    public static function affiche_tableau_affectation_user_groupes($choix_user)
    {
        $return = '';

        //AFFICHAGE DU TABLEAU DES GROUPES DU USER
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
        $childTable .= '<th colspan=3><h4>' . _('admin_gestion_groupe_users_group_of_new_user') . ' :</h4></th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<th></th>';
        $childTable .= '<th>' . _('admin_groupes_groupe') . '</th>';
        $childTable .= '<th>' . _('admin_groupes_libelle') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        // affichage des groupes

        //on rempli un tableau de tous les groupes avec le nom et libellé (tableau de tableau à 3 cellules)
        $tab_groups=array();
        $sql_g = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe";
        if($choix_user!="") {
            $sql_g .= " WHERE g_gid NOT IN (SELECT gr_gid FROM conges_groupe_resp WHERE gr_login =\"" . \includes\SQL::quote($choix_user) . "\")";
        }
        $sql_g .= " ORDER BY g_groupename";
        $ReqLog_g = \includes\SQL::query($sql_g);

        while ($resultat_g=$ReqLog_g->fetch_array()) {
            $tab_gg=array();
            $tab_gg["gid"]=$resultat_g["g_gid"];
            $tab_gg["groupename"]=$resultat_g["g_groupename"];
            $tab_gg["comment"]=$resultat_g["g_comment"];
            $tab_groups[]=$tab_gg;
        }

        $tab_user="";
        // si le user est connu
        // on rempli un autre tableau des groupes du user
        if($choix_user!="") {
            $tab_user=array();
            $sql_gu = 'SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'. \includes\SQL::quote($choix_user).'" ORDER BY gu_gid ';
            $ReqLog_gu = \includes\SQL::query($sql_gu);

            while ($resultat_gu=$ReqLog_gu->fetch_array()) {
                $tab_user[]=$resultat_gu["gu_gid"];
            }
        }

        // ensuite on affiche tous les groupes avec une case cochée si existe le gid dans le 2ieme tableau
        $count = count($tab_groups);
        for ($i = 0; $i < $count; $i++) {
            $gid=$tab_groups[$i]["gid"] ;
            $group=$tab_groups[$i]["groupename"] ;
            $libelle=$tab_groups[$i]["comment"] ;

            if ( ($tab_user!="") && (in_array ($gid, $tab_user)) ) {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\" checked>";
                $class="histo-big";
            } else {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\">";
                $class="histo";
            }

            $childTable .= '<tr class="' . (!($i%2) ? 'i' : 'p') . '">';
            $childTable .= '<td>' . $case_a_cocher .  '</td>';
            $childTable .= '<td class="' . $class . '">' . $group . '&nbsp</td>';
            $childTable .= '<td class="' . $class . '">' . $libelle . '</td>';
            $childTable .= '</tr>';
        }

        $childTable .= '<tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        return $return;
    }

    /**
     * Encapsule le comportement du module de gestion des utilisateurs
     *
     * @return string
     * @access public
     * @static
     */
    public static function userModule()
    {
        $return = '';
        $return .= '<a href="' . ROOT_PATH . 'admin/admin_index.php?onglet=ajout-user" style="float:right" class="btn btn-success">' . _('admin_onglet_add_user') . '</a>';
        $return .= '<h1>' . _('admin_onglet_gestion_user') . '</h1>';

        /*********************/
        /* Etat Utilisateurs */
        /*********************/

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges();
        $tab_type_conges_exceptionnels = [];

        // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        }


        // AFFICHAGE TABLEAU
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
        $childTable .= '<th>' .  _('user') . '</th>';
        $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
        foreach ($tab_type_conges as $id_type_cong => $libelle) {
            $childTable .= '<th>' . $libelle . ' / ' . _('divers_an') . '</th>';
            $childTable .= '<th>' . _('divers_solde') . ' ' . $libelle . '</th>';
        }

        foreach ($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
            $childTable .= '<th>' . _('divers_solde') . ' ' . $libelle . '</th>';
        }

        if($_SESSION['config']['gestion_heures']){
            $childTable .= '<th>' . _('divers_solde') . ' ' . _('heures') . '</th>';
        }

        $childTable .= '<th></th>';
        $childTable .= '<th></th>';
        if($_SESSION['config']['admin_change_passwd'] && ($_SESSION['config']['how_to_connect_user'] == "dbconges")) {
            $childTable .= '<th></th>';
        }
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        // Récuperation des informations des users:
        $tab_info_users=array();
        // si l'admin peut voir tous les users  OU si l'admin n'est pas responsable
        if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" || is_hr($_SESSION['userlogin']) ) {
            $tab_info_users = recup_infos_all_users();
        } else {
            $tab_info_users = recup_infos_all_users_du_resp($_SESSION['userlogin']);
        }
        asort($tab_info_users);
        uasort($tab_info_users, "sortParActif");
        $i = true;
        foreach ($tab_info_users as $current_login => $tab_current_infos) {
            $admin_modif_user= '<a href="admin_index.php?onglet=modif_user&u_login=' . $current_login . '" title="' . _('form_modif') . '"><i class="fa fa-pencil"></i></a>';
            $admin_suppr_user = '<a href="admin_index.php?onglet=suppr_user&u_login=' . $current_login . '" title="' . _('form_supprim') . '"><i class="fa fa-times-circle"></i></a>';
            $admin_chg_pwd_user = '<a href="admin_index.php?onglet=chg_pwd_user&u_login=' . $current_login . '" title="' . _('form_password') . '"><i class="fa fa-key"></i></a>';


            $childTable .= '<tr class="' . (($tab_current_infos['is_active']=='Y') ? 'actif' : 'inactif') . '">';
            $childTable .= '<td class="utilisateur"><strong>' . $tab_current_infos['nom'] . ' ' . $tab_current_infos['prenom'] . '</strong>';
            $childTable .= '<span class="login">' . $current_login . '</span>';
            if($_SESSION['config']['where_to_find_user_email']=="dbconges") {
                $childTable .= '<span class="mail">' . $tab_current_infos['email'] . '</span>';
            }
            // droit utilisateur
            $rights = array();
            if($tab_current_infos['is_active'] == 'N') {
                $rights[] = 'inactif';
            }
            if($tab_current_infos['is_admin'] == 'Y') {
                $rights[] = 'administrateur';
            }
            if($tab_current_infos['is_resp'] == 'Y') {
                $rights[] = 'responsable';
            }
            if($tab_current_infos['is_hr'] == 'Y') {
                $rights[] = 'RH';
            }
            if($tab_current_infos['see_all'] == 'Y') {
                $rights[] = 'voit tout';
            }

            if(count($rights) > 0) {
                $childTable .= '<span class="rights">' . implode(', ', $rights) . '</span>';
            }

            $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($current_login);
            $childTable .= '<span class="responsable"> responsables : <strong>' . implode(', ', $responsables) . '</strong></span>';

            $childTable .= '</td><td>' . $tab_current_infos['quotite'] . ' %</td>';

            //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
            $tab_conges=$tab_current_infos['conges'];

            foreach($tab_type_conges as $id_conges => $libelle) {
                if (isset($tab_conges[$libelle])) {
                    $childTable .= '<td>' . $tab_conges[$libelle]['nb_an'] . '</td>';
                    $childTable .= '<td>' . $tab_conges[$libelle]['solde'] . '</td>';
                } else {
                    $childTable .= '<td>0</td>';
                    $childTable .= '<td>0</td>';
                }
            }

            foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) {
                if (isset($tab_conges[$libelle])) {
                    $childTable .= '<td>' . $tab_conges[$libelle]['solde'] . '</td>';
                } else {
                    $childTable .= '<td>0</td>';
                }
            }

            if($_SESSION['config']['gestion_heures']){
                $childTable .= '<td>' . \App\Helpers\Formatter::timestamp2Duree($tab_current_infos['solde_heure']) . '</td>';
            }

            $childTable .= '<td>' . $admin_modif_user . '</td>';
            $childTable .= '<td>' . $admin_suppr_user . '</td>';
            if(($_SESSION['config']['admin_change_passwd']) && ($_SESSION['config']['how_to_connect_user'] == "dbconges")) {
                $childTable .= '<td>' . $admin_chg_pwd_user . '</td>';
            }
            $childTable .= '</tr>';
            $i = !$i;
        }

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';
        return $return;
    }

    public static function commit_update($u_login_to_update, $new_pwd1, $new_pwd2)
    {

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        if( (strlen($new_pwd1)!=0) && (strlen($new_pwd2)!=0) && (strcmp($new_pwd1, $new_pwd2)==0) ) {

            $passwd_md5=md5($new_pwd1);
            $sql1 = 'UPDATE conges_users  SET u_passwd=\''.$passwd_md5.'\' WHERE u_login="'. \includes\SQL::quote($u_login_to_update).'"' ;
            $result = \includes\SQL::query($sql1);

            if($result) {
                $return .= _('form_modif_ok') . ' !<br><br>';
            } else {
                $return .= _('form_modif_not_ok') . ' !<br><br>';
            }

            $comment_log = "admin_change_password_user : pour $u_login_to_update" ;
            log_action(0, "", $u_login_to_update, $comment_log);

            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            $return .= '<META HTTP-EQUIV=REFRESH CONTENT="2; URL=admin_index.php?onglet=admin-users">';
        } else {
            $return .= '<H3>' . _('admin_verif_param_invalides') . '</H3>';
            $return .= '<form action="' . $PHP_SELF . '?onglet=chg_pwd_user" method="POST">';
            $return .= '<input type="hidden" name="u_login" value="' . $u_login_to_update . '">';

            $return .= '<input type="submit" value="' . _('form_redo') . '">';
            $return .= '</form>';
        }
        return $return;
    }

    public static function modifier($u_login, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        /********************/
        /* Etat utilisateur */
        /********************/
        // AFFICHAGE TABLEAU
        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet . '&u_login_to_update=' . $u_login . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses(['tablo']);
        $table->addAttribute('width', '80%');
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th>' . _('divers_login_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_nom_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $childTable .= '<th>' . _('admin_users_password_1') . '</th>';
        $childTable .= '<th>' . _('admin_users_password_2') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        $childTable .= '<tr align="center">';

        // Récupération des informations
        $sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array()) {
            $text_pwd1="<input type=\"password\" name=\"new_pwd1\" size=\"10\" maxlength=\"30\" value=\"\" autocomplete=\"off\">" ;
            $text_pwd2="<input type=\"password\" name=\"new_pwd2\" size=\"10\" maxlength=\"30\" value=\"\" autocomplete=\"off\">" ;
            $childTable .= '<td>' . $resultat1["u_login"] . '</td><td>' . $resultat1["u_nom"] . '</td><td>' . $resultat1["u_prenom"] . '</td><td>' . $text_pwd1 . '</td><td>' . $text_pwd2 . '</td>';
        }
        $childTable .= '<tr>';
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        $return .= '<input type="submit" value="' . _('form_submit') . '">';
        $return .= '</form>';

        $return .= '<form action="admin_index.php?onglet=admin-users" method="POST">';
        $return .= '<input type="submit" value="' . _('form_cancel') . '">';
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de gestion des groupes et des responsables
     *
     * @param string $onglet Nom de l'onglet à afficher
     *
     * @return string
     * @access public
     * @static
     */
    public static function changeMotDePasseUserModule($onglet)
    {
        $return = '';
        /*************************************/
        // recup des parametres reçus :
        // SERVER

        $u_login            = htmlentities(getpost_variable('u_login'), ENT_QUOTES | ENT_HTML401);
        $u_login_to_update  = htmlentities(getpost_variable('u_login_to_update'), ENT_QUOTES | ENT_HTML401);
        $new_pwd1           = htmlentities(getpost_variable('new_pwd1'), ENT_QUOTES | ENT_HTML401);
        $new_pwd2           = htmlentities(getpost_variable('new_pwd2'), ENT_QUOTES | ENT_HTML401);

        if($u_login!="") {
            $return = '<H1>' . _('admin_chg_passwd_titre') . ' : ' . $u_login . '</H1>';
            $return .= \admin\Fonctions::modifier($u_login, $onglet);
        } else {
            if($u_login_to_update!="") {
                $return .= '<H1>' . _('admin_chg_passwd_titre') . ' : ' . $u_login_to_update . '</H1>';
                $return .= \admin\Fonctions::commit_update($u_login_to_update, $new_pwd1, $new_pwd2);
            } else {
                // renvoit sur la page principale .
                redirect( ROOT_PATH .'admin/admin_index.php?onglet=admin-users', false);
            }
        }
        return $return;
    }

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
            $childTable .= '<td align="center">';
            $childTable .= '<input type="button" value="' . _('form_close_window') . '" onClick="window.close();">';
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

        header_popup();

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
    private static function getDataFile($typeSauvegarde)
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

        header_popup();

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_save_ok') . ' ...</th>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="button" value="' . _('form_close_window') . '" onClick="window.close();">';
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


        header_popup();

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

        header_popup();

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
        // verif des droits du user à afficher la page
        verif_droits_user( "is_admin");


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

    public static function commit_update_user($u_login_to_update, &$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, &$tab_new_reliquat, &$return)
    {
        $dataUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($u_login_to_update);
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $erreurs = [];

        $result=true;

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges = recup_tableau_types_conges();
        $tab_type_conges_excep=array();
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            $tab_type_conges_excep=recup_tableau_types_conges_exceptionnels();
        }

        $valid_1=true;
        $valid_2=true;
        $valid_3=true;
        $valid_reliquat=true;
        $valid_4 = true;
        $valid_5 = true;

        // verification de la validite de la saisie du nombre de jours annuels et du solde pour chaque type de conges
        foreach($tab_type_conges as $id_conges => $libelle) {
            $valid_1=$valid_1 && verif_saisie_decimal($tab_new_jours_an[$id_conges]);  //verif la bonne saisie du nombre d?cimal
            $valid_2=$valid_2 && verif_saisie_decimal($tab_new_solde[$id_conges]);  //verif la bonne saisie du nombre d?cimal
            $valid_reliquat=$valid_reliquat && verif_saisie_decimal($tab_new_reliquat[$id_conges]);  //verif la bonne saisie du nombre d?cimal
        }
        if (false === $valid_1) {
            $erreurs['Nombre jour annuel'] = _('nombre_incorrect');
        }
        if (false === $valid_2) {
            $erreurs['Solde congés'] = _('nombre_incorrect');
        }
        if (false === $valid_reliquat) {
            $erreurs['Nombre reliquat'] = _('nombre_incorrect');
        }

        // si l'application gere les conges exceptionnels ET si des types de conges exceptionnels ont été définis
        if (($_SESSION['config']['gestion_conges_exceptionnels'])&&(count($tab_type_conges_excep) > 0)) {
            $valid_3=true;
            // vérification de la validité de la saisie du nombre de jours annuels et du solde pour chaque type de conges exceptionnels
            foreach($tab_type_conges_excep as $id_conges => $libelle) {
                $valid_3 = $valid_3 && verif_saisie_decimal($tab_new_solde[$id_conges]);  //verif la bonne saisie du nombre décimal
            }
        } else { // sinon on considère $valid_3 comme vrai
            $valid_3=true;
        }

        if (false === $valid_3) {
            $erreurs['Solde congés exceptionnels'] = _('nombre_incorrect');
        }

        if(!\admin\Fonctions::FormAddUserQuotiteOk($tab_new_user['quotite'])
            || !\admin\Fonctions::FormAddUserNameOk($tab_new_user['nom'])
            || !\admin\Fonctions::FormAddUserNameOk($tab_new_user['prenom']))
        {
            $valid_4=false;
        }
        if ($_SESSION['config']['gestion_heures'] && !\admin\Fonctions::FormAddUserSoldeHeureOk($tab_new_user['solde_heure'])) {
            $valid_5=false;
        }

        // si aucune erreur de saisie n'a ete commise
        if(($valid_1) && ($valid_2) && ($valid_3) && ($valid_4) && ($valid_5) && ($valid_reliquat) && $tab_new_user['login']!="") {
            // UPDATE de la table conges_users
            $sql = 'UPDATE conges_users SET u_nom="'. \includes\SQL::quote($tab_new_user['nom']).'", u_prenom="'.\includes\SQL::quote($tab_new_user['prenom']).'", u_is_resp="'. \includes\SQL::quote($tab_new_user['is_resp']).'", u_resp_login=';
            if($tab_new_user['resp_login'] == 'no_resp') {
                $sql .='NULL , ';
            } else {
                $sql .='"'.\includes\SQL::quote($tab_new_user['resp_login']).'",';
            }
            if ($_SESSION['config']['gestion_heures']) {
                $sql .='u_heure_solde='. \App\Helpers\Formatter::hour2Time($tab_new_user['solde_heure']).',';
            }
            $sql .= 'u_is_admin="'. \includes\SQL::quote($tab_new_user['is_admin']).'",u_is_hr="'.\includes\SQL::quote($tab_new_user['is_hr']).'",u_is_active="'.\includes\SQL::quote($tab_new_user['is_active']).'",u_see_all="'.\includes\SQL::quote($tab_new_user['see_all']).'",u_login="'.\includes\SQL::quote($tab_new_user['login']).'",u_quotite="'.\includes\SQL::quote($tab_new_user['quotite']).'",u_email="'. \includes\SQL::quote($tab_new_user['email']).'" WHERE u_login="'.\includes\SQL::quote($u_login_to_update).'"' ;

            \includes\SQL::query($sql);


            /*************************************/
            /* Mise a jour de la table conges_solde_user   */
            foreach($tab_type_conges as $id_conges => $libelle) {
                $sql = 'REPLACE INTO conges_solde_user SET su_nb_an=\''.strtr(round_to_half($tab_new_jours_an[$id_conges]),",",".").'\',su_solde=\''.strtr(round_to_half($tab_new_solde[$id_conges]),",",".").'\',su_reliquat=\''.strtr(round_to_half($tab_new_reliquat[$id_conges]),",",".").'\',su_login="'.\includes\SQL::quote($u_login_to_update).'",su_abs_id='.intval($id_conges).';';
                \includes\SQL::query($sql);

            }

            if ($_SESSION['config']['gestion_conges_exceptionnels']) {
                foreach($tab_type_conges_excep as $id_conges => $libelle) {
                    $sql = 'REPLACE INTO conges_solde_user SET su_nb_an=0, su_solde=\''.strtr(round_to_half($tab_new_solde[$id_conges]),",",".").'\', su_reliquat=\''.strtr(round_to_half($tab_new_reliquat[$id_conges]),",",".").'\', su_login="'.\includes\SQL::quote($u_login_to_update).'", su_abs_id='.intval($id_conges).';';
                    \includes\SQL::query($sql);
                }
            }

            /*************************************/

            // Si changement du login, (on a dèja updaté la table users (mais pas les responsables !!!)) on update toutes les autres tables
            // (les grilles artt, les periodes de conges et les échanges de rtt, etc ....) avec le nouveau login
            if($tab_new_user['login'] != $u_login_to_update) {
                // update table echange_rtt
                $sql = 'UPDATE conges_echange_rtt SET e_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE e_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

                // update table edition_papier
                $sql = 'UPDATE conges_edition_papier SET ep_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE ep_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

                // update table groupe_grd_resp
                $sql = 'UPDATE conges_groupe_grd_resp SET ggr_login= "'. \includes\SQL::quote($tab_new_user['login']).'" WHERE ggr_login="'.\includes\SQL::quote($u_login_to_update).'"  ';
                \includes\SQL::query($sql);

                // update table groupe_resp
                $sql = 'UPDATE conges_groupe_resp SET gr_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE gr_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

                // update table conges_groupe_users
                $sql = 'UPDATE conges_groupe_users SET gu_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE gu_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

                // update table periode
                $sql = 'UPDATE conges_periode SET p_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE p_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

                // update table conges_solde_user
                $sql = 'UPDATE conges_solde_user SET su_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE su_login="'. \includes\SQL::quote($u_login_to_update).'" ' ;
                \includes\SQL::query($sql);

                // update table conges_users
                $sql = 'UPDATE conges_users SET u_resp_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE u_resp_login="'. \includes\SQL::quote($u_login_to_update).'" ' ;
                \includes\SQL::query($sql);
            }

            if($tab_new_user['login'] != $u_login_to_update) {
                $comment_log = "modif_user (old_login = $u_login_to_update)  new_login = ".$tab_new_user['login'];
            } else {
                $comment_log = "modif_user login = $u_login_to_update";
            }

            log_action(0, "", $u_login_to_update, $comment_log);

            $return .= _('form_modif_ok') . ' !<br><br>';

            return true;

        } else { // en cas d'erreur de saisie
            // composition des erreurs
            $errors = '';
            if (!empty($erreurs)) {
                foreach ($erreurs as $erreur) {
                    $errors .= '<li>' . $erreur . '</li>';
                }
            }
            $return .= '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul><br /><a href="admin_index.php?onglet=modif_user&u_login=' . $u_login_to_update . '">' . _('form_retour') . '</a></div>';

            return false;
        }
    }

    public static function modifier_user($u_login, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';
        $soldeHeureId = uniqid();


        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges();

        // recup du tableau des types de conges (seulement les conges)
        if ( $_SESSION['config']['gestion_conges_exceptionnels'] ) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        }

        // Récupération des informations
        $tab_user = recup_infos_du_user($u_login, "");

        /********************/
        /* Etat utilisateur */
        /********************/
        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet . '&u_login_to_update=' . $u_login . '" method="POST">';
        // AFFICHAGE TABLEAU DES INFOS
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
        $childTable .= '<th>' . _('divers_nom_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_login_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $childTable .= '<th>' . _('solde_heure') . '</th>';
        }
        $childTable .= '<th>' . _('admin_users_is_resp') . '</th>';
        $childTable .= '<th>' . _('admin_users_resp_login') . '</th>';
        $childTable .= '<th>' . _('admin_users_is_admin') . '</th>';
        $childTable .= '<th>' . _('admin_users_is_hr') . '</th>';
        $childTable .= '<th>' . _('admin_users_is_active') . '</th>';
        $childTable .= '<th>' . _('admin_users_see_all') . '</th>';

        if($_SESSION['config']['where_to_find_user_email']=="dbconges") {
            $childTable .= '<th>' . _('admin_users_mail') . '</th>';
        }
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        // AFFICHAGE DE LA LIGNE DES VALEURS ACTUELLES A MODIFIER
        $childTable .= '<tr>';
        $childTable .= '<td>' . $tab_user['nom']. '</td>';
        $childTable .= '<td>' . $tab_user['prenom'] . '</td>';
        $childTable .= '<td>' . $tab_user['login'] . '</td>';
        $childTable .= '<td>' . $tab_user['quotite'] . '</td>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $childTable .= '<td>' . \App\Helpers\Formatter::timestamp2Duree($tab_user['solde_heure']) . '</td>';
        }
        $childTable .= '<td>' . $tab_user['is_resp'] . '</td>';
        $childTable .= '<td>' . $tab_user['resp_login'] . '</td>';
        $childTable .= '<td>' . $tab_user['is_admin'] . '</td>';
        $childTable .= '<td>' . $tab_user['is_hr'] . '</td>';
        $childTable .= '<td>' . $tab_user['is_active'] . '</td>';
        $childTable .= '<td>' . $tab_user['see_all'] . '</td>';

        if($_SESSION['config']['where_to_find_user_email']=="dbconges") {
            $childTable .= '<td>' . $tab_user['email'] . '</td>';
        }
        $childTable .= '</tr>';

        // contruction des champs de saisie
        if($_SESSION['config']['export_users_from_ldap']) {
            $text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_user['login']."\" readonly>" ;
        } else {
            $text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_user['login']."\">" ;
        }

        $text_nom="<input class=\"form-control\" type=\"text\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$tab_user['nom']."\">" ;
        $text_prenom="<input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$tab_user['prenom']."\">" ;
        $text_quotite="<input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$tab_user['quotite']."\">" ;
        if($tab_user['is_resp']=="Y") {
            $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" id=\"is_resp_id\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        } else {
            $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" id=\"is_resp_id\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        }

        if($tab_user['is_admin']=="Y") {
            $text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        } else {
            $text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        }

        if($tab_user['is_hr']=="Y") {
            $text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        } else {
            $text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        }

        if($tab_user['is_active']=="Y") {
            $text_is_active="<select class=\"form-control\" name=\"new_is_active\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        } else {
            $text_is_active="<select class=\"form-control\" name=\"new_is_active\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        }

        if($tab_user['see_all']=="Y") {
            $text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        } else {
            $text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        }

        if($_SESSION['config']['where_to_find_user_email']=="dbconges") {
            $text_email="<input class=\"form-control\" type=\"text\" name=\"new_email\" size=\"10\" maxlength=\"99\" value=\"".$tab_user['email']."\">" ;
        }


        $text_resp_login="<select class=\"form-control\" name=\"new_resp_login\" id=\"resp_login_id\" ><option value=\"no_resp\">". _('admin_users_no_resp') ."</option>" ;
        // construction des options du SELECT pour new_resp_login
        $sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" ORDER BY u_nom,u_prenom"  ;
        $ReqLog2 = \includes\SQL::query($sql2);

        while ($resultat2 = $ReqLog2->fetch_array()){
            if($resultat2["u_login"]==$tab_user['resp_login'] ) {
                $text_resp_login=$text_resp_login."<option value=\"".$resultat2["u_login"]."\" selected>".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            } else {
                $text_resp_login=$text_resp_login."<option value=\"".$resultat2["u_login"]."\">".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            }
        }

        $text_resp_login=$text_resp_login."</select>" ;

        // AFFICHAGE ligne de saisie
        $childTable .= '<tr class="update-line">';
        $childTable .= '<td>' . $text_nom . '</td>';
        $childTable .= '<td>' . $text_prenom . '</td>';
        $childTable .= '<td>' . $text_login . '</td>';
        $childTable .= '<td>' . $text_quotite . '</td>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $text_solde_heure="<input class=\"form-control\" type=\"text\" name=\"new_solde_heure\" id=\"" . $soldeHeureId . "\"  size=\"6\" maxlength=\"6\" value=\"".  \App\Helpers\Formatter::timestamp2Duree($tab_user['solde_heure'])."\">" ;
            $childTable .= '<td>' . $text_solde_heure . '</td>';
        }
        $childTable .= '<td>' . $text_is_resp . '</td>';
        $childTable .= '<td>' . $text_resp_login . '</td>';
        $childTable .= '<td>' . $text_is_admin . '</td>';
        $childTable .= '<td>' . $text_is_hr . '</td>';
        $childTable .= '<td>' . $text_is_active . '</td>';
        $childTable .= '<td>' . $text_see_all . '</td>';
        if($_SESSION['config']['where_to_find_user_email']=="dbconges") {
            $childTable .= '<td>' . $text_email . '</td>';
        }
        $childTable .= '</tr></tbody>';
        $childTable .= '<script type="text/javascript">generateTimePicker("' . $soldeHeureId . '");</script>';

        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br><hr/>';

        // AFFICHAGE TABLEAU DES conges annuels et soldes
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
        $childTable .= '<th></th>';
        $childTable .= '<th colspan="2">' . _('admin_modif_nb_jours_an') . ' </th>';
        $childTable .= '<th colspan="2">' . _('divers_solde') . '</th>';
        if( $_SESSION['config']['autorise_reliquats_exercice'] ) {
            $childTable .= '<th colspan="2">' . _('divers_reliquat') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';

        $i = true;
        foreach($tab_type_conges as $id_type_cong => $libelle) {
            $childTable .= '<tr class="' . ($i? 'i' : 'p') . '">';
            $childTable .= '<td>' . $libelle . '</td>';
            // jours / an

            if (isset($tab_user['conges'][$libelle])) {
                $childTable .= '<td>' . $tab_user['conges'][$libelle]['nb_an'] . '</td>';
                $text_jours_an="<input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['nb_an']."\">" ;
            } else {
                $childTable .= '<td>0</td>';
                $text_jours_an='<input class=\"form-control\" type="text" name="tab_new_jours_an['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
            }

            $childTable .= '<td>' . $text_jours_an . '</td>';

            // solde
            if (isset($tab_user['conges'][$libelle])) {
                $childTable .= '<td>' . $tab_user['conges'][$libelle]['solde'] . '</td>';
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['solde']."\">" ;
            } else {
                $childTable .= '<td>0</td>';
                $text_solde_jours='<input class=\"form-control\" type="text" name="tab_new_solde['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
            }

            $childTable .= '<td>' . $text_solde_jours . '</td>';

            // reliquat
            // si on ne les utilise pas, on initialise qd meme le tableau (<input type=\"hidden\") ...
            if($_SESSION['config']['autorise_reliquats_exercice']) {
                if (isset($tab_user['conges'][$libelle])) {
                    $childTable .= '<td>' . $tab_user['conges'][$libelle]['reliquat'] . '</td>';
                    $text_reliquats_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_reliquat[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['reliquat']."\">" ;

                } else {
                    $childTable .= '<td>0</td>';
                    $text_reliquats_jours='<input class=\"form-control\" type="text" name="tab_new_reliquat['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
                }
                $childTable .= '<td>' . $text_reliquats_jours . '</td>';
            } else {
                $childTable .= '<input type="hidden" name="tab_new_reliquat[$id_type_cong]" value="0">';
            }
            $childTable .= '</tr>';
            $i = !$i;
        }

        // recup du tableau des types de conges (seulement les conges)
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong_exp => $libelle) {
                $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
                $childTable .= '<td>' . $libelle . '</td>';
                // jours / an
                $childTable .= '<td>0</td>';
                $childTable .= '<td>0</td>';
                // solde
                $childTable .= '<td>' . $tab_user['conges'][$libelle]['solde'] . '</td>';
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong_exp]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['solde']."\">" ;
                $childTable .= '<td>' . $text_solde_jours . '</td>';
                // reliquat
                // si on ne les utilise pas, on initialise qd meme le tableau (<input type=\"hidden\") ...
                if($_SESSION['config']['autorise_reliquats_exercice']) {
                    $childTable .= '<td>' . $tab_user['conges'][$libelle]['reliquat'] . '</td>';
                    $text_reliquats_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_reliquat[$id_type_cong_exp]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['reliquat']."\">" ;
                    $childTable .= '<td>' . $text_reliquats_jours . '</td>';
                } else {
                    $childTable .= '<input type="hidden" name="tab_new_reliquat[' . $id_type_cong_exp . ']" value="0">';
                }
                $childTable .= '</tr>';
                $i = !$i;
            }
        }

        $childTable .= '</tbody>';
        $childTable .= '<script type="text/javascript">generateTimePicker("' . $soldeHeureId . '");</script>';

        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $listPlannings = \App\ProtoControllers\HautResponsable\Planning::getListPlanning((array) ((int) $tab_user['planningId']));
        $planningName = '';
        if(!empty($listPlannings)){
            $planningName = $listPlannings[0]['name'];
        }
        else {
            $planningName = _('Aucun_planning');
        }
        $return .= '<br><hr/>';
        $return .= '<h4>' . _('admin_planning_utilisateur') . '</h4>';
        $return .= '<div>' . $planningName . '</div>';

        $return .= '<hr /><input class="btn btn-success" type="submit" value="' . _('form_submit') . '"> ';
        $return .= '<a class="btn btn-default" href="admin_index.php?onglet=admin-users">' . _('form_cancel') . '</a>';
        $return .= '</form>';
        return $return;
    }

    /**
     * Encapsule le comportement du module de modification d'utilisateurs
     *
     * @param string $onglet Nom de l'onglet à afficher
     *
     * @return string
     * @access public
     * @static
     */
    public static function modifUserModule($onglet)
    {
        $u_login              = htmlentities(getpost_variable('u_login'));
        $u_login_to_update    = htmlentities(getpost_variable('u_login_to_update')) ;
        $tab_checkbox_sem_imp = htmlentities(getpost_variable('tab_checkbox_sem_imp')) ;
        $tab_checkbox_sem_p   = htmlentities(getpost_variable('tab_checkbox_sem_p')) ;
        $return = '';

        // TITRE
        if($u_login!="") {
            $login_titre = $u_login;
        } elseif($u_login_to_update!="") {
            $login_titre = $u_login_to_update;
        }

        $return .= '<h1>' . _('admin_modif_user_titre') . ' : <strong>' . $login_titre . '</strong></h1>';


        if($u_login!="") {
            $return .= \admin\Fonctions::modifier_user($u_login, $onglet);
        } elseif($u_login_to_update!="") {
            $tab_new_jours_an   = getpost_variable('tab_new_jours_an') ;
            $tab_new_solde      = getpost_variable('tab_new_solde') ;
            $tab_new_reliquat   = getpost_variable('tab_new_reliquat') ;

            $tab_new_user['login']      = htmlspecialchars(getpost_variable('new_login'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['nom']    = htmlspecialchars(getpost_variable('new_nom'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['prenom']     = htmlspecialchars(getpost_variable('new_prenom'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['quotite']    = htmlentities(getpost_variable('new_quotite'), ENT_QUOTES | ENT_HTML401);
            if ($_SESSION['config']['gestion_heures'] ) {
                $tab_new_user['solde_heure']    = htmlentities(getpost_variable('new_solde_heure'), ENT_QUOTES | ENT_HTML401);
            }
            $tab_new_user['is_resp']    = htmlentities(getpost_variable('new_is_resp'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['resp_login'] = htmlentities(getpost_variable('new_resp_login'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['is_admin']   = htmlentities(getpost_variable('new_is_admin'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['is_hr']      = htmlentities(getpost_variable('new_is_hr'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user['is_active']  = getpost_variable('new_is_active') ;
            $tab_new_user['see_all']    = getpost_variable('new_see_all') ;
            $tab_new_user['email']      = getpost_variable('new_email') ;
            $tab_new_user['jour']       = getpost_variable('new_jour') ;
            $tab_new_user['mois']       = getpost_variable('new_mois') ;
            $tab_new_user['year']       = getpost_variable('new_year') ;
            $echo  = '';

            $ok = \admin\Fonctions::commit_update_user($u_login_to_update, $tab_new_user, $tab_new_jours_an, $tab_new_solde, $tab_new_reliquat, $echo);
            if ($ok) {
                redirect( ROOT_PATH .'admin/admin_index.php?onglet=admin-users', false);
                exit;
            } else {
                $return .= $echo;
            }
        } else {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?onglet=admin-users', false);
            exit;
        }
        return $return;
    }

    public static function confirmer($group, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        /*******************/
        /* Groupe en cours */
        /*******************/
        // Récupération des informations
        $sql1 = 'SELECT g_groupename, g_comment, g_double_valid FROM conges_groupe WHERE g_gid = "'.\includes\SQL::quote($group).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        // AFFICHAGE TABLEAU

        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet.'&group_to_delete=' . $group . '" method="POST">';
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
        $childTable .= '<th><b>' . _('admin_groupes_groupe') . '</b></th>';
        $childTable .= '<th><b>' . _('admin_groupes_libelle') . ' / ' . _('divers_comment_maj_1') . '</b></th>';
        if($_SESSION['config']['double_validation_conges']) {
            $childTable .= '<th><b>' . _('admin_groupes_double_valid') . '</b></th>';
        }
        $childTable .= '</tr></thead><tbody><tr>';
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_groupname=$resultat1["g_groupename"];
            $sql_comment=$resultat1["g_comment"];
            $sql_double_valid=$resultat1["g_double_valid"] ;
            $childTable .= '<td>&nbsp;' . $sql_groupname . '&nbsp;</td>';
            $childTable .= '<td>&nbsp;' . $sql_comment . '&nbsp;</td>';
            if($_SESSION['config']['double_validation_conges']) {
                $childTable .= '<td>' . $sql_double_valid . '</td>';
            }
        }
        $childTable .= '</tr></tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr/>';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="admin_index.php?onglet=admin-group">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    public static function suppression($u_login_to_delete)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

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

    public static function affiche_tableau_affectation_user_groupes2($choix_user)
    {
        $return = '';
        //AFFICHAGE DU TABLEAU DES GROUPES DU USER
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);

        // affichage TITRE
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th colspan=3><h4>' . _('admin_gestion_groupe_users_group_of_new_user') . ' :</h4></th>';
        $childTable .= '</tr>';

        $childTable .= '<tr>';
        $childTable .= '<th>&nbsp;</th>';
        $childTable .= '<th>&nbsp;' . _('admin_groupes_groupe') . '&nbsp;:</th>';
        $childTable .= '<th>&nbsp;' . _('admin_groupes_libelle') . '&nbsp;:</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        // affichage des groupes

        //on rempli un tableau de tous les groupes avec le nom et libellé (tableau de tableau à 3 cellules)
        $tab_groups=array();
        $sql_g = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename "  ;
        $ReqLog_g = \includes\SQL::query($sql_g);

        while($resultat_g=$ReqLog_g->fetch_array()) {
            $tab_gg=array();
            $tab_gg["gid"]=$resultat_g["g_gid"];
            $tab_gg["groupename"]=$resultat_g["g_groupename"];
            $tab_gg["comment"]=$resultat_g["g_comment"];
            $tab_groups[]=$tab_gg;
        }

        $tab_user="";
        // si le user est connu
        // on rempli un autre tableau des groupes du user
        if($choix_user!="") {
            $tab_user=array();
            $sql_gu = 'SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'.\includes\SQL::quote($choix_user).'" ORDER BY gu_gid ';
            $ReqLog_gu = \includes\SQL::query($sql_gu);

            while($resultat_gu=$ReqLog_gu->fetch_array()) {
                $tab_user[]=$resultat_gu["gu_gid"];
            }
        }

        // ensuite on affiche tous les groupes avec une case cochée si existe le gid dans le 2ieme tableau
        $count = count($tab_groups);
        for ($i = 0; $i < $count; $i++) {
            $gid=$tab_groups[$i]["gid"] ;
            $group=$tab_groups[$i]["groupename"] ;
            $libelle=$tab_groups[$i]["comment"] ;

            if ( ($tab_user!="") && (in_array ($gid, $tab_user)) ){
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\" checked>";
                $class="histo-big";
            } else {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\">";
                $class="histo";
            }

            $childTable .= '<tr class="'.(!($i%2) ? 'i' : 'p').'">';
            $childTable .= '<td>' . $case_a_cocher . '</td>';
            $childTable .= '<td class="' . $class . '">&nbsp;' . $group . '&nbsp</td>';
            $childTable .= '<td class="' . $class . '">&nbsp;' . $libelle . '&nbsp;</td>';
            $childTable .= '</tr>';
        }
        $childTable .= '<tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        return $return;
    }

    // affichage du formulaire de saisie d'un nouveau user
    public static function affiche_formulaire_ajout_user(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $onglet)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges();

        // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
        if ($_SESSION['config']['gestion_conges_exceptionnels']){
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        }

        /*********************/
        /* Ajout Utilisateur */
        /*********************/

        // TITRE
        $return .= '<h1>' . _('admin_new_users_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '?onglet=' . $onglet . '" method="POST">';

        /****************************************/
        // tableau des infos de user
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
        if ($_SESSION['config']['export_users_from_ldap'] ) {
            $childTable .= '<th>' . _('divers_nom_maj_1') . ' ' . _('divers_prenom_maj_1') . '</th>';
        } else {
            $childTable .= '<th>' . _('divers_login_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_nom_maj_1') . '</th>';
            $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        }
        $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $childTable .= '<th>' . _('solde_heure') . '</th>';
        }
        $childTable .= '<th>' . _('admin_new_users_is_resp') . '</th>';
        $childTable .= '<th>' . _('divers_responsable_maj_1') . '</th>';
        $childTable .= '<th>' . _('admin_new_users_is_admin') . '</th>';
        $childTable .= '<th>' . _('admin_new_users_is_hr') . '</th>';
        $childTable .= '<th>' . _('admin_new_users_see_all') . '</th>';
        if ( !$_SESSION['config']['export_users_from_ldap'] ) {
            $childTable .= '<th>' . _('admin_users_mail') . '</th>';
        }
        if ($_SESSION['config']['how_to_connect_user'] == "dbconges") {
            $childTable .= '<th>' . _('admin_new_users_password') . '</th>';
            $childTable .= '<th>' . _('admin_new_users_password') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';
        $soldeHeureId = uniqid();


        $text_nom="<input class=\"form-control\" type=\"text\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['nom']."\">" ;
        $text_prenom="<input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['prenom']."\">" ;
        if( (!isset($tab_new_user['quotite'])) || ($tab_new_user['quotite']=="") ) {
            $tab_new_user['quotite']=100;
        }
        $text_quotite="<input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$tab_new_user['quotite']."\">" ;
        $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        // PREPARATION DES OPTIONS DU SELECT du resp_login
        $text_resp_login="<select class=\"form-control\" name=\"new_resp_login\" id=\"resp_login_id\" ><option value=\"no_resp\">". _('admin_users_no_resp') ."</option>" ;

        if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" || is_hr($_SESSION['userlogin'])) {
            $sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" ORDER BY u_nom, u_prenom"  ;
        } else {
            $sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" AND u_login=\"".$_SESSION['userlogin']."\" ORDER BY u_nom, u_prenom" ;
        }

        $ReqLog2 = \includes\SQL::query($sql2);

        while ($resultat2 = $ReqLog2->fetch_array()) {
            $current_resp_login=$resultat2["u_login"];
            if($tab_new_user['resp_login']==$current_resp_login) {
                $text_resp_login=$text_resp_login."<option value=\"$current_resp_login\" selected>".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            } else {
                $text_resp_login=$text_resp_login."<option value=\"$current_resp_login\">".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            }
        }
        $text_resp_login=$text_resp_login."</select>" ;

        $text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        $text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        $text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
        $text_email="<input class=\"form-control\" type=\"text\" name=\"new_email\" size=\"10\" maxlength=\"99\" value=\"".$tab_new_user['email']."\">" ;
        $text_password1="<input class=\"form-control\" type=\"password\" name=\"new_password1\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" >" ;
        $text_password2="<input class=\"form-control\" type=\"password\" name=\"new_password2\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" >" ;
        $text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_new_user['login']."\">" ;


        // AFFICHAGE DE LA LIGNE DE SAISIE D'UN NOUVEAU USER

        $childTable .= '<tr class="update-line">';
        // Aj. D.Chabaud - Université d'Auvergne - Sept. 2005
        if ($_SESSION['config']['export_users_from_ldap'] ) {
            // Récupération de la liste des utilisateurs via un ldap :

            // on crée 2 tableaux (1 avec les noms + prénoms, 1 avec les login)
            // afin de pouvoir construire une liste déroulante dans le formulaire qui suit...
            $tab_ldap  = array();
            $tab_login = array();
            \admin\Fonctions::recup_users_from_ldap($tab_ldap, $tab_login);

            // construction de la liste des users récupérés du ldap ...
            array_multisort($tab_ldap, $tab_login); // on trie les utilisateurs par le nom

            $lst_users = "<select multiple size=9 name=new_ldap_user[]><option>------------------</option>\n";
            $i = 0;

            foreach ($tab_login as $login) {
                $lst_users .= "<option value=$tab_login[$i]>$tab_ldap[$i]</option>\n";
                $i++;
            }
            $lst_users .= "</select>\n";
            $childTable .= '<td>' . $lst_users . '</td>';
        } else {
            $childTable .= '<td>' . $text_login . '</td>';
            $childTable .= '<td>' . $text_nom . '</td>';
            $childTable .= '<td>' . $text_prenom . '</td>';
        }

        $childTable .= '<td>' . $text_quotite . '</td>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $text_solde_heure="<input class=\"form-control\" type=\"text\" name=\"new_solde_heure\" id=\"" . $soldeHeureId . "\" size=\"6\" maxlength=\"6\" value=\"".$tab_new_user['solde_heure']."\">" ;
            $childTable .= '<td>' . $text_solde_heure . '</td>';
        }else{
            $text_solde_heure="<input class=\"form-control\" type=\"hidden\" name=\"new_solde_heure\" id=\"" . $soldeHeureId . "\" size=\"6\" maxlength=\"6\" value=\"0\">" ;
            $childTable .= $text_solde_heure; // le champ hidden est ajouté à l'extérieur du tableau
        }
        $childTable .= '<td>' . $text_is_resp . '</td>';
        $childTable .= '<td>' . $text_resp_login . '</td>';
        $childTable .= '<td>' . $text_is_admin . '</td>';
        $childTable .= '<td>' . $text_is_hr . '</td>';
        $childTable .= '<td>' . $text_see_all . '</td>';
        if ( !$_SESSION['config']['export_users_from_ldap'] ) {
            $childTable .= '<td>' . $text_email . '</td>';
        }
        if ($_SESSION['config']['how_to_connect_user'] == "dbconges") {
            $childTable .= '<td>' . $text_password1 . '</td>';
            $childTable .= '<td>' . $text_password2 . '</td>';
        }
        $childTable .= '</tr></tbody>';
        $childTable .= '<script type="text/javascript">generateTimePicker("' . $soldeHeureId . '");</script>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br><hr />';


        /****************************************/
        //tableau des conges annuels et soldes
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        // ligne de titres
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th></th>';
        $childTable .= '<th>' . _('admin_new_users_nb_par_an') . '</th>';
        $childTable .= '<th>' . _('divers_solde') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        $i = true;
        // ligne de saisie des valeurs
        foreach($tab_type_conges as $id_type_cong => $libelle) {
            $childTable .= '<tr class="'.($i?'i':'p').'">';
            $value_jours_an = ( isset($tab_new_jours_an[$id_type_cong]) ? $tab_new_jours_an[$id_type_cong] : 0 );
            $value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
            $text_jours_an="<input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_jours_an\">" ;
            $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
            $childTable .= '<td>' . $libelle . '</td>';
            $childTable .= '<td>' . $text_jours_an.  '</td>';
            $childTable .= '<td>' . $text_solde_jours . '</td>';
            $childTable .= '</tr>';
            $i = !$i;
        }
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) {
                $childTable .= '<tr class="'.($i?'i':'p').'">';
                $value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
                $text_jours_an="<input type=\"hidden\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"0\"> &nbsp; " ;
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
                $childTable .= '<td>'.  $libelle . '</td>';
                $childTable .= '<td>' . $text_jours_an . '</td>';
                $childTable .= '<td>' . $text_solde_jours . '</td>';
                $childTable .= '</tr>';
                $i = !$i;
            }
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';

        $return .= '<br>';
        if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" ||  is_hr($_SESSION['userlogin']) ) {
            $return .= \admin\Fonctions::affiche_tableau_affectation_user_groupes2("");
        } else {
            $return .= \admin\Fonctions::affiche_tableau_affectation_user_groupes2($_SESSION['userlogin']);
        }

        $return .= '<hr>';
        $return .= '<input type="hidden" name="saisie_user" value="ok">';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= ' <a class="btn btn-default" href="' . $PHP_SELF . '">' . _('form_cancel') . '</a>';
        $return .= '</form>';
        return $return;
    }

    public static function verif_new_param(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, &$return = null)
    {
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
            $return .= htmlentities($tab_new_user['login']) . '---' . htmlentities($tab_new_user['nom']) . '---' . htmlentities($tab_new_user['prenom']) . '---' . htmlentities($tab_new_user['quotite']) . '---' . htmlentities($tab_new_user['is_resp']) . '---' . htmlentities($tab_new_user['resp_login']) . '<br>';
            foreach($tab_new_jours_an as $id_cong => $jours_an) {
                $return .= $tab_new_jours_an[$id_cong] . '---' . $tab_new_solde[$id_cong] . '<br>';
            }

            $return .= '<form action="' . $PHP_SELF . '?onglet=ajout-user" method="POST">';
            $return .= '<input type="hidden" name="new_login" value="' . $tab_new_user['login'] . '">';
            $return .= '<input type="hidden" name="new_nom" value="' . $tab_new_user['nom'] . '">';
            $return .= '<input type="hidden" name="new_prenom" value="' . $tab_new_user['prenom'] . '">';
            $return .= '<input type="hidden" name="new_is_resp" value="'  . $tab_new_user['is_resp'] . '">';
            $return .= '<input type="hidden" name="new_resp_login" value="' . $tab_new_user['resp_login'] . '">';
            $return .= '<input type="hidden" name="new_is_admin" value="'  . $tab_new_user['is_admin'] . '">';
            $return .= '<input type="hidden" name="new_is_hr" value="' . $tab_new_user['is_hr'] . '">';
            $return .= '<input type="hidden" name="new_see_all" value="' . $tab_new_user['see_all'] . '">';
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
                $return .= '<input type="hidden" name="new_resp_login" value="' . $tab_new_user['resp_login'] . '">';
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
            } elseif($_SESSION['config']['where_to_find_user_email'] == "dbconges" && strrchr($tab_new_user['email'], "@")==FALSE) {
                $return .= '<h3>' . _('admin_verif_bad_mail') . '</h3>';
                $return .= '<form action="' . $PHP_SELF . '?onglet=ajout-user" method="POST">';
                $return .= '<input type="hidden" name="new_login" value="' . $tab_new_user['login'] . '">';
                $return .= '<input type="hidden" name="new_nom" value="' . $tab_new_user['nom'] . '">';
                $return .= '<input type="hidden" name="new_prenom" value="' . $tab_new_user['prenom'] . '">';
                $return .= '<input type="hidden" name="new_is_resp" value="' . $tab_new_user['is_resp'] . '">';
                $return .= '<input type="hidden" name="new_resp_login" value="' . $tab_new_user['resp_login'] . '">';
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
        if($_SESSION['config']['export_users_from_ldap']) {
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
        if($_SESSION['config']['how_to_connect_user']=='dbconges')
        {
            return !(strlen($password1)==0 || strlen($password2)==0 || strcmp($password1, $password2)!=0);
        } else {
            return (strlen($password1)==0 && strlen($password2)==0);
        }
    }

    public static function ajout_user(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $checkbox_user_groups)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';
        $verifFalse = '';

        // si pas d'erreur de saisie :
        if(\admin\Fonctions::verif_new_param($tab_new_user, $tab_new_jours_an, $tab_new_solde, $verifFalse)==0) {
            $return .= $tab_new_user['login'] . ' --- ' . $tab_new_user['nom'] .  ' --- ' . $tab_new_user['prenom'] . ' --- ' . $tab_new_user['quotite'];
            $return .= ' --- ' . $tab_new_user['is_resp'] . ' --- ' . $tab_new_user['resp_login'] . ' --- ' . $tab_new_user['is_admin'] . ' --- ' . $tab_new_user['is_hr'] . ' --- ' . $tab_new_user['see_all'] . ' --- ' . $tab_new_user['email'] . '<br>';

            foreach($tab_new_jours_an as $id_cong => $jours_an) {
                $return .= $tab_new_jours_an[$id_cong] . ' --- ' . $tab_new_solde[$id_cong] . '<br>';
            }
            $new_date_deb_grille=$tab_new_user['new_year']."-".$tab_new_user['new_mois']."-".$tab_new_user['new_jour'];
            $return .= $new_date_deb_grille . '<br>';

            /*****************************/
            /* INSERT dans conges_users  */
            $motdepasse = ('dbconges' == $_SESSION['config']['how_to_connect_user'])
                ? $tab_new_user['password1']
                : 'none';
            $motdepasse = md5($motdepasse);

            $sql1 = "INSERT INTO conges_users SET ";
            $sql1=$sql1."u_login='".$tab_new_user['login']."', ";
            $sql1=$sql1."u_nom='".addslashes($tab_new_user['nom'])."', ";
            $sql1=$sql1."u_prenom='".addslashes($tab_new_user['prenom'])."', ";
            $sql1=$sql1."u_is_resp='".$tab_new_user['is_resp']."', ";

            if($tab_new_user['resp_login'] == 'no_resp') {
                $sql1=$sql1."u_resp_login= NULL , ";
            } else {
                $sql1=$sql1."u_resp_login='". $tab_new_user['resp_login']."', ";
            }
            $seeAll = ($tab_new_user['is_admin'] || $tab_new_user['is_hr'])
                ? 'Y'
                : 'N';

            $sql1=$sql1."u_is_admin='".$tab_new_user['is_admin']."', ";
            $sql1=$sql1."planning_id = 0, ";
            $sql1=$sql1."u_is_hr='".$tab_new_user['is_hr']."', ";
            $sql1=$sql1."u_see_all='". $seeAll . "', ";
            $sql1=$sql1."u_passwd='$motdepasse', ";
            $sql1=$sql1."u_quotite=".$tab_new_user['quotite'].",";
            $sql1=$sql1."u_heure_solde=".  \App\Helpers\Formatter::hour2Time($tab_new_user['solde_heure']).",";
            $sql1 .= 'date_inscription = "' . date('Y-m-d H:i') . '" ,';
            $sql1=$sql1." u_email='".$tab_new_user['email']."' ";
            $result1 = \includes\SQL::query($sql1);


            /**********************************/
            /* INSERT dans conges_solde_user  */
            foreach($tab_new_jours_an as $id_cong => $jours_an) {
                $sql3 = "INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) ";
                $sql3 = $sql3. "VALUES ('".$tab_new_user['login']."' , $id_cong, ".$tab_new_jours_an[$id_cong].", ".$tab_new_solde[$id_cong].", 0) " ;
                $result3 = \includes\SQL::query($sql3);
            }

            /***********************************/
            /* ajout du user dans ses groupes  */
            $result4=TRUE;
            if( ($checkbox_user_groups!="") ) {
                $result4= \admin\Fonctions::commit_modif_user_groups($tab_new_user['login'], $checkbox_user_groups);
            }

            /*****************************/
            if($result1 && $result3 && $result4) {
                $return .= _('form_modif_ok') . '<br><br>';
            } else {
                $return .= _('form_modif_not_ok') . '<br><br>';
            }

            $comment_log = "ajout_user : ".$tab_new_user['login']." / ".addslashes($tab_new_user['nom'])." ".addslashes($tab_new_user['prenom'])." (".$tab_new_user['quotite']." %)" ;
            log_action(0, "", $tab_new_user['login'], $comment_log);

            /* APPEL D'UNE AUTRE PAGE */
            $return .= '<form action="' . $PHP_SELF . '?onglet=admin-users" method="POST">';
            $return .= '<input type="submit" value="' . _('form_retour') .'">';
            $return .= '</form>';
        } else {
            $return .= $verifFalse;
        }
        return $return;
    }

    public static function commit_modif_user_groups($choix_user, &$checkbox_user_groups)
    {

        $result_insert=FALSE;
        // on supprime tous les anciens groupes du user, puis on ajoute tous ceux qui sont dans la tableau checkbox (si il n'est pas vide)
        $sql_del = 'DELETE FROM conges_groupe_users WHERE gu_login=\''. \includes\SQL::quote($choix_user).'\'';
        $ReqLog_del = \includes\SQL::query($sql_del);

        if( ($checkbox_user_groups!="") && (count ($checkbox_user_groups)!=0) )
        {
            foreach($checkbox_user_groups as $gid => $value)
            {
                $sql_insert = "INSERT INTO conges_groupe_users SET gu_gid=$gid, gu_login='$choix_user' "  ;
                $result_insert = \includes\SQL::query($sql_insert);
            }
        } else {
            $result_insert=TRUE;
        }
        return $result_insert;
    }

    /**
     * Encapsule le comportement du module d'ajout d'utilisateurs
     *
     * @param string $onglet
     *
     * @return void
     * @access public
     * @static
     */
    public static function ajoutUtilisateurModule($onglet)
    {
        $saisie_user = getpost_variable('saisie_user');
        $return      = '';

        // si on recupere les users dans ldap et qu'on vient d'en créer un depuis la liste déroulante
        if ($_SESSION['config']['export_users_from_ldap'] && isset($_POST['new_ldap_user'])) {
            $index = 0;

            // On lance une boucle pour selectionner tous les items
            // traitements : $login contient les valeurs successives
            foreach($_POST['new_ldap_user'] as $login) {
                $tab_login[$index] = $login;
                $index++;
                // cnx à l'annuaire ldap :
                $ds = ldap_connect($_SESSION['config']['ldap_server']);
                ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			    // Support Active Directory
			    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
                if ($_SESSION['config']['ldap_user'] == "") {
                    $bound = ldap_bind($ds);
                } else {
                    $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);
                }

                // recherche des entrées :
                $filter = "(".$_SESSION['config']['ldap_login']."=".$login.")";

                $sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
                $data = ldap_get_entries($ds,$sr);

                foreach ($data as $info) {
                    $tab_new_user[$login]['login']  = $login;
                    $ldap_libelle_prenom        =$_SESSION['config']['ldap_prenom'];
                    $ldap_libelle_nom               = $_SESSION['config']['ldap_nom'];
                    $tab_new_user[$login]['prenom'] = utf8_decode($info[$ldap_libelle_prenom][0]);
                    $tab_new_user[$login]['nom']    = utf8_decode($info[$ldap_libelle_nom][0]);

                    $ldap_libelle_mail              = $_SESSION['config']['ldap_mail'];
                    $tab_new_user[$login]['email']  = $info[$ldap_libelle_mail][0] ;
                }

                $tab_new_user[$login]['quotite']    = htmlentities(getpost_variable('new_quotite'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['solde_heure']= htmlentities(getpost_variable('new_solde_heure'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['is_resp']    = htmlentities(getpost_variable('new_is_resp'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['resp_login'] = htmlentities(getpost_variable('new_resp_login'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['is_admin']   = htmlentities(getpost_variable('new_is_admin'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['is_hr']      = htmlentities(getpost_variable('new_is_hr'), ENT_QUOTES | ENT_HTML401);
                $tab_new_user[$login]['see_all']    = getpost_variable('new_see_all');

                if ($_SESSION['config']['how_to_connect_user'] == "dbconges") {
                    $tab_new_user[$login]['password1'] = getpost_variable('new_password1');
                    $tab_new_user[$login]['password2'] = getpost_variable('new_password2');
                }
                $tab_new_jours_an                 = getpost_variable('tab_new_jours_an');
                $tab_new_solde                    = getpost_variable('tab_new_solde') ;
                $tab_new_user[$login]['new_jour'] = getpost_variable('new_jour');
                $tab_new_user[$login]['new_mois'] = getpost_variable('new_mois');
                $tab_new_user[$login]['new_year'] = getpost_variable('new_year');
            }
        } else {
            $tab_new_user[0]['login']      = htmlspecialchars(getpost_variable('new_login'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['nom']        = htmlspecialchars(getpost_variable('new_nom'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['prenom']     = htmlspecialchars(getpost_variable('new_prenom'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['quotite']    = getpost_variable('new_quotite');
            $tab_new_user[0]['is_resp']    = htmlentities(getpost_variable('new_is_resp'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['solde_heure']= htmlentities(getpost_variable('new_solde_heure'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['resp_login'] = htmlentities(getpost_variable('new_resp_login'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['is_admin']   = htmlentities(getpost_variable('new_is_admin'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['is_hr']      = htmlentities(getpost_variable('new_is_hr'), ENT_QUOTES | ENT_HTML401);
            $tab_new_user[0]['see_all']    = getpost_variable('new_see_all');

            if ($_SESSION['config']['how_to_connect_user'] == "dbconges") {
                $tab_new_user[0]['password1']    = getpost_variable('new_password1');
                $tab_new_user[0]['password2']    = getpost_variable('new_password2');
            }
            $tab_new_user[0]['email']    = htmlentities(getpost_variable('new_email'), ENT_QUOTES | ENT_HTML401);
            $tab_new_jours_an            = getpost_variable('tab_new_jours_an');
            $tab_new_solde               = getpost_variable('tab_new_solde');
            $tab_new_user[0]['new_jour'] = getpost_variable('new_jour');
            $tab_new_user[0]['new_mois'] = getpost_variable('new_mois');
            $tab_new_user[0]['new_year'] = getpost_variable('new_year');
        }

        $checkbox_user_groups = getpost_variable('checkbox_user_groups') ;
        /* FIN de la recup des parametres    */
        /*************************************/

        if($saisie_user=="ok") {
            if($_SESSION['config']['export_users_from_ldap']) {
                foreach($tab_login as $login) {
                    $return .= \admin\Fonctions::ajout_user($tab_new_user[$login], $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups);
                }
            } else {
                $return .= \admin\Fonctions::ajout_user($tab_new_user[0], $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups);
            }
        } else {
            $return .= \admin\Fonctions::affiche_formulaire_ajout_user($tab_new_user[0], $tab_new_jours_an, $tab_new_solde, $onglet);
        }
        return $return;
    }
}
