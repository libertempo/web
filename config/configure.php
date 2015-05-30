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

$DEBUG = FALSE ;
//$DEBUG = TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);

if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


    /*** initialisation des variables ***/
    $action="";
    $tab_new_values=array();
    /************************************/

    /*************************************/
    // recup des parametres reçus :
    // SERVER
    $PHP_SELF=$_SERVER['PHP_SELF'];
    // GET / POST
    $action         = getpost_variable('action') ;
    $tab_new_values = getpost_variable('tab_new_values');

    /*************************************/

    if( $DEBUG ) { echo "tab_new_values = "; print_r($tab_new_values); echo "<br>\n"; }

    if($action=="commit")
        commit_saisie($tab_new_values, $session, $DEBUG);
    else {
        echo "<div class=\"wrapper configure\">\n";
        affichage($session, $DEBUG);
        echo "<div>\n";
    }

/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($session, $DEBUG=FALSE)
{
    $PHP_SELF=$_SERVER['PHP_SELF'];

    // affiche_bouton_retour($session);


    // affichage de la liste des variables

    if($session=="")
        echo "<form action=\"$PHP_SELF\" method=\"POST\"> \n";
    else
        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\"> \n";
    echo "<input type=\"hidden\" name=\"action\" value=\"commit\">\n";

    //requête qui récupère les informations de config
    $sql1 = "SELECT * FROM conges_config ORDER BY conf_groupe ASC";
    $ReqLog1 = SQL::query($sql1);

    $old_groupe="";
    while ($data =$ReqLog1->fetch_array())
    {
        $conf_nom = $data['conf_nom'];
        $conf_valeur = $data['conf_valeur'];
        $conf_groupe = $data['conf_groupe'];
        $conf_type = strtolower($data['conf_type']);
        $conf_commentaire = strtolower($data['conf_commentaire']);

        // changement de groupe de variables
        if($old_groupe != $conf_groupe)
        {
            if($old_groupe!="")
            {
                echo "</td></tr>\n";
                echo "<tr><td align=\"right\">\n";
                echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
                echo "</td></tr>\n";
                echo "</table>\n";
            }
            echo "<br>\n";
            echo "<table width=\"100%\">\n";
            echo "<tr><td>\n";
            echo "    <fieldset class=\"cal_saisie $conf_nom\">\n";
            echo "    <legend class=\"boxlogin\">". _($conf_groupe) ."</legend>\n";
            $old_groupe = $conf_groupe ;
        }

        // si on est sur le parametre "lang" on liste les fichiers de langue du répertoire install/lang
        if($conf_nom=="lang")
        {
            echo "Choisissez votre langue :<br> \n";
            echo "Choose your language :<br>\n";
            // affichage de la liste des langues supportées ...
            // on lit le contenu du répertoire lang et on parse les nom de ficher (ex lang_fr_francais.php)
            //affiche_select_from_lang_directory("tab_new_values[$conf_nom]");
            affiche_select_from_lang_directory('lang', $conf_valeur);
        }
        else
        {
            // affichage commentaire
            echo "<br><i>". _($conf_commentaire) ."</i><br>\n";

            // affichage saisie variable
            if($conf_nom=="installed_version")
            {
                echo "<b>$conf_nom&nbsp;&nbsp;=&nbsp;&nbsp;$conf_valeur</b><br>";
            }
            elseif( ($conf_type=="texte") || ($conf_type=="path") )
            {
                echo "<b>$conf_nom</b>&nbsp;=&nbsp;<input type=\"text\" class=\"form-control\" size=\"50\" maxlength=\"200\" name=\"tab_new_values[$conf_nom]\" value=\"$conf_valeur\"><br>";
            }
            elseif($conf_type=="boolean")
            {
                echo "<b>$conf_nom</b>&nbsp;=&nbsp;<select class=\"form-control\" name=\"tab_new_values[$conf_nom]\">";
                echo "<option value=\"TRUE\"";
                if($conf_valeur=="TRUE") echo "selected";
                echo ">TRUE</option>";
                echo "<option value=\"FALSE\"";
                if($conf_valeur=="FALSE") echo "selected";
                echo ">FALSE</option>";
                echo "</select><br>";
            }
            elseif(substr($conf_type,0,4)=="enum")
            {
                echo "<b>$conf_nom</b>&nbsp;=&nbsp;<select class=\"form-control\" name=\"tab_new_values[$conf_nom]\">";
                $options=explode("/", substr(strstr($conf_type, '='),1));
                for($i=0; $i<count($options); $i++)
                {
                    echo "<option value=\"".$options[$i]."\"";
                    if($conf_valeur==$options[$i]) echo "selected";
                    echo ">".$options[$i]."</option>";
                }
                echo "</select><br>";
            }
            echo "<br>";
        }

    }

    echo "</td></tr>\n";
    echo "<tr><td align=\"right\">\n";
    echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
    echo "</td></tr>\n";


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
    echo "<table width=\"100%\">\n";
    echo "<tr><td>\n";
    echo "    <fieldset class=\"cal_saisie plugins\">\n";
    echo "    <legend class=\"boxlogin\">Plugins</legend>\n";
    foreach($my_plugins as $my_plugin){
        if(is_dir(PLUGINS_DIR."/$my_plugin") && !preg_match("/^\./",$my_plugin))
        {
            echo "Plugin détecté : ";
            echo "<b> $my_plugin </b>. This plugin is installed ? :
            <select class=\"form-control\" name=tab_new_values[".$my_plugin."_installed]>";

            $sql_plug="SELECT p_is_active, p_is_install FROM conges_plugins WHERE p_name = '".$my_plugin."';";
            $ReqLog_plug = SQL::query($sql_plug);
            if($ReqLog_plug->num_rows !=0)
                {
                while($plug = $ReqLog_plug->fetch_array()){
                    $p_install = $plug["p_is_install"];
                    if ($p_install == '1')
                        { echo "<option selected='selected' value='1'>Y</option><option value='0'>N</option>"; }
                    else
                        { echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>"; }
                    echo "</select>";
                    echo " ... Is activated ? : <select class=\"form-control\" name=tab_new_values[".$my_plugin."_activated]>";
                    $p_active = $plug["p_is_active"];
                    if ($p_active == '1')
                        { echo "<option selected='selected' value='1'>Y</option><option value='0'>N</option>"; }
                    else
                        { echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>"; }
                }
            }
            else
                {
                echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>";
                echo "</select>";
                echo " ... Is activated ? : <select class=\"form-control\" name=tab_new_values[".$my_plugin."_activated]>";
                echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>";
                }
            echo "</select>";
            echo "<br />";
            $plug_count++;
        }
    }
    if($plug_count == 0){ echo "No plugin detected."; }
    echo "</td></tr>\n";
    echo "<tr><td align=\"right\">\n";
    echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
    echo "</td></tr>\n";
/**********************************************************************/

    echo "</table>\n";
    echo "</form>\n";
}

function commit_saisie(&$tab_new_values, $session, $DEBUG=FALSE)
{

//$DEBUG=TRUE;
    $PHP_SELF=$_SERVER['PHP_SELF'];

    if($session=="")
        $URL = "$PHP_SELF";
    else
        $URL = "$PHP_SELF?session=$session";

    $timeout=2 ;  // temps d'attente pour rafraichir l'écran après l'update !

    if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n"; }

    foreach($tab_new_values as $key => $value )
    {
        // CONTROLE gestion_conges_exceptionnels
        // si désactivation les conges exceptionnels, on verif s'il y a des conges exceptionnels enregistres ! si oui : changement impossible !
        if(($key=="gestion_conges_exceptionnels") && ($value=="FALSE") )
        {
            $sql_abs="SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type='conges_exceptionnels' ";
            $ReqLog_abs = SQL::query($sql_abs);

            if($ReqLog_abs->num_rows !=0)
            {
                echo "<b>". _('config_abs_desactive_cong_excep_impossible') ."</b><br>\n";
                $value = "TRUE" ;
                $timeout=5 ;
            }
        }

        // CONTROLE jour_mois_limite_reliquats
        // si modif de jour_mois_limite_reliquats, on verifie le format ( 0 ou jj-mm) , sinon : changement impossible !
        if( ($key=="jour_mois_limite_reliquats") && ($value!= "0") )
        {
            $t=explode("-", $value);
            if(checkdate($t[1], $t[0], date("Y"))==FALSE)
            {
                echo "<b>". _('config_jour_mois_limite_reliquats_modif_impossible') ."</b><br>\n";
                $sql_date="SELECT conf_valeur FROM conges_config WHERE conf_nom='jour_mois_limite_reliquats' ";
                $ReqLog_date = SQL::query($sql_date);
                $data = $ReqLog_date->fetch_row();
                $value = $data[0] ;
                $timeout=5 ;
            }
        }

        if(preg_match("/_installed$/",$key) && ($value=="1"))
        {
            $plugin = explode("_",$key);
            $plugin = $plugin[0];
            install_plugin($plugin);
        }
        elseif(preg_match("/_installed$/",$key) && ($value=="0"))
        {
            $plugin = explode("_",$key);
            $plugin = $plugin[0];
            uninstall_plugin($plugin);
        }
        if(preg_match("/_activated$/",$key) && ($value=="1"))
        {
            $plugin = explode("_",$key);
            $plugin = $plugin[0];
            activate_plugin($plugin);
        }
        elseif(preg_match("/_activated$/",$key) && ($value=="0"))
        {
            $plugin = explode("_",$key);
            $plugin = $plugin[0];
            disable_plugin($plugin);
        }

        // Mise à jour
        $sql2 = 'UPDATE conges_config SET conf_valeur = \''.addslashes($value).'\' WHERE conf_nom =\''.SQL::quote($key).'\' ';
	$ReqLog2 = SQL::query($sql2);
    }

    $_SESSION['config']=init_config_tab();      // on re-initialise le tableau des variables de config

    // enregistrement dans les logs
    $comment_log = "nouvelle configuration de php_conges ";
    log_action(0, "", "", $comment_log, $DEBUG);

    echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"$timeout; URL=$URL\">";
}




