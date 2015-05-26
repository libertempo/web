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
	// echo "<th>". _('divers_nom_maj_1') ."</th>\n";
	// echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
	echo "<th>Utilisateur</th>\n";
	// echo "<th>". _('divers_login_maj_1') ."</th>\n";
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
	// echo "<th>". _('admin_users_is_resp') ."</th>\n";
	// echo "<th>". _('admin_users_resp_login') ."</th>\n";
	// echo "<th>". _('admin_users_is_admin') ."</th>\n";
	// echo "<th>". _('admin_users_is_hr') ."</th>\n";
	// echo "<th>". _('admin_users_is_active') ."</th>\n";
	// echo "<th>". _('admin_users_see_all') ."</th>\n";
	echo "<th></th>\n";
	echo "<th></th>\n";
	if($_SESSION['config']['admin_change_passwd'])
		echo "<th></th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	// Récuperation des informations des users:
	$tab_info_users=array();
	// si l'admin peut voir tous les users  OU si on est en mode "responsble virtuel" OU si l'admin n'est pas responsable
	if( $_SESSION['config']['admin_see_all'] || $_SESSION['config']['responsable_virtuel'] || !is_resp($_SESSION['userlogin']) )
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


