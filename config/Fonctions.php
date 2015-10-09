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
namespace config;

/**
 * Regroupement des fonctions liées à la configuration
 *
 */
class Fonctions
{
    public static function commit_vider_table_logs($session, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;
        $PHP_SELF=$_SERVER['PHP_SELF'];

        $sql_delete="TRUNCATE TABLE conges_logs ";
        $ReqLog_delete = \includes\SQL::query($sql_delete);

        // ecriture de cette action dans les logs
        $comment_log = "effacement des logs de php_conges ";
        log_action(0, "", "", $comment_log, $DEBUG);

        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";
        if($session=="")
            redirect( ROOT_PATH .'config/index.php?onglet=logs');
        else
            redirect( ROOT_PATH .'config/index.php?session='.$session . '&onglet=logs');
    }

    public static function confirmer_vider_table_logs($session, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;
        $PHP_SELF=$_SERVER['PHP_SELF'];

        echo "<center>\n";
        echo "<br><h2>". _('confirm_vider_logs') ."</h2><br>\n";
        echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
        echo "<input type=\"hidden\" name=\"action\" value=\"commit_suppr_logs\">\n";
        echo "<input type=\"submit\" value=\"". _('form_delete_logs') ."\">\n";
        echo "</form>\n" ;
        echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
        echo "<input type=\"submit\" value=\"". _('form_cancel') ."\">\n";
        echo "</form>\n" ;
        echo "</center>\n";
    }

    public static function affichage($login_par, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        //requête qui récupère les logs
        $sql1 = "SELECT log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date FROM conges_logs ";
        if($login_par!="")
            $sql1 = $sql1." WHERE log_user_login_par = '$login_par' ";
        $sql1 = $sql1." ORDER BY log_date";

        $ReqLog1 = \includes\SQL::query($sql1);

        if($ReqLog1->num_rows !=0)
        {

            if($session=="")
                echo "<form action=\"$PHP_SELF?onglet=logs\" method=\"POST\"> \n";
            else
                echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\"> \n";

            echo "<br>\n";
            echo "<table class=\"table table-hover table-stripped table-condensed\">\n";

            echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_les_logs_par') ."</td>";
            if($login_par!="")
                echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_tous_les_logs') ." <a href=\"$PHP_SELF?session=$session&onglet=logs\">". _('voir_tous_les_logs') ."</a></td>";
            echo "<tr><td class=\"histo\" colspan=\"5\">&nbsp;</td>";

            // titres
            echo "<tr>\n";
            echo "<td>". _('divers_date_maj_1') ."</td>\n";
            echo "<td>". _('divers_fait_par_maj_1') ."</td>\n";
            echo "<td>". _('divers_pour_maj_1') ."</td>\n";
            echo "<td>". _('divers_comment_maj_1') ."</td>\n";
            echo "<td>". _('divers_etat_maj_1') ."</td>\n";
            echo "</tr>\n";

            // affichage des logs
            while ($data = $ReqLog1->fetch_array())
            {
                $log_login_par = $data['log_user_login_par'];
                $log_login_pour = $data['log_user_login_pour'];
                $log_log_etat = $data['log_etat'];
                $log_log_comment = $data['log_comment'];
                $log_log_date = $data['log_date'];

                echo "<tr>\n";
                echo "<td>$log_log_date</td>\n";
                echo "<td><a href=\"$PHP_SELF?session=$session&onglet=logs&login_par=$log_login_par\"><b>$log_login_par</b></a></td>\n";
                echo "<td>$log_login_pour</td>\n";
                echo "<td>$log_log_comment</td>\n";
                echo "<td>$log_log_etat</td>\n";
                echo "</tr>\n";
            }

            echo "</table>\n";

            // affichage du bouton pour vider les logs
            echo "<input type=\"hidden\" name=\"action\" value=\"suppr_logs\">\n";
            echo "<input class=\"btn btn-danger\" type=\"submit\"  value=\"". _('form_delete_logs') ."\"><br>";
            echo "</form>\n";
        }
        else
            echo  _('no_logs_in_db') ."><br>";
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function logModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);

        if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


        /*** initialisation des variables ***/
        /************************************/

        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action         = getpost_variable('action', "") ;
        $login_par      = getpost_variable('login_par', "") ;

        /*************************************/

        // header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);


