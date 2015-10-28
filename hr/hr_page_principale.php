<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)Copyright (C) 2015 (Prytoegrian)Copyright (C) 2005 (cedric chauvineau)

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
	echo '<h2>'. _('hr_traite_user_etat_conges') ."</H2>\n\n";
	echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
	echo '<thead>';
	echo '<tr>';
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
	echo '<th></th>';
	$nb_colonnes += 1;
	if($_SESSION['config']['editions_papier'])
	{
		echo '<th></th>';
		$nb_colonnes += 1;
	}
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	/***********************************/
	// AFFICHAGE USERS
	/***********************************/
	// AFFICHAGE DE USERS DIRECTS DU RESP

	// Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
	$tab_all_users=recup_infos_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
	if( $DEBUG ) {echo "tab_all_users :<br>\n";  print_r($tab_all_users); echo "<br>\n"; }

	if(count($tab_all_users)==0) // si le tableau est vide (resp sans user !!) on affiche une alerte !
		echo "<tr><td class=\"histo\" colspan=\"".$nb_colonnes."\">". _('resp_etat_aucun_user') ."</td></tr>\n" ;
	else
	{
		foreach($tab_all_users as $current_login => $tab_current_user)
		{
			//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
			$tab_conges=$tab_current_user['conges'];
			$text_affich_user="<a href=\"hr_index.php?session=$session&onglet=traite_user&user_login=$current_login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a>" ;
			$text_edit_papier="<a href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
			if($tab_current_user['is_active'] == "Y" || $_SESSION['config']['print_disable_users'] == 'TRUE')
				{ echo '<tr>'; }
			else
				{ echo '<tr class="hidden">'; }
			echo '<td>'.$tab_current_user['nom']."</td><td>".$tab_current_user['prenom']."</td><td>".$tab_current_user['quotite']."%</td>";
			foreach($tab_type_cong as $id_conges => $libelle)
			{
				echo '<td>'.$tab_conges[$libelle]['nb_an'].'</td>';
				echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
			}
			if ($_SESSION['config']['gestion_conges_exceptionnels'])
			{
				foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
				{
					echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
				}
			}
			echo "<td>$text_affich_user</td>\n";
			if($_SESSION['config']['editions_papier'])
			echo "<td>$text_edit_papier</td>";
			echo '</tr>';
		}
	}



	echo '</tbody>';
	echo '</table>';


	echo '<script>
	$(document).ready(function()
		{
		$("tr:not(.hidden):odd").css("background-color", "#F4F4F4");
		$("#display_hidden").click(function () {
			$(".hidden").slideToggle();
			});
		});
	</script>';
