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

if (file_exists(CONFIG_PATH .'config_ldap.php'))
	include CONFIG_PATH .'config_ldap.php';


include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include INCLUDE_PATH .'session.php';
include ROOT_PATH .'fonctions_calcul.php';


$DEBUG=FALSE;
// $DEBUG=TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);

/*** initialisation des variables ***/
/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF=$_SERVER['PHP_SELF'];
// GET / POST
$choix_action 				= getpost_variable('choix_action');
$year						= getpost_variable('year', 0);
$groupe_id					= getpost_variable('groupe_id');
$id_type_conges				= getpost_variable('id_type_conges');
$new_date_debut				= getpost_variable('new_date_debut'); // valeur par dédaut = aujourd'hui
$new_date_fin  				= getpost_variable('new_date_fin');   // valeur par dédaut = aujourd'hui
$fermeture_id  				= getpost_variable('fermeture_id', 0);
$fermeture_date_debut		= getpost_variable('fermeture_date_debut');
$fermeture_date_fin			= getpost_variable('fermeture_date_fin');
$code_erreur				= getpost_variable('code_erreur', 0);
	
// si les dates de début ou de fin ne sont pas passé par get/post alors date du jours.
if($new_date_debut=="")
{
	if($year==0)
		$new_date_debut=date("d/m/Y") ;
	else
		$new_date_debut=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
}
if($new_date_fin=="")
{
	if($year==0)
		$new_date_fin=date("d/m/Y") ;
	else
		$new_date_fin=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
}

if($year ==0)
	$year= date("Y");

/*************************************/

//debugage
if( $DEBUG ) { echo "choix_action = $choix_action // year = $year // groupe_id = $groupe_id<br>\n"; }
if( $DEBUG ) { echo "new_date_debut = $new_date_debut // new_date_fin = $new_date_fin<br>\n"; }
if( $DEBUG ) { echo "fermeture_id = $fermeture_id // fermeture_date_debut = $fermeture_date_debut // fermeture_date_fin = $fermeture_date_fin<br>\n"; }

/***********************************/
/*  VERIF DES DATES RECUES   */
$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
$timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
$date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
$timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
$date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
$timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

if( $DEBUG ) { echo "timestamp_date_debut = $timestamp_date_debut // timestamp_date_fin = $timestamp_date_fin // timestamp_today = $timestamp_today<br>\n"; }


/*********************************/
/*   COMPOSITION DES ONGLETS...  */
/*********************************/

$onglet = getpost_variable('onglet');

if(!$onglet)
	$onglet = 'saisie';

$onglets = array();
$onglets['saisie'] = _('admin_jours_fermeture_titre') . " " . "<span class=\"current-year\">$year</span>";
$onglets['calendar'] = 'Calendrier des fermetures' . " " . "<span class=\"current-year\">$year</span>";
$onglets['year_nav'] = NULL;

//initialisation de l'action par défaut : saisie_dates pour tous, saisie_groupe en cas de gestion et fermeture par groupe autorisée
if($choix_action=="")
{
	// si pas de gestion par groupe
	if($_SESSION['config']['gestion_groupes']==FALSE)
		 $choix_action="saisie_dates";
	// si gestion par groupe et fermeture_par_groupe
	elseif(($_SESSION['config']['fermeture_par_groupe']) && ($groupe_id=="") )
		 $choix_action="saisie_groupe";
	else
		 $choix_action="saisie_dates";
}

/*********************************/
/*   COMPOSITION DU HEADER...    */
/*********************************/
	
$add_css = '<style>#onglet_menu .onglet{ width: '. (str_replace(',', '.', 100 / count($onglets) )).'% ;}</style>';
	
/***********************************/
// AFFICHAGE DE LA PAGE
header_menu('admin', NULL, $add_css);


