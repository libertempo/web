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

//#################################################################################################

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// affichage du calendrier avec les case à cocher, du mois du début du congés
function  affiche_calendrier_saisie_date($user_login, $year, $mois, $type_debut_fin , $DEBUG=FALSE) {
	$jour_today			= date('j');
	$jour_today_name		= date('D');
	$first_jour_mois_timestamp	= mktime(0,0,0,$mois,1,$year);
	$last_jour_mois_timestamp	= mktime(0,0,0,$mois +1 , 0,$year);
	$mois_name			= date_fr('F', $first_jour_mois_timestamp);
	$first_jour_mois_rang		= date('w', $first_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
	$last_jour_mois_rang		= date('w', $last_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
	$nb_jours_mois			= ( $last_jour_mois_timestamp - $first_jour_mois_timestamp + 60*60 *12 ) / (24 * 60 * 60); // + 60*60 *12 for fucking DST

	if( $first_jour_mois_rang == 0 )
		$first_jour_mois_rang=7 ; // jour de la semaine en chiffre (1=lun , 7=dim)

	if( $last_jour_mois_rang == 0 )
		$last_jour_mois_rang=7 ; // jour de la semaine en chiffre (1=lun , 7=dim)

	echo '<table class="calendrier_saisie_date_debut" cellpadding="0" cellspacing="0">
		<thead>
		<tr align="center" bgcolor="'.$_SESSION['config']['light_grey_bgcolor'].'">
		<td colspan=7 class="titre"> '.$mois_name.' '.$year.' </td>
		</tr>
		<tr bgcolor="'.$_SESSION['config']['light_grey_bgcolor'].'">
		<td class="cal-saisie2">'. _('lundi_1c') .'</td>
		<td class="cal-saisie2">'. _('mardi_1c') .'</td>
		<td class="cal-saisie2">'. _('mercredi_1c') .'</td>
		<td class="cal-saisie2">'. _('jeudi_1c') .'</td>
		<td class="cal-saisie2">'. _('vendredi_1c') .'</td>
		<td class="cal-saisie2">'. _('samedi_1c') .'</td>
		<td class="cal-saisie2">'. _('dimanche_1c') .'</td>
		</tr>
		</thead>
		<tbody>';
	$start_nb_day_before = $first_jour_mois_rang -1;
	$stop_nb_day_before = 7 - $last_jour_mois_rang ;

	for ( $i = - $start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i ++) {
		if ( ($i + $start_nb_day_before ) % 7 == 0)
			echo '<tr>';
		$j_timestamp=mktime (0,0,0,$mois, $i +1 ,$year);
		$td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
				if ($i < 0 || $i > $nb_jours_mois )
					echo '<td class="'.$td_second_class.'">-</td>';
				else
					affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, $type_debut_fin , $DEBUG);
				if ( ($i + $start_nb_day_before ) % 7 == 6)
					echo '<tr>';
	}
	echo '</tbody></table>';
}

function affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG=FALSE)
{
	$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
	if( $val_matin == 'Y' && $val_aprem == 'Y')
		echo '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-j"></td>';
	elseif( $val_matin == 'Y' && $val_aprem == 'N' )
		echo '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-a"></td>';
	elseif( $val_matin == 'N' && $val_aprem == 'Y' )
		echo '<td bgcolor='.$bgcolor.' class="cal-day_semaine_travail_am_rtt_pm_w35">'.$j.'<input type="radio" name="new_debut" value="'.$year.'-'.$mois.'-'.$j.'-p"></td>';
	else {
		$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'</td>';
	}
}

function affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG=FALSE)
{
	$bgcolor = $_SESSION['config']['temps_partiel_bgcolor'];
	if( $val_matin == 'Y' && $val_aprem == 'Y' )  // rtt le matin et l'apres midi !
		echo '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'</td>';
	elseif( $val_matin == 'Y' && $val_aprem == 'N' )
		echo '<td bgcolor='.$bgcolor.' class="cal-day_semaine_rtt_am_travail_pm_w35">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-p"></td>';
	elseif( $val_matin == 'N' && $val_aprem == 'Y' )
		echo '<td bgcolor='.$bgcolor.' class="cal-day_semaine_travail_am_rtt_pm_w35">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-a"></td>';
	else 
	{
		$bgcolor = $_SESSION['config']['semaine_bgcolor'];
		echo '<td bgcolor='.$bgcolor.' class="cal-saisie">'.$j.'<input type="radio" name="new_fin" value="'.$year.'-'.$mois.'-'.$j.'-j"></td>';
	}
}

// retourne le nom du jour de la semaine en francais sur 2 caracteres
function get_j_name_fr_2c($timestamp)
{
	$jour_name_fr_2c=array(0=>'di',1=>'lu', 2=>'ma',3=>'me',4=>'je',5=>'ve',6=>'sa',);
	$jour_num=date('w', $timestamp);
	if (isset($jour_name_fr_2c[$jour_num]))
		return $jour_name_fr_2c[$jour_num];
	else
		return false;
}


//affiche le formulaire de saisie d'une nouvelle demande de conges
function saisie_nouveau_conges($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet,  $DEBUG=FALSE)
{
	//$DEBUG=TRUE;
	if( $DEBUG ) { echo 'user_login = '.$user_login.', year_calendrier_saisie_debut = '.$year_calendrier_saisie_debut.', mois_calendrier_saisie_debut = '.$mois_calendrier_saisie_debut.',year_calendrier_saisie_fin = '.$year_calendrier_saisie_fin.', mois_calendrier_saisie_fin = '.$mois_calendrier_saisie_fin.', onglet = '.$onglet.'<br>';}

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
	$mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
	$mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
	$mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;
	init_tab_jours_fermeture($user_login);

	echo '<form NAME="dem_conges" action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">' ;
	// il faut indiquer le champ de formulaire 'login_user' car il est récupéré par le javascript qui apelle le calcul automatique.
	// echo '<input type="hidden" name="login_user" value="'.$user_login.'">';

	echo '<table cellpadding="0" cellspacing="5" border="0">';
	echo '<tr align="center">';
	echo '<td>';
	echo '<table cellpadding="0" cellspacing="0" border="0">';
	echo '<tr align="center">';
	echo '<td>';
	echo '<fieldset class="cal_saisie">';
	echo '<table cellpadding="0" cellspacing="0" border="0">';
	echo '<tr align="center">';
	echo "<td>\n";
	/******************************************************************/
	// affichage du calendrier de saisie de la date de DEBUT de congès
	/******************************************************************/
	echo '<table cellpadding="0" cellspacing="0" width="250" border="0">';
	echo '<tr>';
	init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
					$mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
					$mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
					$mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
					$mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
					$mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

	// affichage des boutons de défilement
	// recul du mois saisie début
	echo '<td align="center" class="big">';
	echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut_prec.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut_prec.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin.'&user_login='.$user_login.'&onglet='.$onglet.'">';
	echo ' <img src="'. TEMPLATE_PATH . 'img/simfirs.gif" width="16" height="16" border="0" alt="'. _('divers_mois_precedent') .'" title="'. _('divers_mois_precedent') .'"> ';
	echo '</a>';
	echo '</td>';
	echo '<td align="center" class="big">'. _('divers_debut_maj') .' :</td>';

	// affichage des boutons de défilement
	// avance du mois saisie début
	// si le mois de saisie fin est antérieur ou égal au mois de saisie début, on avance les 2 , sinon on avance que le mois de saisie début
	if( (($year_calendrier_saisie_debut_suiv==$year_calendrier_saisie_fin) && ($mois_calendrier_saisie_debut_suiv>=$mois_calendrier_saisie_fin)) || ($year_calendrier_saisie_debut_suiv>$year_calendrier_saisie_fin) )
		$lien_mois_debut_suivant = $PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut_suiv.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut_suiv.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_debut_suiv.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_debut_suiv.'&user_login='.$user_login.'&onglet='.$onglet ;
	else
		$lien_mois_debut_suivant = $PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut_suiv.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut_suiv.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin.'&user_login='.$user_login.'&onglet='.$onglet ;

	echo '<td align="center" class="big">';
	echo '<a href="'.$lien_mois_debut_suivant.'">';
	echo ' <img src="'. TEMPLATE_PATH . 'img/simlast.gif" width="16" height="16" border="0" alt="'. _('divers_mois_suivant') .'" title="'. _('divers_mois_suivant') .'"> ';
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	/*** calendrier saisie date debut ***/
	affiche_calendrier_saisie_date($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, 'new_debut', $DEBUG);
	echo '</td>';
	/**************************************************/
	/* cellule 2 : boutons radio matin ou après midi */
	echo '<td align="left">';
	echo '<input type="radio" name="new_demi_jour_deb" ';
	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;"' ;
		else
			echo 'onChange="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;"' ;
	}

	echo 'value="am" checked><b><u>'. _('form_am') .'</u></b><br><br>';
	echo '<input type="radio" name="new_demi_jour_deb" ';
	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;"' ;
		else
			echo 'onChange="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;"' ;
	}

	echo 'value="pm"><b><u>'. _('form_pm') .'</u></b><br><br>';
	echo '</td>';
	/**************************************************/
	echo '</tr>';
	echo '</table>';
	echo '</fieldset>';
	echo '</td>';
	echo '</tr>';
	echo '<tr align="center">';
	echo '<td><img src="'. TEMPLATE_PATH . 'img/shim.gif" width="15" height="10" border="0" vspace="0" hspace="0"></td>';
	echo '</tr>';
	echo '<tr align="center">';
	echo '<td>';
	echo '<fieldset class="cal_saisie">';
	echo '<table cellpadding="0" cellspacing="0" border="0">';
	echo '<tr align="center">';
	echo '<td>';
	/******************************************************************/
	// affichage du calendrier de saisie de la date de FIN de congès
	/******************************************************************/
	echo '<table cellpadding="0" cellspacing="0" width="250" border="0">';
	echo '<tr>';
	$mois_calendrier_saisie_fin_prec = $mois_calendrier_saisie_fin==1 ? 12 : $mois_calendrier_saisie_fin-1 ;
	$mois_calendrier_saisie_fin_suiv = $mois_calendrier_saisie_fin==12 ? 1 : $mois_calendrier_saisie_fin+1 ;

	// affichage des boutons de défilement
	// recul du mois saisie fin
	// si le mois de saisie fin est antérieur ou égal au mois de saisie début, on recule les 2 , sinon on recule que le mois de saisie fin
	if( (($year_calendrier_saisie_debut==$year_calendrier_saisie_fin_prec) && ($mois_calendrier_saisie_debut>=$mois_calendrier_saisie_fin_prec)) || ($year_calendrier_saisie_debut>$year_calendrier_saisie_fin_prec) )
		$lien_mois_fin_precedent = ''.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_fin_prec.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_prec.'&user_login='.$user_login.'&onglet='.$onglet;
	else
		$lien_mois_fin_precedent = ''.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_prec.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_prec.'&user_login='.$user_login.'&onglet='.$onglet;

	echo '<td align="center" class="big">';
	echo '<a href="'.$lien_mois_fin_precedent.'">';
	echo ' <img src="'. TEMPLATE_PATH . 'img/simfirs.gif" width="16" height="16" border="0" alt="'. _('divers_mois_precedent') .'" title="'. _('divers_mois_precedent') .'">';
	echo ' </a>';
	echo '</td>';
	echo '<td align="center" class="big">'. _('divers_fin_maj') .' :</td>';

	// affichage des boutons de défilement
	// avance du mois saisie fin
	echo '<td align="center" class="big">';
	echo '<a href="'.$PHP_SELF.'?session='.$session.'&year_calendrier_saisie_debut='.$year_calendrier_saisie_debut.'&mois_calendrier_saisie_debut='.$mois_calendrier_saisie_debut.'&year_calendrier_saisie_fin='.$year_calendrier_saisie_fin_suiv.'&mois_calendrier_saisie_fin='.$mois_calendrier_saisie_fin_suiv.'&user_login='.$user_login.'&onglet='.$onglet.'">';
	echo ' <img src="'. TEMPLATE_PATH . 'img/simlast.gif" width="16" height="16" border="0" alt="'. _('divers_mois_suivant') .'" title="'. _('divers_mois_suivant') .'"> ';
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	/*** calendrier saisie date fin ***/
	affiche_calendrier_saisie_date($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, 'new_fin',  $DEBUG);
	echo '</td>';
	/**************************************************/
	/* cellule 2 : boutons radio matin ou après midi */
	echo '<td align="left">';
	echo '<input type="radio" name="new_demi_jour_fin" ';
	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;"' ;
		else
			echo 'onChange="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;"' ;
	}
	echo 'value="am"><b><u>'. _('form_am') .'</u></b><br><br>';
	echo '<input type="radio" name="new_demi_jour_fin"  ';
	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;"' ;
		else
			echo 'onChange="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;"' ;
	}
	echo 'value="pm" checked><b><u>'. _('form_pm') .'</u></b><br><br>';
	echo '</td>';
	/**************************************************/
	echo '</tr>';
	echo '</table>';
	echo '</fieldset>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '<td><img src="'. TEMPLATE_PATH . 'img/shim.gif" width="15" height="2" border="0" vspace="0" hspace="0"></td>';
	echo '<td>';

	/*******************/
	/*   formulaire   .*/
	/*******************/
	echo '<table cellpadding="0" cellspacing="2" border="0" >';
	echo '<tr>';
	echo '<td valign="top">';
	echo '<table cellpadding="2" cellspacing="3" border="0" >';
	//echo '<input type="hidden" name="login_user" value="'.'.$_SESSION['userlogin'].'.'">';
	echo '<input type="hidden" name="login_user" value="'.$user_login.'">';
	echo '<input type="hidden" name="session" value="'.$session.'">';
	// bouton 'compter les jours'
	if($_SESSION['config']['affiche_bouton_calcul_nb_jours_pris'])
	{
		echo '<tr><td colspan="2">';
		echo '<input type="button" onclick="compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;" value="'. _('saisie_conges_compter_jours') .'">';
		echo '</td></tr>';
	}
	// zones de texte
	echo '<tr align="center"><td><b>'. _('saisie_conges_nb_jours') .'</b></td><td><b>'. _('divers_comment_maj_1') .'</b></td></tr>';
	if($_SESSION['config']['disable_saise_champ_nb_jours_pris'])  // zone de texte en readonly et grisée
		$text_nb_jours ='<input type="text" name="new_nb_jours" size="10" maxlength="30" value="" style="background-color: #D4D4D4; " readonly="readonly">' ;
	else
		$text_nb_jours ='<input type="text" name="new_nb_jours" size="10" maxlength="30" value="">' ;

	$text_commentaire='<input type="text" name="new_comment" size="25" maxlength="30" value="">' ;
	echo '<tr align="center">';
	echo '<td>'.($text_nb_jours).'</td><td>'.($text_commentaire).'</td>';
	echo '</tr>';
	echo '<tr align="center"><td><img src="'. TEMPLATE_PATH . 'img/shim.gif" width="15" height="10" border="0" vspace="0" hspace="0"></td><td></td></tr>';
	echo '<tr align="center">';
	echo '<td colspan=2>';
	echo '<input type="hidden" name="user_login" value="'.$user_login.'">';
	echo '<input type="hidden" name="new_demande_conges" value=1>';
	// boutons du formulaire
	// les classes "button_type_submit" et "button_type_cancel"
	// servent à choisir leur position (droite gauche) dans vos feuilles de style (voir style.css)
	echo '<input type="submit" class="button_type_submit" value="'. _('form_submit') .'">   <input type="reset" class="button_type_cancel" value="'. _('form_cancel') .'">';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	/*****************/
	/* boutons radio */
	/*****************/
	// recup d tableau des types de conges
	$tab_type_conges=recup_tableau_types_conges( $DEBUG);
	// recup du tableau des types d'absence
	$tab_type_absence=recup_tableau_types_absence( $DEBUG);
	// recup d tableau des types de conges exceptionnels
	$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels( $DEBUG);
	$already_checked = false;

	echo '<td align="left" valign="top">';
	// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
	// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
	// OU si le user est un RH ou un admin
	if( ( $_SESSION['config']['user_saisie_demande'] && $user_login==$_SESSION['userlogin'] ) || ( $_SESSION['config']['user_saisie_demande']==FALSE && $user_login!=$_SESSION['userlogin'] ) || is_hr($_SESSION['userlogin']) || is_admin($_SESSION['userlogin']) )
	{
		// congés
		echo '<b><i><u>'. _('divers_conges') .' :</u></i></b><br>';
		foreach($tab_type_conges as $id => $libelle)
		{
			if($id==1) 
			{
				echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
				$already_checked = true;
			}
			else
				echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
		}
	}
	// si le user a droit de saisir une mission ET si on est PAS dans une fenetre de responsable
	// OU si le resp a droit de saisir une mission ET si on est PAS dans une fenetre dd'utilisateur
	// OU si le resp a droit de saisir une mission ET si le resp est resp de lui meme
	if( (($_SESSION['config']['user_saisie_mission'])&&($user_login==$_SESSION['userlogin'])) || (($_SESSION['config']['resp_saisie_mission'])&&($user_login!=$_SESSION['userlogin'])) || (($_SESSION['config']['resp_saisie_mission'])&&(is_resp_of_user($_SESSION['userlogin'], $user_login,  $DEBUG))) )
	{
		echo '<br>';
		// absences
		echo '<b><i><u>'. _('divers_absences') .' :</u></i></b><br>';
		foreach($tab_type_absence as $id => $libelle) 
		{
			if (!$already_checked)
			{
				echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
				$already_checked = true;
			}
			else
				echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
		}
	}
	// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
	// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
	if( ($_SESSION['config']['gestion_conges_exceptionnels']) && ((($_SESSION['config']['user_saisie_demande'])&&($user_login==$_SESSION['userlogin'])) || (($_SESSION['config']['user_saisie_demande']==FALSE)&&($user_login!=$_SESSION['userlogin'])) ) )
	{
		echo '<br>';
		// congés exceptionnels
		echo '<b><i><u>'. _('divers_conges_exceptionnels') .' :</u></i></b><br>';
		foreach($tab_type_conges_exceptionnels as $id => $libelle)
		{
			if($id==1) 
				echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
			 else
				echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
		}
	}

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>' ;
}

function saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet,  $DEBUG=FALSE) 
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$new_date_fin = date('d/m/Y');

	echo '<form NAME="dem_conges" action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">
		<div class="row">
		<div class="col-md-6">
		<div class="form-inline">';
	echo "<div class=\"form-group\">\n
		<label for=\"new_deb\">" . _('divers_date_debut') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_debut\" value=\"$new_date_fin\">\n
		</div>";
	echo '<input type="radio" name="new_demi_jour_deb" ';

	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
	// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
	if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
		echo 'onClick="compter_jours();return true;"' ;
	else
		echo 'onChange="compter_jours();return false;"' ;
	}
	echo 'value="am" checked>&nbsp;'. _('form_am');
	echo '<input type="radio" name="new_demi_jour_deb" ';

	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours();return true;"' ;
		else
			echo 'onChange="compter_jours();return false;"' ;
	}
	echo 'value="pm">&nbsp;'. _('form_pm');
	echo '</div>';				   
	echo '</div>';
	echo '<div class="col-md-6">';
	echo '<div class="form-inline">';
	echo "<div class=\"form-group\">\n";
	echo "<label for=\"new_fin\">" . _('divers_date_fin') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_fin\" value=\"$new_date_fin\">\n";
	echo "</div>";
	echo '<input type="radio" name="new_demi_jour_fin" ';

	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
		if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
			echo 'onClick="compter_jours();return true;"' ;
		else
			echo 'onChange="compter_jours();return false;"' ;
		}
		echo 'value="am">&nbsp;'. _('form_am');
		echo '<input class="form-controm" type="radio" name="new_demi_jour_fin"  ';

		if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
		{
			if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
				echo 'onClick="compter_jours();return true;"' ;
			else
				echo 'onChange="compter_jours();return false;"' ;
		}
		echo 'value="pm" checked>&nbsp;'. _('form_pm');
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo "<hr/>\n";

		/*****************/
		/* boutons radio */
		/*****************/
		// recup du tableau des types de conges
		$tab_type_conges=recup_tableau_types_conges( $DEBUG);
		// recup du tableau des types d'absence
		$tab_type_absence=recup_tableau_types_absence( $DEBUG);
		// recup d tableau des types de conges exceptionnels
		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels( $DEBUG);
		$already_checked = false;

		echo '<div class="row type-conges">';
		// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
		// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
		// OU si le user est un RH ou un admin
		if( ( $_SESSION['config']['user_saisie_demande'] && $user_login==$_SESSION['userlogin'] ) || ( $_SESSION['config']['user_saisie_demande']==FALSE && $user_login!=$_SESSION['userlogin'] ) || is_hr($_SESSION['userlogin']) || is_admin($_SESSION['userlogin']) )
		{
			// congés
			echo '<div class="col-md-4">';
			echo '<label>'. _('divers_conges') .'</label>';
			foreach($tab_type_conges as $id => $libelle)
			{
				if($id==1) 
				{
					echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
					$already_checked = true;
				}
				else
					echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
			}
			echo '</div>';
		}

		// si le user a droit de saisir une mission ET si on est PAS dans une fenetre de responsable
		// OU si le resp a droit de saisir une mission ET si on est PAS dans une fenetre dd'utilisateur
		// OU si le resp a droit de saisir une mission ET si le resp est resp de lui meme
		if( (($_SESSION['config']['user_saisie_mission'])&&($user_login==$_SESSION['userlogin'])) || (($_SESSION['config']['resp_saisie_mission'])&&($user_login!=$_SESSION['userlogin'])) || (($_SESSION['config']['resp_saisie_mission'])&&(is_resp_of_user($_SESSION['userlogin'], $user_login,  $DEBUG))) )
		{
			// absences
			echo '<div class="col-md-4">';
			echo '<label>'. _('divers_absences') .'</label>';
			foreach($tab_type_absence as $id => $libelle) {
				if (!$already_checked){
					echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
					$already_checked = true;
				}
				else
					echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
			}
			echo '</div>';
		}

		// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
		// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
		if( ($_SESSION['config']['gestion_conges_exceptionnels']) && ((($_SESSION['config']['user_saisie_demande'])&&($user_login==$_SESSION['userlogin'])) || (($_SESSION['config']['user_saisie_demande']==FALSE)&&($user_login!=$_SESSION['userlogin'])) ) )
		{
		// congés exceptionnels
		echo '<div class="col-md-4">';
		echo '<label>'. _('divers_conges_exceptionnels') .'</label>';
		foreach($tab_type_conges_exceptionnels as $id => $libelle) {
			if($id==1) {
				echo '<input type="radio" name="new_type" value="'.$id.'" checked> '.$libelle.'<br>';
			}
			else
				echo '<input type="radio" name="new_type" value="'.$id.'"> '.$libelle.'<br>';
		}
		echo '</div>';
	}
	echo '</div>';
	echo "<hr/>\n";
	echo '<label>' . _('divers_comment_maj_1') . '</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';

	// zones de texte
	echo '<label>' . _('saisie_conges_nb_jours') .'</label>';
	if($_SESSION['config']['disable_saise_champ_nb_jours_pris'])  // zone de texte en readonly et grisée
		$text_nb_jours ='<input type="text" name="new_nb_jours" size="10" maxlength="30" value="" style="background-color: #D4D4D4; " readonly="readonly">' ;
	else
		$text_nb_jours ='<input type="text" name="new_nb_jours" size="10" maxlength="3" value="">' ;

	echo "$text_nb_jours\n";

	if($_SESSION['config']['affiche_bouton_calcul_nb_jours_pris'])
	{
		echo '<input type="button" class="btn btn-success" onclick="compter_jours();return false;" value="'. _('saisie_conges_compter_jours') .'">';
	}

	echo '<br>';
	echo '<input type="hidden" name="user_login" value="'.$user_login.'">';
	echo '<input type="hidden" name="new_demande_conges" value=1>';
	echo '<input type="hidden" name="session" value="'.$session.'">';
	// boutons du formulaire
	echo '<input type="submit" class="btn btn-success" value="'. _('form_submit') .'">';
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>\n";
	echo "</form>\n";
}

// initialisation des variables pour la navigation mois précédent / mois suivant
// certains arguments sont passés par référence (avec &) car on change leur valeur
function init_var_navigation_mois_year( $mois_calendrier_saisie_debut, $year_calendrier_saisie_debut, &$mois_calendrier_saisie_debut_prec, &$year_calendrier_saisie_debut_prec,	&$mois_calendrier_saisie_debut_suiv, &$year_calendrier_saisie_debut_suiv, $mois_calendrier_saisie_fin, $year_calendrier_saisie_fin, &$mois_calendrier_saisie_fin_prec, &$year_calendrier_saisie_fin_prec, &$mois_calendrier_saisie_fin_suiv, &$year_calendrier_saisie_fin_suiv )
{
	if($mois_calendrier_saisie_debut==1) 
	{
		$mois_calendrier_saisie_debut_prec=12;
		$year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut-1 ;
	}
	else 
	{
		$mois_calendrier_saisie_debut_prec=$mois_calendrier_saisie_debut-1 ;
		$year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut ;
	}
	if($mois_calendrier_saisie_debut==12) 
	{
		$mois_calendrier_saisie_debut_suiv=1;
		$year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut+1 ;
	}
	else 
	{
		$mois_calendrier_saisie_debut_suiv=$mois_calendrier_saisie_debut+1 ;
		$year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut ;
	}

	if($mois_calendrier_saisie_fin==1) 
	{
		$mois_calendrier_saisie_fin_prec=12;
		$year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin-1 ;
	}
	else 
	{
		$mois_calendrier_saisie_fin_prec=$mois_calendrier_saisie_fin-1 ;
		$year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin ;
	}
	if($mois_calendrier_saisie_fin==12) 
	{
		$mois_calendrier_saisie_fin_suiv=1;
		$year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin+1 ;
	}
	else 
	{
		$mois_calendrier_saisie_fin_suiv=$mois_calendrier_saisie_fin+1 ;
		$year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin ;
	}
}


// affiche une chaine représentant un decimal sans 0 à la fin ...
// (un point separe les unités et les decimales et on ne considere que 2 decimales !!!)
// ex : 10.00 devient 10  , 5.50 devient 5.5  , et 3.05 reste 3.05
function affiche_decimal($str, $DEBUG=FALSE)
{
	$champs=explode('.', $str);
	$int=$champs[0];
	$decimal='00';
	if (count($champs)>1)
		$decimal = $champs[1];
	if($decimal=='00')
		return $int ;
	elseif (preg_match('/[0-9][1-9]$/' , $decimal ))
		return $str;
	elseif (preg_match('/([0-9]?)0?$/' , $decimal, $regs ))
		return $int.'.'.$regs[1] ;
	else {
		echo 'ERREUR: affiche_decimal('.$str.') : '.$str.' n\'a pas le format attendu !!!!<br>';
		exit;
	}
}

