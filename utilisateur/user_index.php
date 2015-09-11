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

define('ROOT_PATH', '../');
require ROOT_PATH . 'define.php';

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include INCLUDE_PATH .'session.php';
include ROOT_PATH .'fonctions_calcul.php';
// include  INCLUDE_PATH .'sql.class.php';

//include$_SESSION['config']['lang_file'] ;

$DEBUG=FALSE;
//$DEBUG=TRUE;


if($_SESSION['config']['where_to_find_user_email']=="ldap"){ include CONFIG_PATH .'config_ldap.php';}



	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$onglet = getpost_variable('onglet');
	
	
	/*********************************/
	/*   COMPOSITION DES ONGLETS...  */
	/*********************************/

	$onglets = array();
	
	if( $_SESSION['config']['user_saisie_demande'] || $_SESSION['config']['user_saisie_mission'] )
		$onglets['nouvelle_absence'] = _('divers_nouvelle_absence');

	if( $_SESSION['config']['user_echange_rtt'] )
		$onglets['echange_jour_absence'] = _('user_onglet_echange_abs');
		
	if( $_SESSION['config']['user_saisie_demande'] )
		$onglets['demandes_en_cours'] = _('user_onglet_demandes');
	
	$onglets['historique_conges'] = _('user_onglet_historique_conges');
	$onglets['historique_autres_absences'] = _('user_onglet_historique_abs');

	if( $_SESSION['config']['auth'] && $_SESSION['config']['user_ch_passwd'] )
		$onglets['changer_mot_de_passe'] = _('user_onglet_change_passwd');
	
	if ( !isset($onglets[ $onglet ]) && !in_array($onglet, array('modif_demande','suppr_demande')))
		$onglet = 'nouvelle_absence';
	
	/*********************************/
	/*   COMPOSITION DU HEADER...    */
	/*********************************/
	
	$add_css = '<style>#onglet_menu .onglet{ width: '. (str_replace(',', '.', 100 / count($onglets) )).'% ;}</style>';
	header_menu('user','',$add_css);
	
	
	/*********************************/
	/*   AFFICHAGE DES ONGLETS...  */
	/*********************************/
	
	echo '<div id="onglet_menu">';
	foreach($onglets as $key => $title) {
		echo '<div class="onglet '.($onglet == $key ? ' active': '').'" >
			<a href="'.$PHP_SELF.'?session='.$session.'&onglet='.$key.'">'. $title .'</a>
		</div>';
	}
	echo '</div>';

	
	/*********************************/
	/*   AFFICHAGE DU RECAP ...    */
	/*********************************/

	echo "<div class=\"wrapper\">\n";
	echo '<h3>'._('tableau_recap').'</h3>';
	affiche_tableau_bilan_conges_user( $_SESSION['userlogin'] );
	echo "<hr/>\n";
	echo "</div>\n";

	/*********************************/
	/*   AFFICHAGE DE L'ONGLET ...    */
	/*********************************/
	
	echo '<div class="'.$onglet.' wrapper">';
		include ROOT_PATH . 'utilisateur/user_'.$onglet.'.php';
	echo '</div>';
	
	/*********************************/
	/*   AFFICHAGE DU BOTTOM ...   */
	/*********************************/
	
	bottom();
	exit;
	
