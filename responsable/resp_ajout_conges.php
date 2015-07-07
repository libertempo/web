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

	// echo $twig->render('test.html');
	//var pour resp_ajout_conges_all.php
	$ajout_conges            = getpost_variable('ajout_conges');
	$tab_champ_saisie        = getpost_variable('tab_champ_saisie');
	$tab_commentaire_saisie        = getpost_variable('tab_commentaire_saisie');
	//$tab_champ_saisie_rtt    = getpost_variable('tab_champ_saisie_rtt') ;
	$ajout_global            = getpost_variable('ajout_global');
	$ajout_groupe            = getpost_variable('ajout_groupe');
	$choix_groupe            = getpost_variable('choix_groupe');
	$tab_new_nb_conges_all   = getpost_variable('tab_new_nb_conges_all');
	$tab_calcul_proportionnel = getpost_variable('tab_calcul_proportionnel');
	$tab_new_comment_all     = getpost_variable('tab_new_comment_all');
	
	
	if( $DEBUG ) { echo "tab_new_nb_conges_all = <br>"; print_r($tab_new_nb_conges_all); echo "<br>\n" ;}
	if( $DEBUG ) { echo "tab_calcul_proportionnel = <br>"; print_r($tab_calcul_proportionnel); echo "<br>\n" ;}
	
	
	// titre
	echo "<h1>". _('resp_ajout_conges_titre') ."</h1>\n";
	//connexion mysql
	
	if($ajout_conges=="TRUE")
	{
		ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $DEBUG);
	}
	elseif($ajout_global=="TRUE")
	{
		ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all,  $DEBUG);
	}
	elseif($ajout_groupe=="TRUE")
	{
		ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all,  $DEBUG);
	}
	else
	{
		saisie_ajout($tab_type_cong, $DEBUG);
	}



/************************************************************************/
/*** FONCTIONS ***/

function saisie_ajout( $tab_type_conges,  $DEBUG)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;

	// recup du tableau des types de conges (seulement les congesexceptionnels )
	if ($_SESSION['config']['gestion_conges_exceptionnels']) 
	{
	  $tab_type_conges_exceptionnels = recup_tableau_types_conges_exceptionnels();
	  if( $DEBUG ) { echo "tab_type_conges_exceptionnels = "; print_r($tab_type_conges_exceptionnels); echo "<br><br>\n";}
	}
	else
	  $tab_type_conges_exceptionnels = array();
	
	// recup de la liste de TOUS les users dont $resp_login est responsable 
	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
	// renvoit une liste de login entre quotes et séparés par des virgules
	$tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
	$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
	if( $DEBUG ) { echo "tab_all_users_du_resp =<br>\n"; print_r($tab_all_users_du_resp); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
	
	if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		/************************************************************/
		/* SAISIE GLOBALE pour tous les utilisateurs du responsable */
		affichage_saisie_globale_pour_tous($tab_type_conges,  $DEBUG);
		echo "<br>\n";
		
		/***********************************************************************/
		/* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
		if( $_SESSION['config']['gestion_groupes'] )
		{
			affichage_saisie_globale_groupe($tab_type_conges,  $DEBUG);
		}

		echo "<hr/>\n";
		
		/************************************************************/
		/* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
		affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp,  $DEBUG);
		echo "<br>\n";
		
	}
	else
	 echo  _('resp_etat_aucun_user') ."<br>\n";

}


function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_resp, $tab_all_users_du_grand_resp,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/************************************************************/
	/* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
	echo "<h2>Ajout par utilisateur</h2>\n";
	echo " <form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
	
	// Récupération des informations
	// Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
	//$tab_all_users_du_resp=recup_infos_all_users_du_resp($_SESSION['userlogin']);
	//$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
	
	if( (count($tab_all_users_du_resp)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		// AFFICHAGE TITRES TABLEAU
		echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
		echo "<thead>\n";
		echo "<tr align=\"center\">\n";
		echo "<th>". _('divers_nom_maj_1') ."</th>\n";
		echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
		echo "<th>". _('divers_quotite_maj_1') ."</td>\n";
		foreach($tab_type_conges as $id_conges => $libelle)
		{
			echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
			echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') ."</th>\n" ;
		}
		if ($_SESSION['config']['gestion_conges_exceptionnels'])
		{
			foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
			{
				echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
				echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') ."</th>\n" ;
			}
		}
		echo "<th>". _('divers_comment_maj_1') ."<br></th>\n" ;
		echo"</tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
		
		// AFFICHAGE LIGNES TABLEAU
		$cpt_lignes=0 ;
		$tab_champ_saisie_conges=array();
		
		$i = true;
		// affichage des users dont on est responsable :
		foreach($tab_all_users_du_resp as $current_login => $tab_current_user)
		{		
			echo '<tr class="'.($i?'i':'p').'">';
			//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
			$tab_conges=$tab_current_user['conges']; 
	
			/** sur la ligne ,   **/
			echo "<td>".$tab_current_user['nom']."</td>\n";
			echo "<td>".$tab_current_user['prenom']."</td>\n";
			echo "<td>".$tab_current_user['quotite']."%</td>\n";
	
			foreach($tab_type_conges as $id_conges => $libelle)
			{
				/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
				$champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
				echo "<td>".$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
				echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
			}
			if ($_SESSION['config']['gestion_conges_exceptionnels'])
			{
				foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
				{
					/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
					$champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
					echo "<td><i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
					echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
				}
			}
			echo "<td align=\"center\" class=\"histo\"><input class=\"form-control\" type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
			echo "</tr>\n";
			$cpt_lignes++ ;
			$i = !$i;
		}
		
		// affichage des users dont on est grand responsable :
		if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) )
		{
			$nb_colspan=50;
			echo "<tr align=\"center\"><td class=\"histo\" style=\"background-color: #CCC;\" colspan=\"$nb_colspan\"><i>". _('resp_etat_users_titre_double_valid') ."</i></td></tr>\n";

			$i = true;
			foreach($tab_all_users_du_grand_resp as $current_login => $tab_current_user)
			{		
				echo '<tr class="'.($i?'i':'p').'">';
				//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
				$tab_conges=$tab_current_user['conges']; 
		
				/** sur la ligne ,   **/
				echo "<td>".$tab_current_user['nom']."</td>\n";
				echo "<td>".$tab_current_user['prenom']."</td>\n";
				echo "<td>".$tab_current_user['quotite']."%</td>\n";
		
				foreach($tab_type_conges as $id_conges => $libelle)
				{
					/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
					$champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
					echo "<td>".$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
					echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
				}
				if ($_SESSION['config']['gestion_conges_exceptionnels'])
				{
					foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
					{
						/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
						$champ_saisie_conges="<input type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
						echo "<td><i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
						echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
					}
				}
				echo "<td align=\"center\" class=\"histo\"><input type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
				echo "</tr>\n";
				$cpt_lignes++ ;
			$i = !$i;
			}
		}
		
		echo "</tbody>\n";
		echo "</table></div>\n\n";
	
		echo "<input type=\"hidden\" name=\"ajout_conges\" value=\"TRUE\">\n";
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
		echo " </form> \n";
	}
}