// verif validité des valeurs saisies lors d'une demande de conges par un user ou d'une saisie de conges par le responsable
//  (attention : le $new_nb_jours est passé par référence car on le modifie si besoin)
function verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, &$new_nb_jours, $new_comment)
{
	$verif = true;

	// leur champs doivent etre renseignés dans le formulaire
	if( $new_debut == '' || $new_fin == '' || $new_nb_jours == '' ) 
	{
		echo '<br>'. _('verif_saisie_erreur_valeur_manque') .'<br>';
		$verif = false;
	}

	if ( !preg_match('/([0-9]+)([\.\,]*[0-9]{1,2})*$/', $new_nb_jours) ) 
	{
		echo '<br>'. _('verif_saisie_erreur_nb_jours_bad') .'<br>';
		$verif = false;
	}
	elseif ( preg_match('/([0-9]+)\,([0-9]{1,2})$/', $new_nb_jours, $reg) )
		$new_nb_jours=$reg[1].'.'.$reg[2]; // on remplace la virgule par un point pour les décimaux

	// si la date de fin est antéreieure à la date debut
	if(strnatcmp($new_debut, $new_fin)>0) 
	{
		echo '<br>'. _('verif_saisie_erreur_fin_avant_debut') .'<br>';
		$verif = false;
	}

	// si la date debut et fin = même jour mais début=après midi et fin=matin !!
	if( $new_debut == $new_fin && $new_demi_jour_deb=='pm' && $new_demi_jour_fin == 'am' ) 
	{
		echo '<br>'. _('verif_saisie_erreur_debut_apres_fin') .'<br>';
		$verif = false;
	}
	return $verif;
}


// renvoit la class de cellule du jour indiquée par le timestamp
// (une classe pour les jours de semaine et une pour les jours de week end)
function get_td_class_of_the_day_in_the_week($timestamp_du_jour)
{
	$j_name = date('D', $timestamp_du_jour);
	$j_date = date('Y-m-d', $timestamp_du_jour);

	if( ( $j_name=='Sat' && !$_SESSION['config']['samedi_travail'] ) || ($j_name=='Sun' && !$_SESSION['config']['dimanche_travail'] ) || est_chome($timestamp_du_jour) || est_ferme($timestamp_du_jour) )
		return 'weekend';
	else
		return 'semaine';
}


// recup des infos ARTT ou Temps Partiel :
// attention : les param $val_matin et $val_aprem sont passées par référence (avec &) car on change leur valeur
function recup_infos_artt_du_jour($sql_login, $j_timestamp, &$val_matin, &$val_aprem,  $DEBUG=FALSE)
{
	$num_semaine = date('W', $j_timestamp);
	$jour_name_fr_2c = get_j_name_fr_2c($j_timestamp); // nom du jour de la semaine en francais sur 2 caracteres

	// on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés (cf config de php_conges)
	if ( ( $jour_name_fr_2c != 'sa' || $_SESSION['config']['samedi_travail'] )  && ( $jour_name_fr_2c != 'di' || $_SESSION['config']['dimanche_travail'] ) )
	{
		// verif si le jour fait l'objet d'un echange ....
		$date_j			= date('Y-m-d', $j_timestamp);
		$sql_echange_rtt = 'SELECT e_absence FROM conges_echange_rtt WHERE e_login=\''.SQL::quote($sql_login).'\' AND e_date_jour=\''.SQL::quote($date_j).'\' ';
		$res_echange_rtt = SQL::query($sql_echange_rtt);
		$num_echange_rtt = $res_echange_rtt->num_rows;
		// si le jour est l'objet d'un echange, on tient compte de l'échange
		if( $num_echange_rtt != 0 )
		{
			$result_echange_rtt = $res_echange_rtt->fetch_array();
			if ( in_array($result_echange_rtt['e_absence'] , array( 'J' , 'M') ) )
				$val_matin = 'Y';
			else
				$val_matin = 'N';
			if ( in_array($result_echange_rtt['e_absence'] , array( 'J' , 'A') ) )
				$val_aprem = 'Y';
			else
				$val_aprem = 'N';
		}
		// sinon, on lit la table conges_artt normalement
		else
		{
			$par_sem = $num_semaine % 2 == 0 ? 'p' : 'imp';

			//on calcule la key du tableau $result_artt qui correspond au jour j que l'on est en train d'afficher
			$key_artt_matin = 'sem_'.$par_sem.'_'.$jour_name_fr_2c.'_am' ;
			$key_artt_aprem = 'sem_'.$par_sem.'_'.$jour_name_fr_2c.'_pm' ;

			// recup des ARTT et temps-partiels du user
			$sql_artt='SELECT '.SQL::quote($key_artt_matin).', '.SQL::quote($key_artt_aprem).' FROM conges_artt WHERE a_login = \''.SQL::quote($sql_login).'\' AND a_date_debut_grille <=  \''.SQL::quote($date_j).'\' AND a_date_fin_grille >= \''.SQL::quote($date_j).'\';';
			$res_artt = SQL::query($sql_artt);
			$result_artt = $res_artt->fetch_array();

			if($result_artt[$key_artt_matin] == 'Y')
				$val_matin='Y';
			else
				$val_matin='N';

			if($result_artt[$key_artt_aprem] == 'Y')
				$val_aprem='Y';
			else
				$val_aprem='N';
		}
	}
}


// recup des infos ARTT ou Temps Partiel :
// attention : les param $val_matin et $val_aprem sont passées par référence (avec &) car on change leur valeur
function recup_infos_artt_du_jour_from_tab($sql_login, $j_timestamp, &$val_matin, &$val_aprem, $tab_rtt_echange, $tab_rtt_planifiees, $DEBUG=FALSE)
{

	//$tab_rtt_echange  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
	//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
	// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
	// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )
	//$tab_rtt_planifiees  //tableau indexé dont la clé est le login_user
	// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
	// clé est la date_fin_grille.
	// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
	// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
	// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille

	$num_semaine = date('W', $j_timestamp);
	$jour_name_fr_2c = get_j_name_fr_2c($j_timestamp); // nom du jour de la semaine en francais sur 2 caracteres

	// on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés (cf config de php_conges)
	if ( ( $jour_name_fr_2c != 'sa' || $_SESSION['config']['samedi_travail'] )  && ( $jour_name_fr_2c != 'di' || $_SESSION['config']['dimanche_travail'] ) )
	{
		// verif si le jour fait l'objet d'un echange ....
		// si le jour est l'objet d'un echange, on tient compte de l'échange
		$date_j = date('Y-m-d', $j_timestamp);
		if(isset($tab_rtt_echange[$date_j]) && array_key_exists($sql_login, $tab_rtt_echange[$date_j]))   // si la periode correspond au user que l'on est en train de traiter
		{
			$tab_day = $tab_rtt_echange[$date_j];  // on recup le tableau du jour
			$val_matin = $tab_day[$sql_login]["val_matin"];
			$val_aprem = $tab_day[$sql_login]["val_aprem"];
		}
		// sinon, on lit la table conges_artt normalement
		else
		{
			$par_sem = $num_semaine % 2 == 0 ? 'p' : 'imp';

			//on calcule la key du tableau $result_artt qui correspond au jour j que l'on est en train d'afficher
			$key_artt_matin = 'sem_'.$par_sem.'_'.$jour_name_fr_2c.'_am' ;
			$key_artt_aprem = 'sem_'.$par_sem.'_'.$jour_name_fr_2c.'_pm' ;

			// recup des ARTT et temps-partiels du user :
			// recup des grille du user
			$tab_grille_user = array();
			if(array_key_exists($sql_login, $tab_rtt_planifiees))
				$tab_grille_user = $tab_rtt_planifiees[$sql_login];
			// parcours du tableau des grille pour trouver la key qui correspond à la bonne période
			if(count($tab_grille_user)) 
			{
				foreach ($tab_grille_user as $key => $value) 
				{
					if( $date_j >= $value['date_debut_grille'] && $date_j <= $value['date_fin_grille'] ) // date_jour comprise entre date_deb_grille et date_fin grille
					{
						$val_matin  = $value[$key_artt_matin];
						$val_aprem  = $value[$key_artt_aprem];
					}
				}
			}
		}
	}
}




// verif validité d'un nombre saisi (decimal ou non)
//  (attention : le $nombre est passé par référence car on le modifie si besoin)
function verif_saisie_decimal(&$nombre, $DEBUG=FALSE)
{
	if ( !preg_match('/^-?([0-9]+)([\.\,]?[0-9]?[0-9]?)$/', $nombre) ) 
	{
		echo "<br>". _('verif_saisie_erreur_nb_bad') ." ($nombre)<br>\n";
		return false;
	}

	if( preg_match('/^([0-9]+)\,([0-9]{1,2})$/', $nombre, $reg) )
		$nombre=$reg[1].".".$reg[2]; // on remplace la virgule par un point pour les décimaux
	elseif( preg_match('/^-([0-9]+)\,([0-9]{1,2})$/', $nombre, $reg) )
		$nombre="-".$reg[1].".".$reg[2]; // on remplace la virgule par un point pour les décimaux

	return true;
}



// donne la date en francais (dans la langue voulue)(meme formats que la fonction PHP date() cf manuel php)
function date_fr($code, $timestmp)
{
	$les_mois_longs  = array('pas_de_zero',  _('janvier') ,  _('fevrier') ,  _('mars') ,  _('avril') , _('mai') ,  _('juin') ,  _('juillet') ,  _('aout') , _('septembre') ,  _('octobre') ,  _('novembre') ,  _('decembre') );
	$les_jours_longs  = array( _('dimanche') ,  _('lundi') ,  _('mardi') ,  _('mercredi') , _('jeudi') ,  _('vendredi') ,  _('samedi') );
	$les_jours_courts = array( _('dimanche_short') ,  _('lundi_short') ,  _('mardi_short') , _('mercredi_short') ,  _('jeudi_short') ,  _('vendredi_short') ,  _('samedi_short') );

	switch ($code) 
	{
		case 'F':
			return $les_mois_longs[ date('n', $timestmp) ];
			break;

		case 'l':
			return $les_jours_longs[ date('w', $timestmp) ];
			break;

		case 'D':
			return $les_jours_courts[ date('w', $timestmp) ];
			break;

		default:
			return date($code, $timestmp);
			break;
	}
}



// envoi d'un message d'avertissement
// parametre 1=login de l'expéditeur
// parametre 2=login du destinataire (ou ":responsable:" si envoi au(x) responsable(s))
// parametre 3= numero de l'absence concernée
// parametre 4=objet du message (cf table conges_mail pour les diff valeurs possibles)
function alerte_mail($login_expediteur, $destinataire, $num_periode, $objet,  $DEBUG=FALSE)
{

	/*********************************************/
	// recup des infos concernant l'expéditeur ....
	$mail_array		= find_email_adress_for_user($login_expediteur, $DEBUG);
	$mail_sender_name	= $mail_array[0];
	$mail_sender_addr	= $mail_array[1];

	/*********************************************/
	// recherche des infos concernant le destinataire ...
	// recherche du login du (des) destinataire(s) dans la base
	$dest_mail  = '';
	if( $destinataire == ':responsable:' )  // c'est un message au responsable
	{
		$tab_resp   = get_tab_resp_du_user($login_expediteur,  $DEBUG);
		foreach($tab_resp as $item_login => $item_presence)
		{
			// recherche de l'adresse mail du (des) responsable(s) :
			$mail_array_dest = find_email_adress_for_user($item_login, $DEBUG);
			$mail_dest_name = $mail_array_dest[0];
			$mail_dest_addr = $mail_array_dest[1];

			if( $mail_dest_addr == '' )
				echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
			else
			{
				// on change l'objet si c'est un "new_demande" à un resp absent et qu'on gere les absence de resp !
				if( $_SESSION['config']['gestion_cas_absence_responsable'] && $item_presence == 'absent' && $objet == 'new_demande' )
					$new_objet  = 'new_demande_resp_absent';
				else
					$new_objet  = $objet;

				constuct_and_send_mail($new_objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode,  $DEBUG);
			}
		}

	}
	else   // c'est un message du responsale à un user
	{
		$dest_login		= $destinataire ;
		$mail_array_dest	= find_email_adress_for_user($dest_login, $DEBUG);
		$mail_dest_name 	= $mail_array_dest[0];
		$mail_dest_addr 	= $mail_array_dest[1];

		if( $mail_dest_addr == '' )
			echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
		else
			constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode,  $DEBUG);

		/****************************/
		if( $objet == 'valid_conges' )  // c'est un mail de première validation de demande : il faut faire une copie au(x) grand(s) responsable(s)
		{
			// on recup la liste des grands resp du user
			$tab_grd_resp   = array();
			get_tab_grd_resp_du_user($dest_login, $tab_grd_resp,  $DEBUG);

			if( count($tab_grd_resp) != 0 ) {
				foreach($tab_grd_resp as $item_login) {
					// recherche de l'adresse mail du (des) responsable(s) :
					$mail_array_dest = find_email_adress_for_user($item_login, $DEBUG);
					$mail_dest_name = $mail_array_dest[0];
					$mail_dest_addr = $mail_array_dest[1];

					if( $mail_dest_addr == '' )
						echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
					else
						constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode,  $DEBUG);
				}
			}
		}
	}
}