/*********************************/
/*   AFFICHAGE DES ONGLETS...  */
/*********************************/
echo '<div id="onglet_menu">';
foreach($onglets as $key => $title) 
{
	if($key == 'year_nav') 
	{
		// navigation 
		$prev_link = "$PHP_SELF?session=$session&onglet=$onglet&year=". ($year - 1) . "&groupe_id=$groupe_id";
		$next_link = "$PHP_SELF?session=$session&onglet=$onglet&year=". ($year + 1) . "&groupe_id=$groupe_id";
		echo "<div class=\"onglet calendar-nav\">\n";
		echo "<ul>\n";
		echo "<li><a href=\"$prev_link\" class=\"calendar-prev\"><i class=\"fa fa-chevron-left\"></i><span>année précédente</span></a></li>\n";
		echo "<li class=\"current-year\">$year</li>\n";
		echo "<li><a href=\"$next_link\" class=\"calendar-next\"><i class=\"fa fa-chevron-right\"></i><span>année suivante</span></a></li>\n";
		echo "</ul>\n";
		echo "</div>\n";
	}
	else
	{
		echo '<div class="onglet '.($onglet == $key ? ' active': '').'" ><a href="' . $PHP_SELF . '?session=' . $session . "&year=$year&onglet=" . $key . '">' . $title . '</a></div>';
	}
}
echo '</div>';


// vérifie si les jours fériés sont saisie pour l'année en cours
if( (verif_jours_feries_saisis($date_debut_yyyy_mm_dd, $DEBUG)==FALSE) && (verif_jours_feries_saisis($date_fin_yyyy_mm_dd, $DEBUG)==FALSE) ) {
		$code_erreur=1 ;  // code erreur : jour feriés non saisis
		$onglet="calendar";
}

//initialisation de l'action demandée : saisie_dates, commit_new_fermeture pour enregistrer une fermeture, annul_fermeture pour confirmer une annulation, commit_annul_fermeture pour annuler une fermeture

//en cas de confirmation d'une fermeture :
if($choix_action == "commit_new_fermeture")
{
	// on verifie que $new_date_debut est anterieure a $new_date_fin
	if($timestamp_date_debut > $timestamp_date_fin)
	{
		$code_erreur=2 ;  // code erreur : $new_date_debut est posterieure a $new_date_fin
		$choix_action="saisie_dates";
	}
	// on verifie que ce ne sont pas des dates passées
	elseif($timestamp_date_debut < $timestamp_today)
	{
		$code_erreur=3 ;  // code erreur : saisie de date passée
		$choix_action="saisie_dates";
	}
	// on ne verifie QUE si date_debut ou date_fin sont !=  d'aujourd'hui
	// (car aujourd'hui est la valeur par dédaut des dates, et on ne peut saisir aujourd'hui puisque c'est fermé !)
	elseif( ($timestamp_date_debut==$timestamp_today) || ($timestamp_date_fin==$timestamp_today) )
	{
		$code_erreur=4 ;  // code erreur : saisie de aujourd'hui
		$choix_action="saisie_dates";
	}
	else
	{
		// fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
		$tab_periode_calcul = make_tab_demi_jours_periode($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, "am", "pm", $DEBUG);
		// on verifie si la periode saisie ne chevauche pas une periode existante
		if(verif_periode_chevauche_periode_groupe($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, '', $tab_periode_calcul, $groupe_id, $DEBUG) )
		{
			$code_erreur=5 ;  // code erreur : fermeture chevauche une periode deja saisie
			$choix_action="saisie_dates";
		}
	}
}


if($onglet == 'calendar')
{

	// les jours fériés de l'annee de la periode saisie ne sont pas enregistrés
	if($code_erreur==1)
		echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_annee_non_saisie') . "</div>\n";

       	/************************************************/
	// CALENDRIER DES FERMETURES
	affiche_calendrier_fermeture($year, $DEBUG);
}
elseif($choix_action=="saisie_dates")
{
	if($groupe_id=="") // choix du groupe n'a pas été fait ($_SESSION['config']['fermeture_par_groupe']==FALSE)
		$groupe_id=0;

	// $new_date_debut est anterieure a $new_date_fin
	if($code_erreur==2)
		echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_dates_incompatibles') . "</div>\n";

	// ce ne sont des dates passées
	if($code_erreur==3)
		echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_date_passee_error') . "</div>\n";

	// fermeture le jour même impossible
	if($code_erreur==4)
		echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_fermeture_aujourd_hui') . "</div>\n";

	// la periode saisie chevauche une periode existante
	if($code_erreur==5)
		echo "<div class=\"alert alert-danger\">" . _('admin_jours_fermeture_chevauche_periode') . "</div>\n";

	echo "<div class=\"wrapper\">";
	echo '<a href="' . ROOT_PATH . "admin/admin_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode admin</a>\n";
	if($onglet == 'saisie') 
        	saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur, $DEBUG);
}
elseif($choix_action=="saisie_groupe") 
{
	echo '<div class="wrapper">';
	echo '<a href="' . ROOT_PATH . "admin/admin_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode admin</a>\n";
       	saisie_groupe_fermeture($DEBUG);
        echo '</div>';
}
elseif($choix_action=="commit_new_fermeture") 
{
	echo $title;
	commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges, $DEBUG);
}
elseif($choix_action=="annul_fermeture") 
{
	echo $title;
	confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG);
}
elseif($choix_action=="commit_annul_fermeture") 
{
	echo $title;
	commit_annul_fermeture($fermeture_id, $groupe_id, $DEBUG);
}

