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

	
	init_tab_jours_feries($DEBUG);

	
	$new_echange_rtt    = getpost_variable('new_echange_rtt', 0);

	if( $new_echange_rtt == 1 && $_SESSION['config']['user_echange_rtt'] ) {
	
		$new_debut					= getpost_variable('new_debut');
		$new_fin					= getpost_variable('new_fin');
		$new_comment				= getpost_variable('new_comment');
		$moment_absence_ordinaire	= getpost_variable('moment_absence_ordinaire');
		$moment_absence_souhaitee	= getpost_variable('moment_absence_souhaitee');
	
		echange_absence_rtt($onglet, $new_debut, $new_fin, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee, $DEBUG);
	}
	else {

		$year_calendrier_saisie_debut	= getpost_variable('year_calendrier_saisie_debut'	, date('Y'));
		$mois_calendrier_saisie_debut	= getpost_variable('mois_calendrier_saisie_debut'	, date('m'));
		$year_calendrier_saisie_fin		= getpost_variable('year_calendrier_saisie_fin'		, date('Y'));
		$mois_calendrier_saisie_fin		= getpost_variable('mois_calendrier_saisie_fin'		, date('m'));
		
		echo '<h1>'. _('user_echange_rtt') .'</h1>';

		//affiche le formulaire de saisie d'une nouvelle demande de conges
		saisie_echange_rtt($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet,  $DEBUG);

	}





/**************************************************************************************/
/********  FONCTIONS      ******/
/**************************************************************************************/


//affiche le formulaire d'échange d'un jour de rtt-temps partiel / jour travaillé
function saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
	$mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
	$mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
	$mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

	if( $DEBUG ) { echo 'param = '.$user_login.', '.$year_calendrier_saisie_debut.', '.$mois_calendrier_saisie_debut.', '.$year_calendrier_saisie_fin.', '.$mois_calendrier_saisie_fin.' <br>' ; }

	echo '<form action="'.$PHP_SELF.'?session='.$session.'&&onglet='.$onglet.'" method="POST">' ;

			echo '<table class="table table condensed">';
			echo '<tr align="center">';

			// cellule 1 : calendrier de saisie du jour d'absence
			echo '<td class="cell-top">';
				echo '<table class="table table-bordered table-calendar">';
				echo '<tr>';
					init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
								$mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
								$mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
								$mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
								$mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
								$mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

					// affichage des boutons de défilement
					// recul du mois saisie debut
					echo '<td align="center">';
					echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut_prec.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut_prec.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin.'&user_login='.$user_login.'&onglet='.$onglet.'">';
					echo '<i class="fa fa-chevron-circle-left"></i>';
					echo '</a>';
					echo '</td>';

					// titre du calendrier de saisie du jour d'absence
					echo '<td align="center">'. _('saisie_echange_titre_calendrier_1') . '</td>';

					// affichage des boutons de défilement
					// avance du mois saisie debut
					echo '<td align="center">';
					echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut_suiv.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut_suiv.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin.'&user_login='.$user_login.'&onglet='.$onglet.'">';
					echo '<i class="fa fa-chevron-circle-right"></i>';
					echo '</a>';
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td colspan="3">';
					//*** calendrier saisie date debut ***/
					affiche_calendrier_saisie_jour_absence($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut);
				echo '</td>';
				echo '</tr>';
				echo '</table>';
			echo '</td>';

			// cellule 2 : boutons radio 1/2 journée ou jour complet
			echo '<td class="day-period">';
				echo '<div><input type="radio" name="moment_absence_ordinaire" value="a"><label>'. _('form_am') .'</label><input type="radio" name="moment_absence_souhaitee" value="a"></div>';
				echo '<input type="radio" name="moment_absence_ordinaire" value="p"><label>'. _('form_pm') .'</label><input type="radio" name="moment_absence_souhaitee" value="p"></div>';
				echo '<div><input type="radio" name="moment_absence_ordinaire" value="j" checked><label>'. _('form_day') .'</label><input type="radio" name="moment_absence_souhaitee" value="j" checked></div>';
			echo '</td>';

			// cellule 3 : calendrier de saisie du jour d'absence
			echo '<td class="cell-top">';
				echo '<table class="table table-bordered table-calendar">';
				echo '<tr>';
					$mois_calendrier_saisie_fin_prec = $mois_calendrier_saisie_fin==1 ? 12 : $mois_calendrier_saisie_fin-1 ;
					$mois_calendrier_saisie_fin_suiv = $mois_calendrier_saisie_fin==12 ? 1 : $mois_calendrier_saisie_fin+1 ;

					// affichage des boutons de défilement
					// recul du mois saisie fin
					echo '<td align="center">';
					echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_prec.'&user_login='.$user_login.'&onglet='.$onglet.'">';
					echo '<i class="fa fa-chevron-circle-left"></i>';
					echo '</a>';
					echo '</td>';

					// titre du ecalendrier de saisie du jour d'absence
					echo '<td align="center">' . _('saisie_echange_titre_calendrier_2') . '</td>';

					// affichage des boutons de défilement
					// avance du mois saisie fin
					echo '<td align="center">';
					echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_suiv.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_suiv.'&user_login='.$user_login.'&onglet='.$onglet.'">';
					echo '<i class="fa fa-chevron-circle-right"></i>';
					echo '</a>';
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td colspan="3">';
				//*** calendrier saisie date fin ***/
				affiche_calendrier_saisie_jour_presence($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin);
				echo '</td>';
				echo '</tr>';
				echo '</table>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo "<hr/>\n";
			// cellule 1 : champs texte et boutons (valider/cancel)
			echo '<label>'. _('divers_comment_maj_1') .'</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';
			echo "<hr/>\n";
			echo '<input type="hidden" name="user_login" value="'.schars($user_login).'">';
			echo '<input type="hidden" name="new_echange_rtt" value=1>';
			echo '<input class="btn btn-success" type="submit" value="'. _('form_submit') .'">';
			echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>\n";


		echo '</form>' ;
}



