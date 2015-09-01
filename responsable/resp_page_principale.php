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

defined( '_PHP_CONGES' ) or die( 'Restricted access' );


    /***********************************/
    // AFFICHAGE ETAT CONGES TOUS USERS

        /***********************************/
    // AFFICHAGE TABLEAU (premiere ligne)
    echo "<h1>". _('resp_traite_user_etat_conges') ."</h1>";

    echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
    echo '<thead>';

    $nb_colonnes = 0;

    echo "<tr>\n";
        echo '<th>'. _('divers_nom_maj') .'</th>';
        echo '<th>'. _('divers_prenom_maj') .'</th>';
        echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
        $nb_colonnes = 3;
        foreach($tab_type_cong as $id_conges => $libelle)
        {
            // cas d'une absence ou d'un congé
            echo "<th> $libelle"." / ". _('divers_an_maj') .'</th>';
            echo '<th>'. _('divers_solde_maj') ." ".$libelle .'</th>';
            $nb_colonnes += 2;
        }
        // conges exceptionnels
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
        {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
            {
                echo '<th>'. _('divers_solde_maj') ." $libelle</th>\n";
                $nb_colonnes += 1;
            }
        }
        echo "<th></th>";
        $nb_colonnes += 1;
        if($_SESSION['config']['editions_papier'])
        {
            echo "<th></th>";
            $nb_colonnes += 1;
        }
    echo "</tr>\n";

    echo '</thead>';
    echo '<tbody>';

    /***********************************/
    // AFFICHAGE USERS

    /***********************************/
    // AFFICHAGE DE USERS DIRECTS DU RESP

    // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
    $tab_all_users=recup_infos_all_users_du_resp($_SESSION['userlogin'],  $DEBUG);
    if( $DEBUG ) {echo "tab_all_users :<br>\n";  print_r($tab_all_users); echo "<br>\n"; }

    if(count($tab_all_users)==0) // si le tableau est vide (resp sans user !!) on affiche une alerte !
        echo "<tr align=\"center\"><td class=\"histo\" colspan=\"".$nb_colonnes."\">". _('resp_etat_aucun_user') ."</td></tr>\n" ;
    else
    {
        $i = true;
        foreach($tab_all_users as $current_login => $tab_current_user)
        {
            if($tab_current_user['is_active'] == "Y" || $_SESSION['config']['print_disable_users'] == 'TRUE')
            {
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];
                $text_affich_user="<a class=\"action show\" href=\"resp_index.php?session=$session&onglet=traite_user&user_login=$current_login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a>" ;
                $text_edit_papier="<a class=\"action edit\" href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
                
                echo '<tr class="'.($i?'i':'p').'">';
                echo "<td>".$tab_current_user['nom']."</td><td>".$tab_current_user['prenom']."</td><td>".$tab_current_user['quotite']."%</td>";
                foreach($tab_type_cong as $id_conges => $libelle)
                {
                    echo "<td>".$tab_conges[$libelle]['nb_an'].'</td>';
                    echo "<td>".$tab_conges[$libelle]['solde'].'</td>';
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels'])
                {
                    foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
                    {
                        echo "<td>".$tab_conges[$libelle]['solde'].'</td>';
                    }
                }
                echo "<td>$text_affich_user</td>\n";
                if($_SESSION['config']['editions_papier'])
                    echo "<td>$text_edit_papier</td>";
                echo "</tr>\n";
                $i = !$i;
            }
        }
    }

    /***********************************/
    // AFFICHAGE DE USERS DONT LE RESP EST GRAND RESP

    if($_SESSION['config']['double_validation_conges'])
    {
        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est GRAND responsable
        $tab_all_users_2=recup_infos_all_users_du_grand_resp($_SESSION['userlogin'],  $DEBUG);

        if( $DEBUG ) {echo "tab_all_users_2 :<br>\n";  print_r($tab_all_users_2); echo "<br>\n"; }

        $compteur=0;  // compteur de ligne a afficher en dessous (dés que passe à 1 : on affiche une ligne de titre)

        $i = true;
        foreach($tab_all_users_2 as $current_login_2 => $tab_current_user_2)
        {
            if( !array_key_exists($current_login_2, $tab_all_users) ) // si le user n'est pas déjà dans le tableau précédent (deja affiché)
            {
                $compteur++;
                if($compteur==1)  // alors on affiche une ligne de titre
                {
                    $nb_colspan=9;
                    if ($_SESSION['config']['gestion_conges_exceptionnels'])
                        $nb_colspan=10;

                    echo "<tr align=\"center\"><td class=\"histo\" style=\"background-color: #CCC;\" colspan=\"$nb_colonnes\"><i>". _('resp_etat_users_titre_double_valid') ."</i></td></tr>\n";
                }

                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges_2=$tab_current_user_2['conges'];

                $text_affich_user="<a class=\"action show\" href=\"resp_index.php?session=$session&onglet=traite_user&user_login=$current_login_2\" title=\"". _('resp_etat_users_afficher') ."\"><i class=\"fa fa-eye\"></i></a>" ;
                $text_edit_papier="<a class=\"action print\" href=\"../edition/edit_user.php?session=$session&user_login=$current_login_2\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
                echo '<tr class="'.($i?'i':'p').'">';
                echo "<td>".$tab_current_user_2['nom']."</td><td>".$tab_current_user_2['prenom']."</td><td>".$tab_current_user_2['quotite']."%</td>";
                foreach($tab_type_cong as $id_conges => $libelle)
                {
                    echo "<td>".$tab_conges_2[$libelle]['nb_an']."</td><td>".$tab_conges_2[$libelle]['solde'].'</td>';
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels'])
                {
                    foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
                    {
                        echo "<td>".$tab_conges_2[$libelle]['solde'].'</td>';
                    }
                }
                echo "<td>$text_affich_user</td>\n";
                if($_SESSION['config']['editions_papier'])
                    echo "<td>$text_edit_papier</td>";
                echo "</tr>\n";
                $i = !$i;
            }
        }

    }

    echo '</tbody>';
    echo '</table>';