// construit et envoie le mail
function constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode,  $DEBUG=FALSE)
{

	require_once( LIBRARY_PATH .'phpmailer/PHPMailerAutoload.php' );  // ajout de la classe phpmailer

	/*********************************************/
	// init du mail
	$mail = new PHPMailer();

	if (file_exists(CONFIG_PATH .'config_SMTP.php')) 
	{
		include CONFIG_PATH .'config_SMTP.php';

		if( isset($config_SMTP_host) ) 
		{
			$mail->IsSMTP();
			$mail->Host = $config_SMTP_host;
			$mail->Port = $config_SMTP_port;

			if ( isset($config_SMTP_user) )
			{
				$mail->SMTPAuth = true;
				$mail->Username = $config_SMTP_user;
				$mail->Password = $config_SMTP_pwd;
			}
			if ( isset($config_SMTP_sec) )
				$mail->SMTPSecure = $config_SMTP_sec;
		}
	}
	else 
	{
		if(file_exists('/usr/sbin/sendmail'))
			$mail->IsSendmail();   // send message using the $Sendmail program
		elseif(file_exists('/var/qmail/bin/sendmail'))
			$mail->IsQmail(); // send message using the qmail MTA
		else
			$mail->IsMail(); // send message using PHP mail() function
	}

	// initialisation du langage utilisé par php_mailer
	$mail->SetLanguage( 'fr', LIBRARY_PATH .'phpmailer/language/');
	$mail->FromName	= $mail_sender_name;
	$mail->From		= $mail_sender_addr;
	$mail->AddAddress($mail_dest_addr);

	/*********************************************/
	// recup des infos de l'absence
	if ($num_periode == "test")
	{
		// affiche : "23 / 01 / 2008 (am)"
		$sql_date_deb = "01 / 01 / 2001 (am)";
		// affiche : "23 / 01 / 2008 (am)"
		$sql_date_deb = "02 / 01 / 2001 (am)";
		$sql_nb_jours = 2;
		$sql_commentaire = "Test comment";
		$sql_type_absence = "cp";
		$mail->SMTPDebug = 3; // Much easier if something fails
	}
	else
	{
		$select_abs = 'SELECT conges_periode.p_date_deb,conges_periode.p_demi_jour_deb,conges_periode.p_date_fin,conges_periode.p_demi_jour_fin,conges_periode.p_nb_jours,conges_periode.p_commentaire,conges_type_absence.ta_libelle
				FROM conges_periode, conges_type_absence WHERE conges_periode.p_num='.$num_periode.' AND conges_periode.p_type = conges_type_absence.ta_id;';
		$res_abs = SQL::query($select_abs);
		$rec_abs = $res_abs->fetch_array();
		$tab_date_deb = explode('-', $rec_abs['p_date_deb']);
		// affiche : "23 / 01 / 2008 (am)"
		$sql_date_deb = $tab_date_deb[2]." / ".$tab_date_deb[1]." / ".$tab_date_deb[0]." (".$rec_abs["p_demi_jour_deb"].")" ;
		$tab_date_fin= explode("-", $rec_abs["p_date_fin"]);
		// affiche : "23 / 01 / 2008 (am)"
		$sql_date_fin = $tab_date_fin[2]." / ".$tab_date_fin[1]." / ".$tab_date_fin[0]." (".$rec_abs["p_demi_jour_fin"].")" ;
		$sql_nb_jours = $rec_abs["p_nb_jours"];
		$sql_commentaire = $rec_abs["p_commentaire"];
		$sql_type_absence = $rec_abs["ta_libelle"];
	}

	/*********************************************/
	// construction des sujets et corps des messages
	if($objet=="valid_conges")
	{
		$key1="mail_prem_valid_conges_sujet" ;
		$key2="mail_prem_valid_conges_contenu" ;
	}
	elseif($objet=="accept_conges")
	{
		$key1="mail_valid_conges_sujet" ;
		$key2="mail_valid_conges_contenu" ;
	}
	elseif($objet=="new_demande_resp_absent")
	{
		$key1="mail_new_demande_resp_absent_sujet" ;
		$key2="mail_new_demande_resp_absent_contenu" ;
	}
	else  // $objet== "refus_conges" ou "new_demande" ou "annul_conges"
	{
		$key1="mail_".$objet."_sujet" ;
		$key2="mail_".$objet."_contenu" ;
	}
	$sujet = $_SESSION['config'][$key1];
	$contenu = $_SESSION['config'][$key2];
	$contenu = str_replace("__URL_ACCUEIL_CONGES__", $_SESSION['config']['URL_ACCUEIL_CONGES'], $contenu);
	$contenu = str_replace("__SENDER_NAME__", $mail_sender_name, $contenu);
	$contenu = str_replace("__DESTINATION_NAME__", $mail_dest_name, $contenu);
	$contenu = str_replace("__NB_OF_DAY__", $sql_nb_jours, $contenu);
	$contenu = str_replace("__DATE_DEBUT__", $sql_date_deb, $contenu);
	$contenu = str_replace("__DATE_FIN__", $sql_date_fin, $contenu);
	$contenu = str_replace("__RETOUR_LIGNE__", "\r\n", $contenu);
	$contenu = str_replace("__COMMENT__", $sql_commentaire, $contenu);
	$contenu = str_replace("__TYPE_ABSENCE__", $sql_type_absence, $contenu);

	// construction du corps du mail
	$mail->Subject = stripslashes(utf8_decode($sujet ));
	$mail->Body = stripslashes(utf8_decode($contenu ));


	/*********************************************/
	// ENVOI du mail
	if( $DEBUG )
	{
		echo "SUBJECT = ".$sujet."<br>\n";
		echo "CONTENU = ".$mail->FromName." ".$contenu."<br>\n";
	}
	else
	{
		if(!isset($mail_dest_addr))
		{
			echo "<b>ERROR : No recipient address for the message!</b><br>\n";
			echo "<b>Message was not sent </b><br>";
		}
		else
		{
			if(!$mail->Send())
			{
				echo "<b>Message was not sent </b><br>";
				echo "<b>Mailer Error: " . $mail->ErrorInfo."</b><br>";
			}
		}
	}
}



// recuperation du mail d'un user
// renvoit un tableau a 2 valeurs : prenom+nom et email
function find_email_adress_for_user($login, $DEBUG=FALSE)
{

	$found_mail=array();

	if($_SESSION['config']['where_to_find_user_email']=="ldap") // recherche du mail du user dans un annuaire LDAP
	{
		// cnx à l'annuaire ldap :
		$ds = ldap_connect($_SESSION['config']['ldap_server']);
		if($_SESSION['config']['ldap_protocol_version'] != 0)
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
		if ($_SESSION['config']['ldap_user'] == "")
			 $bound = ldap_bind($ds);
		else $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

		// recherche des entrées correspondantes au "login" passé en paramètre :
		$filter = "(".$_SESSION['config']['ldap_login']."=".$login.")";

		$sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
		$data = ldap_get_entries($ds,$sr);

		foreach ($data as $info)
		{
			$found_mail=array();
			// On récupère le nom et le mail de la personne.
			// Utilisation de la fonction utf8_decode pour corriger les caractères accentués
			// (qnd les noms ou prénoms ont des accents, "ç", ...

			// Les champs LDAP utilisés, bien que censés être uniformes, sont ceux d'un AD 2003.
			$ldap_prenom= $_SESSION['config']['ldap_prenom'];
			$ldap_nom	= $_SESSION['config']['ldap_nom'];
			$ldap_mail	= $_SESSION['config']['ldap_mail'];
			$nom	= utf8_decode($info[$ldap_prenom][0])." ".strtoupper(utf8_decode($info[$ldap_nom][0])) ;
			$addr	= $info[$ldap_mail][0] ;
			array_push($found_mail, $nom) ;
			array_push($found_mail, $addr) ;
		}
	}
	elseif($_SESSION['config']['where_to_find_user_email']=="dbconges") // recherche du mail du user dans la base db_conges
	{
		$req = 'SELECT u_nom, u_prenom, u_email FROM conges_users WHERE u_login=\''.SQL::quote($login).'\' ';
		$res = SQL::query($req);
		$rec = $res->fetch_array();

		$sql_nom = $rec["u_nom"];
		$sql_prenom = $rec["u_prenom"];
		$sql_email = $rec["u_email"];

		array_push($found_mail, $sql_prenom." ".strtoupper($sql_nom)) ;
		array_push($found_mail, $sql_email) ;

	}
	else
	{
		return FALSE;
	}
	return $found_mail ;
}


/**************************************************/
/* recup des échanges de rtt de chaque jour du mois pour tous les users et stockage dans 1 tableau de tableaux */
/**************************************************/
function recup_tableau_rtt_echange($mois, $first_jour, $year,  $tab_logins = false)
{
	$tab_rtt_echange=array();  
	//tableau indexé dont la clé est la date sous forme yyyy-mm-dd
	//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
	// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
	// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )

	// construction du tableau $tab_rtt_echange:

	$date_deb   = date("Y-m-d", mktime (0,0,0,$mois, $first_jour, $year) );
	$date_fin   = date("Y-m-d", mktime (0,0,0,$mois + 1 , $first_jour, $year) );

	$sql	= 'SELECT e_login, e_absence, e_date_jour FROM conges_echange_rtt WHERE e_date_jour >= \''.SQL::quote($date_deb).'\' AND  e_date_jour < \''.SQL::quote($date_fin).'\''.($tab_logins !== false ? 'AND e_login IN (\''.implode('\', \'', $tab_logins).'\')' : '' ).';';
	$result = SQL::query($sql);
	while($l = $result->fetch_array()) {
		$tab_echange = array();
		$tab_echange["val_matin"] = ($l["e_absence"]=='J' || $l["e_absence"]='M' ? 'Y' : 'N');
		$tab_echange["val_aprem"] = ($l["e_absence"]=='J' || $l["e_absence"]='A' ? 'Y' : 'N');
		$tab_rtt_echange[ $l['e_date_jour'] ][ $l["e_login"] ] = $tab_echange;
	}

	return $tab_rtt_echange;
}



/**************************************************/
/* recup dans un tableau des rtt planifiées  pour tous les users */
/**************************************************/
function recup_tableau_rtt_planifiees($mois, $first_jour, $year , $tab_logins = false ) {

	$tab_rtt_planifiees=array();  
	//tableau indexé dont la clé est le login_user
	// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
	// clé est la date_fin_grille.
	// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
	// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
	// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille

	// construction du tableau $tab_rtt_planifie:
	$sql	= 'SELECT a_login AS login, a_date_debut_grille AS date_debut_grille, a_date_fin_grille AS date_fin_grille,
			sem_imp_lu_am, sem_imp_lu_pm, sem_imp_ma_am, sem_imp_ma_pm, sem_imp_me_am, sem_imp_me_pm,
			sem_imp_je_am, sem_imp_je_pm, sem_imp_ve_am, sem_imp_ve_pm, sem_imp_sa_am, sem_imp_sa_pm,
			sem_imp_di_am, sem_imp_di_pm, sem_p_lu_am, sem_p_lu_pm, sem_p_ma_am, sem_p_ma_pm,
			sem_p_me_am, sem_p_me_pm, sem_p_je_am, sem_p_je_pm, sem_p_ve_am, sem_p_ve_pm,
			sem_p_sa_am, sem_p_sa_pm, sem_p_di_am, sem_p_di_pm
			FROM conges_artt'.($tab_logins === false ?'': ' WHERE a_login IN ( \''.implode('\', \'', $tab_logins ).'\')').';';
	$result = SQL::query($sql);

	while($l = $result->fetch_array()) // pour chaque lignes
		$tab_rtt_planifiees[ $l['login'] ][ $l['date_fin_grille'] ] = $l;

	return $tab_rtt_planifiees;
}