function echange_absence_rtt($onglet, $new_debut_string, $new_fin_string, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee, $DEBUG=FALSE)
{
//$DEBUG=TRUE;

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$duree_demande_1="";
	$duree_demande_2="";
	$valid=TRUE;

	// verif si les dates sont renseignées  (si ce n'est pas le cas, on ne verifie meme pas la suite !)
	// $new_debut et $new_fin sont des string au format : $year-$mois-$jour-X  (avec X = j pour "jour entier", a pour "a" (matin), et p pour "pm" (apres midi) )
	if( ($new_debut_string=="")||($new_fin_string=="") )
		$valid=FALSE;
	else
	{
		$date_1=explode("-", $new_debut_string);
		$year_debut=$date_1[0];
		$mois_debut=$date_1[1];
		$jour_debut=$date_1[2];
		$demi_jour_debut=$date_1[3];

		$new_debut="$year_debut-$mois_debut-$jour_debut";

		$date_2=explode("-", $new_fin_string);
		$year_fin=$date_2[0];
		$mois_fin=$date_2[1];
		$jour_fin=$date_2[2];
		$demi_jour_fin=$date_2[3];

		$new_fin="$year_fin-$mois_fin-$jour_fin";


		/********************************************/
		// traitement du jour d'absence à remplacer

		// verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
		if($demi_jour_debut=="j") // on est absent la journee
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="jour";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				$nouvelle_presence_date_1="M";
				$nouvelle_absence_date_1="A";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				$nouvelle_presence_date_1="A";
				$nouvelle_absence_date_1="M";
				$duree_demande_1="demi";
			}
		}
		elseif($demi_jour_debut=="a") // on est absent le matin
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_1="M";
					$nouvelle_absence_date_1="A";
				}
				else
				{
					$nouvelle_presence_date_1="J";
					$nouvelle_absence_date_1="N";
				}
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				if( $DEBUG ) { echo "false_1<br>\n";}
				$valid=FALSE;
			}
		}
		elseif($demi_jour_debut=="p") // on est absent l'aprem
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				if( $DEBUG ) { echo "false_2<br>\n";}
				$valid=FALSE;
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_1="A";
					$nouvelle_absence_date_1="M";
				}
				else
				{
					$nouvelle_presence_date_1="J";
					$nouvelle_absence_date_1="N";
				}
				$duree_demande_1="demi";
			}
		}
		else
			$valid=FALSE;


		/**********************************************/
		// traitement du jour de présence à remplacer

		// verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
		if($demi_jour_fin=="j") // on est present la journee
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="jour";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				$nouvelle_presence_date_2="A";
				$nouvelle_absence_date_2="M";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				$nouvelle_presence_date_2="M";
				$nouvelle_absence_date_2="A";
				$duree_demande_2="demi";
			}
		}
		elseif($demi_jour_fin=="a") // on est present le matin
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_2="A";
					$nouvelle_absence_date_2="M";
				}
				else
				{
					$nouvelle_presence_date_2="N";
					$nouvelle_absence_date_2="j";
				}
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				if( $DEBUG ) { echo "false_3<br>\n";}
				$valid=FALSE;
			}
		}
		elseif($demi_jour_fin=="p") // on est present l'aprem
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				if( $DEBUG ) { echo "false_4<br>\n";}
				$valid=FALSE;
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_2="M";
					$nouvelle_absence_date_2="A";
				}
				else
				{
					$nouvelle_presence_date_2="N";
					$nouvelle_absence_date_2="J";
				}
				$duree_demande_2="demi";
			}
		}
		else
		{
			if( $DEBUG ) { echo "false_5<br>\n";}
			$valid=FALSE;
		}


		if( $DEBUG )
		{
			echo schars($new_debut).' - '.schars($demi_jour_debut).' :: '.schars($new_fin).' - '.schars($demi_jour_fin).'<br>'."\n";
			echo schars($duree_demande_1).'  :: '.schars($duree_demande_2).'<br>'."\n";
		}
		// verif de la concordance des durée (journée avec journée ou 1/2 journée avec1/2 journée)
		if( ($duree_demande_1=="") || ($duree_demande_2=="") || ($duree_demande_1!=$duree_demande_2) )
			$valid=FALSE;
	}



	if($valid)
	{
		echo schars($_SESSION['userlogin']).' --- '.schars($new_debut).' --- '.schars($new_fin).' --- '.schars($new_comment).'<br>'."\n" ;

		// insert du jour d'absence ordinaire (qui n'en sera plus un ou qu'a moitie ...)
		// e_presence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
		// verif si le couple user/date1 existe dans conges_echange_rtt ...
		$sql_verif_echange1='SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login=\''.SQL::quote($_SESSION['userlogin']).'\' AND e_date_jour=\''.SQL::quote($new_debut).'\';';
		$result_verif_echange1 = SQL::query($sql_verif_echange1) ;

		$count_verif_echange1=$result_verif_echange1->num_rows;

		// si le couple user/date1 existe dans conges_echange_rtt : on update
		if($count_verif_echange1!=0)
		{
			$new_comment=addslashes($new_comment);
			//$resultat1=$result_verif_echange1->fetch_array();
			//if($resultatverif_echange1['e_absence'] == 'N' )
			$sql1 = 'UPDATE conges_echange_rtt
					SET e_absence=\''.$nouvelle_absence_date_1.'\', e_presence=\''.$nouvelle_presence_date_1.'\', e_comment=\''.$new_comment.'\'
					WHERE e_login=\''.$_SESSION['userlogin'].'\' AND e_date_jour=\''.SQL::quote($new_debut).'\'  ';
		}
		else // sinon : on insert
		{
			$sql1 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
					VALUES ('".$_SESSION['userlogin']."','$new_debut','$nouvelle_absence_date_1', '$nouvelle_presence_date_1', '$new_comment')" ;
		}
		$result1 = SQL::query($sql1);

		// insert du jour d'absence souhaité (qui en devient un)
		// e_absence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
		// verif si le couple user/date2 existe dans conges_echange_rtt ...
		$sql_verif_echange2='SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login=\''.SQL::quote($_SESSION['userlogin']).'\' AND e_date_jour=\''.SQL::quote($new_fin).'\';';
		$result_verif_echange2 = SQL::query($sql_verif_echange2);

		$count_verif_echange2=$result_verif_echange2->num_rows;

		// si le couple user/date2 existe dans conges_echange_rtt : on update
		if($count_verif_echange2!=0)
		{
			$sql2 = 'UPDATE conges_echange_rtt
					SET e_absence=\''.$nouvelle_absence_date_2.'\', e_presence=\''.$nouvelle_presence_date_2.'\', e_comment=\''.$new_comment.'\'
					WHERE e_login=\''.$_SESSION['userlogin'].'\' AND e_date_jour=\''.$new_fin.'\' ';
		}
		else // sinon: on insert
		{
			$sql2 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
					VALUES ('".$_SESSION['userlogin']."','$new_fin','$nouvelle_absence_date_2', '$nouvelle_presence_date_2', '$new_comment')" ;
		}
		$result2 = SQL::query($sql2) ;

		$comment_log = "echange absence - rtt  ($new_debut_string / $new_fin_string)";
		log_action(0, "", $_SESSION['userlogin'], $comment_log,  $DEBUG);


		if(($result1)&&($result2))
			echo " Changements pris en compte avec succes !<br><br> \n";
		else
			echo " ERREUR ! Une erreur s'est produite : contactez votre responsable !<br><br> \n";

	}
	else
	{
			echo " ERREUR ! Les valeurs saisies sont invalides ou manquantes  !!!<br><br> \n";
	}

		/* RETOUR PAGE PRINCIPALE */
		echo " <form action=\"$PHP_SELF?session=$session&onglet=$onglet\" method=\"POST\"> \n";
		echo "<input type=\"submit\" value=\"Retour\">\n";
		echo " </form> \n";

}