bottom();




/***************************************************************/
/**********  FONCTIONS  ****************************************/

function saisie_groupe_fermeture( $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();


	echo "<h3>fermeture pour tous ou pour un groupe ?</h3>\n";
	echo '<div class="row">';
		echo '<div class="col-md-6">';
			/********************/
			/* Choix Tous       */
			/********************/

			// AFFICHAGE TABLEAU
			echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
				echo "<input type=\"hidden\" name=\"groupe_id\" value=\"0\">\n";
				echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
				echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('admin_jours_fermeture_fermeture_pour_tous') ." !\">  \n";
			echo "</form>\n" ;
		echo '</div>';
		echo '<div class="col-md-6">';
			/********************/
			/* Choix Groupe     */
			/********************/
			// Récuperation des informations :
			$sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

			// AFFICHAGE TABLEAU

			echo "<form action=\"$PHP_SELF?session=$session\" class=\"form-inline\" method=\"POST\">\n" ;
				echo '<div class="form-group" style="margin-right: 10px;">';
					$ReqLog_gr = SQL::query($sql_gr);
					echo "<select  class=\"form-control\" name=\"groupe_id\">";
					while ($resultat_gr = $ReqLog_gr->fetch_array())
					{
						$sql_gid=$resultat_gr["g_gid"] ;
						$sql_group=$resultat_gr["g_groupename"] ;
						$sql_comment=$resultat_gr["g_comment"] ;

						echo "<option value=\"$sql_gid\">$sql_group";
					}
					echo "</select>";
					echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
				echo '</div>';
				echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('admin_jours_fermeture_fermeture_par_groupe') ."\">  \n";
			echo "</form>\n" ;
		echo '</div>';

	/************************************************/
	// HISTORIQUE DES FERMETURES

	$tab_periodes_fermeture = array();
	get_tableau_periodes_fermeture($tab_periodes_fermeture, $DEBUG);
	if(count($tab_periodes_fermeture)!=0)
	{
		echo "<table class=\"table\">\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo "<th colspan=\"2\">Fermetures</th>\n";
		echo "</tr>\n";
		echo "</thead>\n";
		foreach($tab_periodes_fermeture as $tab_periode)
		{
			$date_affiche_1=eng_date_to_fr($tab_periode['date_deb']);
			$date_affiche_2=eng_date_to_fr($tab_periode['date_fin']);
			$fermeture_id =($tab_periode['fermeture_id']);
			$groupe_id =($tab_periode['groupe_id']);
			$groupe_name =($tab_periode['groupe_name']);
			
			if($groupe_id==0)
				$groupe_name = 'Tous';
			else
				$groupe_name = $groupe_name;

			echo "<tr>\n";
			echo "<td>\n";
			echo  _('divers_du') ." <b>$date_affiche_1</b> ". _('divers_au') ." <b>$date_affiche_2</b>  (id $fermeture_id)</b>  ".$groupe_name."\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "<a href=\"$PHP_SELF?session=$session&choix_action=annul_fermeture&fermeture_id=$fermeture_id&groupe_id=$groupe_id&fermeture_date_debut=$date_affiche_1&fermeture_date_fin=$date_affiche_2\">". _('admin_annuler_fermeture') ."</a>\n";
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}


	echo '</div>';
	echo "<hr>\n" ;
	echo "<a class=\"btn\" href=\"/admin/admin_index.php?session=$session\">". _('form_cancel') ."</a>\n";
}


function saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
	$timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
	$date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
	$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
	$timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
	$date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
	$timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

	// on construit le tableau de l'année considérée
	$tab_year=array();
	get_tableau_jour_fermeture($year, $tab_year,  $groupe_id,  $DEBUG);
	if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

	echo "<form id=\"form-fermeture\" class=\"form-inline\" role=\"form\" action=\"$PHP_SELF?session=$session&year=$year\" method=\"POST\">\n";
  	echo "<div class=\"form-group\">\n";
	echo "<label for=\"new_date_debut\">" . _('divers_date_debut') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_date_debut\" value=\"$new_date_debut\">\n";
  	echo "</div>";
  	echo "<div class=\"form-group\">\n";
  	echo "<label for=\"new date_fin\">" . _('divers_date_fin') . "</label><input type=\"text\" class=\"form-control date\" name=\"new_date_fin\" value=\"$new_date_fin\">\n";
  	echo "</div>";
  	echo "<div class=\"form-group\">\n";
	echo "<label for=\"id_type_conges\">" . _('admin_jours_fermeture_affect_type_conges') . "</label>\n";
	echo "<select name=\"id_type_conges\" class=\"form-control\">\n";
   	echo affiche_select_conges_id($DEBUG);
   	echo "</select>\n";
   	echo "</div>\n";
   	echo "<hr/>\n";
 	echo "<input type=\"hidden\" name=\"groupe_id\" value=\"$groupe_id\">\n";
	echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_new_fermeture\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "</form>\n";
}

