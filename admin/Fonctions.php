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
namespace admin;

/**
* Regroupement des fonctions liées à l'admin
*/
class Fonctions
{
    // modifie, pour un resp donné,  les groupes dont il est resp et grands_resp
    public static function modif_resp_groupes($choix_resp, &$checkbox_resp_group, &$checkbox_grd_resp_group,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        $result_insert=TRUE;
        $result_insert_2=TRUE;

        //echo "responsable : $choix_resp<br>\n";
        // on supprime tous les anciens resps du groupe puis on ajoute tous ceux qui sont dans le tableau de la checkbox
        $sql_del = 'DELETE FROM conges_groupe_resp WHERE gr_login="'. \includes\SQL::quote($choix_resp).'"';
        $ReqLog_del = \includes\SQL::query($sql_del);

        // on supprime tous les anciens grands resps du groupe puis on ajoute tous ceux qui sont dans le tableau de la checkbox
        $sql_del_2 = 'DELETE FROM conges_groupe_grd_resp WHERE ggr_login="'. \includes\SQL::quote($choix_resp).'"';
        $ReqLog_del_2 = \includes\SQL::query($sql_del_2);

        // ajout des resp qui sont dans la checkbox
        if($checkbox_resp_group!="") // si la checkbox contient qq chose
        {
            foreach($checkbox_resp_group as $gid => $value)
            {
                $sql_insert = "INSERT INTO conges_groupe_resp SET gr_gid=$gid, gr_login='$choix_resp' "  ;
                $result_insert = \includes\SQL::query($sql_insert);
            }
        }

        // ajout des grands resp qui sont dans la checkbox
        if($checkbox_grd_resp_group!="") // si la checkbox contient qq chose
        {
            foreach($checkbox_grd_resp_group as $grd_gid => $value)
            {
                $sql_insert_2 = "INSERT INTO conges_groupe_grd_resp SET ggr_gid=$grd_gid, ggr_login='$choix_resp' "  ;
                $result_insert_2 = \includes\SQL::query($sql_insert_2);
            }
        }

        if(($result_insert) && ($result_insert_2) )
            echo  _('form_modif_ok') ." !<br><br> \n";
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";

        $comment_log = "mofification groupes dont $choix_resp est responsable ou grand responsable" ;
        log_action(0, "", $choix_resp, $comment_log,  $DEBUG);

        /* APPEL D'UNE AUTRE PAGE */
        echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_gestion_groupes_responsables=resp-group\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo " </form> \n";
    }

    // affiche pour un resp des cases à cocher devant les groupes possibles pour les selectionner.
    public static function affiche_gestion_responsable_groupes($choix_resp, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_resp_groupe') ."</h1>\n";

        //echo "resp = $choix_resp<br>\n";
        /****************************/
        /* Affichage Responsable    */
        /****************************/
        // Récuperation des informations :
        $sql_r = 'SELECT u_nom, u_prenom FROM conges_users WHERE u_login="'. \includes\SQL::quote($choix_resp).'"';
        $ReqLog_r = \includes\SQL::query($sql_r);

        $resultat_r = $ReqLog_r->fetch_array();
        $sql_nom=$resultat_r["u_nom"] ;
        $sql_prenom=$resultat_r["u_prenom"] ;

        echo "<h2>Responsable : <strong>$sql_prenom $sql_nom</strong></h2>\n";
        echo "<hr/>\n";

        //on rempli un tableau de tous les groupe avec le groupename, le commentaire (tableau de tableaux à 3 cellules)
        // Récuperation des groupes :
        $tab_groupe=array();
        $sql_groupe = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename "  ;
        $ReqLog_groupe = \includes\SQL::query($sql_groupe);

        while($resultat_groupe=$ReqLog_groupe->fetch_array())
        {
            $tab_g=array();
            $tab_g["gid"]=$resultat_groupe["g_gid"];
            $tab_g["group"]=$resultat_groupe["g_groupename"];
            $tab_g["comment"]=$resultat_groupe["g_comment"];
            $tab_groupe[]=$tab_g;
        }

        //on rempli un tableau de tous les groupes a double validation avec le groupename, le commentaire (tableau de tableau à 3 cellules)
        $tab_groupe_dbl_valid=array();
        $sql_g2 = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe WHERE g_double_valid='Y' ORDER BY g_groupename "  ;
        $ReqLog_g2 = \includes\SQL::query($sql_g2);

        while($resultat_groupe_2=$ReqLog_g2->fetch_array())
        {
            $tab_g_2=array();
            $tab_g_2["gid"]=$resultat_groupe_2["g_gid"];
            $tab_g_2["group"]=$resultat_groupe_2["g_groupename"];
            $tab_g_2["comment"]=$resultat_groupe_2["g_comment"];
            $tab_groupe_dbl_valid[]=$tab_g_2;
        }

        /*****************************************************************************/

        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
        echo "<div class=\"row\">\n";
        echo "<div class=\"col-md-6\">";
        echo "<h3>Responsable</h3>\n";
        /*******************************************/
        //AFFICHAGE DU TABLEAU DES GROUPES DONT RESP EST RESPONSABLE
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        // affichage TITRE
        echo "<tr>\n";
        echo "	<th>&nbsp;</th>\n";
        echo "	<th>". _('admin_groupes_groupe') ."</th>\n";
        echo "	<th>". _('admin_groupes_libelle') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // on rempli un autre tableau des groupes dont resp est responsables
        $tab_resp=array();
        $sql_r = 'SELECT gr_gid FROM conges_groupe_resp WHERE gr_login="'. \includes\SQL::quote($choix_resp).'" ORDER BY gr_gid ';
        $ReqLog_r = \includes\SQL::query($sql_r);

        while($resultat_r=$ReqLog_r->fetch_array())
        {
            $tab_resp[]=$resultat_r["gr_gid"];
        }

        // ensuite on affiche tous les groupes avec une case cochée si exist groupename dans le 2ieme tableau
        $count = count($tab_groupe);
        for ($i = 0; $i < $count; $i++)
        {
            $gid=$tab_groupe[$i]["gid"] ;
            $group=$tab_groupe[$i]["group"] ;
            $comment=$tab_groupe[$i]["comment"] ;

            if (in_array ($gid, $tab_resp))
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_resp_group[$gid]\" value=\"$gid\" checked>";
                $class="histo-big";
            }
            else
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_resp_group[$gid]\" value=\"$gid\">";
                $class="histo";
            }