// affichage du calendrier du mois avec les case à cocher sur les jour d'absence
function  affiche_calendrier_saisie_jour_absence($user_login, $year, $mois, $DEBUG=FALSE)
{
	$jour_today					= date('j');
	$jour_today_name			= date('D');

	$first_jour_mois_timestamp	= mktime(0,0,0,$mois,1,$year);
	$last_jour_mois_timestamp	= mktime(0,0,0,$mois +1 , 1 ,$year);
	
	$mois_name					= date_fr('F', $first_jour_mois_timestamp);
	
	$first_jour_mois_rang		= date('w', $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	$last_jour_mois_rang		= date('w', $last_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	$nb_jours_mois				= ( $last_jour_mois_timestamp - $first_jour_mois_timestamp  + 60*60 *12 ) / (24 * 60 * 60);// + 60*60 *12 for fucking DST
	
	if( $first_jour_mois_rang == 0 )
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
		
	if( $last_jour_mois_rang == 0 )
		$last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo '<table class="table calendrier_saisie_date">';
		echo '<thead>
				<tr>
					<th colspan="7" class="titre"> '.$mois_name.' '.$year.' </th>
				</tr>
				<tr>
					<th class="cal-saisie2">'. _('lundi_1c') .'</th>
					<th class="cal-saisie2">'. _('mardi_1c') .'</th>
					<th class="cal-saisie2">'. _('mercredi_1c') .'</th>
					<th class="cal-saisie2">'. _('jeudi_1c') .'</th>
					<th class="cal-saisie2">'. _('vendredi_1c') .'</th>
					<th class="cal-saisie2">'. _('samedi_1c') .'</th>
					<th class="cal-saisie2">'. _('dimanche_1c') .'</th>
				</tr>
			</thead>';
		echo '<tbody>';

			$start_nb_day_before = $first_jour_mois_rang -1;
			$stop_nb_day_before = 7 - $last_jour_mois_rang ;
			
			
			for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
				if ( ($i + $start_nb_day_before ) % 7 == 0)
					echo '<tr>';
					
				$j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
				$td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
				
				if ($i <= 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
					echo '<td class="'.$td_second_class.'">-</td>';
				}
				else {	
					$val_matin='';
					$val_aprem='';
					recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem,  $DEBUG);
					affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1, $DEBUG);
				}
				
				if ( ($i + $start_nb_day_before ) % 7 == 6)
					echo '<tr>';
			}

		echo '</tbody>';
	echo '</table>';
	
}