//renvoi un tableau des jours de fermeture
function get_tableau_jour_fermeture($year, &$tab_year,  $groupe_id,  $DEBUG=FALSE)
{
	$sql_select = " SELECT jf_date FROM conges_jours_fermeture WHERE DATE_FORMAT(jf_date, '%Y-%m-%d') LIKE '$year%'  ";
	// on recup les fermeture du groupe + les fermetures de tous !
	if($groupe_id==0)
		$sql_select = $sql_select."AND jf_gid = 0";
	else
		$sql_select = $sql_select."AND  (jf_gid = $groupe_id OR jf_gid =0 ) ";
	$res_select = SQL::query($sql_select);
	$num_select =$res_select->num_rows;

	if($num_select!=0)
	{
	        while($result_select = $res_select->fetch_array())
		{
		        $tab_year[]=$result_select["jf_date"];
		}
	}
}

// Affichage d'un SELECT de formulaire pour choix d'un type d'absence
function affiche_select_conges_id($DEBUG=FALSE)
{
	$tab_conges=recup_tableau_types_conges( $DEBUG);
	$tab_conges_except=recup_tableau_types_conges_exceptionnels( $DEBUG);

	foreach($tab_conges as $id => $libelle)
	{
		if($libelle == 1)
			echo "<option value=\"$id\" selected>$libelle</option>\n";
		else
			echo "<option value=\"$id\">$libelle</option>\n";
	}
	if(count($tab_conges_except)!=0)
	{
		foreach($tab_conges_except as $id => $libelle)
		{
			if($libelle == 1)
				echo "<option value=\"$id\" selected>$libelle</option>\n";
			else
				echo "<option value=\"$id\">$libelle</option>\n";
		}
	}
}



// retourne un tableau des periodes de fermeture (pour un groupe donné (gid=0 pour tout le monde))
function get_tableau_periodes_fermeture(&$tab_periodes_fermeture, $DEBUG=FALSE)
{
	$req_1="SELECT DISTINCT conges_periode.p_date_deb, conges_periode.p_date_fin, conges_periode.p_fermeture_id, conges_jours_fermeture.jf_gid, conges_groupe.g_groupename FROM conges_periode, conges_jours_fermeture LEFT JOIN conges_groupe ON conges_jours_fermeture.jf_gid=conges_groupe.g_gid WHERE conges_periode.p_fermeture_id = conges_jours_fermeture.jf_id AND conges_periode.p_etat='ok' ORDER BY conges_periode.p_date_deb DESC  ";
	$res_1 = SQL::query($req_1);

	$num_select = $res_1->num_rows;
	if($num_select!=0)
	{
		while($result_select = $res_1->fetch_array())
		{
			$tab_periode=array();
			$tab_periode['date_deb']=$result_select["p_date_deb"];
			$tab_periode['date_fin']=$result_select["p_date_fin"];
			$tab_periode['fermeture_id']=$result_select["p_fermeture_id"];
			$tab_periode['groupe_id']=$result_select["jf_gid"];
			$tab_periode['groupe_name']=$result_select["g_groupename"];
			$tab_periodes_fermeture[]=$tab_periode;
		}
	}
}