            echo '<tr class="'.(!($i%2)?'i':'p').'">';
            echo "	<td>$case_a_cocher</td>\n";
            echo "	<td class=\"$class\"> $group </td>\n";
            echo "	<td class=\"$class\"> $comment </td>\n";
            echo "</tr>\n";
        }

        echo "</tbody>\n\n";
        echo "</table>\n\n";
        /*******************************************/
        echo "</div>\n";
        // si on a configuré la double validation
        if($_SESSION['config']['double_validation_conges'])
        {
            echo "<div class=\"col-md-6\">";
            echo "<h3>Grand Responsable</h3>\n";
            /*******************************************/
            //AFFICHAGE DU TABLEAU DES GROUPES DONT RESP EST GRAND RESPONSABLE
            echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
            echo "<thead>\n";
            echo "	<th>&nbsp;</th>\n";
            echo "	<th>". _('admin_groupes_groupe') ."</th>\n";
            echo "	<th>". _('admin_groupes_libelle') ."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";

            // on rempli un autre tableau des groupes dont resp est GRAND responsables
            $tab_grd_resp=array();
            $sql_gr = 'SELECT ggr_gid FROM conges_groupe_grd_resp WHERE ggr_login="'. \includes\SQL::quote($choix_resp).'" ORDER BY ggr_gid ';
            $ReqLog_gr = \includes\SQL::query($sql_gr);

            while($resultat_gr=$ReqLog_gr->fetch_array())
            {
                $tab_grd_resp[]=$resultat_gr["ggr_gid"];
            }

            // ensuite on affiche tous les groupes avec une case cochée si exist groupename dans le 2ieme tableau
            $count = count($tab_groupe_dbl_valid);
            for ($i = 0; $i < $count; $i++)
            {
                $gid=$tab_groupe_dbl_valid[$i]["gid"] ;
                $group=$tab_groupe_dbl_valid[$i]["group"] ;
                $comment=$tab_groupe_dbl_valid[$i]["comment"] ;

                if (in_array($gid, $tab_grd_resp))
                {
                    $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_grd_resp_group[$gid]\" value=\"$gid\" checked>";
                    $class="histo-big";
                }
                else
                {
                    $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_grd_resp_group[$gid]\" value=\"$gid\">";
                    $class="histo";
                }

                echo '<tr class="'.(!($i%2)?'i':'p').'">';
                echo "	<td>$case_a_cocher</td>\n";
                echo "	<td class=\"$class\"> $group </td>\n";
                echo "	<td class=\"$class\"> $comment </td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n\n";
            echo "</table>\n\n";
            echo "</div>\n\n";
            /*******************************************/
        }

        echo "</div>\n\n";

        echo "<hr/>\n";

        echo "<input type=\"hidden\" name=\"change_responsable_group\" value=\"ok\">\n";
        echo "<input type=\"hidden\" name=\"choix_resp\" value=\"$choix_resp\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_gestion_groupes_responsables=resp-group\">". _('form_annul') ."</a>\n";
        echo "</form>\n" ;
    }

    // affiche le tableau des responsables pour choisir sur lequel on va gerer les groupes dont il est resp
    public static function affiche_choix_responsable_groupes( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_resp_groupe') ."</h1>\n";


        // Récuperation des informations :
        $sql_resp = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp='Y' AND u_login!='conges' AND u_login!='admin' ORDER BY u_nom, u_prenom"  ;
        $ReqLog_resp = \includes\SQL::query($sql_resp);

        /*************************/
        /* Choix Responsable     */
        /*************************/
        // AFFICHAGE TABLEAU
        echo "<h2>". _('admin_aff_choix_resp_titre') ."</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th>". _('divers_responsable_maj_1') ."</th>\n";
        echo "	<th>". _('divers_login') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        while ($resultat_resp = $ReqLog_resp->fetch_array())
        {

            $sql_login=$resultat_resp["u_login"] ;
            $sql_nom=$resultat_resp["u_nom"] ;
            $sql_prenom=$resultat_resp["u_prenom"] ;

            $text_choix_resp="<a href=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_resp=$sql_login\"><strong>$sql_nom&nbsp;$sql_prenom</strong></a>" ;

            echo '<tr class="'.($i?'i':'p').'">';
            echo "<td>$text_choix_resp</td>\n";
            echo "<td>$sql_login</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }
        echo "</tbody>\n\n";
        echo "</table>\n\n";
    }

    // modifie, pour un groupe donné,  ses resp et grands_resp
    public static function modif_group_responsables($choix_group, &$checkbox_group_resp, &$checkbox_group_grd_resp,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $result_insert=TRUE;
        $result_insert_2=TRUE;

        //echo "groupe : $choix_group<br>\n";
        // on supprime tous les anciens resp du groupe puis on ajoute tous ceux qui sont dans le tableau de la checkbox
        $sql_del = 'DELETE FROM conges_groupe_resp WHERE gr_gid='.\includes\SQL::quote($choix_group);
        $ReqLog_del = \includes\SQL::query($sql_del);

        // on supprime tous les anciens grand resp du groupe puis on ajoute tous ceux qui sont dans le tableau de la checkbox
        $sql_del_2 = 'DELETE FROM conges_groupe_grd_resp WHERE ggr_gid='. \includes\SQL::quote($choix_group);
        $ReqLog_del_2 = \includes\SQL::query($sql_del_2);


        // ajout des resp qui sont dans la checkbox
        if($checkbox_group_resp!="") // si la checkbox contient qq chose
        {
            foreach($checkbox_group_resp as $login => $value)
            {
                $sql_insert = "INSERT INTO conges_groupe_resp SET gr_gid=$choix_group, gr_login='$login' "  ;
                $result_insert = \includes\SQL::query($sql_insert);
            }
        }

        // ajout des grands resp qui sont dans la checkbox
        if($checkbox_group_grd_resp!="") // si la checkbox contient qq chose
        {
            foreach($checkbox_group_grd_resp as $grd_login => $grd_value)
            {
                $sql_insert_2 = "INSERT INTO conges_groupe_grd_resp SET ggr_gid=$choix_group, ggr_login='$grd_login' "  ;
                $result_insert_2 = \includes\SQL::query($sql_insert_2);
            }
        }

        if( ($result_insert) && ($result_insert_2) )
            echo  _('form_modif_ok') ." !<br><br> \n";
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";

        $comment_log = "mofification_responsables_du_groupe : $choix_group" ;
        log_action(0, "", "", $comment_log,  $DEBUG);

        /* APPEL D'UNE AUTRE PAGE */
        echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_gestion_groupes_responsables=group-resp\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo " </form> \n";
    }

    // affiche pour un groupe des cases à cocher devant les resp et grand_resp possibles pour les selectionner.
    public static function affiche_gestion_groupes_responsables($choix_group, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>" . _('admin_onglet_groupe_resp') . "</h1>\n";


        /***********************/
        /* Affichage Groupe    */
        /***********************/
        // Récuperation des informations :
        $sql_gr = 'SELECT g_groupename, g_comment, g_double_valid FROM conges_groupe WHERE g_gid='.\includes\SQL::quote($choix_group);
        $ReqLog_gr = \includes\SQL::query($sql_gr);

        $resultat_gr = $ReqLog_gr->fetch_array();
        $sql_groupename=$resultat_gr["g_groupename"] ;
        $sql_comment=$resultat_gr["g_comment"] ;
        $sql_double_valid=$resultat_gr["g_double_valid"] ;

        // AFFICHAGE NOM DU GROUPE
        echo "<h2>Groupe : <strong>$sql_groupename</strong></h2>\n";
        echo "<hr/>\n";
        //on rempli un tableau de tous les responsables avec le login, le nom, le prenom (tableau de tableau à 3 cellules
        // Récuperation des responsables :
        $tab_resp=array();
        $sql_resp = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login!='conges' AND u_is_resp='Y' ORDER BY u_nom, u_prenom "  ;
        $ReqLog_resp = \includes\SQL::query($sql_resp);

        while($resultat_resp=$ReqLog_resp->fetch_array())
        {
            $tab_r=array();
            $tab_r["login"]=$resultat_resp["u_login"];
            $tab_r["nom"]=$resultat_resp["u_nom"];
            $tab_r["prenom"]=$resultat_resp["u_prenom"];
            $tab_resp[]=$tab_r;
        }
        /*****************************************************************************/
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
        echo "<div class=\"row\">\n";
        echo "<div class=\"col-md-6\">\n";
        echo "<h3>". _('admin_gestion_groupe_resp_responsables') ."</h3>\n";

        /*******************************************/
        //AFFICHAGE DU TABLEAU DES RESPONSBLES DU GROUPE
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";

        // affichage TITRE
        echo "<tr>\n";
        echo "	<th>&nbsp;</th>\n";
        echo "	<th>". _('divers_personne_maj_1') ."</th>\n";
        echo "	<th>". _('divers_login') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // on rempli un autre tableau des responsables du groupe
        $tab_group=array();
        $sql_gr = 'SELECT gr_login FROM conges_groupe_resp WHERE gr_gid='. \includes\SQL::quote($choix_group).' ORDER BY gr_login ';
        $ReqLog_gr = \includes\SQL::query($sql_gr);

        while($resultat_gr=$ReqLog_gr->fetch_array())
        {
            $tab_group[]=$resultat_gr["gr_login"];
        }

        // ensuite on affiche tous les responsables avec une case cochée si exist login dans le 2ieme tableau
        $count = count($tab_resp);
        for ($i = 0; $i < $count; $i++)
        {
            $login=$tab_resp[$i]["login"] ;
            $nom=$tab_resp[$i]["nom"] ;
            $prenom=$tab_resp[$i]["prenom"] ;

            if (in_array ($login, $tab_group))
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_resp[$login]\" value=\"$login\" checked>";
                $class="histo-big";
            }
            else
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_resp[$login]\" value=\"$login\">";
                $class="histo";
            }

            echo '<tr class="'.(!($i%2)?'i':'p').'">';
            echo "	<td>$case_a_cocher</td>\n";
            echo "	<td class=\"$class\">$nom&nbsp;$prenom</td>\n";
            echo "	<td class=\"$class\">$login</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n\n";
        echo "</table>\n\n";
        /*******************************************/
        echo "</div>\n";
        echo "<div class=\"col-md-6\">\n";
        // si on a configuré la double validation et que le groupe considéré est a double valid
        if( ($_SESSION['config']['double_validation_conges']) && ($sql_double_valid=="Y") )
        {
            echo "<h3>". _('admin_gestion_groupe_grand_resp_responsables') ."</h3>\n";
            /*******************************************/
            //AFFICHAGE DU TABLEAU DES GRANDS RESPONSBLES DU GROUPE
            echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
            echo "<thead>\n";

            // affichage TITRE
            echo "<tr>\n";
            echo "	<th>&nbsp;</th>\n";
            echo "	<th>". _('divers_personne_maj_1') ."&nbsp;:</th>\n";
            echo "	<th>". _('divers_login') ."&nbsp;:</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";

            // on rempli un autre tableau des grands responsables du groupe
            $tab_group_grd=array();
            $sql_ggr = 'SELECT ggr_login FROM conges_groupe_grd_resp WHERE ggr_gid='. \includes\SQL::quote($choix_group).' ORDER BY ggr_login ';
            $ReqLog_ggr = \includes\SQL::query($sql_ggr);

            while($resultat_ggr=$ReqLog_ggr->fetch_array())
            {
                $tab_group_grd[]=$resultat_ggr["ggr_login"];
            }

            // ensuite on affiche tous les grands responsables avec une case cochée si exist login dans le 3ieme tableau
            $count = count($tab_resp);
            for ($i = 0; $i < $count; $i++)
            {
                $login=$tab_resp[$i]["login"] ;
                $nom=$tab_resp[$i]["nom"] ;
                $prenom=$tab_resp[$i]["prenom"] ;

                if (in_array ($login, $tab_group_grd))
                {
                    $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_grd_resp[$login]\" value=\"$login\" checked>";
                    $class="histo-big";
                }
                else
                {
                    $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_grd_resp[$login]\" value=\"$login\">";
                    $class="histo";
                }

                echo '<tr class="'.(!($i%2)?'i':'p').'">';
                echo "	<td>$case_a_cocher</td>\n";
                echo "	<td class=\"$class\">$nom&nbsp;$prenom</td>\n";
                echo "	<td class=\"$class\">$login</td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n\n";
            echo "</table>\n\n";
            /*******************************************/
        }

        echo "</div>\n";
        echo "</div>\n";
        echo "<hr/>\n";
        echo "<input type=\"hidden\" name=\"change_group_responsables\" value=\"ok\">\n";
        echo "<input type=\"hidden\" name=\"choix_group\" value=\"$choix_group\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_gestion_groupes_responsables=group-resp\">". _('form_annul') ."</a>\n";
        echo "</form>\n" ;
    }

    // affiche le tableau des groupes pour choisir sur quel groupe on va gerer les responsables
    public static function affiche_choix_groupes_responsables( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        /********************/
        /* Choix Groupe     */
        /********************/
        // Récuperation des informations :
        $sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

        // AFFICHAGE TABLEAU
        echo "<h1>" . _('admin_onglet_groupe_resp') . "</h1>\n";
        echo "<h2>" . _('admin_aff_choix_groupe_titre') . "</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th>" . _('admin_groupes_groupe') . "</th>\n";
        echo "	<th>" . _('admin_groupes_libelle') . "</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $ReqLog_gr = \includes\SQL::query($sql_gr);
        while ($resultat_gr = $ReqLog_gr->fetch_array())
        {
            $sql_gid=$resultat_gr["g_gid"] ;
            $sql_groupename=$resultat_gr["g_groupename"] ;
            $sql_comment=$resultat_gr["g_comment"] ;

            $text_choix_group="<a href=\"$PHP_SELF?session=$session&onglet=admin-group-responsables&choix_group=$sql_gid\"><strong>$sql_groupename</strong></a>" ;

            echo '<tr>';
            echo "<td>$text_choix_group</td>\n";
            echo "<td>$sql_comment</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n";
        echo "</table>\n\n";
    }

    // affichage des pages de gestion des responsables des groupes
    public static function affiche_choix_gestion_groupes_responsables($choix_group, $choix_resp, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        if( $choix_group!="" )    // si un groupe choisi : on affiche la gestion par groupe
        {
            \admin\Fonctions::affiche_gestion_groupes_responsables($choix_group, $onglet, $DEBUG);
        }
        elseif( $choix_resp!="" )     // si un resp choisi : on affiche la gestion par resp
        {
            \admin\Fonctions::affiche_gestion_responsable_groupes($choix_resp, $onglet, $DEBUG);
        }
        else    // si pas de groupe ou de resp choisi : on affiche les choix
        {
            echo "<div class=\"row\">\n";
            echo "<div class=\"col-md-6\">";
            \admin\Fonctions::affiche_choix_groupes_responsables($DEBUG);
            echo "</div>\n";
            echo "<div class=\"col-md-6\">";
            \admin\Fonctions::affiche_choix_responsable_groupes($DEBUG);
            echo "</div>\n";
            echo "</div>\n";
        }
    }

    /**
     * Encapsule le comportement du module de gestion des groupes et des responsables
     *
     * @param string $onglet Nom de l'onglet à afficher
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function groupeResponsableModule($onglet, $DEBUG = false)
    {
        $choix_group    = getpost_variable('choix_group') ;
        $choix_resp     = getpost_variable('choix_resp') ;

        $change_group_responsables	= getpost_variable('change_group_responsables') ;
        $change_responsable_group	= getpost_variable('change_responsable_group') ;

        if($change_group_responsables=="ok")
        {
            $checkbox_group_resp		= getpost_variable('checkbox_group_resp') ;
            $checkbox_group_grd_resp	= getpost_variable('checkbox_group_grd_resp') ;
            \admin\Fonctions::modif_group_responsables($choix_group, $checkbox_group_resp, $checkbox_group_grd_resp, $DEBUG);
        }
        elseif($change_responsable_group=="ok")
        {
            $checkbox_resp_group		= getpost_variable('checkbox_resp_group') ;
            $checkbox_grd_resp_group	= getpost_variable('checkbox_grd_resp_group') ;

            \admin\Fonctions::modif_resp_groupes($choix_resp, $checkbox_resp_group, $checkbox_grd_resp_group, $DEBUG);
        }
        else
        {
            \admin\Fonctions::affiche_choix_gestion_groupes_responsables($choix_group, $choix_resp, $onglet);
        }
    }

    public static function modif_user_groups($choix_user, &$checkbox_user_groups,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $result_insert=commit_modif_user_groups($choix_user, $checkbox_user_groups,  $DEBUG);

        if($result_insert)
            echo  _('form_modif_ok') ." !<br><br> \n";
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";

        $comment_log = "mofification_des groupes auxquels $choix_user appartient" ;
        log_action(0, "", $choix_user, $comment_log,  $DEBUG);

        /* APPEL D'UNE AUTRE PAGE */
        echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group-users\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo " </form> \n";
    }

    public static function modif_group_users($choix_group, &$checkbox_group_users,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // on supprime tous les anciens users du groupe puis on ajoute tous ceux qui sont dans le tableau checkbox (si il n'est pas vide)
        $sql_del = 'DELETE FROM conges_groupe_users WHERE gu_gid='. \includes\SQL::quote($choix_group).' ';
        $ReqLog_del = \includes\SQL::query($sql_del);

        if(is_array($checkbox_group_users) && count ($checkbox_group_users)!=0)
        {
            foreach($checkbox_group_users as $login => $value)
            {
                //$login=$checkbox_group_users[$i] ;
                $sql_insert = "INSERT INTO conges_groupe_users SET gu_gid=$choix_group, gu_login='$login' "  ;
                $result_insert = \includes\SQL::query($sql_insert);
            }
        }
        else
            $result_insert=TRUE;

        if($result_insert)
            echo  _('form_modif_ok') ."<br><br> \n";
        else
            echo  _('form_modif_not_ok') ."<br><br> \n";

        $comment_log = "mofification_users_du_groupe : $choix_group" ;
        log_action(0, "", "", $comment_log,  $DEBUG);

        /* APPEL D'UNE AUTRE PAGE */
        echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group-users\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo " </form> \n";
    }

    public static function affiche_gestion_groupes_users($choix_group, $onglet, $DEBUG=FALSE) {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>" . _('admin_onglet_groupe_user') . "</h1>\n";


        /************************/
        /* Affichage Groupes    */
        /************************/
        // Récuperation des informations :
        $sql_gr = 'SELECT g_groupename, g_comment FROM conges_groupe WHERE g_gid='. \includes\SQL::quote($choix_group);
        $ReqLog_gr = \includes\SQL::query($sql_gr);
        $resultat_gr = $ReqLog_gr->fetch_array();
        $sql_group=$resultat_gr["g_groupename"] ;
        $sql_comment=$resultat_gr["g_comment"] ;


        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';

        //AFFICHAGE DU TABLEAU DES USERS DU GROUPE
        echo "<h2>". _('admin_gestion_groupe_users_membres') . " <strong>$sql_group</strong>, $sql_comment</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";

        // affichage TITRE
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th></th>\n";
        echo "	<th>". _('divers_personne_maj_1') ."</th>\n";
        echo "	<th>". _('divers_login') . "</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // affichage des users

        //on rempli un tableau de tous les users avec le login, le nom, le prenom (tableau de tableau à 3 cellules
        // Récuperation des utilisateurs :
        $tab_users=array();
        $sql_users = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom, u_prenom "  ;
        $ReqLog_users = \includes\SQL::query($sql_users);

        while($resultat_users=$ReqLog_users->fetch_array())
        {
            $tab_u=array();
            $tab_u["login"]=$resultat_users["u_login"];
            $tab_u["nom"]=$resultat_users["u_nom"];
            $tab_u["prenom"]=$resultat_users["u_prenom"];
            $tab_users[]=$tab_u;
        }
        // on rempli un autre tableau des users du groupe
        $tab_group=array();
        $sql_gu = 'SELECT gu_login FROM conges_groupe_users WHERE gu_gid="'. \includes\SQL::quote($choix_group).'" ORDER BY gu_login ';
        $ReqLog_gu = \includes\SQL::query($sql_gu);

        while($resultat_gu=$ReqLog_gu->fetch_array())
        {
            $tab_group[]=$resultat_gu["gu_login"];
        }

        // ensuite on affiche tous les users avec une case cochée si exist login dans le 2ieme tableau
        $count = count($tab_users);
        for ($i = 0; $i < $count; $i++)
        {
            $login=$tab_users[$i]["login"] ;
            $nom=$tab_users[$i]["nom"] ;
            $prenom=$tab_users[$i]["prenom"] ;

            if (in_array ($login, $tab_group))
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_users[$login]\" value=\"$login\" checked>";
                $class="histo-big";
            }
            else
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_group_users[$login]\" value=\"$login\">";
                $class="histo";
            }

            echo '<tr class="'.(!($i%2)?'i':'p').'">';
            echo "	<td>$case_a_cocher</td>\n";
            echo "	<td class=\"$class\">$nom $prenom</td>\n";
            echo "	<td class=\"$class\">$login</td>\n";
            echo "</tr>\n";
        }

        echo "<tbody>\n";
        echo "</table>\n\n";
        echo "<hr/>\n";
        echo "<input type=\"hidden\" name=\"change_group_users\" value=\"ok\">\n";
        echo "<input type=\"hidden\" name=\"choix_group\" value=\"$choix_group\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group-users\">". _('form_annul') ."</a>\n";
        echo "</form>\n" ;
    }

    public static function affiche_choix_groupes_users($DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_groupe_user') ."</h1>\n\n";


        /********************/
        /* Choix Groupe     */
        /********************/
        // Récuperation des informations :
        $sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

        // AFFICHAGE TABLEAU
        echo "<h2>". _('admin_aff_choix_groupe_titre') ."</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th>". _('admin_groupes_groupe') ."</th>\n";
        echo "	<th>". _('admin_groupes_libelle') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        $ReqLog_gr = \includes\SQL::query($sql_gr);
        while ($resultat_gr = $ReqLog_gr->fetch_array())
        {

            $sql_gid=$resultat_gr["g_gid"] ;
            $sql_group=$resultat_gr["g_groupename"] ;
            $sql_comment=$resultat_gr["g_comment"] ;

            $choix_group="<a href=\"$PHP_SELF?session=$session&onglet=admin-group-users&choix_group=$sql_gid\"><b>$sql_group</b></a>" ;

            echo '<tr class="'.($i?'i':'p').'">';
            echo "<td><b>$choix_group</b></td>\n";
            echo "<td>$sql_comment</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }
        echo "</tbody>\n";
        echo "</table>\n\n";
    }

    public static function affiche_gestion_user_groupes($choix_user, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_user_groupe') ."</h1>\n\n";


        /************************/
        /* Affichage Groupes    */
        /************************/

        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';

        \admin\Fonctions::affiche_tableau_affectation_user_groupes($choix_user,  $DEBUG);

        echo "<hr/>\n";

        echo "<input type=\"hidden\" name=\"change_user_groups\" value=\"ok\">\n";
        echo "<input type=\"hidden\" name=\"choix_user\" value=\"$choix_user\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group-users\">". _('form_annul') ."</a>\n";
        echo "</form>\n" ;
    }

    public static function affiche_choix_user_groupes( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_user_groupe') ."</h1>\n";


        /********************/
        /* Choix User       */
        /********************/
        // Récuperation des informations :
        $sql_user = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom, u_prenom"  ;

        // AFFICHAGE TABLEAU
        echo "<h2>". _('admin_aff_choix_user_titre') ."</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th>" . _('divers_nom_maj_1') ."  ". _('divers_prenom_maj_1') . "</th>\n";
        echo "<th>" . _('divers_login_maj_1') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        $ReqLog_user = \includes\SQL::query($sql_user);
        while ($resultat_user = $ReqLog_user->fetch_array())
        {

            $sql_login=$resultat_user["u_login"] ;
            $sql_nom=$resultat_user["u_nom"] ;
            $sql_prenom=$resultat_user["u_prenom"] ;

            $choix="<a href=\"$PHP_SELF?session=$session&onglet=admin-group-users&choix_user=$sql_login\"><b>$sql_nom $sql_prenom</b></a>" ;

            echo '<tr class="'.($i?'i':'p').'">';
            echo "<td>$choix</td>\n";
            echo "<td>$sql_login</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }
        echo "</tbody>\n\n";
        echo "</table>\n\n";
    }

    public static function affiche_tableau_affectation_user_groupes($choix_user,  $DEBUG=FALSE)
    {
        echo "<h2>" . _('admin_gestion_groupe_users_group_of_user') . (($choix_user!="") ? " <strong> $choix_user </strong>" : '') . "</h2>\n";

        //AFFICHAGE DU TABLEAU DES GROUPES DU USER
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th></th>\n";
        echo "	<th>" . _('admin_groupes_groupe') . "</th>\n";
        echo "	<th>" . _('admin_groupes_libelle') . "</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // affichage des groupes

        //on rempli un tableau de tous les groupes avec le nom et libellé (tableau de tableau à 3 cellules)
        $tab_groups=array();
        $sql_g = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename "  ;
        $ReqLog_g = \includes\SQL::query($sql_g);

        while($resultat_g=$ReqLog_g->fetch_array())
        {
            $tab_gg=array();
            $tab_gg["gid"]=$resultat_g["g_gid"];
            $tab_gg["groupename"]=$resultat_g["g_groupename"];
            $tab_gg["comment"]=$resultat_g["g_comment"];
            $tab_groups[]=$tab_gg;
        }

        $tab_user="";
        // si le user est connu
        // on rempli un autre tableau des groupes du user
        if($choix_user!="")
        {
            $tab_user=array();
            $sql_gu = 'SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'. \includes\SQL::quote($choix_user).'" ORDER BY gu_gid ';
            $ReqLog_gu = \includes\SQL::query($sql_gu);

            while($resultat_gu=$ReqLog_gu->fetch_array())
            {
                $tab_user[]=$resultat_gu["gu_gid"];
            }
        }

        // ensuite on affiche tous les groupes avec une case cochée si existe le gid dans le 2ieme tableau
        $count = count($tab_groups);
        for ($i = 0; $i < $count; $i++)
        {
            $gid=$tab_groups[$i]["gid"] ;
            $group=$tab_groups[$i]["groupename"] ;
            $libelle=$tab_groups[$i]["comment"] ;

            if ( ($tab_user!="") && (in_array ($gid, $tab_user)) )
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\" checked>";
                $class="histo-big";
            }
            else
            {
                $case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\">";
                $class="histo";
            }

            echo '<tr class="'.(!($i%2)?'i':'p').'">';
            echo "	<td>$case_a_cocher</td>\n";
            echo "	<td class=\"$class\">$group&nbsp</td>\n";
            echo "	<td class=\"$class\">$libelle</td>\n";
            echo "</tr>\n";
        }

        echo "<tbody>\n";
        echo "</table>\n\n";
    }

    public static function affiche_choix_gestion_groupes_users($choix_group, $choix_user, $onglet,$DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];


        if( $choix_group!="" )     // si un groupe choisi : on affiche la gestion par groupe
        {
            \admin\Fonctions::affiche_gestion_groupes_users($choix_group, $onglet, $DEBUG);
        }
        elseif( $choix_user!="" )     // si un user choisi : on affiche la gestion par user
        {
            \admin\Fonctions::affiche_gestion_user_groupes($choix_user, $onglet, $DEBUG);
        }
        else    // si pas de groupe ou de user choisi : on affiche les choix
        {
            echo "<div class=\"row\">\n";
            echo "<div class=\"col-md-6\">\n";
            \admin\Fonctions::affiche_choix_groupes_users($DEBUG);
            echo "</div>\n";
            echo "<div class=\"col-md-6\">\n";
            \admin\Fonctions::affiche_choix_user_groupes($DEBUG);
            echo "</div>\n";
            echo "</div>\n";
        }
    }

    /**
     * Encapsule le comportement du module de la gestion de groupes et d'utilisateurs
     *
     * @param string $onglet Nom de l'onglet à afficher
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function groupUserModule($onglet, $DEBUG = false)
    {
        $change_group_users	= getpost_variable('change_group_users') ;
        $change_user_groups	= getpost_variable('change_user_groups') ;
        $choix_group		= getpost_variable('choix_group') ;
        $choix_user			= getpost_variable('choix_user') ;

        if($change_group_users=="ok")
        {
            $checkbox_group_users	= getpost_variable('checkbox_group_users');
            \admin\Fonctions::modif_group_users($choix_group, $checkbox_group_users, $DEBUG);
        }
        elseif($change_user_groups=="ok")
        {
            $checkbox_user_groups	= getpost_variable('checkbox_user_groups');
            \admin\Fonctions::modif_user_groups($choix_user, $checkbox_user_groups,  $DEBUG);
        }
        else
        {
            \admin\Fonctions::affiche_choix_gestion_groupes_users($choix_group, $choix_user, $onglet, $DEBUG);
        }
    }

    // recup le nombre de users d'un groupe donné
    public static function get_nb_users_du_groupe($group_id,  $DEBUG=FALSE)
    {

        $sql1='SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid = '. \includes\SQL::quote($group_id).' ORDER BY gu_login ';
        $ReqLog1 = \includes\SQL::query($sql1);

        $nb_users = $ReqLog1->num_rows;

        return $nb_users;

    }

    public static function verif_new_param_group($new_group_name, $new_group_libelle, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // verif des parametres reçus :
        if(strlen($new_group_name)==0) {
            echo "<H3> ". _('admin_verif_param_invalides') ." </H3>\n" ;
            echo "$new_group_name --- $new_group_libelle<br>\n";
            echo "<form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"new_group_name\" value=\"$new_group_name\">\n";
            echo "<input type=\"hidden\" name=\"new_group_libelle\" value=\"$new_group_libelle\">\n";

            echo "<input type=\"hidden\" name=\"saisie_group\" value=\"faux\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</form>\n" ;

            return 1;
        }
        else {
            // verif si le groupe demandé n'existe pas déjà ....
            $sql_verif='select g_groupename from conges_groupe where g_groupename="'. \includes\SQL::quote($new_group_name).'" ';
            $ReqLog_verif = \includes\SQL::query($sql_verif);
            $num_verif = $ReqLog_verif->num_rows;
            if ($num_verif!=0)
            {
                echo "<H3> ". _('admin_verif_groupe_invalide') ." </H3>\n" ;
                echo "<form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\">\n" ;
                echo "<input type=\"hidden\" name=\"new_group_name\" value=\"$new_group_name\">\n";
                echo "<input type=\"hidden\" name=\"new_group_libelle\" value=\"$new_group_libelle\">\n";

                echo "<input type=\"hidden\" name=\"saisie_group\" value=\"faux\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n" ;

                return 1;
            }
            else
                return 0;
        }
    }

    public static function ajout_groupe($new_group_name, $new_group_libelle, $new_group_double_valid,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        if(\admin\Fonctions::verif_new_param_group($new_group_name, $new_group_libelle,  $DEBUG)==0)  // verif si les nouvelles valeurs sont coohérentes et n'existe pas déjà
        {
            $ngm=stripslashes($new_group_name);
            echo "$ngm --- $new_group_libelle<br>\n";

            $sql1 = "INSERT INTO conges_groupe SET g_groupename='$new_group_name', g_comment='$new_group_libelle', g_double_valid ='$new_group_double_valid' " ;
            $result = \includes\SQL::query($sql1);

            $new_gid= \includes\SQL::getVar('insert_id');
            // par défaut le responsable virtuel est resp de tous les groupes !!!
            // $sql2 = "INSERT INTO conges_groupe_resp SET gr_gid=$new_gid, gr_login='conges' " ;
            // $result = SQL::query($sql2);

            if($result)
                echo  _('form_modif_ok') ."<br><br> \n";
            else
                echo  _('form_modif_not_ok') ."<br><br> \n";

            $comment_log = "ajout_groupe : $new_gid / $new_group_name / $new_group_libelle (double_validation : $new_group_double_valid)" ;
            log_action(0, "", "", $comment_log, $DEBUG);

            /* APPEL D'UNE AUTRE PAGE */
            echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\"> \n";
            echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
            echo " </form> \n";
        }
    }

    public static function affiche_gestion_groupes($new_group_name, $new_group_libelle, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        echo "<h1>". _('admin_onglet_gestion_groupe') ."</h1>\n\n";

        /*********************/
        /* Etat Groupes	   */
        /*********************/
        // Récuperation des informations :
        $sql_gr = "SELECT g_gid, g_groupename, g_comment, g_double_valid FROM conges_groupe ORDER BY g_groupename"  ;

        // AFFICHAGE TABLEAU
        echo "<h2>". _('admin_gestion_groupe_etat') ."</h2>\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "    <th>". _('admin_groupes_groupe') ."</th>\n";
        echo "    <th>". _('admin_groupes_libelle') ."</th>\n";
        echo "    <th>". _('admin_groupes_nb_users') ."</th>\n";
        if($_SESSION['config']['double_validation_conges'])
            echo "    <th>". _('admin_groupes_double_valid') ."</th>\n";
        echo "    <th></th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        $ReqLog_gr = \includes\SQL::query($sql_gr);
        while ($resultat_gr = $ReqLog_gr->fetch_array())
        {
            $sql_gid=$resultat_gr["g_gid"] ;
            $sql_group=$resultat_gr["g_groupename"] ;
            $sql_comment=$resultat_gr["g_comment"] ;
            $sql_double_valid=$resultat_gr["g_double_valid"] ;
            $nb_users_groupe = \admin\Fonctions::get_nb_users_du_groupe($sql_gid, $DEBUG);

            $admin_modif_group="<a href=\"admin_index.php?onglet=modif_group&session=$session&group=$sql_gid\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>" ;
            $admin_suppr_group="<a href=\"admin_index.php?onglet=suppr_group&session=$session&group=$sql_gid\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>" ;

            echo '<tr class="'.($i?'i':'p').'">';
            echo "<td><b>$sql_group</b></td>\n";
            echo "<td>$sql_comment</td>\n";
            echo "<td>$nb_users_groupe</td>\n";
            if($_SESSION['config']['double_validation_conges'])
                echo "<td>$sql_double_valid</td>\n";
            echo "<td class=\"action\">$admin_modif_group $admin_suppr_group</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }
        echo "</tbody>\n\n";
        echo "</table>\n\n";

        echo "<hr/>\n";

        /*********************/
        /* Ajout Groupe      */
        /*********************/

        // TITRE

        echo "<h2>". _('admin_groupes_new_groupe') ."</h2>\n";
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
        echo "<table class=\"tablo\">\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th><b>". _('admin_groupes_groupe') ."</b></th>\n";
        echo "<th>". _('admin_groupes_libelle') ." / ". _('divers_comment_maj_1') ."</th>\n";
        if($_SESSION['config']['double_validation_conges'])
            echo "    <th>". _('admin_groupes_double_valid') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $text_groupname="<input class=\"form-control\" type=\"text\" name=\"new_group_name\" size=\"30\" maxlength=\"50\" value=\"".$new_group_name."\">" ;
        $text_libelle="<input class=\"form-control\" type=\"text\" name=\"new_group_libelle\" size=\"50\" maxlength=\"250\" value=\"".$new_group_libelle."\">" ;

        echo "<tr>\n";
        echo "<td>$text_groupname</td>\n";
        echo "<td>$text_libelle</td>\n";
        if($_SESSION['config']['double_validation_conges'])
        {
            $text_double_valid="<select class=\"form-control\" name=\"new_group_double_valid\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
            echo "<td>$text_double_valid</td>\n";
        }
        echo "</tr>\n";
        echo "</tbody>\n";
        echo "</table>";

        echo "<hr>\n";
        echo "<input type=\"hidden\" name=\"saisie_group\" value=\"ok\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        // echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;
    }
    
    /**
     * Encapsule le comportement du module de gestion des groupes
     *
     * @param string $onglet Nom de l'onglet à afficher
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function groupeModule($onglet, $DEBUG = false)
    {
        $saisie_group   		= getpost_variable('saisie_group') ;
        $new_group_name			= addslashes( getpost_variable('new_group_name')) ;
        $new_group_libelle		= addslashes( getpost_variable('new_group_libelle')) ;
        $new_group_double_valid	= getpost_variable('new_group_double_valid') ;

        if($saisie_group=="ok")
        {
            \admin\Fonctions::ajout_groupe($new_group_name, $new_group_libelle, $new_group_double_valid,  $DEBUG);
        }
        else
        {
            \admin\Fonctions::affiche_gestion_groupes($new_group_name, $new_group_libelle, $onglet, $DEBUG);
        }
    }

    /**
     * Encapsule le comportement du module de gestion des utilisateurs
     *
     * @param string $session
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function userModule($session, $DEBUG = false)
    {
        echo "<h1> ". _('admin_onglet_gestion_user') ."</h1>\n";

        /*********************/
        /* Etat Utilisateurs */
        /*********************/

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges($DEBUG);

        // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
        if ( $_SESSION['config']['gestion_conges_exceptionnels'] )
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

        // AFFICHAGE TABLEAU
        // echo "<h3><font color=\"red\">". _('admin_users_titre') ." :</font></h3>\n";

        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th>". _('user') ."</th>\n";
        echo "<th>". _('divers_quotite_maj_1') ."</th>\n";
        foreach($tab_type_conges as $id_type_cong => $libelle)
        {
            echo "<th>$libelle / ". _('divers_an') ."</th>\n";
            echo "<th>". _('divers_solde') ." $libelle</th>\n";
        }

        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
            {
                echo "<th>". _('divers_solde') ." $libelle</th>\n";
            }
        }
        echo "<th></th>\n";
        echo "<th></th>\n";
        if($_SESSION['config']['admin_change_passwd'])
            echo "<th></th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // Récuperation des informations des users:
        $tab_info_users=array();
        // si l'admin peut voir tous les users  OU si l'admin n'est pas responsable
        if( $_SESSION['config']['admin_see_all'] || !is_resp($_SESSION['userlogin']) )
            $tab_info_users = recup_infos_all_users($DEBUG);
        else
            $tab_info_users = recup_infos_all_users_du_resp($_SESSION['userlogin'], $DEBUG);


        $i = true;
        foreach($tab_info_users as $current_login => $tab_current_infos)
        {


            $admin_modif_user="<a href=\"admin_index.php?onglet=modif_user&session=$session&u_login=$current_login\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>" ;
            $admin_suppr_user="<a href=\"admin_index.php?onglet=suppr_user&session=$session&u_login=$current_login\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>" ;
            $admin_chg_pwd_user="<a href=\"admin_index.php?onglet=chg_pwd_user&session=$session&u_login=$current_login\" title=\"". _('form_password') ."\"><i class=\"fa fa-key\"></i></a>" ;


            echo '<tr class="' . (($tab_current_infos['is_active']=='Y') ? 'actif' : 'inactif') . '">';
            echo "<td class=\"utilisateur\"><strong>" . $tab_current_infos['nom'] . " " . $tab_current_infos['prenom'] ."</strong>";
            echo '<span class="login">' . $current_login . "</span>";
            if($_SESSION['config']['where_to_find_user_email']=="dbconges")
                echo "<span class=\"mail\">".$tab_current_infos['email']."</span>\n";
            // droit utilisateur
            $rights = array();
            if($tab_current_infos['is_admin'] == 'Y')
                $rights[] = 'administrateur';
            if($tab_current_infos['is_resp'] == 'Y')
                $rights[] = 'responsable';
            if($tab_current_infos['is_hr'] == 'Y')
                $rights[] = 'RH';
            if($tab_current_infos['see_all'] == 'Y')
                $rights[] = 'voit tout';

            if(count($rights) > 0) 
                echo "<span class=\"rights\"> " . implode(', ', $rights) . "</span>";

            echo "<span class=\"responsable\"> responsable : <strong>" . $tab_current_infos['resp_login'] . "</strong></span>";

            echo "</td>\n";
            echo "<td>".$tab_current_infos['quotite']."%</td>\n";

            //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
            $tab_conges=$tab_current_infos['conges'];

            foreach($tab_type_conges as $id_conges => $libelle)
            {
                if (isset($tab_conges[$libelle]))
                {
                    echo "<td>".$tab_conges[$libelle]['nb_an']."</td>\n";
                    echo "<td>".$tab_conges[$libelle]['solde']."</td>\n";
                }
                else
                {
                    echo "<td>0</td>\n";
                    echo "<td>0</td>\n";
                }
            }

            if ($_SESSION['config']['gestion_conges_exceptionnels'])
            {
                foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
                {
                    if (isset($tab_conges[$libelle]))
                        echo "<td>".$tab_conges[$libelle]['solde']."</td>\n";
                    else
                        echo "<td>0</td>\n";
                }
            }

            echo "<td>$admin_modif_user</td>\n";
            echo "<td>$admin_suppr_user</td>\n";
            if(($_SESSION['config']['admin_change_passwd']) && ($_SESSION['config']['how_to_connect_user'] == "dbconges"))
                echo "<td>$admin_chg_pwd_user</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }

        echo "</tbody>\n";
        echo"</table>\n\n";
        echo "<br>\n";
    }
}