// affichage du calendrier du mois avec les case à cocher sur les jour de présence
function  affiche_calendrier_saisie_jour_presence($user_login, $year, $mois, $DEBUG=FALSE)
{
	$jour_today					= date('j');
	$jour_today_name			= date('D');

	$first_jour_mois_timestamp	= mktime(0,0,0,$mois,1,$year);
	$last_jour_mois_timestamp	= mktime(0,0,0,$mois +1 , 1,$year);
	
	$mois_name					= date_fr('F', $first_jour_mois_timestamp);
	
	$first_jour_mois_rang		= date('w', $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	$last_jour_mois_rang		= date('w', $last_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	$nb_jours_mois				= ( $last_jour_mois_timestamp - $first_jour_mois_timestamp  + 60*60 *12 ) / (24 * 60 * 60);// + 60*60 *12 for fucking DST
	
	if( $first_jour_mois_rang == 0 )
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
		
	if( $last_jour_mois_rang == 0 )
		$last_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo '<table class="table calendrier_saisie_date">';
		echo '<thead>
				<tr>
					<th colspan="7" class="titre"> '.$mois_name.' '.$year.' </th>
				</tr>
				<tr>
					<th class="cal-saisie2">'. _('lundi_1c') .'</th>
					<th class="cal-saisie2">'. _('mardi_1c') .'</th>
					<th class="cal-saisie2">'. _('mercredi_1c') .'</th>
					<th class="cal-saisie2">'. _('jeudi_1c') .'</th>
					<th class="cal-saisie2">'. _('vendredi_1c') .'</th>
					<th class="cal-saisie2">'. _('samedi_1c') .'</th>
					<th class="cal-saisie2">'. _('dimanche_1c') .'</th>
				</tr>
			</thead>';
		echo '<tbody>';

			$start_nb_day_before = $first_jour_mois_rang -1;
			$stop_nb_day_before = 7 - $last_jour_mois_rang ;
			
			
			for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
				if ( ($i + $start_nb_day_before ) % 7 == 0)
					echo '<tr>';
				
				
				$j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
				$td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
				
				if ($i <= 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
					echo '<td class="'.$td_second_class.'">-</td>';
				}
				else {	
					$val_matin='';
					$val_aprem='';
					recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem,  $DEBUG);
					affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i+1, $DEBUG);
				}
				
				if ( ($i + $start_nb_day_before ) % 7 == 6)
					echo '<tr>';
			}

		echo '</tbody>';
	echo '</table>';
	

}