        if($action=="suppr_logs")
            \config\Fonctions::confirmer_vider_table_logs($session, $DEBUG);
        elseif($action=="commit_suppr_logs")
            \config\Fonctions::commit_vider_table_logs($session, $DEBUG);
        else
            \config\Fonctions::affichage($login_par, $session, $DEBUG);
        // bottom();
    }

    public static function commit_modif($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";


        // update de la table
        foreach($tab_new_values as $nom_mail => $tab_mail)
        {
            $subject = addslashes($tab_mail['subject']);
            $body = addslashes($tab_mail['body']) ;
            $req_update='UPDATE conges_mail SET mail_subject=\''.$subject.'\', mail_body=\''.$body.'\' WHERE mail_nom="'. \includes\SQL::quote($nom_mail).'" ';
            $result1 = \includes\SQL::query($req_update);
        }
        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

        $comment_log = "configuration des mails d\'alerte";
        log_action(0, "", "", $comment_log, $DEBUG);

        if( $DEBUG )
            echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
        else
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
    }

    public static function test_config($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";

        // update de la table
        $mail_array             = find_email_adress_for_user($_SESSION['userlogin'], $DEBUG);
        $mail_sender_name       = $mail_array[0];
        $mail_sender_addr       = $mail_array[1];
        constuct_and_send_mail("valid_conges", "Test email", $mail_sender_addr, $mail_sender_name, $mail_sender_addr, "test", $DEBUG);
        //  echo "<p>Mail sent</p>"; exit(0);

        echo "<span class = \"messages\">". _('Mail_test_ok') ."</span><br>";

        $comment_log = "test d\'envoi mail d\'alerte";
        log_action(0, "", "", $comment_log, $DEBUG);

        if( $DEBUG )
            echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
        else
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
    }

    public static function affichage_config_mail($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";

        /**************************************/
        // affichage du titre
        echo "<div class=\"alert alert-info\"> ". _('config_mail_alerte_config') ."</div>\n";
        /**************************************/

        // affichage de la liste des type d'absence existants

        //requête qui récupère les informations de la table conges_type_absence
        $sql1 = "SELECT * FROM conges_mail ";
        $ReqLog1 = \includes\SQL::query($sql1);

        echo "<form method=\"POST\" action=\"$URL\"> \n";
        echo "<input type=\"hidden\" name=\"action\" value=\"test\" /> \n";
        echo "<input class=\"btn btn-success\" type=\"submit\"  value=\"". _('test_mail_config') ."\"><br>\n";
        echo _('test_mail_comment');
        echo "</form> \n";

        echo "    <form action=\"$URL\" method=\"POST\"> \n";
        while ($data = $ReqLog1->fetch_array())
        {
            $mail_nom = stripslashes($data['mail_nom']);
            $mail_subject = stripslashes($data['mail_subject']);
            $mail_body = stripslashes($data['mail_body']);

            $legend =$mail_nom ;
            $key = $mail_nom."_comment";
            $comment =  _($key)  ;

            echo "<br>\n";
            echo "<table>\n";
            echo "<tr><td>\n";
            echo "    <fieldset class=\"cal_saisie\">\n";
            echo "    <legend class=\"boxlogin\">$legend</legend>\n";
            echo "    <i>$comment</i><br><br>\n";
            echo "    <table>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_subject') ."</b></td>\n";
            echo "    	<td class=\"config\"><input class=\"form-control\" type=\"text\" size=\"80\" name=\"tab_new_values[$mail_nom][subject]\" value=\"$mail_subject\"></td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_body') ."</b></td>\n";
            echo "    	<td class=\"config\"><textarea class=\"form-control\" rows=\"6\" cols=\"80\" name=\"tab_new_values[$mail_nom][body]\" value=\"$mail_body\">$mail_body</textarea></td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\">&nbsp;</td>\n";
            echo "    	<td class=\"config\">\n";
            echo "    		<i>". _('mail_remplace_url_accueil_comment') ."<br>\n";
            echo "    		". _('mail_remplace_sender_name_comment') ."<br>\n";
            echo "    		". _('mail_remplace_destination_name_comment') ."<br>\n";
            echo "    		". _('mail_remplace_nb_jours') ."<br>\n";
            echo "    		". _('mail_remplace_date_debut') ."<br>\n";
            echo "    		". _('mail_remplace_date_fin') ."<br>\n";
            echo "    		". _('mail_remplace_commentaire') ."<br>\n";
            echo "    		". _('mail_remplace_type_absence') ."<br>\n";
            echo "    		". _('mail_remplace_retour_ligne_comment') ."</i>\n";
            echo "    	</td>\n";
            echo "    </tr>\n";

            echo "    </table>\n";
            echo "</td></tr>\n";
            echo "</table>\n";
        }

        echo "    <input type=\"hidden\" name=\"action\" value=\"modif\">\n";
        echo "<hr/>\n";
        echo "    <input class=\"btn btn-success\" type=\"submit\"  value=\"". _('form_save_modif') ."\"><br>\n";
        echo "    </form>\n";
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function mailModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);


        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');

        /*************************************/

        if($DEBUG)
        {
            print_r($tab_new_values); echo "<br>\n";
            echo "$action<br>\n";
        }

        /*********************************/
        /*********************************/

        if($action=="modif")
            \config\Fonctions::commit_modif($tab_new_values, $session, $DEBUG);
        if($action=="test")
            \config\Fonctions::test_config($tab_new_values, $session, $DEBUG);


        \config\Fonctions::affichage_config_mail($tab_new_values, $session, $DEBUG);

        /*********************************/
        /*********************************/

        // bottom();
    }
}