function affichage_saisie_globale_pour_tous($tab_type_conges,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/************************************************************/
	/* SAISIE GLOBALE pour tous les utilisateurs du responsable */
	echo "<h2>". _('resp_ajout_conges_ajout_all') ."</h2>\n";
	echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
	echo "	<fieldset class=\"cal_saisie\">\n";
	echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th colspan=\"2\">" . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . "</th>\n";
	echo "<th>" ._('resp_ajout_conges_calcul_prop') . "</th>\n";
	echo "<th>" . _('divers_comment_maj_1') . "</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	foreach($tab_type_conges as $id_conges => $libelle)
	{
		echo "	<tr>\n";
		echo "		<td><strong>$libelle<strong></td>\n";
		echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
		echo "		<td>Oui <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked></td>\n";
		echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
		echo "	</tr>\n";
	}
	echo "</table></div>\n";
	// texte sur l'arrondi du calcul proportionnel
	echo "<p>" . _('resp_ajout_conges_calcul_prop_arondi') . "!) </p>\n";
	// bouton valider
	echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_global') ."\">\n";
	echo "</fieldset>\n";
	echo "<input type=\"hidden\" name=\"ajout_global\" value=\"TRUE\">\n";
	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
	echo "</form> \n";
}


function affichage_saisie_globale_groupe($tab_type_conges,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/***********************************************************************/
	/* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */

	// on établi la liste complète des groupes dont on est le resp (ou le grd resp)
	$list_group_resp=get_list_groupes_du_resp($_SESSION['userlogin']);
	if( ($_SESSION['config']['double_validation_conges']) && ($_SESSION['config']['grand_resp_ajout_conges']) )
		$list_group_grd_resp=get_list_groupes_du_grand_resp($_SESSION['userlogin'],  $DEBUG);
	else
		$list_group_grd_resp="";
		
	$list_group="";
	if($list_group_resp!="")
	{
		$list_group = $list_group_resp;
		if($list_group_grd_resp!="")
			$list_group = $list_group.",".$list_group_grd_resp;
	}
	else
	{
		if($list_group_grd_resp!="")
			$list_group = $list_group_grd_resp;
	}
	
		
	if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
	{
		echo "<h2>". _('resp_ajout_conges_ajout_groupe') ."</h2>\n";
		echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
		echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
		echo "<tr><td>\n";
		echo "	<fieldset class=\"cal_saisie\">\n";
		echo "	<table>\n";
		echo "	<tr>\n";
		echo "		<td class=\"big\">". _('resp_ajout_conges_choix_groupe') ." : </td>\n";
			// création du select pour le choix du groupe
			$text_choix_group="<select name=\"choix_groupe\" >";
			$sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
			$ReqLog_group = SQL::query($sql_group) ;
				
			while ($resultat_group = $ReqLog_group->fetch_array()) 
			{
				$current_group_id=$resultat_group["g_gid"];
				$current_group_name=$resultat_group["g_groupename"];
				$text_choix_group=$text_choix_group."<option value=\"$current_group_id\" >$current_group_name</option>";
			}
			$text_choix_group=$text_choix_group."</select>" ;

		echo "		<td colspan=\"3\">$text_choix_group</td>\n";
		echo "	</tr>\n";
		foreach($tab_type_conges as $id_conges => $libelle)
		{
			echo "	<tr>\n";
			echo "		<td class=\"big\">". _('resp_ajout_conges_nb_jours_groupe_1') ." <strong>$libelle</strong> ". _('resp_ajout_conges_nb_jours_groupe_2') ." </td>\n";
			echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
			echo "		<td> ( ". _('resp_ajout_conges_calcul_prop') ." </td>\n";
			echo "		<td>". _('resp_ajout_conges_oui') ." <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked> )</td>\n";
			echo "		<td>". _('divers_comment_maj_1') ." : <input type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
			echo "	</tr>\n";
		}
		echo "	</table>\n";
		echo "	</fieldset>\n";
		echo "</td></tr>\n";
		echo "</table></div>\n";
		echo "(". _('resp_ajout_conges_calcul_prop_arondi') ." !)<br><br>\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_groupe') ."\">\n";
		echo "<input type=\"hidden\" name=\"ajout_groupe\" value=\"TRUE\">\n";
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "</form> \n";
	}
}

