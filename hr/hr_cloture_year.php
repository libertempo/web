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



	/*************************************/
	// recup des parametres reçus :

	$cloture_users           = getpost_variable('cloture_users');
	$cloture_globale         = getpost_variable('cloture_globale');
	$cloture_groupe          = getpost_variable('cloture_groupe');
	/*************************************/
	
	
	/** initialisation des tableaux des types de conges/absences  **/
	// recup du tableau des types de conges (conges et congesexceptionnels)
	// on concatene les 2 tableaux
	$tab_type_cong = ( recup_tableau_types_conges($DEBUG) + recup_tableau_types_conges_exceptionnels($DEBUG)  );

	// titre
	echo '<h2>'. _('resp_cloture_exercice_titre') ."</H2>\n\n";
		
	if($cloture_users=="TRUE") {
		$tab_cloture_users       = getpost_variable('tab_cloture_users');
		cloture_users($tab_type_cong, $tab_cloture_users, $tab_commentaire_saisie, $DEBUG);
		
		redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
		exit;
	}
	elseif($cloture_globale=="TRUE") {
		cloture_globale($tab_type_cong, $DEBUG);
		
		redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
		exit;
	}
	elseif($cloture_groupe=="TRUE") {
		$choix_groupe            = getpost_variable('choix_groupe');
		cloture_globale_groupe($choix_groupe, $tab_type_cong, $DEBUG);
		
		redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
		exit;
	}
	else {
		saisie_cloture($tab_type_cong,$DEBUG);
	}

	


/************************************************************************/
/*** FONCTIONS ***/

function saisie_cloture( $tab_type_conges, $DEBUG)
{
//$DEBUG;
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;

	// recup de la liste de TOUS les users dont $resp_login est responsable 
	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
	// renvoit une liste de login entre quotes et séparés par des virgules
	$tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
	$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);    
	if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
	
	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		/************************************************************/
		/* SAISIE GLOBALE pour tous les utilisateurs du responsable */
		affichage_cloture_globale_pour_tous($tab_type_conges, $DEBUG);
		echo "<br>\n";
		
		/***********************************************************************/
		/* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
		if( $_SESSION['config']['gestion_groupes'] )
		{
			affichage_cloture_globale_groupe($tab_type_conges, $DEBUG);
		}
		echo "<br>\n";

		/************************************************************/
		/* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
		affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG);
		echo "<br>\n";
		
	}
	else
	 echo  _('resp_etat_aucun_user') ."<br>\n";
}


function affichage_cloture_user_par_user($tab_type_conges, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/************************************************************/
	/* CLOTURE EXERCICE USER PAR USER pour tous les utilisateurs du responsable */
	
	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		echo "<form action=\"$PHP_SELF?session=$session&onglet=cloture_year\" method=\"POST\"> \n";
		echo "<table>\n";
		echo '<tr>';
		echo "<td align=\"center\">\n";
		echo "<fieldset class=\"cal_saisie\">\n";
		echo "<legend class=\"boxlogin\">". _('resp_cloture_exercice_users') ."</legend>\n";
		echo "	<table>\n";
		echo "	<tr>\n";
		echo "	<td align=\"center\">\n";

		// AFFICHAGE TITRES TABLEAU
		echo "	<table cellpadding=\"2\" class=\"tablo\">\n";
		echo "  <thead>\n";
		echo "	<tr>\n";
		echo "	<th>". _('divers_nom_maj_1') .'</th>';
		echo "	<th>". _('divers_prenom_maj_1') .'</th>';
		echo "	<th>". _('divers_quotite_maj_1') .'</th>';
		foreach($tab_type_conges as $id_conges => $libelle)
		{
			echo "	<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
		}
		echo "	<th>". _('divers_cloturer_maj_1') ."<br></th>\n" ;
		echo "	<th>". _('divers_comment_maj_1') ."<br></th>\n" ;
		echo "	</tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		
		// AFFICHAGE LIGNES TABLEAU

		// affichage des users dont on est responsable :
		$i = true;
		foreach($tab_all_users_du_hr as $current_login => $tab_current_user)
		{		
			affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i);
			$i = !$i;
		}
		
		echo "	</tbody>\n\n";
		echo "	</table>\n\n";

		echo "	</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "	<td align=\"center\">\n";
		echo "	<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
		echo "	</td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		
		echo "</fieldset>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"cloture_users\" value=\"TRUE\">\n";
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "</form> \n";
	}
}

