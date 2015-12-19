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
        if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" || is_hr($_SESSION['userlogin']) )
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

    public static function commit_update($u_login_to_update, $new_pwd1, $new_pwd2, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        if( (strlen($new_pwd1)!=0) && (strlen($new_pwd2)!=0) && (strcmp($new_pwd1, $new_pwd2)==0) )
        {

            $passwd_md5=md5($new_pwd1);
            $sql1 = 'UPDATE conges_users  SET u_passwd=\''.$passwd_md5.'\' WHERE u_login="'. \includes\SQL::quote($u_login_to_update).'"' ;
            $result = \includes\SQL::query($sql1);

            if($result)
                echo  _('form_modif_ok') ." !<br><br> \n";
            else
                echo  _('form_modif_not_ok') ." !<br><br> \n";

            $comment_log = "admin_change_password_user : pour $u_login_to_update" ;
            log_action(0, "", $u_login_to_update, $comment_log, $DEBUG);

            if( $DEBUG )
            {
                echo "<form action=\"admin_index.php?session=$session&onglet=admin-users\" method=\"POST\">\n" ;
                echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
                echo "</form>\n" ;
            }
            else
            {
                /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-users\">";
            }

        }
        else
        {
            echo "<H3> ". _('admin_verif_param_invalides') ." </H3>\n" ;
            echo "<form action=\"$PHP_SELF?session=$session&onglet=chg_pwd_user\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"u_login\" value=\"$u_login_to_update\">\n";

            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</form>\n" ;
        }
    }

    public static function modifier($u_login, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        /********************/
        /* Etat utilisateur */
        /********************/
        // AFFICHAGE TABLEAU
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&u_login_to_update='.$u_login.'" method="POST">';
        echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
        echo '<thead>';
        echo '<tr>';
        echo "<th>". _('divers_login_maj_1') ."</th>\n";
        echo "<th>". _('divers_nom_maj_1') ."</th>\n";
        echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
        echo "<th>". _('admin_users_password_1') ."</th>\n";
        echo "<th>". _('admin_users_password_2') ."</th>\n";
        echo "</tr>\n";
        echo '</thead>';
        echo '<tbody>';

        echo "<tr align=\"center\">\n";

        // Récupération des informations
        $sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array()) {
            $text_pwd1="<input type=\"password\" name=\"new_pwd1\" size=\"10\" maxlength=\"30\" value=\"\">" ;
            $text_pwd2="<input type=\"password\" name=\"new_pwd2\" size=\"10\" maxlength=\"30\" value=\"\">" ;
            echo  "<td>".$resultat1["u_login"]."</td><td>".$resultat1["u_nom"]."</td><td>".$resultat1["u_prenom"]."</td><td>$text_pwd1</td><td>$text_pwd2</td>\n";
        }
        echo "<tr>\n";
        echo '<tbody>';
        echo "</table>\n\n";
        echo "<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "</form>\n" ;

        echo "<form action=\"admin_index.php?session=$session&onglet=admin-users\" method=\"POST\">\n" ;
        echo "<input type=\"submit\" value=\"". _('form_cancel') ."\">\n";
        echo "</form>\n"  ;
    }

    /**
     * Encapsule le comportement du module de gestion des groupes et des responsables
     *
     * @param string $onglet Nom de l'onglet à afficher
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function changeMotDePasseUserModule($onglet, $session, $DEBUG = false)
    {
        /*************************************/
        // recup des parametres reçus :
        // SERVER

        $u_login            = getpost_variable('u_login') ;
        $u_login_to_update  = getpost_variable('u_login_to_update') ;
        $new_pwd1           = getpost_variable('new_pwd1') ;
        $new_pwd2           = getpost_variable('new_pwd2') ;
        /*************************************/


        if($u_login!="")
        {
            echo "<H1>". _('admin_chg_passwd_titre') ." : $u_login .</H1>\n\n";
            \admin\Fonctions::modifier($u_login, $onglet, $DEBUG);
        }
        else
        {
            if($u_login_to_update!="") {
                echo "<H1>". _('admin_chg_passwd_titre') ." : $u_login_to_update .</H1>\n\n";
                \admin\Fonctions::commit_update($u_login_to_update, $new_pwd1, $new_pwd2, $DEBUG);
            }
            else {
                // renvoit sur la page principale .
                redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
            }
        }
    }

