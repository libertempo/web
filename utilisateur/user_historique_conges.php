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
	

	echo '<h1>'. _('user_historique_conges') .'</h1>';

	//affiche le tableau de l'hitorique des conges

	
	// affichage de l'année et des boutons de défilement
	$year_affichage_prec = $year_affichage-1 ;
	$year_affichage_suiv = $year_affichage+1 ;
	
	echo "<b>";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_conges&year_affichage=$year_affichage_prec\"><<</a>";
	echo '&nbsp&nbsp&nbsp  '.schars($year_affichage).' &nbsp&nbsp&nbsp';
	echo '<a href="'.schars($PHP_SELF).'?session='.schars($session).'&onglet=historique_conges&year_affichage='.schars($year_affichage_suiv).'">>></a>';
	echo "</b><br><br>\n";


	// Récupération des informations
	// on ne recup QUE les periodes de type "conges"(cf table conges_type_absence) ET pas les demandes
	$sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, ta_libelle
			 FROM conges_periode as a, conges_type_absence as b
			WHERE a.p_login = '".$_SESSION['userlogin']."'
			AND (a.p_type=b.ta_id)
			AND ( (b.ta_type='conges') OR (b.ta_type='conges_exceptionnels') )
			AND (p_etat='ok' OR  p_etat='refus' OR  p_etat='annul')
			AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";

	if($tri_date=="descendant")
		$sql2=$sql2." ORDER BY p_date_deb DESC ";
	else
		$sql2=$sql2." ORDER BY p_date_deb ASC ";

	$ReqLog2 = \includes\SQL::query($sql2) ;

	$count2=$ReqLog2->num_rows;
	if($count2==0)
	{
		echo "<b>". _('user_conges_aucun_conges') ."</b><br>\n";
	}
	else
	{
		// AFFICHAGE TABLEAU
		echo "<table class=\"table table-responsive table-condensed table-stripped table-hover\">\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo " <th>\n";
		echo  _('divers_debut_maj_1')  ;
		echo " </th>\n";
		echo " <th>". _('divers_fin_maj_1') ."</th>\n";
		echo " <th>". _('divers_type_maj_1') ."</th>\n";
		echo " <th>". _('divers_nb_jours_maj_1') ."</th>\n";
		echo " <th>". _('divers_comment_maj_1') ."</th>\n";
		echo " <th>". _('divers_etat_maj_1') ."</th>\n";
		echo " <th>". _('divers_motif_refus') ."</th>\n";
		if($_SESSION['config']['affiche_date_traitement'])
		{
			echo "<td>". _('divers_date_traitement') ."</td>\n" ;
		}

		echo "</tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";

		$i = true;
		while ($resultat2 = $ReqLog2->fetch_array())
		{
			$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"], $DEBUG);
			$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
			if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
			$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"], $DEBUG);
			$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
			if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
			$sql_p_nb_jours = $resultat2["p_nb_jours"];
			$sql_p_commentaire = $resultat2["p_commentaire"];
			//$sql_p_type = $resultat2["p_type"];
			$sql_p_type = $resultat2["ta_libelle"];
			$sql_p_etat = $resultat2["p_etat"];
			$sql_p_motif_refus=$resultat2["p_motif_refus"] ;
			$sql_p_date_demande = $resultat2["p_date_demande"];
			$sql_p_date_traitement = $resultat2["p_date_traitement"];

			echo '<tr class="'.($i?'i':'p').'">';
				echo '<td class="histo">'.schars($sql_p_date_deb).' _ '.schars($demi_j_deb).'</td>';
				echo '<td class="histo">'.schars($sql_p_date_fin).' _ '.schars($demi_j_fin).'</td>' ;
				echo '<td class="histo">'.schars($sql_p_type).'</td>' ;
				echo '<td class="histo">'.affiche_decimal($sql_p_nb_jours).'</td>' ;
				echo '<td class="histo">'.schars($sql_p_commentaire).'</td>' ;

				
				echo "<td>";
				if($sql_p_etat=="refus")
					echo  _('divers_refuse') ;
				elseif($sql_p_etat=="annul")
					echo  _('divers_annule') ;
				else
					echo schars($sql_p_etat);
				echo "</td>\n" ;
				
				
				if($sql_p_etat=="refus") {
					if($sql_p_motif_refus=="")
						$sql_p_motif_refus= _('divers_inconnu') ;
					echo '<td class="histo">'.schars($sql_p_motif_refus).'</td>'."\n";
				}
				elseif($sql_p_etat=="annul")
				{
					if($sql_p_motif_refus=="")
						$sql_p_motif_refus= _('divers_inconnu') ;
					echo'<td class="histo">'.schars($sql_p_motif_refus).'</td>'."\n";
				}
				elseif($sql_p_etat=="ok")
				{
					if($sql_p_motif_refus=="")
						$sql_p_motif_refus=" ";
					echo'<td class="histo">'.schars($sql_p_motif_refus).'</td>'."\n";
				}
				echo "</td>\n";
				
				if($_SESSION['config']['affiche_date_traitement'])
				{
					echo '<td class="histo-left">'.schars( _('divers_demande') ).' : '.schars($sql_p_date_demande).'<br>'."\n";
					$text_lang_a_afficher="divers_traitement_$sql_p_etat" ; // p_etat='ok' OR  p_etat='refus' OR  p_etat='annul' .....
					echo schars( _($text_lang_a_afficher) ).' : '.schars($sql_p_date_traitement).'</td>'."\n" ;
				}
			
			echo '</tr>';
			$i = !$i;
		}
		echo "</tbody>\n\n";
		echo "</table>\n\n";
	}
	echo "<br><br>\n" ;