function affiche_ligne_du_user($current_login, $tab_type_conges, $tab_current_user, $i = true)
{
	echo '<tr class="'.($i?'i':'p').'">';
	//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
	$tab_conges=$tab_current_user['conges']; 
	
	/** sur la ligne ,   **/
	echo " <td>".$tab_current_user['nom'].'</td>';
	echo " <td>".$tab_current_user['prenom'].'</td>';
	echo " <td>".$tab_current_user['quotite']."%</td>\n";
	
	foreach($tab_type_conges as $id_conges => $libelle)
	{
		echo " <td>".$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
	}
			
	// si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on peut le cocher
	if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice'])
		echo "	<td align=\"center\" class=\"histo\"><input type=\"checkbox\" name=\"tab_cloture_users[$current_login]\" value=\"TRUE\" checked></td>\n";
	else
		echo "	<td align=\"center\" class=\"histo\"><img src=\"". TEMPLATE_PATH ."img/stop.png\" width=\"16\" height=\"16\" border=\"0\" ></td>\n";
			
	$comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");
	echo "	<td align=\"center\" class=\"histo\"><input type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"20\" maxlength=\"200\" value=\"$comment_cloture\"></td>\n";
	echo " 	</tr>\n";
}


function affichage_cloture_globale_pour_tous($tab_type_conges, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/************************************************************/
	/* CLOTURE EXERCICE GLOBALE pour tous les utilisateurs du responsable */
	
	echo "<form action=\"$PHP_SELF?session=$session&onglet=cloture_year\" method=\"POST\"> \n";
	echo "<table>\n";
	echo "<tr><td align=\"center\">\n";
	echo "	<fieldset class=\"cal_saisie\">\n";
	echo "	<legend class=\"boxlogin\">". _('resp_cloture_exercice_all') ."</legend>\n";
	echo "	<table>\n";
	echo "	<tr>\n";
	echo "		<td class=\"big\">&nbsp;&nbsp;&nbsp;". _('resp_cloture_exercice_for_all_text_confirmer') ." &nbsp;&nbsp;&nbsp;</td>\n";
	echo "	</tr>\n";
	// bouton valider
	echo "	<tr>\n";
	echo "		<td colspan=\"5\" align=\"center\"><input type=\"submit\" value=\"". _('form_valid_cloture_global') ."\"></td>\n";
	echo "	</tr>\n";
	echo "	</table>\n";
	echo "	</fieldset>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"cloture_globale\" value=\"TRUE\">\n";
	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
	echo "</form> \n";
}


function affichage_cloture_globale_groupe($tab_type_conges, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	
	/***********************************************************************/
	/* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */

	// on établi la liste complète des groupes dont on est le resp (ou le grd resp)
	$list_group=get_list_groupes_du_resp($_SESSION['userlogin']);
		
	if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
	{
		echo "<form action=\"$PHP_SELF\" method=\"POST\"> \n";
		echo "<table>\n";
		echo "<tr><td align=\"center\">\n";
		echo "	<fieldset class=\"cal_saisie\">\n";
		echo "	<legend class=\"boxlogin\">". _('resp_cloture_exercice_groupe') ."</legend>\n";
		
		echo "	<table>\n";
		echo "	<tr>\n";

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

		echo "		<td class=\"big\">". _('resp_ajout_conges_choix_groupe') ." : $text_choix_group</td>\n";
		
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"big\">". _('resp_cloture_exercice_for_groupe_text_confirmer') ." </td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\"><input type=\"submit\" value=\"". _('form_valid_cloture_group') ."\"></td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		
		echo "	</fieldset>\n";
		echo "</td></tr>\n";
		echo "</table>\n";

		echo "<input type=\"hidden\" name=\"onglet\" value=\"cloture_exercice\">\n";
		echo "<input type=\"hidden\" name=\"cloture_groupe\" value=\"TRUE\">\n";
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "</form> \n";
	}
}

/*********************************************************************************************/