//function confirm_saisie_fermeture($tab_checkbox_j_ferme, $year_calendrier_saisie, $groupe_id, $DEBUG=FALSE)
function confirm_annul_fermeture($fermeture_id, $groupe_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	echo '<div class="wrapper">';
	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo  _('divers_fermeture_du') ."  <b>$fermeture_date_debut</b> ". _('divers_au') ." <b>$fermeture_date_fin</b>. \n";
	echo "<b>". _('admin_annul_fermeture_confirm') .".</b><br>\n";
	echo "<input type=\"hidden\" name=\"fermeture_id\" value=\"$fermeture_id\">\n";
	echo "<input type=\"hidden\" name=\"fermeture_date_debut\" value=\"$fermeture_date_debut\">\n";
	echo "<input type=\"hidden\" name=\"fermeture_date_fin\" value=\"$fermeture_date_fin\">\n";
	echo "<input type=\"hidden\" name=\"groupe_id\" value=\"$groupe_id\">\n";
	echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_annul_fermeture\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_continuer') ."\">\n";
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>";
	echo "</form>\n";
	echo "</div>\n";
}

function commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges,  $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();


	// on transforme les formats des dates
	$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
	$date_debut=$tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0];
	$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
	$date_fin=$tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0];
	if( $DEBUG ) { echo "date_debut = $date_debut  // date_fin = $date_fin<br>\n"; }


	/*****************************/
	// on construit le tableau des users affectés par les fermetures saisies :
	if($groupe_id==0)  // fermeture pour tous !
		$list_users = get_list_all_users( $DEBUG);
	else
		$list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

	$tab_users = explode(",", $list_users);
	if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

//******************************
// !!!!
	// type d'absence à modifier ....
//	$id_type_conges = 1 ; //"cp" : conges payes

	//calcul de l'ID de de la fermeture (en fait l'ID de la saisie de fermeture)
	$new_fermeture_id=get_last_fermeture_id( $DEBUG) + 1;

	/***********************************************/
	/** enregistrement des jours de fermetures   **/
	$tab_fermeture=array();
	for($current_date=$date_debut; $current_date <= $date_fin; $current_date=jour_suivant($current_date))
	{
		$tab_fermeture[] = $current_date;
	}
	if( $DEBUG ) { echo "tab_fermeture =<br>\n"; print_r($tab_fermeture) ; echo "<br>\n"; }
	// on insere les nouvelles dates saisies dans conges_jours_fermeture
	$result=insert_year_fermeture($new_fermeture_id, $tab_fermeture, $groupe_id,  $DEBUG);

	$opt_debut='am';
	$opt_fin='pm';

	/*********************************************************/
	/** insersion des jours de fermetures pour chaque user  **/
	foreach($tab_users as $current_login)
	{
	    $current_login = trim($current_login);
		// on enleve les quotes qui ont été ajoutées lors de la creation de la liste
		$current_login = trim($current_login, "\'");

		// on compte le nb de jour à enlever au user (par periode et au total)
		// on ne met à jour la table conges_periode
		$nb_jours = 0;
		$comment="" ;

		// $nb_jours = compter($current_login, $date_debut, $date_fin, $opt_debut, $opt_fin, $comment,  $DEBUG);
		$nb_jours = compter($current_login, "", $date_debut, $date_fin, $opt_debut, $opt_fin, $comment, $DEBUG);

		if ($DEBUG) echo "<br>user_login : " . $current_login . " nbjours : " . $nb_jours . "<br>\n";

		// on ne met à jour la table conges_periode .
		$commentaire =  _('divers_fermeture') ;
		$etat = "ok" ;
		$num_periode = insert_dans_periode($current_login, $date_debut, $opt_debut, $date_fin, $opt_fin, $nb_jours, $commentaire, $id_type_conges, $etat, $new_fermeture_id, $DEBUG) ;

		// mise à jour du solde de jours de conges pour l'utilisateur $current_login
		if ($nb_jours != 0) {
			soustrait_solde_et_reliquat_user($current_login, "", $nb_jours, $id_type_conges, $date_debut, $opt_debut, $date_fin, $opt_fin, $DEBUG);

		}
	}

	// on recharge les jours fermés dans les variables de session
	init_tab_jours_fermeture($_SESSION['userlogin'],  $DEBUG);

	echo '<div class="wrapper">';

	if($result)
		echo "<br>". _('form_modif_ok') ."<br><br>\n";
	else
		echo "<br>". _('form_modif_not_ok') ." !<br><br>\n";

	$comment_log = "saisie des jours de fermeture de $date_debut a $date_fin" ;
	log_action(0, "", "", $comment_log,  $DEBUG);
	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
	echo "</form>\n";
	echo '</div>';
}