// recup des data d'une table sous forme de INSERT ...
    public static function get_table_data($table,  $DEBUG=FALSE)
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
    public static function get_table_structure($table, $DEBUG=FALSE)
    {
        $chaine_drop="DROP TABLE IF EXISTS  `$table` ;\n";
        $chaine_create = "CREATE TABLE `$table` ( ";

        // description des champs :
        $sql_champs='SHOW FIELDS FROM '. \includes\SQL::quote($table);
        $ReqLog_champs = \includes\SQL::query($sql_champs) ;
        $count_champs=$ReqLog_champs->num_rows;
        $i=0;
        while ($resultat_champs = $ReqLog_champs->fetch_array())
        {
            $sql_field=$resultat_champs['Field'];
            $sql_type=$resultat_champs['Type'];
            $sql_null=$resultat_champs['Null'];
            $sql_key=$resultat_champs['Key'];
            $sql_default=$resultat_champs['Default'];
            $sql_extra=$resultat_champs['Extra'];

            $chaine_create=$chaine_create." `$sql_field` $sql_type ";
            if($sql_null != "YES")
                $chaine_create=$chaine_create." NOT NULL ";
            if(!empty($sql_default))
            {
                if($sql_default=="CURRENT_TIMESTAMP")
                    $chaine_create=$chaine_create." default $sql_default ";		// pas de quotes !
                else
                    $chaine_create=$chaine_create." default '$sql_default' ";
            }
            if(!empty($sql_extra))
                $chaine_create=$chaine_create." $sql_extra ";
            if($i<$count_champs-1)
                $chaine_create=$chaine_create.",";
            $i++;
        }

        // description des index :
        $sql_index = 'SHOW KEYS FROM '. \includes\SQL::quote($table).'';
        $ReqLog_index = \includes\SQL::query($sql_index) ;
        $count_index=$ReqLog_index->num_rows;
        $i=0;

        // il faut faire une liste pour prendre les PRIMARY, le nom de la colonne et
        // genérer un PRIMARY KEY ('key1'), PRIMARY KEY ('key2', ...)
        // puis on regarde ceux qui ne sont pas PRIMARY et on regarde s'ils sont UNIQUE ou pas et
        // on génére une liste= UNIQUE 'key1' ('key1') , 'key2' ('key2') , ....
        // ou une liste= KEY key1' ('key1') , 'key2' ('key2') , ....
        $list_primary="";
        $list_unique="";
        $list_key="";
        while ($resultat_index = $ReqLog_index->fetch_array())
        {
            $sql_key_name=$resultat_index['Key_name'];
            $sql_column_name=$resultat_index['Column_name'];
            $sql_non_unique=$resultat_index['Non_unique'];

            if($sql_key_name=="PRIMARY")
            {
                if($list_primary=="")
                    $list_primary=" PRIMARY KEY (`$sql_column_name` ";
                else
                    $list_primary=$list_primary.", `$sql_column_name` ";
            }
            elseif($sql_non_unique== 0)
            {
                if($list_unique=="")
                    $list_unique=" UNIQUE  `$sql_column_name` (`$sql_key_name`) ";
                else
                    $list_unique = $list_unique.", `$sql_column_name` (`$sql_key_name`) ";
            }
            else
            {
                if($list_key=="")
                    $list_key=" KEY  `$sql_column_name` (`$sql_key_name`) ";
                else
                    $list_key=$list_key.", KEY `$sql_column_name` (`$sql_key_name`) ";
            }
        }

        if($list_primary!="")
            $list_primary=$list_primary." ) ";

        if($list_primary!="")
            $chaine_create=$chaine_create.",    ".$list_primary;
        if($list_unique!="")
            $chaine_create=$chaine_create.",    ".$list_unique;
        if($list_key!="")
            $chaine_create=$chaine_create.",    ".$list_key;

        $chaine_create=$chaine_create." ) DEFAULT CHARSET=latin1;\n#\n";

        return($chaine_drop.$chaine_create);
    }

    public static function restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        header_popup();	

        echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

        if( ($fichier_restaure_error!=0)||($fichier_restaure_size==0) ) // s'il y a eu une erreur dans le telechargement OU taille==0
            //(cf code erreur dans fichier features.file-upload.errors.html de la doc php)
        {
            //message d'erreur et renvoit sur la page précédente (choix fichier)

            echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
            echo "<table>\n";
            echo "<tr>\n";
            echo "<th> ". _('admin_sauve_db_bad_file') ." : <br>$fichier_restaure_name</th>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td align=\"center\">\n";
            echo "	<input type=\"hidden\" name=\"choix_action\" value=\"restaure\">\n";
            echo "	<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td align=\"center\">\n";
            echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
            echo "</td>\n";
            echo "</tr>\n";
            echo "</table>\n";
            echo "</form>\n";

        }
        else
        {
            $result = execute_sql_file($fichier_restaure_tmpname, $DEBUG);

            echo "<form action=\"\" method=\"POST\">\n";
            echo "<table>\n";
            echo "<tr>\n";
            echo "<th>". _('admin_sauve_db_restaure_ok') ." !</th>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td align=\"center\">&nbsp;</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td align=\"center\">\n";
            echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\">\n";
            echo "</td>\n";
            echo "</tr>\n";
            echo "</table>\n";
            echo "</form>\n";
        }

        bottom();
    }

    // RESTAURATION
    public static function choix_restaure($DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        header_popup();	

        echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";
        echo "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th>". _('admin_sauve_db_restaure') ."<br>". _('admin_sauve_db_file_to_restore') ." :</th>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\"> <input type=\"file\" name=\"fichier_restaure\"> </td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\">&nbsp;</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\"> <font color=\"red\">". _('admin_sauve_db_warning') ." !</font> </td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\">&nbsp;</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "	<td align=\"center\">\n";
        echo "		<input type=\"hidden\" name=\"choix_action\" value=\"restaure\">\n";
        echo "		<input type=\"submit\" value=\"". _('admin_sauve_db_do_restaure') ."\">\n";
        echo "	</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td align=\"center\">\n";
        echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";

        bottom();
    }

    public static function commit_sauvegarde($type_sauvegarde, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        header("Pragma: no-cache");
        header("Content-Type: text/x-delimtext; name=\"php_conges_".$type_sauvegarde.".sql\"");
        header("Content-disposition: attachment; filename=php_conges_".$type_sauvegarde.".sql");

        //
        // Build the sql script file...
        //
        $maintenant=date("d-m-Y H:i:s");
        echo "#\n";
        echo "# PHP_CONGES\n";
        echo "#\n# DATE : $maintenant\n";
        echo "#\n";

        //recup de la liste des tables
        $sql1="SHOW TABLES";
        $ReqLog = \includes\SQL::query($sql1) ;
        while ($resultat = $ReqLog->fetch_array())
        {
            $table=$resultat[0] ;

            echo "#\n#\n# TABLE: $table \n#\n";
            if(($type_sauvegarde=="all") || ($type_sauvegarde=="structure") )
            {
                echo "# Struture : \n#\n";
                echo \admin\Fonctions::get_table_structure($table);
            }
            if(($type_sauvegarde=="all") || ($type_sauvegarde=="data") )
            {
                echo "# Data : \n#\n";
                echo \admin\Fonctions::get_table_data($table);
            }
        }
    }

    public static function sauve($type_sauvegarde, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        redirect(ROOT_PATH .'admin/admin_db_sauve.php?session='.$session.'&choix_action=sauvegarde&type_sauvegarde='.$type_sauvegarde.'&commit=ok', false);

        header_popup();	

        echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th colspan=\"2\">". _('admin_sauve_db_save_ok') ." ...</th>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";

        bottom();
    }

    // SAUVEGARDE
    public static function choix_sauvegarde($DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();


        header_popup();	

        echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th colspan=\"2\">". _('admin_sauve_db_options') ."</th>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "	<td><input type=\"radio\" name=\"type_sauvegarde\" value=\"all\" checked></td>\n";
        echo "	<td>". _('admin_sauve_db_complete') ."</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "	<td><input type=\"radio\" name=\"type_sauvegarde\" value=\"data\"></td>\n";
        echo "	<td>". _('admin_sauve_db_data_only') ."</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	&nbsp;\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "	<td colspan=\"2\" align=\"center\">\n";
        echo "		<input type=\"hidden\" name=\"choix_action\" value=\"sauvegarde\">\n";
        echo "		<input type=\"submit\" value=\"". _('admin_sauve_db_do_sauve') ."\">\n";
        echo "	</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";

        bottom();
    }

    // CHOIX
    public static function choix_save_restore($DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        header_popup();	

        echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

        echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th colspan=\"2\">". _('admin_sauve_db_choisissez') ." :</th>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td><input type=\"radio\" name=\"choix_action\" value=\"sauvegarde\" checked></td>\n";
        echo "<td><b> ". _('admin_sauve_db_sauve') ."</b></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td><input type=\"radio\" name=\"choix_action\" value=\"restaure\" /></td>\n";
        echo "<td><b> ". _('admin_sauve_db_restaure') ."</b></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	&nbsp;\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan=\"2\" align=\"center\">\n";
        echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";

        bottom();
    }

    /**
     * Encapsule le comportement du module de sauvegarde / restauration de bdd
     *
     * @param string $session
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function saveRestoreModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);


        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $choix_action    = getpost_variable('choix_action');
        $type_sauvegarde = getpost_variable('type_sauvegarde');
        $commit          = getpost_variable('commit');

        $fichier_restaure_name="";
        $fichier_restaure_tmpname="";
        $fichier_restaure_size=0;
        $fichier_restaure_error=4;
        if(isset($_FILES['fichier_restaure']))
        {
            $fichier_restaure_name=$_FILES['fichier_restaure']['name'];
            $fichier_restaure_size=$_FILES['fichier_restaure']['size'];
            $fichier_restaure_tmpname=$_FILES['fichier_restaure']['tmp_name'];
            $fichier_restaure_error=$_FILES['fichier_restaure']['error'];
        }
        /*************************************/
        if( $DEBUG ) {	echo "_FILES = <br>\n"; print_r($_FILES); echo "<br>\n"; }


        if($choix_action=="")
            \admin\Fonctions::choix_save_restore($DEBUG);
        elseif($choix_action=="sauvegarde")
        {
            if( (!isset($type_sauvegarde)) || ($type_sauvegarde=="") )
                \admin\Fonctions::choix_sauvegarde($DEBUG);
            else
            {
                if( (!isset($commit)) || ($commit=="") )
                    \admin\Fonctions::sauve($type_sauvegarde);
                else
                    \admin\Fonctions::commit_sauvegarde($type_sauvegarde, $DEBUG);
            }
        }
        elseif($choix_action=="restaure")
        {
            if( (!isset($fichier_restaure_name)) || ($fichier_restaure_name=="")||(!isset($fichier_restaure_tmpname)) || ($fichier_restaure_tmpname=="") )
                \admin\Fonctions::choix_restaure($DEBUG);
            else
                \admin\Fonctions::restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error, $DEBUG);
        }
        else
            /* APPEL D'UNE AUTRE PAGE immediat */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=admin_index.php?session=$session&onglet=admin-users\">";
    }

    public static function commit_update_groupe($group_to_update, $new_groupname, $new_comment, $new_double_valid,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $result=TRUE;

        $new_comment=addslashes($new_comment);
        echo "$group_to_update---$new_groupname---$new_comment---$new_double_valid<br>\n";


        // UPDATE de la table conges_groupe
        $sql1 = 'UPDATE conges_groupe  SET g_groupename=\''.$new_groupname.'\', g_comment=\''.$new_comment.'\' , g_double_valid=\''.$new_double_valid.'\' WHERE g_gid= "'. \includes\SQL::quote($group_to_update).'"'  ;
        $result1 = \includes\SQL::query($sql1);
        if($result1==FALSE)
            $result==FALSE;


        $comment_log = "modif_groupe ($group_to_update) : $new_groupname , $new_comment (double_valid = $new_double_valid)";
        log_action(0, "", "", $comment_log,  $DEBUG);

        if($result)
            echo  _('form_modif_ok') ." !<br><br> \n";
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";

        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-group\">";
    }

    public static function modifier_groupe($group, $onglet, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // Récupération des informations
        $sql1 = 'SELECT g_groupename, g_comment, g_double_valid FROM conges_groupe WHERE g_gid = "'. \includes\SQL::quote($group).'"';

        // AFFICHAGE TABLEAU
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&group_to_update='.$group.'" method="POST">';
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th>". _('admin_groupes_groupe') ."</th>\n";
        echo "<th>". _('admin_groupes_libelle') ." / ". _('divers_comment_maj_1') ."</th>\n";
        if($_SESSION['config']['double_validation_conges'])
            echo "	<th>". _('admin_groupes_double_valid') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $ReqLog1 = \includes\SQL::query($sql1);
        while ($resultat1 = $ReqLog1->fetch_array())
        {
            $sql_groupename=$resultat1["g_groupename"];
            $sql_comment=$resultat1["g_comment"];
            $sql_double_valid=$resultat1["g_double_valid"] ;
        }


        // AFICHAGE DE LA LIGNE DES VALEURS ACTUELLES A MOFIDIER
        echo "<tr>\n";
        echo "<td>$sql_groupename</td>\n";
        echo "<td>$sql_comment</td>\n";
        if($_SESSION['config']['double_validation_conges'])
            echo "<td>$sql_double_valid</td>\n";
        echo "</tr>\n";

        // contruction des champs de saisie
        $text_group="<input class=\"form-control\" type=\"text\" name=\"new_groupname\" size=\"30\" maxlength=\"50\" value=\"".$sql_groupename."\">" ;
        $text_comment="<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"50\" maxlength=\"200\" value=\"".$sql_comment."\">" ;

        // AFFICHAGE ligne de saisie
        echo "<tr>\n";
        echo "<td>$text_group</td>\n";
        echo "<td>$text_comment</td>\n";
        if($_SESSION['config']['double_validation_conges'])
        {
            $text_double_valid="<select class=\"form-control\" name=\"new_double_valid\" ><option value=\"N\" ";
            if($sql_double_valid=="N")
                $text_double_valid=$text_double_valid."SELECTED";
            $text_double_valid=$text_double_valid.">N</option><option value=\"Y\" ";
            if($sql_double_valid=="Y")
                $text_double_valid=$text_double_valid."SELECTED";
            $text_double_valid=$text_double_valid.">Y</option></select>" ;
            echo "<td>$text_double_valid</td>\n";
        }
        echo "</tr>\n";
        echo "</tbody>\n";

        echo "</table>";


        echo "<hr/>\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-group\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;

    }

    /**
     * Encapsule le comportement du module de modif des groupes
     *
     * @param string $session
     * @param mixed  $onglet
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function modifGroupeModule($session, $onglet, $DEBUG = false)
    {
        /*************************************/
        // recup des parametres reçus :

        $group 				= getpost_variable('group');
        $group_to_update 	= getpost_variable('group_to_update');
        $new_groupname 		= getpost_variable('new_groupname');
        $new_comment 		= getpost_variable('new_comment');
        $new_double_valid	= getpost_variable('new_double_valid');
        /*************************************/

        // TITRE
        echo "<h1>". _('admin_modif_groupe_titre') ."</h1>\n";


        if($group!="" )
        {
            \admin\Fonctions::modifier_groupe($group, $onglet, $DEBUG);
        }
        elseif($group_to_update!="")
        {
            \admin\Fonctions::commit_update_groupe($group_to_update, $new_groupname, $new_comment, $new_double_valid,  $DEBUG);
        }
        else
        {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-group', false);
        }
    }

    public static function tab_grille_rtt_from_checkbox($tab_checkbox_sem_imp, $tab_checkbox_sem_p, $DEBUG=FALSE)
    {
        $tab_grille=array();
        $semaine=array("lu", "ma", "me", "je", "ve", "sa", "di");

        // initialiastaion du tableau
        foreach($semaine as $day){
            $key1="sem_imp_".$day."_am";
            $key2="sem_imp_".$day."_pm";
            $tab_grille[$key1] = "";
            $tab_grille[$key2] = "";
            $key3="sem_p_".$day."_am";
            $key4="sem_p_".$day."_pm";
            $tab_grille[$key3] = "";
            $tab_grille[$key4] = "";
        }

        // mise a jour du tab avec les valeurs des chechbox
        if($tab_checkbox_sem_imp!="") {
            while (list ($key, $val) = each ($tab_checkbox_sem_imp)) {
                $tab_grille[$key]=$val;
            }
        }
        if($tab_checkbox_sem_p!="") {
            while (list ($key, $val) = each ($tab_checkbox_sem_p)) {
                $tab_grille[$key]=$val;
            }
        }

        if( $DEBUG )
        {
            echo "tab_grille_rtt_from_checkbox :<br>\n";
            print_r($tab_grille);
            echo "<br>\n";
        }
        return $tab_grille;
    }

    public static function get_current_grille_rtt($u_login_to_update, $DEBUG=FALSE)
    {
        $tab_grille=array();

        $sql = 'SELECT * FROM conges_artt WHERE a_login="'. \includes\SQL::quote($u_login_to_update).'" AND a_date_fin_grille=\'9999-12-31\' ';
        $ReqLog1 = \includes\SQL::query($sql);

        while ($resultat1 = $ReqLog1->fetch_array()) {
            $tab_grille['sem_imp_lu_am'] = $resultat1['sem_imp_lu_am'] ;
            $tab_grille['sem_imp_lu_pm'] = $resultat1['sem_imp_lu_pm'] ;
            $tab_grille['sem_imp_ma_am'] = $resultat1['sem_imp_ma_am'] ;
            $tab_grille['sem_imp_ma_pm'] = $resultat1['sem_imp_ma_pm'] ;
            $tab_grille['sem_imp_me_am'] = $resultat1['sem_imp_me_am'] ;
            $tab_grille['sem_imp_me_pm'] = $resultat1['sem_imp_me_pm'] ;
            $tab_grille['sem_imp_je_am'] = $resultat1['sem_imp_je_am'] ;
            $tab_grille['sem_imp_je_pm'] = $resultat1['sem_imp_je_pm'] ;
            $tab_grille['sem_imp_ve_am'] = $resultat1['sem_imp_ve_am'] ;
            $tab_grille['sem_imp_ve_pm'] = $resultat1['sem_imp_ve_pm'] ;
            $tab_grille['sem_imp_sa_am'] = $resultat1['sem_imp_sa_am'] ;
            $tab_grille['sem_imp_sa_pm'] = $resultat1['sem_imp_sa_pm'] ;
            $tab_grille['sem_imp_di_am'] = $resultat1['sem_imp_di_am'] ;
            $tab_grille['sem_imp_di_pm'] = $resultat1['sem_imp_di_pm'] ;
            $tab_grille['sem_p_lu_am'] = $resultat1['sem_p_lu_am'] ;
            $tab_grille['sem_p_lu_pm'] = $resultat1['sem_p_lu_pm'] ;
            $tab_grille['sem_p_ma_am'] = $resultat1['sem_p_ma_am'] ;
            $tab_grille['sem_p_ma_pm'] = $resultat1['sem_p_ma_pm'] ;
            $tab_grille['sem_p_me_am'] = $resultat1['sem_p_me_am'] ;
            $tab_grille['sem_p_me_pm'] = $resultat1['sem_p_me_pm'] ;
            $tab_grille['sem_p_je_am'] = $resultat1['sem_p_je_am'] ;
            $tab_grille['sem_p_je_pm'] = $resultat1['sem_p_je_pm'] ;
            $tab_grille['sem_p_ve_am'] = $resultat1['sem_p_ve_am'] ;
            $tab_grille['sem_p_ve_pm'] = $resultat1['sem_p_ve_pm'] ;
            $tab_grille['sem_p_sa_am'] = $resultat1['sem_p_sa_am'] ;
            $tab_grille['sem_p_sa_pm'] = $resultat1['sem_p_sa_pm'] ;
            $tab_grille['sem_p_di_am'] = $resultat1['sem_p_di_am'] ;
            $tab_grille['sem_p_di_pm'] = $resultat1['sem_p_di_pm'] ;
        }

        if( $DEBUG )
        {
            echo "get_current_grille_rtt :<br>\n";
            print_r($tab_grille);
            echo "<br>\n";
        }
        return $tab_grille;
    }

    public static function commit_update_user($u_login_to_update, &$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, &$tab_new_reliquat, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;

        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $result=TRUE;

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges = recup_tableau_types_conges($DEBUG);
        $tab_type_conges_excep=array();
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
            $tab_type_conges_excep=recup_tableau_types_conges_exceptionnels($DEBUG);

        if( $DEBUG )
        {
            echo "tab_new_jours_an = <br>\n"; print_r($tab_new_jours_an); echo "<br>\n";
            echo "tab_new_solde = <br>\n"; print_r($tab_new_solde); echo "<br>\n";
            echo "tab_new_reliquat = <br>\n"; print_r($tab_new_reliquat); echo "<br>\n";
            echo "tab_type_conges = <br>\n"; print_r($tab_type_conges); echo "<br>\n";
            echo "tab_type_conges_excep = <br>\n"; print_r($tab_type_conges_excep); echo "<br>\n";
        }


        echo htmlentities($u_login_to_update)."---".htmlentities($tab_new_user['nom'])."---".htmlentities($tab_new_user['prenom'])."---".htmlentities($tab_new_user['quotite'])."---".htmlentities($tab_new_user['is_resp'])."---".htmlentities($tab_new_user['resp_login'])."---".htmlentities($tab_new_user['is_admin'])."---".htmlentities($tab_new_user['is_hr'])."---".htmlentities($tab_new_user['is_active'])."---".htmlentities($tab_new_user['see_all'])."---".htmlentities($tab_new_user['email'])."---".htmlentities($tab_new_user['login'])."<br>\n";


        $valid_1=TRUE;
        $valid_2=TRUE;
        $valid_3=TRUE;
        $valid_reliquat=TRUE;

        // verification de la validite de la saisie du nombre de jours annuels et du solde pour chaque type de conges
        foreach($tab_type_conges as $id_conges => $libelle)
        {
            $valid_1=$valid_1 && verif_saisie_decimal($tab_new_jours_an[$id_conges], $DEBUG);  //verif la bonne saisie du nombre d?cimal
            $valid_2=$valid_2 && verif_saisie_decimal($tab_new_solde[$id_conges], $DEBUG);  //verif la bonne saisie du nombre d?cimal
            $valid_reliquat=$valid_reliquat && verif_saisie_decimal($tab_new_reliquat[$id_conges], $DEBUG);  //verif la bonne saisie du nombre d?cimal
        }

        // si l'application gere les conges exceptionnels ET si des types de conges exceptionnels ont été définis
        if (($_SESSION['config']['gestion_conges_exceptionnels'])&&(count($tab_type_conges_excep) > 0))
        {
            $valid_3=TRUE;
            // vérification de la validité de la saisie du nombre de jours annuels et du solde pour chaque type de conges exceptionnels
            foreach($tab_type_conges_excep as $id_conges => $libelle)
            {
                $valid_3 = $valid_3 && verif_saisie_decimal($tab_new_solde[$id_conges], $DEBUG);  //verif la bonne saisie du nombre décimal
            }
        }
        // sinon on considère $valid_3 comme vrai
        else
            $valid_3=TRUE;

        if( $DEBUG )
        {
            echo "valid_1 = $valid_1  //  valid_2 = $valid_2  //  valid_3 = $valid_3  //  valid_reliquat = $valid_reliquat <br>\n";
        }


        // si aucune erreur de saisie n'a ete commise
        if(($valid_1) && ($valid_2) && ($valid_3) && ($valid_reliquat) && $tab_new_user['login']!="")
        {
            // UPDATE de la table conges_users
            $sql = 'UPDATE conges_users SET u_nom="'. \includes\SQL::quote($tab_new_user['nom']).'", u_prenom="'.\includes\SQL::quote($tab_new_user['prenom']).'", u_is_resp="'. \includes\SQL::quote($tab_new_user['is_resp']).'", u_resp_login="'.\includes\SQL::quote($tab_new_user['resp_login']).'",u_is_admin="'. \includes\SQL::quote($tab_new_user['is_admin']).'",u_is_hr="'.\includes\SQL::quote($tab_new_user['is_hr']).'",u_is_active="'.\includes\SQL::quote($tab_new_user['is_active']).'",u_see_all="'.\includes\SQL::quote($tab_new_user['see_all']).'",u_login="'.\includes\SQL::quote($tab_new_user['login']).'",u_quotite="'.\includes\SQL::quote($tab_new_user['quotite']).'",u_email="'. \includes\SQL::quote($tab_new_user['email']).'" WHERE u_login="'.\includes\SQL::quote($u_login_to_update).'"' ;

            \includes\SQL::query($sql);


            /*************************************/
            /* Mise a jour de la table conges_solde_user   */
            foreach($tab_type_conges as $id_conges => $libelle)
            {
                $sql = 'REPLACE INTO conges_solde_user SET su_nb_an=\''.strtr(round_to_half($tab_new_jours_an[$id_conges]),",",".").'\',su_solde=\''.strtr(round_to_half($tab_new_solde[$id_conges]),",",".").'\',su_reliquat=\''.strtr(round_to_half($tab_new_reliquat[$id_conges]),",",".").'\',su_login="'.\includes\SQL::quote($u_login_to_update).'",su_abs_id='.intval($id_conges).';';
                echo $sql;
                \includes\SQL::query($sql);

            }

            if ($_SESSION['config']['gestion_conges_exceptionnels'])
            {
                foreach($tab_type_conges_excep as $id_conges => $libelle)
                {
                    $sql = 'REPLACE INTO conges_solde_user SET su_nb_an=0, su_solde=\''.strtr(round_to_half($tab_new_solde[$id_conges]),",",".").'\', su_reliquat=\''.strtr(round_to_half($tab_new_reliquat[$id_conges]),",",".").'\', su_login="'.\includes\SQL::quote($u_login_to_update).'", su_abs_id='.intval($id_conges).';';
                    echo $sql;
                    \includes\SQL::query($sql);
                }
            }

            /*************************************/
            /* Mise a jour de la table artt si besoin :   */
            $tab_grille_rtt_actuelle = \admin\Fonctions::get_current_grille_rtt($u_login_to_update, $DEBUG);
            $tab_new_grille_rtt= \admin\Fonctions::tab_grille_rtt_from_checkbox($tab_checkbox_sem_imp, $tab_checkbox_sem_p, $DEBUG);

            if($tab_grille_rtt_actuelle != $tab_new_grille_rtt)
            {
                $new_date_deb_grille=$tab_new_user['year']."-".$tab_new_user['mois']."-".$tab_new_user['jour'];

                /****************************/
                /***   phase 1 :  ***/
                // si la derniere grille est ancienne, on l'update (on update la date de fin de grille)
                // sinon, si la derniere grille date d'aujourd'hui, on la supprime

                // on regarde si la grille artt a deja été modifiée aujourd'hui :
                $sql='SELECT a_date_fin_grille FROM conges_artt
                    WHERE a_login="'.\includes\SQL::quote($u_login_to_update).'" AND a_date_debut_grille="'. \includes\SQL::quote($new_date_deb_grille).'";';
                $result_grille = \includes\SQL::query($sql);

                $count_grille=$result_grille->num_rows;

                if($count_grille==0) // si pas de grille modifiée aujourd'hui : on update la date de fin de la derniere grille
                {
                    // date de fin de la grille précedent :
                    // $new_date_fin_grille = $new_date_deb_grille -1 jour !
                    $new_jour_num= (integer) $tab_new_user['jour'];
                    $new_mois_num= (integer) $tab_new_user['mois'];
                    $new_year_num= (integer) $tab_new_user['year'];
                    $new_date_fin_grille=date("Y-m-d", mktime(0, 0, 0, $new_mois_num, $new_jour_num-1, $new_year_num)); // int mktime(int hour, int minute, int second, int month, int day, int year )

                    // UPDATE de la table conges_artt
                    // en fait, on update la dernière grille (on update la date de fin de grille), et on ajoute une nouvelle
                    // grille (avec sa date de début de grille)

                    // on update la dernière grille (on update la date de fin de grille)
                    $sql = 'UPDATE conges_artt SET a_date_fin_grille="'. \includes\SQL::quote($new_date_fin_grille).'" WHERE a_login="'. \includes\SQL::quote($u_login_to_update).'"  AND a_date_fin_grille=\'9999-12-31\' ';
                    \includes\SQL::query($sql);
                }
                else  // si une grille modifiée aujourd'hui : on delete cette grille
                {
                    $sql='DELETE FROM conges_artt WHERE a_login="'. \includes\SQL::quote($u_login_to_update).'" AND a_date_debut_grille="'. \includes\SQL::quote($new_date_deb_grille).'"';
                    \includes\SQL::query($sql);
                }

                /****************************/
                /***   phase 2 :  ***/
                // on Insert la nouvelle grille (celle qui commence aujourd'hui)
                //  on met à 'Y' les demi-journées de rtt (et seulement celles là)
                $list_columns="";
                $list_valeurs="";
                $i=0;
                if($tab_checkbox_sem_imp!="") {
                    while (list ($key, $val) = each ($tab_checkbox_sem_imp)) {
                        if($i!=0)
                        {
                            $list_columns=$list_columns.", ";
                            $list_valeurs=$list_valeurs.", ";
                        }
                        $list_columns=$list_columns." $key ";
                        $list_valeurs=$list_valeurs." '$val' ";
                        $i=$i+1;
                    }
                }
                if($tab_checkbox_sem_p!="") {
                    while (list ($key, $val) = each ($tab_checkbox_sem_p)) {
                        if($i!=0)
                        {
                            $list_columns=$list_columns.", ";
                            $list_valeurs=$list_valeurs.", ";
                        }
                        $list_columns=$list_columns." $key ";
                        $list_valeurs=$list_valeurs." '$val' ";
                        $i=$i+1;
                    }
                }
                if( ($list_columns!="") && ($list_valeurs!="") )
                {
                    $sql = "INSERT INTO conges_artt (a_login, $list_columns, a_date_debut_grille ) VALUES ('$u_login_to_update', $list_valeurs, '$new_date_deb_grille') " ;
                    \includes\SQL::query($sql);
                }
            }

            // Si changement du login, (on a dèja updaté la table users (mais pas les responsables !!!)) on update toutes les autres tables
            // (les grilles artt, les periodes de conges et les échanges de rtt, etc ....) avec le nouveau login
            if($tab_new_user['login'] != $u_login_to_update)
            {
                // update table artt
                $sql = 'UPDATE conges_artt SET a_login="'. \includes\SQL::quote($tab_new_user['login']).'" WHERE a_login="'. \includes\SQL::quote($u_login_to_update).'" ';
                \includes\SQL::query($sql);

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

            if($tab_new_user['login'] != $u_login_to_update)
                $comment_log = "modif_user (old_login = $u_login_to_update)  new_login = ".$tab_new_user['login'];
            else
                $comment_log = "modif_user login = $u_login_to_update";

            log_action(0, "", $u_login_to_update, $comment_log,  $DEBUG);

            echo  _('form_modif_ok') ." !<br><br> \n";

            }
            // en cas d'erreur de saisie
            else
            {
                echo  _('form_modif_not_ok') ." !<br><br> \n";
            }

    }

    public static function modifier_user($u_login, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges($DEBUG);

        // recup du tableau des types de conges (seulement les conges)
        if ( $_SESSION['config']['gestion_conges_exceptionnels'] )
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

        // Récupération des informations
        $tab_user = recup_infos_du_user($u_login, "", $DEBUG);

        /********************/
        /* Etat utilisateur */
        /********************/
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&u_login_to_update='.$u_login.'" method="POST">';
        // AFFICHAGE TABLEAU DES INFOS
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th>". _('divers_nom_maj_1') ."</th>\n";
        echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
        echo "<th>". _('divers_login_maj_1') ."</th>\n";
        echo "<th>". _('divers_quotite_maj_1') ."</th>\n";
        echo "<th>". _('admin_users_is_resp') ."</th>\n";
        echo "<th>". _('admin_users_resp_login') ."</th>\n";
        echo "<th>". _('admin_users_is_admin') ."</th>\n";
        echo "<th>". _('admin_users_is_hr') ."</th>\n";
        echo "<th>". _('admin_users_is_active') ."</th>\n";
        echo "<th>". _('admin_users_see_all') ."</th>\n";

        if($_SESSION['config']['where_to_find_user_email']=="dbconges")
            echo "<th>". _('admin_users_mail') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        // AFICHAGE DE LA LIGNE DES VALEURS ACTUELLES A MOFIDIER
        echo "<tr>\n";
        echo "<td>".$tab_user['nom']."</td>\n";
        echo "<td>".$tab_user['prenom']."</td>\n";
        echo "<td>".$tab_user['login']."</td>\n";
        echo "<td>".$tab_user['quotite']."</td>\n";
        echo "<td>".$tab_user['is_resp']."</td>\n";
        echo "<td>".$tab_user['resp_login']."</td>\n";
        echo "<td>".$tab_user['is_admin']."</td>\n";
        echo "<td>".$tab_user['is_hr']."</td>\n";
        echo "<td>".$tab_user['is_active']."</td>\n";
        echo "<td>".$tab_user['see_all']."</td>\n";

        if($_SESSION['config']['where_to_find_user_email']=="dbconges")
            echo "<td>".$tab_user['email']."</td>\n";
        echo "</tr>\n";

        // contruction des champs de saisie
	if($_SESSION['config']['export_users_from_ldap'])
		$text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_user['login']."\" disabled>" ;
	else
		$text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_user['login']."\">" ;

        $text_nom="<input class=\"form-control\" type=\"text\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$tab_user['nom']."\">" ;
        $text_prenom="<input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$tab_user['prenom']."\">" ;
        $text_quotite="<input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$tab_user['quotite']."\">" ;
        if($tab_user['is_resp']=="Y")
            $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" id=\"is_resp_id\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        else
            $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" id=\"is_resp_id\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        if($tab_user['is_admin']=="Y")
            $text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        else
            $text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        if($tab_user['is_hr']=="Y")
            $text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        else
            $text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        if($tab_user['is_active']=="Y")
            $text_is_active="<select class=\"form-control\" name=\"new_is_active\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        else
            $text_is_active="<select class=\"form-control\" name=\"new_is_active\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        if($tab_user['see_all']=="Y")
            $text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"Y\">Y</option><option value=\"N\">N</option></select>" ;
        else
            $text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        if($_SESSION['config']['where_to_find_user_email']=="dbconges")
            $text_email="<input class=\"form-control\" type=\"text\" name=\"new_email\" size=\"10\" maxlength=\"99\" value=\"".$tab_user['email']."\">" ;


        $text_resp_login="<select class=\"form-control\" name=\"new_resp_login\" id=\"resp_login_id\" >" ;
        // construction des options du SELECT pour new_resp_login
        $sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" ORDER BY u_nom,u_prenom"  ;
        $ReqLog2 = \includes\SQL::query($sql2);

        while ($resultat2 = $ReqLog2->fetch_array())
        {
            if($resultat2["u_login"]==$tab_user['resp_login'] )
                $text_resp_login=$text_resp_login."<option value=\"".$resultat2["u_login"]."\" selected>".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            else
                $text_resp_login=$text_resp_login."<option value=\"".$resultat2["u_login"]."\">".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
        }

        $text_resp_login=$text_resp_login."</select>" ;

        // AFFICHAGE ligne de saisie
        echo "<tr class=\"update-line\">\n";
        echo "<td>$text_nom</td>\n";
        echo "<td>$text_prenom</td>\n";
        echo "<td>$text_login</td>\n";
        echo "<td>$text_quotite</td>\n";
        echo "<td>$text_is_resp</td>\n";
        echo "<td>$text_resp_login</td>\n";
        echo "<td>$text_is_admin</td>\n";
        echo "<td>$text_is_hr</td>\n";
        echo "<td>$text_is_active</td>\n";
        echo "<td>$text_see_all</td>\n";
        if($_SESSION['config']['where_to_find_user_email']=="dbconges")
            echo "<td>$text_email</td>\n";
        echo "</tr>\n";
        echo "</tbody>\n";
        echo "</table><br>\n\n";
        echo "<hr/>\n";

        // AFFICHAGE TABLEAU DES conges annuels et soldes
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th></th>\n";
        echo "<th colspan=\"2\">". _('admin_modif_nb_jours_an') ." </th>\n";
        echo "<th colspan=\"2\">". _('divers_solde') ."</th>\n";
        if( $_SESSION['config']['autorise_reliquats_exercice'] )
        {
            echo "<th colspan=\"2\">". _('divers_reliquat') ."</th>\n";
        }
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        foreach($tab_type_conges as $id_type_cong => $libelle)
        {
            echo '<tr class="'.($i?'i':'p').'">';
            echo "<td>$libelle</td>\n";
            // jours / an

            if (isset($tab_user['conges'][$libelle]))
            {
                echo "<td>".$tab_user['conges'][$libelle]['nb_an']."</td>\n";
                $text_jours_an="<input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['nb_an']."\">" ;
            }
            else
            {
                echo "<td>0</td>\n";
                $text_jours_an='<input class=\"form-control\" type="text" name="tab_new_jours_an['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
            }

            echo "<td>$text_jours_an</td>\n";

            // solde
            if (isset($tab_user['conges'][$libelle]))
            {
                echo "<td>".$tab_user['conges'][$libelle]['solde']."</td>\n";
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['solde']."\">" ;
            }
            else
            {
                echo "<td>0</td>\n";
                $text_solde_jours='<input class=\"form-control\" type="text" name="tab_new_solde['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
            }

            echo "<td>$text_solde_jours</td>\n";

            // reliquat
            // si on ne les utilise pas, on initialise qd meme le tableau (<input type=\"hidden\") ...
            if($_SESSION['config']['autorise_reliquats_exercice'])
            {
                if (isset($tab_user['conges'][$libelle]))
                {
                    echo "<td>".$tab_user['conges'][$libelle]['reliquat']."</td>\n";
                    $text_reliquats_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_reliquat[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['reliquat']."\">" ;

                }
                else
                {
                    echo "<td>0</td>\n";
                    $text_reliquats_jours='<input class=\"form-control\" type="text" name="tab_new_reliquat['.$id_type_cong.']" size="5" maxlength="5" value="0">' ;
                }
                echo "<td>$text_reliquats_jours</td>\n";
            }
            else
                echo "<input type=\"hidden\" name=\"tab_new_reliquat[$id_type_cong]\" value=\"0\">" ;
            echo "</tr>\n";
            $i = !$i;
        }

        // recup du tableau des types de conges (seulement les conges)
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
        {
            foreach($tab_type_conges_exceptionnels as $id_type_cong_exp => $libelle)
            {
                echo '<tr class="'.($i?'i':'p').'">';
                echo "<td>$libelle</td>\n";
                // jours / an
                echo "<td>0</td>\n";
                echo "<td>0</td>\n";
                // solde
                echo "<td>".$tab_user['conges'][$libelle]['solde']."</td>\n";
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong_exp]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['solde']."\">" ;
                echo "<td>$text_solde_jours</td>\n";
                // reliquat
                // si on ne les utilise pas, on initialise qd meme le tableau (<input type=\"hidden\") ...
                if($_SESSION['config']['autorise_reliquats_exercice'])
                {
                    echo "<td>".$tab_user['conges'][$libelle]['reliquat']."</td>\n";
                    $text_reliquats_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_reliquat[$id_type_cong_exp]\" size=\"5\" maxlength=\"5\" value=\"".$tab_user['conges'][$libelle]['reliquat']."\">" ;
                    echo "<td>$text_reliquats_jours</td>\n";
                }
                else
                    echo "<input type=\"hidden\" name=\"tab_new_reliquat[$id_type_cong_exp]\" value=\"0\">" ;
                echo "</tr>\n";
                $i = !$i;
            }
        }

        echo "</tbody>\n";
        echo "</table><br>\n\n";

        echo "<hr/>\n";

        /*********************************************************/
        // saisie des jours d'abscence RTT ou temps partiel:
        saisie_jours_absence_temps_partiel($u_login,$DEBUG);
        echo "<hr/>\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-users\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;
    }
    
    /**
     * Encapsule le comportement du module de modification d'utilisateurs
     *
     * @param string $session
     * @param string $onglet Nom de l'onglet à afficher
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function modifUserModule($session, $onglet, $DEBUG = false)
    {
        $u_login		= getpost_variable('u_login') ;
        $u_login_to_update      = getpost_variable('u_login_to_update') ;
        $tab_checkbox_sem_imp   = getpost_variable('tab_checkbox_sem_imp') ;
        $tab_checkbox_sem_p     = getpost_variable('tab_checkbox_sem_p') ;

        // TITRE
        if($u_login!="")
            $login_titre = $u_login;
        elseif($u_login_to_update!="")
            $login_titre = $u_login_to_update;

        echo "<h1>". _('admin_modif_user_titre') ." : <strong>$login_titre</strong></h1>\n\n";


        if($u_login!="")
        {
            \admin\Fonctions::modifier_user($u_login, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $onglet, $DEBUG);
        }
        elseif($u_login_to_update!="")
        {
            $tab_new_jours_an   = getpost_variable('tab_new_jours_an') ;
            $tab_new_solde      = getpost_variable('tab_new_solde') ;
            $tab_new_reliquat   = getpost_variable('tab_new_reliquat') ;

            $tab_new_user['login']      = getpost_variable('new_login') ;
            $tab_new_user['nom']	= getpost_variable('new_nom') ;
            $tab_new_user['prenom']     = getpost_variable('new_prenom') ;
            $tab_new_user['quotite']    = getpost_variable('new_quotite') ;
            $tab_new_user['is_resp']    = getpost_variable('new_is_resp') ;
            $tab_new_user['resp_login'] = getpost_variable('new_resp_login') ;
            $tab_new_user['is_admin']   = getpost_variable('new_is_admin') ;
            $tab_new_user['is_hr']      = getpost_variable('new_is_hr') ;
            $tab_new_user['is_active']  = getpost_variable('new_is_active') ;
            $tab_new_user['see_all']    = getpost_variable('new_see_all') ;
            $tab_new_user['email']      = getpost_variable('new_email') ;
            $tab_new_user['jour']       = getpost_variable('new_jour') ;
            $tab_new_user['mois']       = getpost_variable('new_mois') ;
            $tab_new_user['year']       = getpost_variable('new_year') ;

            \admin\Fonctions::commit_update_user($u_login_to_update, $tab_new_user, $tab_new_jours_an, $tab_new_solde, $tab_new_reliquat, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $DEBUG);
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
            exit;

        }
        else
        {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
            exit;
        }
    }

    public static function suppression_group($group_to_delete,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $sql1 = 'DELETE FROM conges_groupe WHERE g_gid = '.\includes\SQL::quote($group_to_delete);
        $result = \includes\SQL::query($sql1);

        $sql2 = 'DELETE FROM conges_groupe_users WHERE gu_gid = '.\includes\SQL::quote($group_to_delete);
        $result2 = \includes\SQL::query($sql2);

        $sql3 = 'DELETE FROM conges_groupe_resp WHERE gr_gid = '.\includes\SQL::quote($group_to_delete);
        $result3 = \includes\SQL::query($sql3);

        if($_SESSION['config']['double_validation_conges'])
        {
            $sql4 = 'DELETE FROM conges_groupe_grd_resp WHERE ggr_gid = '.\includes\SQL::quote($group_to_delete);
            $result4 = \includes\SQL::query($sql4);
        }

        $comment_log = "suppression_groupe ($group_to_delete)";
        log_action(0, "", "", $comment_log,  $DEBUG);

        if($result)
            echo  _('form_modif_ok') ." !<br><br> \n";
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";

        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-group\">";
    }

    public static function confirmer($group, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        /*******************/
        /* Groupe en cours */
        /*******************/
        // Récupération des informations
        $sql1 = 'SELECT g_groupename, g_comment, g_double_valid FROM conges_groupe WHERE g_gid = "'.\includes\SQL::quote($group).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        // AFFICHAGE TABLEAU

        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&group_to_delete='.$group.'" method="POST">';
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th><b>". _('admin_groupes_groupe') ."</b></th>\n";
        echo "<th><b>". _('admin_groupes_libelle') ." / ". _('divers_comment_maj_1') ."</b></th>\n";
        if($_SESSION['config']['double_validation_conges'])
            echo "	<th><b>". _('admin_groupes_double_valid') ."</b></th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";
        echo "<tr>\n";
        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_groupname=$resultat1["g_groupename"];
            $sql_comment=$resultat1["g_comment"];
            $sql_double_valid=$resultat1["g_double_valid"] ;
            echo "<td>&nbsp;$sql_groupname&nbsp;</td>\n"  ;
            echo "<td>&nbsp;$sql_comment&nbsp;</td>\n" ;
            if($_SESSION['config']['double_validation_conges'])
                echo "<td>$sql_double_valid</td>\n";
        }
        echo "</tr>\n";
        echo "</tbody>\n";
        echo "</table>\n";
        echo "<hr/>\n";
        echo "<input class=\"btn btn-danger\" type=\"submit\" value=\"". _('form_supprim') ."\">\n";
        echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-group\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;
    }

    /**
     * Encapsule le comportement du module de suppression des groupes
     *
     * @param string $session
     * @param string $onglet Nom de l'onglet à afficher
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function supprimerGroupeModule($session, $onglet, $DEBUG = false)
    {
        $group = getpost_variable('group');
        $group_to_delete = getpost_variable('group_to_delete');
        /*************************************/

        // TITRE
        echo "<h1>". _('admin_suppr_groupe_titre') ."</h1>\n";


        if($group!="")
        {
            \admin\Fonctions::confirmer($group, $onglet, $DEBUG);
        }
        elseif($group_to_delete!="")
        {
            \admin\Fonctions::suppression_group($group_to_delete,  $DEBUG);
        }
        else
        {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-group', false);
        }
    }

    public static function suppression($u_login_to_delete, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        $sql1 = 'DELETE FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result = \includes\SQL::query($sql1);

        $sql2 = 'DELETE FROM conges_periode WHERE p_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result2 = \includes\SQL::query($sql2);

        $sql3 = 'DELETE FROM conges_artt WHERE a_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result3 = \includes\SQL::query($sql3);

        $sql4 = 'DELETE FROM conges_echange_rtt WHERE e_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result4 = \includes\SQL::query($sql4);

        $sql5 = 'DELETE FROM conges_groupe_resp WHERE gr_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result5 = \includes\SQL::query($sql5);

        $sql6 = 'DELETE FROM conges_groupe_users WHERE gu_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
        $result6 = \includes\SQL::query($sql6);

        $sql7 = 'DELETE FROM conges_solde_user WHERE su_login = "'.\includes\SQL::quote($u_login_to_delete).'"';
        $result7 = \includes\SQL::query($sql7);


        $comment_log = "suppression_user ($u_login_to_delete)";
        log_action(0, "", $u_login_to_delete, $comment_log, $DEBUG);

        if($result)
            echo  _('form_modif_ok') ." !<br><br> \n" ;
        else
            echo  _('form_modif_not_ok') ." !<br><br> \n";
    }

    public static function confirmer_suppression($u_login, $onglet, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        /****************************/
        /* Etat Utilisateur en cours */
        /*****************************/
        // AFFICHAGE TABLEAU
        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&u_login_to_delete='.$u_login.'" method="POST">';
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo '<thead>';
        echo '<tr>';
        echo "<th>". _('divers_login_maj_1') ."</th>\n";
        echo "<th>". _('divers_nom_maj_1') ."</th>\n";
        echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
        echo "</tr>\n";
        echo '</thead>';
        echo '<tbody>';

        // Récupération des informations
        $sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        echo "<tr>\n";
        while ($resultat1 = $ReqLog1->fetch_array())
        {
            echo "<td>".$resultat1["u_login"]."</td>\n";
            echo "<td>".$resultat1["u_nom"]."</td>\n";
            echo "<td>".$resultat1["u_prenom"]."</td>\n";
        }
        echo "</tr>\n";
        echo '</tbody>';
        echo "</table><br>\n\n";
        echo "<input class=\"btn btn-danger\" type=\"submit\" value=\"". _('form_supprim') ."\">\n";
        echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-users\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;
    }

    /**
     * Encapsule le comportement du module de suppression des utilisateurs
     *
     * @param string $session
     * @param string $onglet
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function supprimerUtilisateurModule($session, $onglet, $DEBUG = false)
    {
        /*************************************/
        // recup des parametres reçus :

        $u_login = getpost_variable('u_login') ;
        $u_login_to_delete = getpost_variable('u_login_to_delete') ;
        /*************************************/

        // TITRE
        if($u_login!="")
            $login_titre = $u_login;
        elseif($u_login_to_delete!="")
            $login_titre = $u_login_to_delete;

        echo "<h1>". _('admin_suppr_user_titre') ." : <strong>$login_titre</strong></h1>\n";


        if($u_login!="")
        {
            \admin\Fonctions::confirmer_suppression($u_login, $onglet, $DEBUG);
        }
        elseif($u_login_to_delete!="")
        {
            \admin\Fonctions::suppression($u_login_to_delete, $DEBUG);
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
            exit;
        }
        else
        {
            // renvoit sur la page principale .
            redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
            exit;
        }
    }

    public static function recup_users_from_ldap(&$tab_ldap, &$tab_login, $DEBUG=FALSE)
    {
        // cnx à l'annuaire ldap :
        $ds = \ldap_connect($_SESSION['config']['ldap_server']);
        if($_SESSION['config']['ldap_protocol_version'] != 0)
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
        if ($_SESSION['config']['ldap_user'] == "")
            $bound = ldap_bind($ds);  // connexion anonyme au serveur
        else
            $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

        // recherche des entrées :
        if ($_SESSION['config']['ldap_filtre_complet'] != "")
            $filter = $_SESSION['config']['ldap_filtre_complet'];
        else
            $filter = "(&(".$_SESSION['config']['ldap_nomaff']."=*)(".$_SESSION['config']['ldap_filtre']."=".$_SESSION['config']['ldap_filrech']."))";

        $sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
        $data = ldap_get_entries($ds,$sr);

        foreach ($data as $info)
        {
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

    public static function affiche_tableau_affectation_user_groupes2($choix_user,  $DEBUG=FALSE)
    {
        //AFFICHAGE DU TABLEAU DES GROUPES DU USER
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";

        // affichage TITRE
        echo "<thead>\n";
        echo "<tr>\n";
        echo "	<th colspan=3><h3>". _('admin_gestion_groupe_users_group_of_new_user') ." :</h3></th>\n";
        echo "</tr>\n";

        echo "<tr>\n";
        echo "	<th>&nbsp;</th>\n";
        echo "	<th>&nbsp;". _('admin_groupes_groupe') ."&nbsp;:</th>\n";
        echo "	<th>&nbsp;". _('admin_groupes_libelle') ."&nbsp;:</th>\n";
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
            $sql_gu = 'SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'.\includes\SQL::quote($choix_user).'" ORDER BY gu_gid ';
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
            echo "	<td class=\"$class\">&nbsp;$group&nbsp</td>\n";
            echo "	<td class=\"$class\">&nbsp;$libelle&nbsp;</td>\n";
            echo "</tr>\n";
        }

        echo "<tbody>\n";
        echo "</table>\n\n";
    }

    // affichage du formulaire de saisie d'un nouveau user
    public static function affiche_formulaire_ajout_user(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $onglet,  $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_conges=recup_tableau_types_conges($DEBUG);

        // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
        {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);
        }

        if( $DEBUG ) { echo "tab_type_conges = <br>\n"; print_r($tab_type_conges); echo "<br>\n"; }

        /*********************/
        /* Ajout Utilisateur */
        /*********************/

        // TITRE
        echo "<h1>" . _('admin_new_users_titre') . "</h1>\n";

        echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';

        /****************************************/
        // tableau des infos de user

        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        if ($_SESSION['config']['export_users_from_ldap'] )
            echo "<th>". _('divers_nom_maj_1') ." ". _('divers_prenom_maj_1') ."</th>\n";
        else
        {
            echo "<th>". _('divers_login_maj_1') ."</th>\n";
            echo "<th>". _('divers_nom_maj_1') ."</th>\n";
            echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
        }
        echo "<th>". _('divers_quotite_maj_1') ."</th>\n";
        echo "<th>". _('admin_new_users_is_resp') ."</th>\n";
        echo "<th>". _('divers_responsable_maj_1') ."</th>\n";
        echo "<th>". _('admin_new_users_is_admin') ."</th>\n";
        echo "<th>". _('admin_new_users_is_hr') ."</th>\n";
        echo "<th>". _('admin_new_users_see_all') ."</th>\n";
        if ( !$_SESSION['config']['export_users_from_ldap'] )
            echo "<th>". _('admin_users_mail') ."</th>\n";
        if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
        {
            echo "<th>". _('admin_new_users_password') ."</th>\n";
            echo "<th>". _('admin_new_users_password') ."</th>\n";
        }
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $text_nom="<input class=\"form-control\" type=\"text\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['nom']."\">" ;
        $text_prenom="<input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['prenom']."\">" ;
        if( (!isset($tab_new_user['quotite'])) || ($tab_new_user['quotite']=="") )
            $tab_new_user['quotite']=100;
        $text_quotite="<input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$tab_new_user['quotite']."\">" ;
        $text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

        // PREPARATION DES OPTIONS DU SELECT du resp_login
        $text_resp_login="<select class=\"form-control\" name=\"new_resp_login\" id=\"resp_login_id\" ><option value=\"no_resp\">". _('admin_users_no_resp') ."</option>" ;
	if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" || is_hr($_SESSION['userlogin'],  $DEBUG=FALSE))
        	$sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" ORDER BY u_nom, u_prenom"  ;
	else
		$sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" AND u_login=\"".$_SESSION['userlogin']."\" ORDER BY u_nom, u_prenom" ;

        $ReqLog2 = \includes\SQL::query($sql2);

        while ($resultat2 = $ReqLog2->fetch_array()) {
            $current_resp_login=$resultat2["u_login"];
            if($tab_new_user['resp_login']==$current_resp_login)
                $text_resp_login=$text_resp_login."<option value=\"$current_resp_login\" selected>".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
            else
                $text_resp_login=$text_resp_login."<option value=\"$current_resp_login\">".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
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

        echo "<tr class=\"update-line\">\n";
        // Aj. D.Chabaud - Université d'Auvergne - Sept. 2005
        if ($_SESSION['config']['export_users_from_ldap'] )
        {
            // Récupération de la liste des utilisateurs via un ldap :

            // on crée 2 tableaux (1 avec les noms + prénoms, 1 avec les login)
            // afin de pouvoir construire une liste déroulante dans le formulaire qui suit...
            $tab_ldap  = array();
            $tab_login = array();
            \admin\Fonctions::recup_users_from_ldap($tab_ldap, $tab_login, $DEBUG);

            // construction de la liste des users récupérés du ldap ...
            array_multisort($tab_ldap, $tab_login); // on trie les utilisateurs par le nom

            $lst_users = "<select multiple size=9 name=new_ldap_user[]><option>------------------</option>\n";
            $i = 0;

            foreach ($tab_login as $login)
            {
                $lst_users .= "<option value=$tab_login[$i]>$tab_ldap[$i]</option>\n";
                $i++;
            }
            $lst_users .= "</select>\n";
            echo "<td>$lst_users</td>\n";
        }
        else
        {
            echo "<td>$text_login</td>\n";
            echo "<td>$text_nom</td>\n";
            echo "<td>$text_prenom</td>\n";
        }

        echo "<td>$text_quotite</td>\n";
        echo "<td>$text_is_resp</td>\n";
        echo "<td>$text_resp_login</td>\n";
        echo "<td>$text_is_admin</td>\n";
        echo "<td>$text_is_hr</td>\n";
        echo "<td>$text_see_all</td>\n";
        if ( !$_SESSION['config']['export_users_from_ldap'] )
            echo "<td>$text_email</td>\n";
        if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
        {
            echo "<td>$text_password1</td>\n";
            echo "<td>$text_password2</td>\n";
        }
        echo "</tr>\n";
        echo "</tbody>\n";
        echo "</table>\n";

        echo "<br>\n";


        /****************************************/
        //tableau des conges annuels et soldes

        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
        // ligne de titres
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th></th>\n";
        echo "<th>". _('admin_new_users_nb_par_an') ."</th>\n";
        echo "<th>". _('divers_solde') ."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        $i = true;
        // ligne de saisie des valeurs
        foreach($tab_type_conges as $id_type_cong => $libelle)
        {
            echo '<tr class="'.($i?'i':'p').'">';
            $value_jours_an = ( isset($tab_new_jours_an[$id_type_cong]) ? $tab_new_jours_an[$id_type_cong] : 0 );
            $value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
            $text_jours_an="<input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_jours_an\">" ;
            $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
            echo "<td>$libelle</td>\n";
            echo "<td>$text_jours_an</td>\n";
            echo "<td>$text_solde_jours</td>\n";
            echo "</tr>\n";
            $i = !$i;
        }
        if ($_SESSION['config']['gestion_conges_exceptionnels']) {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
            {
                echo '<tr class="'.($i?'i':'p').'">';
                $value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
                $text_jours_an="<input type=\"hidden\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"0\"> &nbsp; " ;
                $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
                echo "<td>$libelle</td>\n";
                echo "<td>$text_jours_an</td>\n";
                echo "<td>$text_solde_jours</td>\n";
                echo "</tr>\n";
                $i = !$i;
            }
        }
        echo "</tbody>\n";
        echo "</table>\n";

        echo "<br>\n\n";

        // saisie de la grille des jours d'abscence ARTT ou temps partiel:
        saisie_jours_absence_temps_partiel($tab_new_user['login'],  $DEBUG);


        // si gestion des groupes :  affichage des groupe pour y affecter le user
        if($_SESSION['config']['gestion_groupes'])
        {
            echo "<br>\n";
	if( $_SESSION['config']['admin_see_all'] || $_SESSION['userlogin']=="admin" ||  is_hr($_SESSION['userlogin']) )
            \admin\Fonctions::affiche_tableau_affectation_user_groupes2("",  $DEBUG);
	else
            \admin\Fonctions::affiche_tableau_affectation_user_groupes2($_SESSION['userlogin'],  $DEBUG);
        }

        echo "<hr>\n";
        echo "<input type=\"hidden\" name=\"saisie_user\" value=\"ok\">\n";
        echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>\n";
        echo "</form>\n" ;
    }

    public static function verif_new_param(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        foreach($tab_new_jours_an as $id_cong => $jours_an)
        {
            $valid=verif_saisie_decimal($tab_new_jours_an[$id_cong], $DEBUG);    //verif la bonne saisie du nombre décimal
            $valid=verif_saisie_decimal($tab_new_solde[$id_cong], $DEBUG);    //verif la bonne saisie du nombre décimal
        }
        if( $DEBUG )
        {
            echo "tab_new_jours_an = "; print_r($tab_new_jours_an) ; echo "<br>\n";
            echo "tab_new_solde = "; print_r($tab_new_solde) ; echo "<br>\n";
        }


        // verif des parametres reçus :
        // si on travaille avec la base dbconges, on teste tout, mais si on travaille avec ldap, on ne teste pas les champs qui viennent de ldap ...
        if(!\admin\Fonctions::test_form_add_user($tab_new_user, $DEBUG=FALSE))
        {
            echo "<h3><font color=\"red\"> ". _('admin_verif_param_invalides') ." </font></h3>\n"  ;
            // affichage des param :
            echo htmlentities($tab_new_user['login'])."---".htmlentities($tab_new_user['nom'])."---".htmlentities($tab_new_user['prenom'])."---".htmlentities($tab_new_user['quotite'])."---".htmlentities($tab_new_user['is_resp'])."---".htmlentities($tab_new_user['resp_login'])."<br>\n";
            foreach($tab_new_jours_an as $id_cong => $jours_an)
            {
                echo $tab_new_jours_an[$id_cong]."---".$tab_new_solde[$id_cong]."<br>\n";
            }

            echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n"  ;
            echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
            echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
            echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
            echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
            echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
            echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
            echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
            echo "<input type=\"hidden\" name=\"new_see_all\" value=\"".$tab_new_user['see_all']."\">\n";
            echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
            echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";
            foreach($tab_new_jours_an as $id_cong => $jours_an)
            {
                echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
                echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
            }

            echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo"</form>\n" ;

            return 1;
        }
        else {

            // verif si le login demandé n'existe pas déjà ....
            $sql_verif='SELECT u_login FROM conges_users WHERE u_login="'.\includes\SQL::quote($tab_new_user['login']).'"';
            $ReqLog_verif = \includes\SQL::query($sql_verif);

            $num_verif = $ReqLog_verif->num_rows;
            if ($num_verif!=0)
            {
                echo "<h3><font color=\"red\"> ". _('admin_verif_login_exist') ." </font></h3>\n"  ;
                echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n"  ;
                echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
                echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
                echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
                echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
                echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
                echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";

                foreach($tab_new_jours_an as $id_cong => $jours_an)
                {
                    echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
                    echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
                }

                echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n" ;

                return 1;
            }
            elseif($_SESSION['config']['where_to_find_user_email'] == "dbconges" && strrchr($tab_new_user['email'], "@")==FALSE)
            {
                echo "<h3> ". _('admin_verif_bad_mail') ." </h3>\n" ;
                echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n" ;
                echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
                echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
                echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
                echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
                echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
                echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
                echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";

                foreach($tab_new_jours_an as $id_cong => $jours_an)
                {
                    echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
                    echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
                }

                echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
                echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n" ;

                return 1;
            }
            else
                return 0;
        }
    }

    public static function test_form_add_user($tab_new_user, $DEBUG=FALSE) {
        if($_SESSION['config']['export_users_from_ldap']) {
            return \admin\Fonctions::FormAddUserLoginOk($tab_new_user['login']) && \admin\Fonctions::FormAddUserQuotiteOk($tab_new_user['quotite']);
        } else {
            return \admin\Fonctions::FormAddUserLoginOk($tab_new_user['login']) && \admin\Fonctions::FormAddUserQuotiteOk($tab_new_user['quotite']) && \admin\Fonctions::FormAddUserNameOk($tab_new_user['nom']) && \admin\Fonctions::FormAddUserNameOk($tab_new_user['prenom']) && \admin\Fonctions::FormAddUserpasswdOk($tab_new_user['password1'],$tab_new_user['password2']);
        }
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

    public static function ajout_user(&$tab_new_user, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, &$tab_new_jours_an, &$tab_new_solde, $checkbox_user_groups, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

        if( $DEBUG )
        {
            echo "tab_new_jours_an = "; print_r($tab_new_jours_an) ; echo "<br>\n";
            echo "tab_new_solde = "; print_r($tab_new_solde) ; echo "<br>\n";
        }

        // si pas d'erreur de saisie :
        if( \admin\Fonctions::verif_new_param($tab_new_user, $tab_new_jours_an, $tab_new_solde, $DEBUG)==0)
        {
            echo $tab_new_user['login']."---".$tab_new_user['nom']."---".$tab_new_user['prenom']."---".$tab_new_user['quotite']."\n";
            echo "---".$tab_new_user['is_resp']."---".$tab_new_user['resp_login']."---".$tab_new_user['is_admin']."---".$tab_new_user['is_hr']."---".$tab_new_user['see_all']."---".$tab_new_user['email']."<br>\n";

            foreach($tab_new_jours_an as $id_cong => $jours_an)
            {
                echo $tab_new_jours_an[$id_cong]."---".$tab_new_solde[$id_cong]."<br>\n";
            }
            $new_date_deb_grille=$tab_new_user['new_year']."-".$tab_new_user['new_mois']."-".$tab_new_user['new_jour'];
            echo "$new_date_deb_grille<br>\n" ;

            /*****************************/
            /* INSERT dans conges_users  */
            if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
                $motdepasse = md5($tab_new_user['password1']);
            else
                $motdepasse = "none";



            $sql1 = "INSERT INTO conges_users SET ";
            $sql1=$sql1."u_login='".$tab_new_user['login']."', ";
            $sql1=$sql1."u_nom='".addslashes($tab_new_user['nom'])."', ";
            $sql1=$sql1."u_prenom='".addslashes($tab_new_user['prenom'])."', ";
            $sql1=$sql1."u_is_resp='".$tab_new_user['is_resp']."', ";

            if($tab_new_user['resp_login'] == 'no_resp')
                $sql1=$sql1."u_resp_login= NULL , ";
            else
                $sql1=$sql1."u_resp_login='". $tab_new_user['resp_login']."', ";


            $sql1=$sql1."u_is_admin='".$tab_new_user['is_admin']."', ";
            $sql1=$sql1."u_is_hr='".$tab_new_user['is_hr']."', ";
            $sql1=$sql1."u_see_all='".$tab_new_user['see_all']."', ";
            $sql1=$sql1."u_passwd='$motdepasse', ";
            $sql1=$sql1."u_quotite=".$tab_new_user['quotite'].",";
            $sql1=$sql1." u_email='".$tab_new_user['email']."' ";
            $result1 = \includes\SQL::query($sql1);


            /**********************************/
            /* INSERT dans conges_solde_user  */
            foreach($tab_new_jours_an as $id_cong => $jours_an)
            {
                $sql3 = "INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) ";
                $sql3 = $sql3. "VALUES ('".$tab_new_user['login']."' , $id_cong, ".$tab_new_jours_an[$id_cong].", ".$tab_new_solde[$id_cong].", 0) " ;
                $result3 = \includes\SQL::query($sql3);
            }


            /*****************************/
            /* INSERT dans conges_artt  */
            $list_colums_to_insert="a_login";
            $list_values_to_insert="'".$tab_new_user['login']."'";
            // on parcours le tableau des jours d'absence semaine impaire
            if($tab_checkbox_sem_imp!="") {
                while (list ($key, $val) = each ($tab_checkbox_sem_imp)) {
                    $list_colums_to_insert="$list_colums_to_insert, $key";
                    $list_values_to_insert="$list_values_to_insert, '$val'";
                }
            }
            if($tab_checkbox_sem_p!="") {
                while (list ($key, $val) = each ($tab_checkbox_sem_p)) {
                    $list_colums_to_insert="$list_colums_to_insert, $key";
                    $list_values_to_insert="$list_values_to_insert, '$val'";
                }
            }

            $sql2 = "INSERT INTO conges_artt ($list_colums_to_insert, a_date_debut_grille) VALUES ($list_values_to_insert, '$new_date_deb_grille')" ;
            $result2 = \includes\SQL::query($sql2);


            /***********************************/
            /* ajout du user dans ses groupes  */
            $result4=TRUE;
            if( ($_SESSION['config']['gestion_groupes']) && ($checkbox_user_groups!="") )
            {
                $result4=commit_modif_user_groups($tab_new_user['login'], $checkbox_user_groups, $DEBUG);
            }



            /*****************************/

            if($result1 && $result2 && $result3 && $result4)
                echo  _('form_modif_ok') ."<br><br> \n";
            else
                echo  _('form_modif_not_ok') ."<br><br> \n";

            $comment_log = "ajout_user : ".$tab_new_user['login']." / ".addslashes($tab_new_user['nom'])." ".addslashes($tab_new_user['prenom'])." (".$tab_new_user['quotite']." %)" ;
            log_action(0, "", $tab_new_user['login'], $comment_log, $DEBUG);

            /* APPEL D'UNE AUTRE PAGE */
            echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-users\" method=\"POST\"> \n";
            echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
            echo " </form> \n";
        }
    }

    /**
     * Encapsule le comportement du module d'ajout d'utilisateurs
     *
     * @param string $onglet
     * @param bool   $DEBUG   Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function ajoutUtilisateurModule($onglet, $DEBUG = false)
    {
        $saisie_user     = getpost_variable('saisie_user') ;

        // si on recupere les users dans ldap et qu'on vient d'en créer un depuis la liste déroulante
        if ($_SESSION['config']['export_users_from_ldap']  && isset($_POST['new_ldap_user']))
        {
            $index = 0;
            // On lance une boucle pour selectionner tous les items
            // traitements : $login contient les valeurs successives
            foreach($_POST['new_ldap_user'] as $login)
            {
                $tab_login[$index]=$login;
                $index++;
                // cnx à l'annuaire ldap :
                $ds = ldap_connect($_SESSION['config']['ldap_server']);
                ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3) ;
                if ($_SESSION['config']['ldap_user'] == "")
                    $bound = ldap_bind($ds);
                else
                    $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

                // recherche des entrées :
                $filter = "(".$_SESSION['config']['ldap_login']."=".$login.")";

                $sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
                $data = ldap_get_entries($ds,$sr);

                foreach ($data as $info)
                {
                    $tab_new_user[$login]['login']	= $login;
                    $ldap_libelle_prenom		=$_SESSION['config']['ldap_prenom'];
                    $ldap_libelle_nom		=$_SESSION['config']['ldap_nom'];
                    $tab_new_user[$login]['prenom']	= utf8_decode($info[$ldap_libelle_prenom][0]);
                    $tab_new_user[$login]['nom']	= utf8_decode($info[$ldap_libelle_nom][0]);

                    $ldap_libelle_mail				=$_SESSION['config']['ldap_mail'];
                    $tab_new_user[$login]['email']	= $info[$ldap_libelle_mail][0] ;
                }

                $tab_new_user[$login]['quotite']	= getpost_variable('new_quotite') ;
                $tab_new_user[$login]['is_resp']	= getpost_variable('new_is_resp') ;
                $tab_new_user[$login]['resp_login']	= getpost_variable('new_resp_login') ;
                $tab_new_user[$login]['is_admin']	= getpost_variable('new_is_admin') ;
                $tab_new_user[$login]['is_hr']		= getpost_variable('new_is_hr') ;
                $tab_new_user[$login]['see_all']	= getpost_variable('new_see_all') ;

                if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
                {
                    $tab_new_user[$login]['password1']= getpost_variable('new_password1') ;
                    $tab_new_user[$login]['password2']= getpost_variable('new_password2') ;
                }
                $tab_new_jours_an			= getpost_variable('tab_new_jours_an') ;
                $tab_new_solde				= getpost_variable('tab_new_solde') ;
                $tab_checkbox_sem_imp			= getpost_variable('tab_checkbox_sem_imp') ;
                $tab_checkbox_sem_p			= getpost_variable('tab_checkbox_sem_p') ;
                $tab_new_user[$login]['new_jour']	= getpost_variable('new_jour') ;
                $tab_new_user[$login]['new_mois']	= getpost_variable('new_mois') ;
                $tab_new_user[$login]['new_year']	= getpost_variable('new_year') ;
            }
        }
        else
        {
            $tab_new_user[0]['login']		= getpost_variable('new_login') ;
            $tab_new_user[0]['nom']			= getpost_variable('new_nom') ;
            $tab_new_user[0]['prenom']		= getpost_variable('new_prenom') ;


            $tab_new_user[0]['quotite']		= getpost_variable('new_quotite') ;
            $tab_new_user[0]['is_resp']		= getpost_variable('new_is_resp') ;
            $tab_new_user[0]['resp_login']	= getpost_variable('new_resp_login') ;
            $tab_new_user[0]['is_admin']	= getpost_variable('new_is_admin') ;
            $tab_new_user[0]['is_hr']		= getpost_variable('new_is_hr') ;
            $tab_new_user[0]['see_all']		= getpost_variable('new_see_all') ;

            if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
            {
                $tab_new_user[0]['password1']	= getpost_variable('new_password1') ;
                $tab_new_user[0]['password2']	= getpost_variable('new_password2') ;
            }
            $tab_new_user[0]['email']	= getpost_variable('new_email') ;
            $tab_new_jours_an			= getpost_variable('tab_new_jours_an') ;
            $tab_new_solde				= getpost_variable('tab_new_solde') ;
            $tab_checkbox_sem_imp		= getpost_variable('tab_checkbox_sem_imp') ;
            $tab_checkbox_sem_p			= getpost_variable('tab_checkbox_sem_p') ;
            $tab_new_user[0]['new_jour']= getpost_variable('new_jour') ;
            $tab_new_user[0]['new_mois']= getpost_variable('new_mois') ;
            $tab_new_user[0]['new_year']= getpost_variable('new_year') ;
        }


        $checkbox_user_groups= getpost_variable('checkbox_user_groups') ;
        /* FIN de la recup des parametres    */
        /*************************************/



        if($saisie_user=="ok") {
            if($_SESSION['config']['export_users_from_ldap'] ) {
                foreach($tab_login as $login) {
                    \admin\Fonctions::ajout_user($tab_new_user[$login], $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups, $DEBUG);
                }
            }
            else
                \admin\Fonctions::ajout_user($tab_new_user[0], $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups, $DEBUG);
        }
        else {
            \admin\Fonctions::affiche_formulaire_ajout_user($tab_new_user[0], $tab_new_jours_an, $tab_new_solde, $onglet, $DEBUG);
        }
    }
}
