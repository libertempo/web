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


if($_SESSION['config']['where_to_find_user_email']=="ldap"){ include CONFIG_PATH .'config_ldap.php';}


	$tri_date = getpost_variable('tri_date', "ascendant");
	$year_affichage = getpost_variable('year_affichage' , date("Y") );
	
	echo '<h1>'. _('user_historique_abs') .' :</h1>';

	// affichage de l'année et des boutons de défilement
	$year_affichage_prec = $year_affichage-1 ;
	$year_affichage_suiv = $year_affichage+1 ;
	
	echo "<b>";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences&year_affichage=$year_affichage_prec\"><<</a>";
	echo "&nbsp&nbsp&nbsp  $year_affichage &nbsp&nbsp&nbsp";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences&year_affichage=$year_affichage_suiv\">>></a>";
	echo "</b><br><br>\n";


	// Récupération des informations
	$sql4 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num, ta_libelle
			FROM conges_periode as a, conges_type_absence as b
			WHERE a.p_login = "'.\includes\SQL::quote($_SESSION['userlogin']).'"
			AND (a.p_type=b.ta_id)
			AND (b.ta_type=\'absences\')
			AND (p_date_deb LIKE \''.intval($year_affichage).'%\' OR p_date_fin LIKE \''.intval($year_affichage).'%\') ';

	if($tri_date=="descendant")
		$sql4=$sql4." ORDER BY p_date_deb DESC ";
	else
		$sql4=$sql4." ORDER BY p_date_deb ASC ";

	$ReqLog4 = \includes\SQL::query($sql4) ;

	$count4=$ReqLog4->num_rows;
	if($count4==0)
	{
		echo "<b>". _('user_abs_aucune_abs') ."</b><br>\n";
	}
	else
	{
		// AFFICHAGE TABLEAU
		echo "<table cellpadding=\"2\"  class=\"tablo\" width=\"80%\">\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo "<td>\n";
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=descendant\"><img src=\"". TEMPLATE_PATH ."img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo  _('divers_debut_maj_1')  ;
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=ascendant\"><img src=\"". TEMPLATE_PATH ."img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo "</td>\n";
		echo "<td>". _('divers_fin_maj_1') ."</td>\n";
		echo "<td>". _('user_abs_type') ."</td>\n";
		echo "<td>". _('divers_nb_jours_maj_1') ."</td>\n";
		echo "<td>". _('divers_comment_maj_1') ."</td>\n";
		echo "<td>". _('divers_etat_maj_1') ."</td>\n";
		echo "<td></td><td></td>\n";
		if($_SESSION['config']['affiche_date_traitement'])
		{
			echo "<td>". _('divers_date_traitement') ."</td>\n" ;
		}
		echo "</tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";

		$i = true;
		while ($resultat4 = $ReqLog4->fetch_array())
		{
			$sql_login= $resultat4["p_login"];
			$sql_date_deb= eng_date_to_fr($resultat4["p_date_deb"], $DEBUG);
			$sql_p_demi_jour_deb = $resultat4["p_demi_jour_deb"];
			if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
			$sql_date_fin= eng_date_to_fr($resultat4["p_date_fin"], $DEBUG);
			$sql_p_demi_jour_fin = $resultat4["p_demi_jour_fin"];
			if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
			$sql_nb_jours= affiche_decimal($resultat4["p_nb_jours"], $DEBUG);
			$sql_commentaire= $resultat4["p_commentaire"];
			//$sql_type=$resultat4["p_type"];
			$sql_type=$resultat4["ta_libelle"];
			$sql_etat=$resultat4["p_etat"];
			$sql_motif_refus=$resultat4["p_motif_refus"] ;
			$sql_date_demande = $resultat4["p_date_demande"];
			$sql_date_traitement = $resultat4["p_date_traitement"];
			$sql_num= $resultat4["p_num"];

			// si le user a le droit de saisir lui meme ses absences et qu'elle n'est pas deja annulee, on propose de modifier ou de supprimer
			if(($sql_etat != "annul")&&($_SESSION['config']['user_saisie_mission']))
			{
				$user_modif_mission="<a href=\"user_index.php?session=$session&p_num=$sql_num&onglet=modif_demande\">". _('form_modif') ."</a>" ;
				$user_suppr_mission="<a href=\"user_index.php?session=$session&p_num=$sql_num&onglet=suppr_demande\">". _('form_supprim') ."</a>" ;
			}
			else
			{
				$user_modif_mission=" - " ;
				$user_suppr_mission=" - " ;
			}

			echo '<tr class="'.($i?'i':'p').'">';
				echo '<td class="histo">'.schars($sql_date_deb).' _ '.schars($demi_j_deb).'</td>';
				echo '<td class="histo">'.schars($sql_date_fin).' _ '.schars($demi_j_fin).'</td>' ;
				echo '<td class="histo">'.schars($sql_type).'</td>' ;
				echo '<td class="histo">'.affiche_decimal($sql_nb_jours).'</td>' ;
				echo '<td class="histo">'.schars($sql_commentaire).'</td>' ;
				
				if($sql_etat=="refus")
				{
					if($sql_motif_refus=="")
						$sql_motif_refus= _('divers_inconnu') ;
					echo '<br><i>".'.schars( _('divers_motif_refus') ).'." : '.schars($sql_motif_refus).'</i>';
				}
				elseif($sql_etat=="annul")
				{
					if($sql_motif_refus=="")
						$sql_motif_refus= _('divers_inconnu') ;
					echo '<br><i>".'.schars( _('divers_motif_annul') ).'." : '.schars($sql_motif_refus).'</i>';
				}
				echo "</td>\n";
				echo "<td>";
				if($sql_etat=="refus")
					echo  _('divers_refuse') ;
				elseif($sql_etat=="annul")
					echo  _('divers_annule') ;
				else
					echo schars($sql_etat);
				echo "</td>\n";
				echo '<td class="histo">'.($user_modif_mission).'</td>'."\n";
				echo '<td class="histo">'.($user_suppr_mission).'</td>'."\n";
				if($_SESSION['config']['affiche_date_traitement'])
				{
					echo '<td class="histo-left">'.schars( _('divers_demande') ).' : '.schars($sql_date_demande).'<br>'.schars( _('divers_traitement') ).' : '.schars($sql_date_traitement).'</td>'."\n" ;
				}
			echo "</tr>\n";
			$i = !$i;
		}
		echo "</tbody>\n\n";
		echo "</table>\n\n";
	}
	echo "<br><br>\n";