// cloture / debut d'exercice user par user pour les users du resp (ou grand resp)
function cloture_users($tab_type_conges, $tab_cloture_users, $tab_commentaire_saisie, $DEBUG=FALSE) 
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id(); 

	// recup de la liste de TOUS les users dont $resp_login est responsable 
	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
	// renvoit une liste de login entre quotes et séparés par des virgules
	$tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
	$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
	if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_cloture_users =<br>\n"; print_r($tab_cloture_users); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_commentaire_saisie =<br>\n"; print_r($tab_commentaire_saisie); echo "<br>\n"; }
	
	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		// traitement des users dont on est responsable :
		foreach($tab_all_users_du_hr as $current_login => $tab_current_user)
		{		
			// tab_cloture_users[$current_login]=TRUE si checkbox "cloturer" est cochée
			if( (isset($tab_cloture_users[$current_login])) && ($tab_cloture_users[$current_login]=TRUE) )
			{
				$commentaire = $tab_commentaire_saisie[$current_login];
				cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire, $DEBUG);
			}
		}
	}
}


function cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $commentaire, $DEBUG=FALSE)
{
	// si le num d'exercice du user est < à celui de l'appli (il n'a pas encore été basculé): on le bascule d'exercice
	if($tab_current_user['num_exercice'] < $_SESSION['config']['num_exercice'])
	{
		// calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée)
		set_nouvelle_date_limite_reliquat($DEBUG);
		
		//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
		$tab_conges_current_user=$tab_current_user['conges']; 
		foreach($tab_type_conges as $id_conges => $libelle)
		{
			$user_nb_jours_ajout_an = $tab_conges_current_user[$libelle]['nb_an'];
			$user_solde_actuel= $tab_conges_current_user[$libelle]['solde'];
			$user_reliquat_actuel= $tab_conges_current_user[$libelle]['reliquat'];
			
			if( $DEBUG ) {echo "$current_login --- $id_conges --- $user_nb_jours_ajout_an<br>\n";}
	
			/**********************************************/
			/* Modification de la table conges_solde_user */
			
			if($_SESSION['config']['autorise_reliquats_exercice'])
			{
				// ATTENTION : si le solde du user est négatif, on ne compte pas de reliquat et le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
				if($user_solde_actuel>0)
				{
					//calcul du reliquat pour l'exercice suivant
					if($_SESSION['config']['nb_maxi_jours_reliquats']!=0)
					{
						if($user_solde_actuel <= $_SESSION['config']['nb_maxi_jours_reliquats'])
							$new_reliquat = $user_solde_actuel ;
						else
							$new_reliquat = $_SESSION['config']['nb_maxi_jours_reliquats'] ;
					}
					else
						$new_reliquat = $user_reliquat_actuel + $user_solde_actuel ;

					$new_reliquat = str_replace(',', '.', $new_reliquat);
					//
					// update D'ABORD du reliquat
					$sql_reliquat = 'UPDATE conges_solde_user SET su_reliquat = '.$new_reliquat.' WHERE su_login=\''.SQL::quote($current_login).'\'  AND su_abs_id = '.$id_conges;
					$ReqLog_reliquat = SQL::query($sql_reliquat) ;
				}
				else
					$new_reliquat = $user_solde_actuel ; // qui est nul ou negatif

				$new_solde = $user_nb_jours_ajout_an + $new_reliquat  ;
				$new_solde = str_replace(',', '.', $new_solde);

				// update du solde
				$sql_solde = 'UPDATE conges_solde_user SET su_solde = '.intval($new_solde).' WHERE su_login=\''.SQL::quote($current_login).'\'  AND su_abs_id = '.intval($id_conges).';';
				$ReqLog_solde = SQL::query($sql_solde) ;
			}
			else
			{
				// ATTENTION : meme si on accepte pas les reliquats, si le solde du user est négatif, il faut le reporter: le nouveau solde est nb_jours_an + le solde actuel (qui est négatif)
				if($user_solde_actuel < 0)
					$new_solde = $user_nb_jours_ajout_an + $user_solde_actuel ; // qui est nul ou negatif
				else
					$new_solde = $user_nb_jours_ajout_an ;

				$new_solde = str_replace(',', '.', $new_solde);
				$sql_solde = 'UPDATE conges_solde_user SET su_solde = '.intval($new_solde).' WHERE su_login=\''.SQL::quote($current_login).'\' AND su_abs_id = '.intval($id_conges).';';
				$ReqLog_solde = SQL::query($sql_solde) ;
			}

			/* Modification de la table conges_users */
			// ATTENTION : ne pas faire "SET u_num_exercice = u_num_exercice+1" dans la requete SQL car on incrémenterait pour chaque type d'absence !
			$new_num_exercice=$_SESSION['config']['num_exercice'] ;
			$sql2 = 'UPDATE conges_users SET u_num_exercice = '.$new_num_exercice.' WHERE u_login=\''.SQL::quote($current_login).'\'  ';
			$ReqLog2 = SQL::query($sql2) ;
			
			// on insert l'ajout de conges dans la table periode (avec le commentaire)
			$date_today=date("Y-m-d");
			insert_dans_periode($current_login, $date_today, "am", $date_today, "am", $user_nb_jours_ajout_an, $commentaire, $id_conges, "ajout", 0, $DEBUG);
	    }
	    
	    // on incrémente le num_exercice de l'application si tous les users on été basculés.
	    update_appli_num_exercice($DEBUG);
	}	
}