/*********************************************************************************************/


function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie,  $DEBUG=FALSE) 
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id(); 

	foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
	{
	  foreach($tab_conges as $id_conges => $user_nb_jours_ajout)
	  {
		$user_nb_jours_ajout_float =(float) $user_nb_jours_ajout ;
		$valid=verif_saisie_decimal($user_nb_jours_ajout_float, $DEBUG);   //verif la bonne saisie du nombre décimal
	    if($valid)
	    {
	      if( $DEBUG ) {echo "$user_name --- $id_conges --- $user_nb_jours_ajout_float<br>\n";}

	      if($user_nb_jours_ajout_float!=0)
	      {
			/* Modification de la table conges_users */
			$sql1 = "UPDATE conges_solde_user SET su_solde = su_solde+$user_nb_jours_ajout_float WHERE su_login='$user_name' AND su_abs_id = $id_conges " ;
			/* On valide l'UPDATE dans la table ! */
			$ReqLog1 = SQL::query($sql1) ;
			
/*			// Enregistrement du commentaire relatif à l'ajout de jours de congés 
			$comment = $tab_commentaire_saisie[$user_name];
			$sql1 = "INSERT INTO conges_historique_ajout (ha_login, ha_date, ha_abs_id, ha_nb_jours, ha_commentaire)
					  VALUES ('$user_name', NOW(), $id_conges, $user_nb_jours_ajout_float , '$comment')";
			$ReqLog1 = SQL::query($sql1) ;
*/	
			// on insert l'ajout de conges dans la table periode
			$commentaire =  _('resp_ajout_conges_comment_periode_user') ;
			insert_ajout_dans_periode($DEBUG, $user_name, $user_nb_jours_ajout_float, $id_conges, $commentaire);
	      }
	    }
	  }
	}

	if( $DEBUG )
	{
		echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
		echo "</form>\n" ;
	}
	else
	{
		echo " ". _('form_modif_ok') ." <br><br> \n";
		/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session\">";
	}

}