function commit_annul_fermeture($fermeture_id, $groupe_id,  $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if( $DEBUG ) { echo "fermeture_id = $fermeture_id <br>\n"; }


	/*****************************/
	// on construit le tableau des users affectés par les fermetures saisies :
	if($groupe_id==0)  // fermeture pour tous !
		$list_users = get_list_all_users( $DEBUG);
	else
		$list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

	$tab_users = explode(",", $list_users);
	if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

	/***********************************************/
	/** suppression des jours de fermetures   **/
	// on suprimme les dates de cette fermeture dans conges_jours_fermeture
	$result=delete_year_fermeture($fermeture_id, $groupe_id,  $DEBUG);


	// on va traiter user par user pour annuler sa periode de conges correspondant et lui re-crediter son solde
	foreach($tab_users as $current_login)
	{
	    $current_login = trim($current_login);
		// on enleve les quotes qui ont été ajoutées lors de la creation de la liste
		$current_login = trim($current_login, "\'");

		// on recupère les infos de la periode ....
		$sql_credit='SELECT p_num, p_nb_jours, p_type FROM conges_periode WHERE p_login=\''.SQL::quote($current_login).'\' AND p_fermeture_id=\'' . SQL::quote($fermeture_id) .'\' AND p_etat=\'ok\'';
		$result_credit = SQL::query($sql_credit);
		$row_credit = $result_credit->fetch_array();
		$sql_num_periode=$row_credit['p_num'];
		$sql_nb_jours_a_crediter=$row_credit['p_nb_jours'];
		$sql_type_abs=$row_credit['p_type'];


		// on met à jour la table conges_periode .
		$etat = "annul" ;
	 	$sql1 = 'UPDATE conges_periode SET p_etat = \''.SQL::quote($etat).'\' WHERE p_num='.SQL::quote($sql_num_periode) ;
	    $ReqLog = SQL::query($sql1);

		// mise à jour du solde de jours de conges pour l'utilisateur $current_login
		if ($sql_nb_jours_a_crediter != 0)
		{
		        $sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde + '.SQL::quote($sql_nb_jours_a_crediter).' WHERE su_login=\''.SQL::quote($current_login).'\' AND su_abs_id = '.SQL::quote($sql_type_abs) ;
		        $ReqLog = SQL::query($sql1);
		}
	}

	echo '<div class="wrapper">';
	if($result)
		echo "<br>". _('form_modif_ok') ."<br><br>\n";
	else
		echo "<br>". _('form_modif_not_ok') ." !<br><br>\n";

	// on enregistre cette action dan les logs
	if($groupe_id==0)  // fermeture pour tous !
		$comment_log = "annulation fermeture $fermeture_id (pour tous) " ;
	else
		$comment_log = "annulation fermeture $fermeture_id (pour le groupe $groupe_id)" ;
	log_action(0, "", "", $comment_log,  $DEBUG);

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
	echo "</form>\n";
	echo '</div>';

}