// verifie si tous les users on été basculés de l'exercice précédent vers le suivant.
// si oui : on incrémente le num_exercice de l'application
function update_appli_num_exercice($DEBUG=FALSE)
{
	// verif
	$appli_num_exercice = $_SESSION['config']['num_exercice'] ;
	$sql_verif = "SELECT u_login FROM conges_users WHERE u_login != 'admin' AND u_login != 'conges' AND u_num_exercice != $appli_num_exercice "  ;
	$ReqLog_verif = SQL::query($sql_verif) ;
				
	if($ReqLog_verif->num_rows == 0)
	{
		/* Modification de la table conges_appli */
		$sql_update= "UPDATE conges_appli SET appli_valeur = appli_valeur+1 WHERE appli_variable='num_exercice' ";
		$ReqLog_update = SQL::query($sql_update) ;
		
		// ecriture dans les logs
		$new_appli_num_exercice = $appli_num_exercice+1 ;
		log_action(0, "", "", "fin/debut exercice (appli_num_exercice : $appli_num_exercice -> $new_appli_num_exercice)", $DEBUG);
	} 

}


// cloture / debut d'exercice pour TOUS les users du resp (ou grand resp)
function cloture_globale($tab_type_conges, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id(); 

	// recup de la liste de TOUS les users dont $resp_login est responsable 
	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
	// renvoit une liste de login entre quotes et séparés par des virgules
	$tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
	$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
	if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }
	
	$comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
	{
		// traitement des users dont on est responsable :
		foreach($tab_all_users_du_hr as $current_login => $tab_current_user)
		{		
			cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture, $DEBUG);
		}
	}
	


}

// cloture / debut d'exercice pour TOUS les users d'un groupe'
function cloture_globale_groupe($group_id, $tab_type_conges, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id(); 

	// recup de la liste de TOUS les users du groupe
	$tab_all_users_du_groupe=recup_infos_all_users_du_groupe($group_id, $DEBUG);
	if( $DEBUG ) { echo "tab_all_users_du_groupe =<br>\n"; print_r($tab_all_users_du_groupe); echo "<br>\n"; }
	if( $DEBUG ) { echo "tab_type_conges =<br>\n"; print_r($tab_type_conges); echo "<br>\n"; }
	
	$comment_cloture =  _('resp_cloture_exercice_commentaire') ." ".date("m/Y");

	if(count($tab_all_users_du_groupe)!=0)
	{
		// traitement des users dont on est responsable :
		foreach($tab_all_users_du_groupe as $current_login => $tab_current_user)
		{		
			cloture_current_year_for_login($current_login, $tab_current_user, $tab_type_conges, $comment_cloture, $DEBUG);
		}
	}

}

// calcule de la date limite d'utilisation des reliquats (si on utilise une date limite et qu'elle n'est pas encore calculée) et stockage dans la table
function set_nouvelle_date_limite_reliquat($DEBUG=FALSE)
{
	//si on autorise les reliquats
	if($_SESSION['config']['autorise_reliquats_exercice'])
	{
		// s'il y a une date limite d'utilisationdes reliquats (au format jj-mm)
		if($_SESSION['config']['jour_mois_limite_reliquats']!=0)
		{
			// nouvelle date limite au format aaa-mm-jj
			$t=explode("-", $_SESSION['config']['jour_mois_limite_reliquats']);
			$new_date_limite = date("Y")."-".$t[1]."-".$t[0];
			
			//si la date limite n'a pas encore été updatée
			if($_SESSION['config']['date_limite_reliquats'] < $new_date_limite)
			{
				/* Modification de la table conges_appli */
				$sql_update= 'UPDATE conges_appli SET appli_valeur = \''.$new_date_limite.'\' WHERE appli_variable=\'date_limite_reliquats\';';
				$ReqLog_update = SQL::query($sql_update) ;
				
			}
		}
	}
}