function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	// $tab_new_nb_conges_all[$id_conges]= nb_jours
	// $tab_calcul_proportionnel[$id_conges]= TRUE / FALSE
	
	// recup de la liste de TOUS les users dont $resp_login est responsable 
	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
	// renvoit une liste de login entre quotes et séparés par des virgules
	$list_users_du_resp = get_list_all_users_du_resp($_SESSION['userlogin'],  $DEBUG);
	if( $DEBUG ) { echo "list_all_users_du_resp = $list_users_du_resp<br>\n";}
	
	if( $DEBUG ) { echo "tab_new_nb_conges_all = <br>"; print_r($tab_new_nb_conges_all); echo "<br>\n" ;}
	if( $DEBUG ) { echo "tab_calcul_proportionnel = <br>"; print_r($tab_calcul_proportionnel); echo "<br>\n" ;}

	foreach($tab_new_nb_conges_all as $id_conges => $nb_jours)
	{
		if($nb_jours!=0)
		{
			$comment = $tab_new_comment_all[$id_conges];
			
			$sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
			$ReqLog1 = SQL::query($sql1);
				
			while($resultat1 = $ReqLog1->fetch_array()) 
			{
				$current_login  =$resultat1["u_login"];
				$current_quotite=$resultat1["u_quotite"];
				
				if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
					$nb_conges=$nb_jours;
				else
					// pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2 
					$nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;

				$nb_conges_ok = verif_saisie_decimal($nb_conges, $DEBUG);
				if ($nb_conges_ok)
				{
					// 1 : update de la table conges_solde_user
					$req_update = "UPDATE conges_solde_user SET su_solde = su_solde+$nb_conges
							WHERE  su_login = '$current_login' AND su_abs_id = $id_conges   ";
					$ReqLog_update = SQL::query($req_update);
			
					// 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
					$commentaire =  _('resp_ajout_conges_comment_periode_all') ;
					// ajout conges
					insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
				}		
			}
			// 3 : Enregistrement du commentaire relatif à l'ajout de jours de congés 
			if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
				$comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
			else
				$comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
			log_action(0, "ajout", "tous", $comment_log,  $DEBUG);
		}
	}
	
	if( $DEBUG )
	{
		echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
		echo "</form>\n" ;
	}
	else
	{
		echo " ". _('form_modif_ok') ." <br><br> \n";
		/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
		redirect( ROOT_PATH .'responsable/resp_index.php?session=' . $session );
	}
}

function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all,  $DEBUG=FALSE)
{
	// $tab_new_nb_conges_all[$id_conges]= nb_jours
	// $tab_calcul_proportionnel[$id_conges]= TRUE / FALSE
	
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	// recup de la liste des users d'un groupe donné 
	$list_users = get_list_users_du_groupe($choix_groupe,  $DEBUG);
	
	foreach($tab_new_nb_conges_all as $id_conges => $nb_jours)
	{
		if($nb_jours!=0)
		{
			$comment = $tab_new_comment_all[$id_conges];

			$sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) ORDER BY u_login ";
			$ReqLog1 = SQL::query($sql1);
				
			while ($resultat1 = $ReqLog1->fetch_array()) 
			{
				$current_login  =$resultat1["u_login"];
				$current_quotite=$resultat1["u_quotite"];
				
				if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
					$nb_conges=$nb_jours;
				else
					// pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2 
					$nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
					$nb_conges_ok = verif_saisie_decimal($nb_conges, $DEBUG);
				if($nb_conges_ok){
					// 1 : on update conges_solde_user
					$req_update = "UPDATE conges_solde_user SET su_solde = su_solde+$nb_conges
							WHERE  su_login = '$current_login' AND su_abs_id = $id_conges   ";
					$ReqLog_update = SQL::query($req_update);
				
					// 2 : on insert l'ajout de conges dans la table periode
					// recup du nom du groupe
					$groupename= get_group_name_from_id($choix_groupe,  $DEBUG);
					$commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";
				
					// ajout conges
					insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
				}

			}

			$group_name = get_group_name_from_id($choix_groupe,  $DEBUG);
			// 3 : Enregistrement du commentaire relatif à l'ajout de jours de congés 
			if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
				$comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
			else
				$comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
			log_action(0, "ajout", "groupe", $comment_log,  $DEBUG);
		}
	}

	if( $DEBUG )
	{
		echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
		echo "</form>\n" ;
	}
	else
	{
		echo " ". _('form_modif_ok') ." <br><br> \n";
		redirect( ROOT_PATH .'responsable/resp_index.php?session=' . $session );
	}
}



// on insert l'ajout de conges dans la table periode
function insert_ajout_dans_periode($DEBUG, $login, $nb_jours, $id_type_abs, $commentaire)
{
	$date_today=date("Y-m-d");
	
	$result=insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0,  $DEBUG);
}