// verifie si la periode donnee chevauche une periode de conges d'un des user du groupe ..
// retourne TRUE si chevauchement et FALSE sinon !
function verif_periode_chevauche_periode_groupe($date_debut, $date_fin, $num_current_periode='', $tab_periode_calcul, $groupe_id,  $DEBUG=FALSE)
{
	/*****************************/
	// on construit le tableau des users affectés par les fermetures saisies :
	if($groupe_id==0)  // fermeture pour tous !
		$list_users = get_list_all_users( $DEBUG);
	else
		$list_users = get_list_users_du_groupe($groupe_id,  $DEBUG);

	$tab_users = explode(",", $list_users);
	if( $DEBUG ) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

	foreach($tab_users as $current_login)
	{
		$current_login = trim($current_login);
		// on enleve les quotes qui ont été ajoutées lors de la creation de la liste
		$current_login = trim($current_login, "\'");
		$comment="";
		if(verif_periode_chevauche_periode_user($date_debut, $date_fin, $current_login, $num_current_periode, $tab_periode_calcul, $comment, $DEBUG))
			return TRUE;
	}
}

// recup l'id de la derniere fermeture (le max)
function get_last_fermeture_id( $DEBUG=FALSE)
{
	$req_1="SELECT MAX(jf_id) FROM conges_jours_fermeture ";
	$res_1 = SQL::query($req_1);
	$row_1 = $res_1->fetch_array();
	if(!$row_1)
		return 0;     // si la table est vide, on renvoit 0
	else
		return $row_1[0];
}

// supprime une fermeture
function delete_year_fermeture($fermeture_id, $groupe_id,  $DEBUG=FALSE)
{

	$sql_delete="DELETE FROM conges_jours_fermeture WHERE jf_id = '$fermeture_id' AND jf_gid= '$groupe_id' ;";
	$result = SQL::query($sql_delete);
	return TRUE;
}

//insertion des nouvelles dates de fermeture
function insert_year_fermeture($fermeture_id, $tab_j_ferme, $groupe_id,  $DEBUG=FALSE)
{
	$sql_insert="";
	foreach($tab_j_ferme as $jf_date )
	{
		$sql_insert="INSERT INTO conges_jours_fermeture (jf_id, jf_gid, jf_date) VALUES ($fermeture_id, $groupe_id, '$jf_date') ;";
		$result_insert = SQL::query($sql_insert);
	}
	return TRUE;
}

//calendrier des fermeture
function affiche_calendrier_fermeture($year, $groupe_id = 0, $DEBUG=FALSE) {

	// on construit le tableau de l'année considérée
	$tab_year=array();
	get_tableau_jour_fermeture($year, $tab_year,  $groupe_id,  $DEBUG);

	echo "<div class=\"calendar calendar-year\">\n";
	$months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

	foreach ($months as $month) {
		echo "<div class=\"month\">\n";
		echo "<div class=\"wrapper\">\n";
		echo affiche_calendrier_fermeture_mois($year, $month, $tab_year);
		echo "</div>\n";
		echo "</div>";
	}
	echo "</div>";

}


function  affiche_calendrier_fermeture_mois($year, $mois, $tab_year, $DEBUG=FALSE)
{
	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo "<table>\n";
	/* affichage  2 premieres lignes */
	echo "	<thead>\n";
	echo "	<tr><th colspan=7 class=\"titre\"> $mois_name $year </th></tr>\n" ;
	echo "	<tr>\n";
	echo "		<th class=\"cal-saisie2\">". _('lundi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('mardi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('mercredi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('jeudi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('vendredi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('samedi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('dimanche_1c') ."</th>\n";
	echo "	</tr>\n" ;
	echo "	</thead>\n" ;

	/* affichage ligne 1 du mois*/
	echo "<tr>\n";
	// affichage des cellules vides jusqu'au 1 du mois ...
	for($i=1; $i<$first_jour_mois_rang; $i++)
	{
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td class=\"month-out cal-saisie2\">&nbsp;</td>";
	}
	// affichage des cellules du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td class=\"cal-saisie2 month-out\">&nbsp;</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 6 du mois (derniere ligne)*/
	echo "<tr>\n";
	for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)))
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td class=\"month-out cal-saisie2\">&nbsp;</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}

?>
