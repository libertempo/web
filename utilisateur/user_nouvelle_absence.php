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


// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
init_tab_jours_feries();

// si le user peut saisir ses demandes et qu'il vient d'en saisir une ...


$new_demande_conges = getpost_variable('new_demande_conges', 0);

if( $new_demande_conges == 1 && $_SESSION['config']['user_saisie_demande'] ) 
{
	$new_debut	    = getpost_variable('new_debut');
	$new_demi_jour_deb  = getpost_variable('new_demi_jour_deb');
	$new_fin	    = getpost_variable('new_fin');
	$new_demi_jour_fin  = getpost_variable('new_demi_jour_fin');
	$new_comment	    = getpost_variable('new_comment');
	$new_type	    = getpost_variable('new_type');

	$user_login	    = $_SESSION['userlogin'];

	if( $_SESSION['config']['disable_saise_champ_nb_jours_pris'] ) 
		$new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment,  $DEBUG);
	else
	   $new_nb_jours = getpost_variable('new_nb_jours') ;

	new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $DEBUG);
}
else
{
	$year_calendrier_saisie_debut   = getpost_variable('year_calendrier_saisie_debut'   , date('Y'));
	$mois_calendrier_saisie_debut   = getpost_variable('mois_calendrier_saisie_debut'   , date('m'));
	$year_calendrier_saisie_fin     = getpost_variable('year_calendrier_saisie_fin'     , date('Y'));
	$mois_calendrier_saisie_fin     = getpost_variable('mois_calendrier_saisie_fin'     , date('m'));

	/**************************/
	/* Nouvelle Demande */
	/**************************/

	echo '<h1>'. _('divers_nouvelle_absence') .'</h1>';

	 //affiche le formulaire de saisie d'une nouvelle demande de conges
	// saisie_nouveau_conges($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $DEBUG);

	//affiche le formulaire de saisie d'une nouvelle demande de conges
	saisie_nouveau_conges2($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $DEBUG);
}


// verifie les parametre de la nouvelle demande :si ok : enregistre la demande dans table conges_periode
function new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $DEBUG=FALSE)
{

	//conversion des dates
	$new_debut = convert_date($new_debut);
	$new_fin = convert_date($new_fin);   

	// print_r($new_fin);
	//$new_nb_jours = get_nb_jour($new_debut, $new_fin, $new_demi_jour_deb, $new_demi_jour_fin);
	
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// echo " $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type<br><br>\n";

	// exit;

	// verif validité des valeurs saisies
	$valid = verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

	// verifie que le solde de conges sera encore positif après validation
	if( $_SESSION['config']['solde_toujours_positif'] ) {
	$valid = $valid && verif_solde_user($_SESSION['userlogin'], $new_type, $new_nb_jours, $DEBUG);
	}

	if( $valid ) {

	if( in_array(get_type_abs($new_type, $DEBUG) , array('conges','conges_exceptionnels') ) )
		$new_etat = 'demande' ;
	else
		$new_etat = 'ok' ;

	$new_comment = addslashes($new_comment);

	$periode_num = insert_dans_periode($_SESSION['userlogin'], $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $new_etat, 0, $DEBUG);

	if ( $periode_num != 0 ) {
		echo schars( _('form_modif_ok') ).' !<br><br>'."\n";
		//envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)
		if($_SESSION['config']['mail_new_demande_alerte_resp'])
			alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_demande", $DEBUG);
	}
	else
		echo schars( _('form_modif_not_ok') ).' !<br><br>'."\n";
	}
	else {
		echo schars( _('resp_traite_user_valeurs_not_ok') ).' !<br><br>'."\n";
	}

	/* RETOUR PAGE PRINCIPALE */
	// echo '<form action="'.$PHP_SELF.'?session='.$session.'" method="POST">';
	// echo '<input type="submit" value="'. _('form_retour') .'">';
	// echo '</form>';
	
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_retour') ."</a>\n";

}


function verif_solde_user($user_login, $type_conges, $nb_jours,  $DEBUG=FALSE)
{
	$verif = TRUE;
	// on ne tient compte du solde que pour les absences de type conges (conges avec solde annuel)
	if (get_type_abs($type_conges,  $DEBUG)=="conges")
	{
		// recup du solde de conges de type $type_conges pour le user de login $user_login
		$select_solde='SELECT su_solde FROM conges_solde_user WHERE su_login=\''.SQL::quote($user_login).'\' AND su_abs_id='.SQL::quote($type_conges);
		$ReqLog_solde_conges = SQL::query($select_solde);
		$resultat_solde = $ReqLog_solde_conges->fetch_array();
		$sql_solde_user = $resultat_solde["su_solde"];

		// recup du nombre de jours de conges de type $type_conges pour le user de login $user_login qui sont à valider par son resp ou le grd resp
		$select_solde_a_valider='SELECT SUM(p_nb_jours) FROM conges_periode WHERE p_login=\''.SQL::quote($user_login).'\' AND p_type='.SQL::quote($type_conges).' AND (p_etat=\'demande\' OR p_etat=\'valid\') ';
		$ReqLog_solde_conges_a_valider = SQL::query($select_solde_a_valider);
		$resultat_solde_a_valider = $ReqLog_solde_conges_a_valider->fetch_array();
		$sql_solde_user_a_valider = $resultat_solde_a_valider["SUM(p_nb_jours)"];
		if ($sql_solde_user_a_valider == NULL )
			$sql_solde_user_a_valider = 0;

		// vérification du solde de jours de type $type_conges
		if ($sql_solde_user < $nb_jours+$sql_solde_user_a_valider)
		{
			echo '<p class="bg-danger">'.schars( _('verif_solde_erreur_part_1') ).' ('.(float)schars($nb_jours).') '.schars( _('verif_solde_erreur_part_2') ).' ('.(float)schars($sql_solde_user).') '.schars( _('verif_solde_erreur_part_3') ).' ('.(float)schars($sql_solde_user_a_valider).')</p>'."\n";
			$verif = FALSE;
		}
	}
	return $verif;
}


// renvoit le type d'absence (conges ou absence) d'une absence
function get_type_abs($_type_abs_id,  $DEBUG=FALSE)
{

	$sql_abs='SELECT ta_type FROM conges_type_absence WHERE ta_id=\''.SQL::quote($_type_abs_id).'\'';
	$ReqLog_abs = SQL::query($sql_abs);

	if($resultat_abs = $ReqLog_abs->fetch_array())
	return $resultat_abs["ta_type"];
	else
	return "" ;
}
