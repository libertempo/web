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


//var pour resp_traite_demande_all.php
$tab_bt_radio   = getpost_variable('tab_bt_radio');
$tab_text_refus = getpost_variable('tab_text_refus');

// titre
echo '<h2>'. _('resp_traite_demandes_titre') .'</h2>';

// si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
if( $tab_bt_radio == '' )
	affiche_all_demandes_en_cours($tab_type_cong, $DEBUG);
	else 
	{
		traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG);
		redirect( ROOT_PATH .'hr/hr_index.php?session='.$session.'&onglet='.$onglet, false);
		exit;
	}

/********************************************/
/*   FONCTIONS   */

function affiche_all_demandes_en_cours($tab_type_conges, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;
	$count1=0;
	$count2=0;

	$tab_type_all_abs = recup_tableau_tout_types_abs();

	// recup du tableau des types de conges (seulement les conges exceptionnels)
	$tab_type_conges_exceptionnels=array();
	if ($_SESSION['config']['gestion_conges_exceptionnels'])
		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

	/*********************************/
	// Récupération des informations
	/*********************************/

	// Récup dans un tableau de tableau des informations de tous les users
	$tab_all_users=recup_infos_all_users($DEBUG);
	if( $DEBUG ) { echo "tab_all_users :<br>\n"; print_r($tab_all_users); echo "<br><br>\n";}

	// si tableau des users du resp n'est pas vide
	if( count($tab_all_users)!=0 )
	{
		// constitution de la liste (séparé par des virgules) des logins ...
		$list_users="";
		foreach($tab_all_users as $current_login => $tab_current_user)
		{
			if($list_users=="")
				$list_users= "'$current_login'" ;
			else
				$list_users=$list_users.", '$current_login'" ;
		}
	}

	/*********************************/




	echo " <form action=\"$PHP_SELF?session=$session&onglet=traitement_demandes\" method=\"POST\"> \n" ;

	/*********************************/
	/* TABLEAU DES DEMANDES DES USERS*/
	/*********************************/

	// si tableau des users n'est pas vide :)
	if( count($tab_all_users)!=0 )
	{

		// Récup des demandes en cours pour les users :
		$sql1 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
		$sql1=$sql1." WHERE p_etat IN (\"valid\",\"demande\") ";
		$sql1=$sql1." AND p_login IN ($list_users) ";
		$sql1=$sql1." ORDER BY p_num";

		$ReqLog1 = \includes\SQL::query($sql1) ;

		$count1 = $ReqLog1->num_rows;
		if($count1!=0)
		{
			// AFFICHAGE TABLEAU DES DEMANDES EN COURS

			echo "<h3>". _('resp_traite_demandes_titre_tableau_1') ."</h3>\n" ;
			echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\">\n" ;
			echo '<thead>' ;
			echo '<tr>' ;
			echo '<th>'. _('divers_nom_maj_1') ."<br>". _('divers_prenom_maj_1') .'</th>' ;

			if($_SESSION['config']['double_validation_conges'])
				echo '<th>'. _('admin_groupes_double_valid') .'</th>' ;

			echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
			echo "<th>". _('divers_type_maj_1') ."</th>\n" ;
			echo '<th>'. _('divers_debut_maj_1') .'</th>' ;
			echo '<th>'. _('divers_fin_maj_1') .'</th>' ;
			echo '<th>'. _('divers_comment_maj_1') .'</th>' ;
			echo '<th>'. _('resp_traite_demandes_nb_jours') .'</th>';
			echo "<th>". _('divers_solde') ."</th>\n" ;
			echo '<th>'. _('divers_accepter_maj_1') .'</th>' ;
			echo '<th>'. _('divers_refuser_maj_1') .'</th>' ;
			echo '<th>'. _('resp_traite_demandes_attente') .'</th>' ;
			echo '<th>'. _('resp_traite_demandes_motif_refus') .'</th>' ;
			if( $_SESSION['config']['affiche_date_traitement'] )
			echo '<th>'. _('divers_date_traitement') .'</th>' ;
			echo '</tr>';
			echo '</thead>' ;
			echo '<tbody>' ;
			$i = true;
			$tab_bt_radio=array();
			while ($resultat1 = $ReqLog1->fetch_array())
			{
				/** sur la ligne ,   **/
				/** le 1er bouton radio est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--OK"> */
				/**  et le 2ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--not_OK"> */
				/**  et le 3ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--RIEN"> */

				$sql_p_date_deb         = $resultat1["p_date_deb"];
				$sql_p_date_fin         = $resultat1["p_date_fin"];
				$sql_p_date_deb_fr      = eng_date_to_fr($resultat1["p_date_deb"]);
				$sql_p_date_fin_fr      = eng_date_to_fr($resultat1["p_date_fin"]);
				$sql_p_demi_jour_deb    = $resultat1["p_demi_jour_deb"] ;
				$sql_p_demi_jour_fin    = $resultat1["p_demi_jour_fin"] ;
				$sql_p_commentaire      = $resultat1["p_commentaire"];
				$sql_p_num              = $resultat1["p_num"];
				$sql_p_login            = $resultat1["p_login"];
				$sql_p_nb_jours         = affiche_decimal($resultat1["p_nb_jours"]);
				$sql_p_type             = $resultat1["p_type"];
				$sql_p_date_demande     = $resultat1["p_date_demande"];
				$sql_p_date_traitement  = $resultat1["p_date_traitement"];

				if($sql_p_demi_jour_deb=="am")
					$demi_j_deb="mat";
				else
					$demi_j_deb="aprm";

				if($sql_p_demi_jour_fin=="am")
					$demi_j_fin="mat";
				else
					$demi_j_fin="aprm";

				// on construit la chaine qui servira de valeur à passer dans les boutons-radio
				$chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";
				$boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
				$boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
				$boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";
				$text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

				echo '<tr class="'.($i?'i':'p').'">';
				echo "<td><b>".$tab_all_users[$sql_p_login]['nom']."</b><br>".$tab_all_users[$sql_p_login]['prenom']."</td>";

				if($_SESSION['config']['double_validation_conges'])
					echo "<td>".$tab_all_users[$sql_p_login]['double_valid']."</td>";

				echo "<td>".$tab_all_users[$sql_p_login]['quotite']."%</td>";
				echo "<td>".$tab_type_all_abs[$sql_p_type]['libelle']."</td>\n";	
				echo "<td>$sql_p_date_deb_fr <span class=\"demi\">$demi_j_deb</span></td><td>$sql_p_date_fin_fr <span class=\"demi\">$demi_j_fin</span></td><td>$sql_p_commentaire</td><td><b>$sql_p_nb_jours</b></td>";
				$tab_conges=$tab_all_users[$sql_p_login]['conges'];
				echo "<td>".$tab_conges[$tab_type_all_abs[$sql_p_type]['libelle']]['solde']."</td>";
				echo "<td>$boutonradio1</td><td>$boutonradio2</td><td>$boutonradio3</td><td>$text_refus</td>\n";
				if($_SESSION['config']['affiche_date_traitement'])
				{
					if($sql_p_date_demande == NULL)
						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
					else
						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
				}
				echo '</tr>' ;
				$i = !$i;
			} // while
			echo '</tbody>' ;
			echo '</table>' ;
		} //if($count1!=0)
	} //if( count($tab_all_users)!=0 )

	echo "<br>\n";

	if(($count1==0) && ($count2==0))
		echo "<strong>". _('resp_traite_demandes_aucune_demande') ."</strong>\n";
	else {
		echo "<hr/>\n";
		echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n" ;
	}
	echo " </form> \n" ;

}


function traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	while($elem_tableau = each($tab_bt_radio))
	{
		$champs             = explode("--", $elem_tableau['value']);
		$user_login         = $champs[0];
		$user_nb_jours_pris = $champs[1];
		$type_abs           = $champs[2];   // id du type de conges demandé
		$date_deb           = $champs[3];
		$demi_jour_deb      = $champs[4];
		$date_fin           = $champs[5];
		$demi_jour_fin      = $champs[6];
		$reponse            = $champs[7];

		$numero             = $elem_tableau['key'];
		$numero_int         = (int) $numero;
		echo "$numero---$user_login---$user_nb_jours_pris---$reponse<br>\n";

		/* Modification de la table conges_periode */
		if(strcmp($reponse, "OK")==0)
		{
			/* UPDATE table "conges_periode" */
			$sql1 = 'UPDATE conges_periode SET p_etat=\'ok\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );' ;
			/* On valide l'UPDATE dans la table "conges_periode" ! */
			$ReqLog1 = \includes\SQL::query($sql1) ;
			if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {
			
				// Log de l'action
				log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse",  $DEBUG);
				
				/* UPDATE table "conges_solde_user" (jours restants) */
				soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);
				
				//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
				if($_SESSION['config']['mail_valid_conges_alerte_user'])
					alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges",  $DEBUG);
			}
		}
		elseif(strcmp($reponse, "not_OK")==0)
		{
			// recup du motif de refus
			$motif_refus=addslashes($tab_text_refus[$numero_int]);
			$sql1 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';
			
			/* On valide l'UPDATE dans la table ! */
			$ReqLog1 = \includes\SQL::query($sql1) ;
			if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {
			
				// Log de l'action
				log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : refus",  $DEBUG);
				
				
				//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
				if($_SESSION['config']['mail_refus_conges_alerte_user'])
					alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges",  $DEBUG);
			}
		}
	}

	if( $DEBUG )
	{
		echo "<form action=\"$PHP_SELF?sesssion=$session&onglet=traitement_demande\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
		echo "</form>\n" ;
	}
	else
	{
		echo  _('form_modif_ok') ."<br><br> \n";
		/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&onglet=traitement_demandes\">";
	}
			//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
			if($_SESSION['config']['mail_refus_conges_alerte_user'])
				alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges", $DEBUG);

}