// affiche une liste déroulante des jours du mois : la variable est $new_jour
function affiche_selection_new_jour($default)
{
	echo "<select class=\"form-control\" name=\"new_jour\" >\n";
	for($i=1; $i<10; $i++)
	{
		if($default=="0$i")
			echo "<option value=\"0$i\" selected >0$i</option>\n";
		else
			echo "<option value=\"0$i\">0$i</option>\n";
	}
	for($i=10; $i<32; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// affiche une liste déroulante des mois de l'année : la variable est $new_mois
function affiche_selection_new_mois($default)
{
	echo "<select class=\"form-control\" name=\"new_mois\" >\n";
	for($i=1; $i<10; $i++)
	{
		echo "$default : $i<br>\n";
		if($default=="0$i")
			echo "<option value=\"0$i\" selected >0$i</option>\n";
		else
			echo "<option value=\"0$i\">0$i</option>\n";
	}
	for($i=10; $i<13; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// affiche une liste déroulante d'année : la variable est $new_year
function affiche_selection_new_year($an_debut, $an_fin, $default)
{
	echo "<select class=\"form-control\" name=\"new_year\" >\n";
	for($i=$an_debut; $i<$an_fin+1; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// met la date aaaa-mm-jj dans le format jj-mm-aaaa
function eng_date_to_fr($une_date, $DEBUG=FALSE)
{
 return substr($une_date, 8)."-".substr($une_date, 5, 2)."-".substr($une_date, 0, 4);

}


// affichage de la cellule correspondant au jour dans les calendrier de saisie (demande de conges, etc ...)
function affiche_cellule_jour_cal_saisie($login, $j_timestamp, $td_second_class, $result,  $DEBUG=FALSE)
{
	$date_j=date('Y-m-d', $j_timestamp);
	$j=date('d', $j_timestamp);
	$class_am='travail_am';
	$class_pm='travail_pm';
	$val_matin='';
	$val_aprem='';

	// recup des infos ARTT ou Temps Partiel :
	// la fonction suivante change les valeurs de $val_matin $val_aprem ....
	recup_infos_artt_du_jour($login, $j_timestamp, $val_matin, $val_aprem,  $DEBUG);

	//## AFICHAGE ##
	if($val_matin=='Y') 
	{
		$class_am='rtt_am';
	}
	if($val_aprem=='Y') 
	{
		$class_pm = 'rtt_pm';
	}


	$jour_today=date('j');
	$mois_today=date('m');
	$year_today=date('Y');
	$timestamp_today = mktime (0,0,0,$mois_today,$jour_today,$year_today);
	// si la saisie de conges pour une periode passée est interdite : pas de case à cocher dans les dates avant aujourd'hui
	if( $_SESSION['config']['interdit_saisie_periode_date_passee']  && $j_timestamp < $timestamp_today )
		echo '<td  class="cal-saisie '.$td_second_class.' '.$class_am.' '.$class_pm.'">'.$j.'</td>';
	else
	{
		echo '<td  class="cal-saisie '.$td_second_class.' '.$class_am.' '.$class_pm.'">'.$j.'<input type="radio" name="'.$result.'" ';
		if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
		{
			// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
			if( (isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE) )
				echo 'onClick="compter_jours();return true;"';
			else
				echo 'onChange="compter_jours();return false;"';
		}
		echo ' value="'.$date_j.'"></td>';
	}
}

// recup du nom d'un groupe grace à son group_id
function get_group_name_from_id($groupe_id)
{
	$req_name='SELECT g_groupename FROM conges_groupe WHERE g_gid='.SQL::quote($groupe_id);
	$ReqLog_name = SQL::query($req_name);
	$resultat_name = $ReqLog_name->fetch_array();
	return $resultat_name["g_groupename"];
}

// recup du nom d'un groupe grace à son group_id
function get_groups_name()
{
	$sql	= 'SELECT g_gid, g_groupename FROM conges_groupe;';
	$requete	= SQL::query($sql);
	$tab = array();
	while( $l = $requete->fetch_array() ) 
	{
		$tab[ $l['g_gid'] ] = $l['g_groupename'];
	}
	return $tab;
}

// recup de la liste de TOUS les users dont $resp_login est responsable
// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
// renvoie une liste de login entre quotes et séparés par des virgules
function get_list_all_users_du_resp($resp_login,  $DEBUG=FALSE)
{

	$list_users="";
	$sql1="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' AND u_login!='$resp_login'";

	// si resp virtuel, on renvoie tout le monde, sinon, seulement ceux dont on est responsable
	if($_SESSION['config']['responsable_virtuel']==FALSE)
	{
		$sql1 = $sql1." AND  ( u_resp_login='$resp_login' " ;
		if($_SESSION['config']['gestion_groupes'] )
		{
			$list_users_group=get_list_users_des_groupes_du_resp_sauf_resp($resp_login, $DEBUG);
			if($list_users_group!="")
				$sql1=$sql1." OR u_login IN ($list_users_group) ";
		}

		$sql1=$sql1." ) " ;
	}
	$sql1 = $sql1." ORDER BY u_nom " ;
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$current_login=$resultat1["u_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}

	/************************************/
	// gestion des absence des responsables :
	// on recup la liste des users des resp absents, dont $resp_login est responsable
	if($_SESSION['config']['gestion_cas_absence_responsable'])
	{
		// recup liste des resp absents, dont $resp_login est responsable
		$sql_2='SELECT DISTINCT(u_login) FROM conges_users WHERE u_is_resp=\'Y\' AND u_login!=\''.SQL::quote($resp_login).'\' AND u_login!=\'conges\' AND u_login!=\'admin\'';
		// si resp virtuel, on renvoie tout le monde, sinon, seulement ceux dont on est responsable
		if($_SESSION['config']['responsable_virtuel']==FALSE)
		{
			$sql_2 = $sql_2." AND  ( u_resp_login='$resp_login' " ;
			if($_SESSION['config']['gestion_groupes'] )
			{
				$list_users_group=get_list_users_des_groupes_du_resp_sauf_resp($resp_login, $DEBUG);
				if($list_users_group!="")
					$sql_2=$sql_2." OR u_login IN ($list_users_group) ";
			}
			$sql_2=$sql_2." ) " ;
		}
		$sql_2 = $sql_2." ORDER BY u_nom " ;

		$ReqLog_2 = SQL::query($sql_2);

		// on va verifier si les resp récupérés sont absents (si oui, c'est $resp_login qui traite leurs users
		while ($resultat_2 = $ReqLog_2->fetch_array())
		{
			$current_resp=$resultat_2["u_login"];
			// verif dans la base si le current_resp est absent :
			$req = 'SELECT p_num FROM conges_periode WHERE p_login = \''.SQL::quote($current_resp).'\' AND p_etat = \'ok\' AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW()) AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())';
			$ReqLog_3 = SQL::query($req);

			// si le current resp est absent : on recup la liste de ses users pour les traiter .....
			if ($ReqLog_3->num_rows!=0)
			{
				if($list_users=="")
					$list_users=get_list_all_users_du_resp($current_resp,  $DEBUG);
				else
					$list_users=$list_users.", ".get_list_all_users_du_resp($current_resp,  $DEBUG);
			}
		}

	}
	// FIN gestion des absence des responsables :
	/************************************/

	if( $DEBUG ) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}

// recup de la liste des users d'un groupe donné
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_du_groupe($group_id,  $DEBUG=FALSE)
{
	$list_users=array();
	$sql1='SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid = '.intval($group_id).' ORDER BY gu_login ';
	$ReqLog1 = SQL::query($sql1);
	while ($resultat1 = $ReqLog1->fetch_array())
		$list_users[] = '\''.SQL::quote($resultat1["gu_login"]).'\'';

	$list_users = implode(' , ', $list_users);

	if( $DEBUG ) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}

// recup de la liste des groupes dont $resp_login est responsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_resp($resp_login,  $DEBUG=FALSE)
{
	$list_group="";
	$sql1='SELECT gr_gid FROM conges_groupe_resp WHERE gr_login=\''.SQL::quote($resp_login).'\' ORDER BY gr_gid';
	$ReqLog1 = SQL::query($sql1);

	if($ReqLog1->num_rows !=0)
	{
		while ($resultat1 = $ReqLog1->fetch_array())
		{
			$current_group=$resultat1["gr_gid"];
			if($list_group=="")
				$list_group="$current_group";
			else
				$list_group=$list_group.", $current_group";
		}
	}
	if( $DEBUG ) { echo "list_group = $list_group<br>\n" ;}

	return $list_group;
}

// recup de la liste des groupes dont $resp_login est grandresponsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_grand_resp($resp_login)
{
	$list_group="";
	$sql1='SELECT ggr_gid FROM conges_groupe_grd_resp WHERE ggr_login=\''.SQL::quote($resp_login).'\' ORDER BY ggr_gid';
	$ReqLog1 = SQL::query($sql1);

	if($ReqLog1->num_rows!=0)
	{
		while ($resultat1 = $ReqLog1->fetch_array())
		{
			$current_group=$resultat1["ggr_gid"];
			if($list_group=="")
				$list_group="$current_group";
			else
				$list_group=$list_group.", $current_group";
		}
	}
	return $list_group;
}

// recup de la liste des logins des groupes dont $resp_login est grandresponsable
function get_list_login_du_grand_resp($resp_login)
{
	$list_logins = array();
	$sql1='SELECT gu_login FROM conges_groupe_grd_resp JOIN conges_groupe_users ON ggr_gid = gu_gid WHERE ggr_login=\''.SQL::quote($resp_login).'\';';
	$ReqLog1 = SQL::query($sql1);

	if($ReqLog1->num_rows!=0)
	{
		while ($resultat1 = $ReqLog1->fetch_array())
				$list_logins[] = $resultat1["gu_login"];
	}
	return $list_logins;
}

// recup de la liste des groupes à double validation
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid( $DEBUG=FALSE)
{
	$list_groupes_double_valid="";
	$sql1="SELECT g_gid FROM conges_groupe WHERE g_double_valid='Y' ORDER BY g_gid ";
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$current_gid=$resultat1["g_gid"];
		if($list_groupes_double_valid=="")
			$list_groupes_double_valid="$current_gid";
		else
			$list_groupes_double_valid=$list_groupes_double_valid.", $current_gid";
	}

	if( $DEBUG ) { echo "list_groupes_double_valid = $list_groupes_double_valid<br>\n" ;}

	return $list_groupes_double_valid;

}

// recup de la liste des groupes à double validation, dont $resp_login est responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_resp($resp_login,  $DEBUG=FALSE)
{
	$list_groupes_double_valid_du_resp="";
	$list_groups=get_list_groupes_du_resp($resp_login,  $DEBUG);

	if($list_groups!="") // si $resp_login est responsable d'au moins un groupe
	{
		$sql1='SELECT DISTINCT(g_gid) FROM conges_groupe WHERE g_double_valid=\'Y\' AND g_gid IN ('.SQL::quote($list_groups).') ORDER BY g_gid ';
		$ReqLog1 = SQL::query($sql1);

		while ($resultat1 = $ReqLog1->fetch_array())
		{
			$current_gid=$resultat1["g_gid"];
			if($list_groupes_double_valid_du_resp=="")
				$list_groupes_double_valid_du_resp="$current_gid";
			else
				$list_groupes_double_valid_du_resp=$list_groupes_double_valid_du_resp.", $current_gid";
		}
	}
	if( $DEBUG ) { echo "list_groupes_double_valid_du_resp = $list_groupes_double_valid_du_resp<br>\n" ;}

	return $list_groupes_double_valid_du_resp;

}

// recup de la liste des groupes à double validation, dont $resp_login est GRAND responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_grand_resp($resp_login,  $DEBUG=FALSE)
{

	$list_groupes_double_valid_du_grand_resp="";

	$sql1='SELECT DISTINCT(ggr_gid) FROM conges_groupe_grd_resp WHERE ggr_login=\''.SQL::quote($resp_login).'\' ORDER BY ggr_gid ';
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$current_gid=$resultat1["ggr_gid"];
		if($list_groupes_double_valid_du_grand_resp=="")
			$list_groupes_double_valid_du_grand_resp="$current_gid";
		else
			$list_groupes_double_valid_du_grand_resp=$list_groupes_double_valid_du_grand_resp.", $current_gid";
	}
	if( $DEBUG ) { echo "list_groupes_double_valid_du_grand_resp = $list_groupes_double_valid_du_grand_resp<br>\n" ;}

	return $list_groupes_double_valid_du_grand_resp;
}

// recup de la liste des users des groupes dont $user_login est membre
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_des_groupes_du_user($user_login,  $DEBUG=FALSE)
{
	$list_users=array();
	$list_groups=get_list_groupes_du_user($user_login,  $DEBUG);
	if($list_groups!="") // si $user_login est membre d'au moins un groupe
	{
		$sql1='SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid IN ('.$list_groups.') ORDER BY gu_login ';
		$ReqLog1 = SQL::query($sql1);

		while ($resultat1 = $ReqLog1->fetch_array())
			$list_users[] = '\''.SQL::quote($resultat1["gu_login"]).'\'';
	}
	$list_users = implode(' , ', $list_users);
	return $list_users;
}

// recup de la liste des groupes dont $resp_login est membre
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_user($user_login,  $DEBUG=FALSE)
{
	$list_group=array();
	$sql1='SELECT gu_gid FROM conges_groupe_users WHERE gu_login=\''.SQL::quote($user_login).'\' ORDER BY gu_gid';
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
		$list_group[] = $resultat1["gu_gid"];
	$list_group = implode(' , ', $list_group);
	return $list_group;
}

// recup de la liste de TOUS les users (sauf "conges" et "admin"
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_all_users($DEBUG=FALSE)
{
	$list_users="";
	$sql1="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_login " ;
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$current_login=$resultat1["u_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}

	if( $DEBUG ) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}


// recup de la liste des groupes (tous)
// renvoit une liste de group_id séparés par des virgules
function get_list_all_groupes($DEBUG=FALSE)
{
	$list_group = array();

	// on select dans conges_groupe_users pour ne récupérer QUE les groupes qui ont des users !!
	$sql1="SELECT DISTINCT(gu_gid) FROM conges_groupe_users ORDER BY gu_gid";
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array())
		$list_group[] = $resultat1["gu_gid"];

	return implode(',',$list_group);
}


// construit le tableau des responsables d'un user
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
//renvoit un tableau indexé de resp_login => "absent" ou "present"
function get_tab_resp_du_user($user_login,  $DEBUG=FALSE)
{
	$tab_resp=array();
	if($_SESSION['config']['responsable_virtuel'])
	{
		$tab_resp["conges"]="present";
	}
	else
	{
		if( $DEBUG ) {echo ">> RECHERCHE des RESPONSABLES de : $user_login<br>\n";}
		// recup du resp indiqué dans la table users (sauf s'il est resp de lui meme)
		$req = 'SELECT u_resp_login FROM conges_users WHERE u_login=\''.SQL::quote($user_login).'\';';
		$res = SQL::query($req);
		$rec = $res->fetch_array();
		if ($rec['u_resp_login'] !== NULL)
			$tab_resp[$rec['u_resp_login']]="present";

		// recup des resp des groupes du user
		if($_SESSION['config']['gestion_groupes'])
		{
			$list_groups=get_list_groupes_du_user($user_login,  $DEBUG);
			if($list_groups!="")
			{
				$tab_gid=explode(",", $list_groups);
				foreach($tab_gid as $gid)
				{
					$gid=trim($gid);
					$sql2='SELECT gr_login FROM conges_groupe_resp WHERE gr_gid='.SQL::quote($gid).' AND gr_login!=\''.SQL::quote($user_login).'\'';
					$ReqLog1 = SQL::query($sql2);

					while ($resultat1 = $ReqLog1->fetch_array())
					{
						//attention à ne pas mettre 2 fois le meme resp dans le tableau
						if (in_array($resultat1["gr_login"], $tab_resp)==FALSE)
							$tab_resp[$resultat1["gr_login"]]="present";
					}
				}
			}
		}
		if( $DEBUG ) {echo "tab_resp intermediaire =\n"; print_r($tab_resp); echo "<br>\n";}

		/************************************/
		// gestion des absence des responsables :
		// on verifie que les resp sont présents, si tous absent, on cherhe les resp des resp, et ainsi de suite ....
		if($_SESSION['config']['gestion_cas_absence_responsable'])
		{
			if( $DEBUG ) {echo "gestion des absence des responsables<br>\n"; }

			// on va verifier si les resp récupérés sont absents
			$nb_present=count($tab_resp);
			foreach ($tab_resp as $current_resp => $presence )
			{
				// verif dans la base si le current_resp est absent :
				$req = 'SELECT p_num
										 FROM conges_periode
										 WHERE p_login =\''.SQL::quote($current_resp).'\'
										 AND p_etat = \'ok\'
										 AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
										 AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())';
				$ReqLog_3 = SQL::query($req);
				if($ReqLog_3->num_rows!=0)
				{
					$nb_present=$nb_present-1;
					$tab_resp[$current_resp]="absent";
				}
			}

			//si aucun resp present on recupere les resp du resp
			if($nb_present==0)
			{
				$new_tab_resp=array();
				if( $DEBUG ) { echo "zero resp présent<br>\n"; }
				foreach ($tab_resp as $current_resp => $presence)
				{
					// attention ,on evite le cas ou le user est son propre resp (sinon on boucle infiniment)
					if($current_resp != $user_login)
						$new_tab_resp = array_merge  ( $new_tab_resp , get_tab_resp_du_user($current_resp,  $DEBUG));
				}
				$tab_resp = array_merge  ( $tab_resp, $new_tab_resp);
			}

		}
		// FIN gestion des absence des responsables :
		/************************************/
	}

	if( $DEBUG ) {echo "return tab_resp =\n"; print_r($tab_resp); echo "<br>\n";}
	return $tab_resp;
}


// construit le tableau des grands responsables d'un user
// (tab des grd resp des groupes à double_valid dont le user fait partie
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
function get_tab_grd_resp_du_user($user_login, &$tab_grd_resp,  $DEBUG=FALSE)
{
	// recup des resp des groupes du user
	if($_SESSION['config']['gestion_groupes'])
	{
		$list_groups=get_list_groupes_du_user($user_login,  $DEBUG);
		if( $DEBUG ) { echo "list_groups : <br>$list_groups<br>\n"; }

		if($list_groups!="")
		{
			$tab_gid=explode(",", $list_groups);
			foreach($tab_gid as $gid)
			{
				$gid=trim($gid);
				$sql1='SELECT ggr_login FROM conges_groupe_grd_resp WHERE ggr_gid='.SQL::quote($gid);
				$ReqLog1 = SQL::query($sql1);

				while ($resultat1 = $ReqLog1->fetch_array())
				{
					//attention à ne pas mettre 2 fois le meme resp dans le tableau
					if (in_array($resultat1["ggr_login"], $tab_grd_resp)==FALSE)
						$tab_grd_resp[]=$resultat1["ggr_login"];
				}
			}
		}
	}
}


function valid_ldap_user($username, $DEBUG=FALSE)
{
/* fonction utilisée avec le mode d'authentification ldap.
   En effet, si un utilisateur (enregistré dans le ldap) tente de se
connecter alors qu'il n'a pas de compte dans
   la base, il n'y a aucun message qui lui indique !

   Retourne TRUE, si tout est ok... ($username dans la table conges_users)
   False, sinon

*/
	// connexion MySQL + selection de la database sur le serveur

	$req = 'SELECT COUNT(*) FROM conges_users WHERE u_login=\''.SQL::quote($username).'\';';
	$res = SQL::query($req);
	$cpt = $res->fetch_array();
	$cpt = $cpt[0];

	return ( $cpt != 0 );
}


// verifie si un user est responasble ou pas
// renvoit TRUE si le login est responsable dans la table conges_users, FALSE sinon.
function is_resp($login)
{
	static $sql_is_resp = array();
	if (!isset($sql_is_resp[$login])) 
	{
		// recup de qq infos sur le user
		$select_info='SELECT u_is_resp FROM conges_users WHERE u_login=\''.SQL::quote($login).'\'';
		$ReqLog_info = SQL::query($select_info);
		$resultat_info = $ReqLog_info->fetch_array();
		$sql_is_resp[$login]=$resultat_info["u_is_resp"];
	}

	return ($sql_is_resp[$login]=='Y');
}

// verifie si un user est HR ou pas
// renvoit TRUE si le login est HR dans la table conges_users, FALSE sinon.
function is_hr($login,  $DEBUG=FALSE)
{
	static $sql_is_hr = array();
	if (!isset($sql_is_hr[$login])) 
	{
		// recup de qq infos sur le user
		$select_info='SELECT u_is_hr FROM conges_users WHERE u_login=\''.SQL::quote($login).'\';';
		$ReqLog_info = SQL::query($select_info);
		$resultat_info = $ReqLog_info->fetch_array();
		$sql_is_hr[$login]=$resultat_info["u_is_hr"];
	}

	return ($sql_is_hr[$login]=='Y');
}
// verifie si un user est valide ou pas
// renvoit TRUE si le login est enable dans la table conges_users, FALSE sinon.
function is_active($login,  $DEBUG=FALSE)
{
	static $sql_is_active = array();
	if (!isset($sql_is_active[$login])) 
	{
		// recup de qq infos sur le user
		$select_info='SELECT u_is_active FROM conges_users WHERE u_login=\''.SQL::quote($login).'\';';
		$ReqLog_info = SQL::query($select_info);
		$resultat_info = $ReqLog_info->fetch_array();
		$sql_is_active[$login]=$resultat_info["u_is_active"];
	}

	return ($sql_is_active[$login]=='Y');
}

// verifie si un user est responsable d'un second user
// renvoit TRUE si le $resp_login est responsable du $user_login, FALSE sinon.
function is_resp_of_user($resp_login, $user_login,  $DEBUG=FALSE)
{
	if ( !$_SESSION['config']['gestion_groupes'] )
	{
		// recup de qq infos sur le user
		$select_info='SELECT u_resp_login FROM conges_users WHERE u_login=\''.SQL::quote($user_login).'\';';
		$ReqLog_info = SQL::query($select_info);

		$resultat_info = $ReqLog_info->fetch_array();
		$sql_resp_login=$resultat_info["u_resp_login"];

		return ($resp_login==$sql_resp_login);
	}
	else 
	{

//		if ( $_SESSION['config']['double_validation_conges'] ){
			$ReqLog_info = SQL::query('SELECT count(*)
									FROM `conges_groupe_users`
									JOIN conges_groupe_resp ON gr_gid = gu_gid
									WHERE gu_login = \''.SQL::quote($user_login).'\'
									AND gr_login = \''.SQL::quote($resp_login).'\';');
			$resultat_info = $ReqLog_info->fetch_array();
			return ($resultat_info[0] != 0);
//		}
		$ReqLog_info = SQL::query('SELECT count(*)
								FROM `conges_groupe_users`
								JOIN conges_groupe_grd_resp ON ggr_gid = gu_gid
								WHERE gu_login = \''.SQL::quote($user_login).'\'
								AND ggr_login = \''.SQL::quote($resp_login).'\';');
		$resultat_info = $ReqLog_info->fetch_array();
		if ($resultat_info[0] != 0)
			return true;

		return false;
	}
}



// verifie si un user est administrateur ou pas
// renvoit TRUE si le login est administrateur dans la table conges_users, FALSE sinon.
function is_admin($login, $DEBUG=FALSE)
{
	static $sql_is_admin = array();
	if (!isset($sql_is_admin[$login])) {
		// recup de qq infos sur le user
		$select_info='SELECT u_is_admin FROM conges_users WHERE u_login=\''.SQL::quote($login).'\';';
		$ReqLog_info = SQL::query($select_info);

		$resultat_info = $ReqLog_info->fetch_array();
		$sql_is_admin[$login]=$resultat_info["u_is_admin"];
	}

	return ($sql_is_admin[$login]=='Y');
}




// on insert une nouvelle periode dans la table periode
// retourne le num d'auto_incremente (p_num) ou 0 en cas l'erreur
function insert_dans_periode($login, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $nb_jours, $commentaire, $id_type_abs, $etat, $id_fermeture, $DEBUG=FALSE)
{
	// Récupération du + grand p_num (+ grand numero identifiant de conges)
	$sql1 = "SELECT max(p_num) FROM conges_periode" ;
	$ReqLog1 = SQL::query($sql1);
	if ( $num_new_demande = $ReqLog1->fetch_row() )
		$num_new_demande = $num_new_demande[0] +1;
	else
		$num_new_demande = 1;

	$sql2 = "INSERT INTO conges_periode SET p_login='$login',p_date_deb='$date_deb', p_demi_jour_deb='$demi_jour_deb',p_date_fin='$date_fin', p_demi_jour_fin='$demi_jour_fin', p_nb_jours='$nb_jours', p_commentaire='$commentaire', p_type='$id_type_abs', p_etat='$etat', ";

	if($id_fermeture!=0)
		$sql2 = $sql2." p_fermeture_id='$id_fermeture' ," ;
	if($etat=="demande")
		$sql2 = $sql2." p_date_demande=NOW() ," ;
	else
		$sql2 = $sql2." p_date_traitement=NOW() ," ;

	$sql2 = $sql2." p_num='$num_new_demande' " ;
	$result = SQL::query($sql2);

	if($id_fermeture!=0)
		$comment_log = "saisie de fermeture num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
	elseif($etat=="demande")
		$comment_log = "demande de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
	else
		$comment_log = "saisie de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";

	log_action($num_new_demande, $etat, $login, $comment_log, $DEBUG);

	if($result)
		return $num_new_demande;
	else
		return 0;
}


// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_feries()
{
	if (empty($_SESSION['tab_j_feries'])) 
	{
		$_SESSION['tab_j_feries']=array();

		$sql_select='SELECT jf_date FROM conges_jours_feries;';
		$res_select = SQL::query($sql_select);

		while( $row = $res_select->fetch_array()) 
		{
			$_SESSION['tab_j_feries'][]=$row['jf_date'];
		}
	}
}

// renvoit TRUE si le jour est chomé (férié), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_feries"]
function est_chome($timestamp)
{
	$j_date=date("Y-m-d", $timestamp);
	if(isset($_SESSION["tab_j_feries"]))
		return in_array($j_date, $_SESSION["tab_j_feries"]);
	else
		return FALSE;
}


// initialise le tableau des variables de config (renvoit un tableau)
function init_config_tab()
{
	static $userlogin = null;
	static $result = null;
	if ($result === null || $userlogin != $_SESSION['userlogin']) 
	{

		include ROOT_PATH .'version.php';
		include CONFIG_PATH .'dbconnect.php';
		$tab = array();

		/******************************************/
		//  recup des variables de version.php
		if(isset($config_php_conges_version)) $tab['php_conges_version'] = $config_php_conges_version ;
		if(isset($config_url_site_web_php_conges)) $tab['url_site_web_php_conges'] = $config_url_site_web_php_conges ;

		/******************************************/
		//  recup des variables de la table conges_appli
		$sql_appli = "SELECT appli_variable, appli_valeur FROM conges_appli;";
		$req_appli = SQL::query($sql_appli) ;

		while ($data_appli = $req_appli->fetch_array()) 
		{
			$key	= $data_appli[0];
			$value	= $data_appli[1];
			$tab[$key]	= $value;
		}

		/******************************************/
		//  recup des variables de la table conges_config

		$sql_config = "SELECT conf_nom, conf_valeur, conf_type FROM conges_config;";
		$req_config = SQL::query($sql_config) ;

		while ($data = $req_config->fetch_array()) 
		{
			$key	= $data[0];
			$value	= $data[1];
			$type	= $data[2];

			if($value == "FALSE")
				$value = false;
			elseif($value == "TRUE")
				$value = true;
			elseif($type == "path")
				$value =  ROOT_PATH ."/".$value ;

			$tab[$key] = $value;
		}


		/******************************************/
		//  recup des mails dans  la table conges_mail
		$sql_mail = "SELECT mail_nom, mail_subject, mail_body FROM conges_mail;";
		$req_mail = SQL::query($sql_mail) ;

		while ($data_mail = $req_mail->fetch_array()) 
		{
			$mail_nom	= $data_mail[0];
			$key1	= $mail_nom."_sujet";
			$key2	= $mail_nom."_contenu";
			$sujet	= $data_mail[1];
			$corps	= $data_mail[2];
			$tab[$key1] = $sujet ;
			$tab[$key2] = $corps ;
		}

		/******************************************/
		//  config_ldap.php
		if (file_exists(CONFIG_PATH .'config_ldap.php')) 
		{
			include CONFIG_PATH .'config_ldap.php';
			if(isset($config_ldap_protocol_version))
				$tab['ldap_protocol_version'] = $config_ldap_protocol_version ;
			else
				$tab['ldap_protocol_version'] = 0;

			if(isset($config_ldap_server))	$tab['ldap_server']	= $config_ldap_server ;
			if(isset($config_ldap_bupsvr))	$tab['ldap_bupsvr']	= $config_ldap_bupsvr ;
			if(isset($config_basedn))	$tab['basedn']		= $config_basedn ;
			if(isset($config_ldap_user))	$tab['ldap_user']	= $config_ldap_user ;
			if(isset($config_ldap_pass))	$tab['ldap_pass']	= $config_ldap_pass ;
			if(isset($config_searchdn))	$tab['searchdn']	= $config_searchdn ;
			if(isset($config_ldap_prenom))	$tab['ldap_prenom']	= $config_ldap_prenom ;
			if(isset($config_ldap_nom))	$tab['ldap_nom']	= $config_ldap_nom ;
			if(isset($config_ldap_mail))	$tab['ldap_mail']	= $config_ldap_mail ;
			if(isset($config_ldap_login))	$tab['ldap_login']	= $config_ldap_login ;
			if(isset($config_ldap_nomaff))	$tab['ldap_nomaff']	= $config_ldap_nomaff ;
			if(isset($config_ldap_filtre))	$tab['ldap_filtre']	= $config_ldap_filtre ;
			if(isset($config_ldap_filrech))	$tab['ldap_filrech']	= $config_ldap_filrech ;
			if(isset($config_ldap_filtre_complet)) $tab['ldap_filtre_complet']  = $config_ldap_filtre_complet ;
		}

		/******************************************/
		//  config_CAS.php
		if (file_exists(CONFIG_PATH .'config_CAS.php')) 
		{
			include CONFIG_PATH .'config_CAS.php';
			if(isset($config_CAS_host))	$tab['CAS_host']	= $config_CAS_host ;
			if(isset($config_CAS_portNumber)) $tab['CAS_portNumber'] = $config_CAS_portNumber ;
			if(isset($config_CAS_URI))	$tab['CAS_URI']		= $config_CAS_URI ;
		}

		/******************************************/
		//  recup de qq infos sur le user
		if(isset($_SESSION['userlogin'])) 
		{
			$sql_user = "SELECT u_nom, u_prenom, u_is_resp, u_is_admin, u_is_hr, u_is_active FROM conges_users WHERE u_login='".$_SESSION['userlogin']."' ";
			$req_user = SQL::query($sql_user) ;

			if($data_user = $req_user->fetch_array()) 
			{
				$_SESSION['u_nom']	= $data_user[0] ;
				$_SESSION['u_prenom']	= $data_user[1] ;
				$_SESSION['is_resp']	= $data_user[2] ;
				$_SESSION['is_admin']	= $data_user[3] ;
				$_SESSION['is_hr']	= $data_user[4] ;
				$_SESSION['is_active']	= $data_user[5] ;
			}
		}

		/******************************************/
		$result = $tab;
		if (isset($_SESSION['userlogin']))
			$userlogin = $_SESSION['userlogin'];
	}
	return $result;
}




// Récupère le contenu d une variable $_GET / $_POST
function getpost_variable($variable, $default="")
{
   $valeur = (isset($_POST[$variable]) ? $_POST[$variable]  : (isset($_GET[$variable]) ? $_GET[$variable]   : $default));

   return   $valeur;
}


// recup TRUE si le user a "u_see_all" à 'Y' dans la table users, FALSE sinon
function get_user_see_all($login)
{

	$request = 'SELECT u_see_all FROM conges_users WHERE u_login=\''.SQL::quote($login).'\';';
	$data = SQL::query($request);

	if($l = $data->fetch_array()) 
	{
		$see_all = $l['u_see_all'];
		return ($see_all == 'Y');
	}
	else
		return FALSE;
}


// recup dans un tableau des types de conges
function recup_tableau_types_conges()
{
	$result = array();
	$request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'conges\';';
	$data   = SQL::query($request);

	while ($l = $data->fetch_array()) 
	{
		$id = $l['ta_id'];
		$result[$id] = $l['ta_libelle'];
	}
	return $result;
}

// recup dans un tableau des types d'absence
function recup_tableau_types_absence()
{
	$result = array();
	$request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'absences\';';
	$data   = SQL::query($request);

	while ($l = $data->fetch_array()) 
	{
		$id = $l['ta_id'];
		$result[$id] = $l['ta_libelle'];
	}
	return $result;
}

// recup dans un tableau des types de conges exceptionnels
function recup_tableau_types_conges_exceptionnels()
{
	$result = array();
	$request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'conges_exceptionnels\';';
	$data   = SQL::query($request);

	while ($l = $data->fetch_array()) 
	{
		$id = $l['ta_id'];
		$result[$id] = $l['ta_libelle'];
	}
	return $result;
}

// recup dans un tableau de tableau les infos des types de conges et absences
function recup_tableau_tout_types_abs( )
{
	$result = array();
	if ( $_SESSION['config']['gestion_conges_exceptionnels'] ) // on prend tout les types de conges
		$request = 'SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence;';
	else // on prend tout les types de conges SAUF les conges exceptionnels
		$request = 'SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence WHERE conges_type_absence.ta_type != \'conges_exceptionnels\';';

	$data = SQL::query($request);

	while ($resultat_cong = $data->fetch_array()) 
	{
		$id = $resultat_cong['ta_id'];
		$result[$id] = array('type' =>  $resultat_cong['ta_type'],'libelle' => $resultat_cong['ta_libelle'],'short_libelle' =>  $resultat_cong['ta_short_libelle'],);
	}
	return $result;
}

// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
function recup_tableau_conges_for_user($login, $hide_conges_exceptionnels)
{
	// on pourrait tout faire en un seule select, mais cela bug si on change la prise en charge des conges exceptionnels en cours d'utilisation ...

	if ($_SESSION['config']['gestion_conges_exceptionnels'] && ! $hide_conges_exceptionnels) // on prend tout les types de conges
		$request = 'SELECT ta_libelle, su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = \''.SQL::quote($login).'\' ORDER BY su_abs_id ASC;';
	else // on prend tout les types de conges SAUF les conges exceptionnels
		$request = 'SELECT ta_libelle, su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_type != \'conges_exceptionnels\' AND conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = \''.SQL::quote($login).'\' ORDER BY su_abs_id ASC;';

	$data   = SQL::query($request);

	$result = array();

	while ($l = $data->fetch_array()) 
	{
		$sql_id = $l['ta_libelle'];
		$result[$sql_id] = array('nb_an' => affiche_decimal($l['su_nb_an']),'solde' => affiche_decimal($l['su_solde']),'reliquat' => affiche_decimal($l['su_reliquat']),);
	}

	return $result;
}

// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
function recup_tableau_conges_for_users( $hide_conges_exceptionnels, $logins = false)
{
	// on pourrait tout faire en un seule select, mais cela bug si on change la prise en charge des conges exceptionnels en cours d'utilisation ...

	if ($logins === false)
		$logins = '';
	else
		$logins = ' AND su_login IN ( \''.implode('\', \'',$logins).'\') ';

	if ($_SESSION['config']['gestion_conges_exceptionnels'] && ! $hide_conges_exceptionnels) // on prend tout les types de conges
		$request = 'SELECT su_login, ta_libelle,  su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_id = conges_solde_user.su_abs_id '.$logins.' ORDER BY ta_type , su_abs_id ASC';
	else // on prend tout les types de conges SAUF les conges exceptionnels
		$request = 'SELECT su_login, ta_libelle, su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_type != \'conges_exceptionnels\' AND conges_type_absence.ta_id = conges_solde_user.su_abs_id '.$logins.' ORDER BY su_abs_id ASC';

	$data = SQL::query($request);

	$result=array();
	while ($l = $data->fetch_array()) 
	{
		$tab=array();
		$tab['nb_an'] = affiche_decimal($l['su_nb_an']);
		$tab['solde'] = affiche_decimal($l['su_solde']);
		$tab['reliquat'] = affiche_decimal($l['su_reliquat']);
		$result[ $l['su_login'] ][ $l['ta_libelle'] ]   = $tab;
	}
	return $result;
}

// affichage du tableau récapitulatif des solde de congés d'un user
function affiche_tableau_bilan_conges_user($login, $DEBUG=FALSE) 
{
	$request = 'SELECT u_quotite FROM conges_users where u_login = \''.SQL::quote($login).'\';';
	$ReqLog = SQL::query($request) ;
	$resultat = $ReqLog->fetch_array();
	$sql_quotite=$resultat['u_quotite'];

	// recup dans un tableau de tableaux les nb et soldes de conges d'un user
	$tab_cong_user = recup_tableau_conges_for_user($login, true ,$DEBUG);

	// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
	if ($_SESSION['config']['gestion_conges_exceptionnels'])
		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

	echo "<table class=\"table table-hover table-responsive table-condensed table-bordered\">\n";
	echo '<thead>';
	echo '<tr><td></td><td colspan="' . (count($tab_cong_user) * 2 ).'">SOLDES</td></tr>';
	echo '<tr>';
	echo '<th class="titre">'. _('divers_quotite') .'</th>';

	foreach($tab_cong_user as $id => $val) 
	{
		if ($_SESSION['config']['gestion_conges_exceptionnels'] && in_array($id,$tab_type_conges_exceptionnels))
			echo '<th class="solde">'.$id.'</th>';
		else
			echo '<th class="annuel">'.$id.' / '. _('divers_an_maj') .'</th><th class="solde">'.$id.'</th>';
	}
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';
	echo '<td class="quotite">'.$sql_quotite.'%</td>';
	foreach($tab_cong_user as $id => $val)
	{
		if ($_SESSION['config']['gestion_conges_exceptionnels']  && in_array($id,$tab_type_conges_exceptionnels))
			echo '<td class="solde">'.$val['solde'].( $val['reliquat'] > 0 ? ' ('. _('dont_reliquat').' '.$val['reliquat'].')': '') .'</td>';
		else
			echo '<td class="annuel">'.$val['nb_an'].'</td><td class="solde">'.$val['solde'].( $val['reliquat'] > 0 ?' ('. _('dont_reliquat').' '.$val['reliquat'].')':'').'</td>';
	}
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
}

// renvoit un tableau de tableau contenant les informations du user
// renvoit FALSE si erreur
function recup_infos_du_user($login, $list_groups_double_valid, $DEBUG=FALSE)
{
	$tab=array();
	$sql1 = 'SELECT u_login, u_nom, u_prenom, u_is_resp, u_resp_login, u_is_admin, u_is_hr, u_is_active, u_see_all, u_passwd, u_quotite, u_email, u_num_exercice FROM conges_users ' .
			'WHERE u_login=\''.SQL::quote($login).'\';';
	$ReqLog = SQL::query($sql1) ;

	if($resultat = $ReqLog->fetch_array())
	{
		$tab_user=array();
		$tab_user['login']	= $resultat['u_login'];;
		$tab_user['nom']	= $resultat['u_nom'];
		$tab_user['prenom']	= $resultat['u_prenom'];
		$tab_user['is_resp']	= $resultat['u_is_resp'];
		$tab_user['resp_login']	= $resultat['u_resp_login'];
		$tab_user['is_admin']	= $resultat['u_is_admin'];
		$tab_user['is_hr']	= $resultat['u_is_hr'];
		$tab_user['is_active']	= $resultat['u_is_active'];
		$tab_user['see_all']	= $resultat['u_see_all'];
		$tab_user['passwd']	= $resultat['u_passwd'];
		$tab_user['quotite']	= $resultat['u_quotite'];
		$tab_user['email']	= $resultat['u_email'];
		$tab_user['num_exercice'] = $resultat['u_num_exercice'];
		$tab_user['conges']	= recup_tableau_conges_for_user($login, false, $DEBUG);

		$tab_user['double_valid'] = "N";

		// on regarde ici si le user est dans un groupe qui fait l'objet d'une double validation
		if($_SESSION['config']['double_validation_conges'])
		{
			if($list_groups_double_valid!="") // si $resp_login est responsable d'au moins un groupe a double validation
			{
				$sql1='SELECT gu_login FROM conges_groupe_users WHERE gu_login=\''.SQL::quote($login).'\' AND gu_gid IN ('.$list_groups_double_valid.') ORDER BY gu_gid, gu_login;';
				$ReqLog1 = SQL::query($sql1);

				if($ReqLog1->num_rows  !=0)
					$tab_user['double_valid'] = 'Y';
			}
		}
		return $tab_user ;
	}
	else
		return FALSE;
}

// renvoit un tableau de tableau contenant les informations de tous les users
function recup_infos_all_users($DEBUG=FALSE)
{
	$tab=array();
	$list_groupes_double_validation=get_list_groupes_double_valid($DEBUG);
	$sql1 = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom";
	$ReqLog = SQL::query($sql1);

	while ($resultat =$ReqLog->fetch_array())
	{
		$tab_user=array();
		$sql_login=$resultat["u_login"];

		$tab[$sql_login] = recup_infos_du_user($sql_login, $list_groupes_double_validation, $DEBUG);
	}
	return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users d'un groupe donné
function recup_infos_all_users_du_groupe($group_id, $DEBUG=FALSE)
{
	$tab=array();
	// recup de la liste de tous les users du groupe ...
	$list_all_users_du_groupe = get_list_users_du_groupe($group_id, $DEBUG);
	if( $DEBUG ) { echo "list_all_users_du_groupe :<br>\n"; print_r($list_all_users_du_groupe); echo "<br><br>\n";}

	$list_groupes_double_validation=get_list_groupes_double_valid($DEBUG);
	if( $DEBUG ) { echo "list_groupes_double_validation :<br>\n"; print_r($list_groupes_double_validation); echo "<br><br>\n";}

	if(strlen($list_all_users_du_groupe)!=0)
	{
		$tab_users_du_groupe=explode(",", $list_all_users_du_groupe);
		foreach($tab_users_du_groupe as $current_login)
		{
			$current_login = trim($current_login);
			$current_login = trim($current_login, "\'");  // on enleve les quotes qui ont été ajouté lors de la creation de la liste
			$tab[$current_login] = recup_infos_du_user($current_login, $list_groupes_double_validation, $DEBUG);
		}
	}
	return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users dont $login est responsable
function recup_infos_all_users_du_resp($login, $DEBUG=FALSE)
{
	$tab=array();

	// recup de la liste de tous les users du resp ...
	$list_all_users_du_resp = get_list_all_users_du_resp($login, $DEBUG);
	if( $DEBUG ) { echo "list_all_users_du_resp :<br>\n"; print_r($list_all_users_du_resp); echo "<br><br>\n";}

	// recup de la liste des groupes à double validation, dont $login est responsable
	// (servira à dire pour chaque user s'il est dans un de ces groupe ou non , donc s'il fait l'objet d'une double valid ou non )
	$list_groups_double_valid_du_resp=get_list_groupes_double_valid_du_resp($login, $DEBUG);
	if( $DEBUG ) { echo "list_groups_double_valid :<br>\n"; print_r($list_groups_double_valid_du_resp); echo "<br><br>\n";}

	if(strlen($list_all_users_du_resp)!=0)
	{
		$tab_users_du_resp=explode(",", $list_all_users_du_resp);
		foreach($tab_users_du_resp as $current_login)
		{
			$current_login = trim($current_login);
			$current_login = trim($current_login, "\'");  // on enleve les quotes qui ont été ajouté lors de la creation de la liste

			$tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid_du_resp, $DEBUG);
		}
	}

	return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users dont $login est GRAND responsable
function recup_infos_all_users_du_grand_resp($login, $DEBUG=FALSE)
{
	$tab=array();
	$list_groups_double_valid=get_list_groupes_double_valid_du_grand_resp($login, $DEBUG);
	if( $DEBUG ) { echo "list_groups_double_valid :<br>\n"; print_r($list_groups_double_valid); echo "<br><br>\n";}

	if($list_groups_double_valid!="")
	{
		// recup de la liste des users des groupes de la liste $list_groups_double_valid
		$sql_users = 'SELECT DISTINCT(gu_login) FROM conges_groupe_users, conges_users WHERE gu_gid IN ('.SQL::quote($list_groups_double_valid).') AND gu_login=u_login ORDER BY u_nom;';
		$ReqLog_users = SQL::query($sql_users) ;
		$list_all_users_dbl_valid="";
		while ($resultat_users =$ReqLog_users->fetch_array())
		{
			$current_login=$resultat_users["gu_login"];
			if($list_all_users_dbl_valid=="")
				$list_all_users_dbl_valid="'$current_login'";
			else
				$list_all_users_dbl_valid=$list_all_users_dbl_valid.", '$current_login'";
		}

		if($list_all_users_dbl_valid!="")
		{
			$tab_users_du_resp=explode(",", $list_all_users_dbl_valid);
			foreach($tab_users_du_resp as $current_login)
			{
				$current_login = trim($current_login);
				$current_login = trim($current_login, "\'");  // on enleve les qote qui on été ajouté lors de la creation de la liste
				$tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid, $DEBUG);
			}
		} 
	} 
	return $tab ;
}

// execute sequentiellement les requètes d'un fichier .sql
function execute_sql_file($file, $DEBUG=FALSE)
{
	// lecture du fichier SQL
	// et execution de chaque ligne ....
	$lines = file ($file);
	$sql_requete="";
	foreach ($lines as $line_num => $line)
	{
		$line=trim($line);
		if( (substr($line, 0, 1)!="#") && ($line!="") )  //on ne prend pas les lignes de commentaire
		{
			$sql_requete = $sql_requete.$line ;
			if(substr($sql_requete, -1, 1)==";") // alors la requete est finie !
			{
				if( $DEBUG )
					echo "$sql_requete<br>\n";
				$result = SQL::query($sql_requete);
				$sql_requete="";
			}
		}
	}
	return TRUE;
}

// verif des droits du user à afficher la page qu'il demande (pour éviter les hacks par bricolage d'URL)
// verif_droits_user($session, "is_admin", $DEBUG);
function verif_droits_user($session, $niveau_droits, $DEBUG=FALSE)
{
	if( $DEBUG ) { print_r($_SESSION); echo "<br><br>\n"; }

	$niveau_droits = strtolower($niveau_droits);

	// verif si $_SESSION['is_admin'] ou $_SESSION['is_resp'] ou $_SESSION['is_hr'] =="N" ou $_SESSION['is_active'] =="N"
	if($_SESSION[$niveau_droits]=="N")
	{
		// on recupere les variable utiles pour le suite :
		$url_accueil_conges = $_SESSION['config']['URL_ACCUEIL_CONGES'] ;
		$lang_divers_acces_page_interdit =  _('divers_acces_page_interdit') ;
		$lang_divers_user_disconnected	=  _('divers_user_disconnected') ;
		$lang_divers_veuillez		=  _('divers_veuillez') ;
		$lang_divers_vous_authentifier	=  _('divers_vous_authentifier') ;

		// on delete la session et on renvoit sur l'authentification (page d'accueil)
		session_delete($session);

		// message d'erreur !
		echo "<center>\n";
		echo "<font color=\"red\">$lang_divers_acces_page_interdit</font><br>$lang_divers_user_disconnected<br>\n";
		echo "$lang_divers_veuillez <a href='$url_accueil_conges/index.php' target='_top'> $lang_divers_vous_authentifier .</a>\n";
		echo "</center>\n";
		exit;
	}
}


// on lit le contenu du répertoire lang et on parse les nom de ficher (ex lang_fr_francais.php)
function affiche_select_from_lang_directory( $select_name, $default )
{
	if(empty($select_name)){$select_name = 'lang';}
	if(empty($default)){$default = 'fr_FR';}
	echo '<select id="'.$select_name.'" name="'.$select_name.'" class="form-control">';
	$langs = glob( LOCALE_PATH .'*' );
	var_dump( $langs );
	foreach($langs as $lang ) {
		$lang = basename($lang);
		if( $lang == $default )
			echo '<option value="'.$lang.'" selected >'.$lang.'</option>';
		else
			echo '<option value="'.$lang.'">'.$lang.'</option>';
	}
	echo "</select>\n";
}

// on insert les logs des periodes de conges
// retourne TRUE ou FALSE
function log_action($num_periode, $etat_periode, $login_pour, $comment, $DEBUG=FALSE)
{
	if(isset($_SESSION['userlogin']))
		$user = $_SESSION['userlogin'] ;
	else
		$user = "inconnu";

	$sql1 = 'INSERT INTO conges_logs SET log_p_num=\''.SQL::quote($num_periode).'\',log_user_login_par=\''.SQL::quote($user).'\',log_user_login_pour=\''.SQL::quote($login_pour).'\',log_etat=\''.SQL::quote($etat_periode).'\',log_comment=\''.SQL::quote($comment).'\',log_date=NOW()';
	$result = SQL::query($sql1);

	return $result;
}

// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_fermeture($user,  $DEBUG=FALSE)
{
	$_SESSION["tab_j_fermeture"]=array();
	$sql_select='SELECT DISTINCT jf_date FROM conges_jours_fermeture, conges_groupe_users WHERE gu_login=\''.SQL::quote($user).'\' AND gu_gid=jf_gid';
	$res_select = SQL::query($sql_select);

	while( $row = $res_select->fetch_array())
		$_SESSION["tab_j_fermeture"][]=$row["jf_date"];
}

// renvoit TRUE si le jour est fermé (fermeture), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_fermeture"]
function est_ferme($timestamp)
{
	$j_date=date("Y-m-d", $timestamp);
	if(isset($_SESSION["tab_j_fermeture"]))
		return in_array($j_date, $_SESSION["tab_j_fermeture"]);
	else
		return FALSE;
}

// renvoit le "su_reliquat" pour un user et un type de conges donné
function get_reliquat_user_conges($login, $type_abs,  $DEBUG=FALSE)
{
	$select_info='SELECT su_reliquat FROM conges_solde_user WHERE su_login=\''.SQL::quote($login).'\' AND su_abs_id=\''.SQL::quote($type_abs).'\'';
	$ReqLog_info = SQL::query($select_info);
	$resultat_info = $ReqLog_info->fetch_array();
	$sql_reliquat=$resultat_info["su_reliquat"];

	return $sql_reliquat;
}

/*  si date_fin_conges < date_limite_reliquat => alors on décompte dans reliquats
	si date_debut_conges > date_limite_reliquat => alors on ne décompte pas dans reliquats
	si gonges demandé est à cheval sur la date_limite_reliquat => il faut decompter le nb_jours_pris du solde, puis il faut
	calculer le nb_jours_avant pris avant la date limite, et on le decompte des reliquats, et calculer le nb_jours_apres
	d'apres la date limite et ne pas le décompter des reliquats !!!
*/
function soustrait_solde_et_reliquat_user($user_login, $num_current_periode, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin,  $DEBUG=FALSE)
{

$user_nb_jours_pris = number_format($user_nb_jours_pris, 1, '.', '');

	//si on autorise les reliquats
	if($_SESSION['config']['autorise_reliquats_exercice'])
	{
		//recup du reliquat du user pour ce type d'absence
		$reliquat=get_reliquat_user_conges($user_login, $type_abs,  $DEBUG);
		//echo "reliquat = $reliquat<br>\n";
		// s'il y a une date limite d'utilisationdes reliquats (au format jj-mm)
		if($_SESSION['config']['jour_mois_limite_reliquats']!=0)
		{
			//si date_fin_conges < date_limite_reliquat => alors on décompte dans reliquats
			if($date_fin < $_SESSION['config']['date_limite_reliquats'])
			{
				if($reliquat>$user_nb_jours_pris)
					$new_reliquat = $reliquat-$user_nb_jours_pris;
				else
					$new_reliquat = 0;
			}
			//si date_debut_conges > date_limite_reliquat => alors on ne décompte pas dans reliquats
			elseif($date_deb >= $_SESSION['config']['date_limite_reliquats'])
			{
				$new_reliquat = $reliquat;
			}
			//si conges demandé est à cheval sur la date_limite_reliquat => il faut decompter le nb_jours_pris du solde, puis il faut
			//calculer le nb_jours_avant pris avant la date limite, et on le decompte des reliquats, et calculer le nb_jours_apres
			//d'apres la data limite et ne pas le décompter des reliquats !!!
			else
			{
				include 'fonctions_calcul.php' ;
				$nb_reliquats_a_deduire = compter($user_login, $num_current_periode, $date_deb, $_SESSION['config']['date_limite_reliquats'], $demi_jour_deb, "pm", null ,  $DEBUG);

				if($reliquat > $nb_reliquats_a_deduire)
					$new_reliquat = $reliquat - $nb_reliquats_a_deduire;
				else
					$new_reliquat = 0;
			}
		}
		// s'il n'y a pas de date limite d'utilisation des reliquats
		else
		{
			if($reliquat>$user_nb_jours_pris)
				$new_reliquat = $reliquat-$user_nb_jours_pris;
			else
				$new_reliquat = 0;
		}

		$sql2 = 'UPDATE conges_solde_user SET su_solde=su_solde-'.SQL::quote($user_nb_jours_pris).', su_reliquat='.SQL::quote($new_reliquat).' WHERE su_login=\''.SQL::quote($user_login).'\'  AND su_abs_id='.SQL::quote($type_abs).' ';
	}
	else
	{
		$sql2 = 'UPDATE conges_solde_user SET su_solde=su_solde-'.SQL::quote($user_nb_jours_pris).' WHERE su_login=\''.SQL::quote($user_login).'\'  AND su_abs_id=\''.$type_abs.'\' ';
	}
	$ReqLog2 = SQL::query($sql2) ;
}

// recup de la liste des users des groupes dont $resp_login est responsable mais ne remonte pas les autres responsables
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_des_groupes_du_resp_sauf_resp($resp_login, $DEBUG=FALSE)
{
	$list_users_des_groupes_du_resp_sauf_resp="";
	$list_groups=get_list_groupes_du_resp($resp_login, $DEBUG);
	if($list_groups!="") // si $resp_login est responsable d'au moins un groupe
	{
		$sql1="SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid IN ($list_groups) AND gu_login NOT IN (SELECT gr_login FROM conges_groupe_resp WHERE gr_gid IN ($list_groups)) ORDER BY gu_login ";
		$ReqLog1 = SQL::query($sql1);

		while ($resultat1 = $ReqLog1->fetch_array())
		{
			$current_login=$resultat1["gu_login"];
			if($list_users_des_groupes_du_resp_sauf_resp=="")
				$list_users_des_groupes_du_resp_sauf_resp="'$current_login'";
			else
				$list_users_des_groupes_du_resp_sauf_resp=$list_users_des_groupes_du_resp_sauf_resp.", '$current_login'";
		}
	}
	if( $DEBUG ) { echo "list_users_des_groupes_du_resp_sauf_resp= $list_users_des_groupes_du_resp_sauf_resp<br>\n" ;}

	return $list_users_des_groupes_du_resp_sauf_resp;
}

/*--------- ajout fonction probesys -------------------*/
//date au format d/m/Y -> Y-m-d
function convert_date($date){
	$date_component = explode('/', $date);
	$date_component = array_reverse($date_component);

	return implode('-', $date_component);
}

//date au format d/m/Y -> d-m-Y
function revert_date($date){
	$date_component = explode('-', $date);

	return implode('/', $date_component);
}

//date au format d/m/Y 
function get_nb_jour($date_deb, $date_fin, $demi_jour_deb, $demi_jour_fin){
	$date_deb = new DateTime($date_deb); //inclusive
	$date_fin = new DateTime($date_fin); //exclusive
	$diff = $date_deb->diff($date_fin);
	$diff = $diff->format("%a");

	if($demi_jour_deb == 'am' && $demi_jour_fin =='pm') {
		$diff = $diff + 1;
	}

	if($demi_jour_deb == $demi_jour_fin) {
		$diff = $diff + 0.5;
	}

	return $diff;
}

