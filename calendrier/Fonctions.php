<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
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
namespace calendrier;

/**
* Regroupement des fonctions liées au calendrier
*/
class Fonctions
{

    // Récupération des users à afficher:
    // renvoit un tableau de tableau
    public static function recup_tableau_des_users_a_afficher($select_groupe,  $DEBUG=FALSE)
    {

    	// si acces sans authentification est permis : alors droit de voir tout le monde
    	// sinon, on verifie si le user a le droite de voir tout le monde
    	if( ($_SESSION['config']['consult_calendrier_sans_auth']) && (!isset($_SESSION['userlogin'])) )
    	{
    		//si gestion des groupes et un groupe a ete selectionne
    		if( ($_SESSION['config']['gestion_groupes']) && ($select_groupe!=0) )
    		{
    			$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    			$sql1 = $sql1." WHERE u_login!='conges' AND u_login!='admin' ";

    			//recup de la liste des users des groupes dont le user est membre
    			$list_users=get_list_users_du_groupe($select_groupe,  $DEBUG);
    			if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
    				$sql1 = $sql1." AND u_login IN ($list_users) ORDER BY u_nom, u_prenom ";
    		}
    		else // affiche tous les users
    		{
    			$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    			//$sql1 = $sql1." WHERE u_login!='conges' AND u_resp_login = 'conges' ORDER BY u_nom, u_prenom";
    			$sql1 = $sql1." WHERE u_login!='conges'  AND u_login!='admin' ORDER BY u_nom, u_prenom";
    		}
    	}
    	//sinon (authentification, le user est identifié)
    	else
    	{
    		//construction de la requete sql pour recupérer les users à afficher :

    		//si le user a le droit de voir tout le monde
    		$user_see_all_in_calendrier=get_user_see_all($_SESSION['userlogin']);
    		if($user_see_all_in_calendrier) // si le user a "u_see_all" à "Y" dans la table users : affiche tous les users
    		{
    			//si gestion des groupes et un groupe a ete selectionne
    			if( ($_SESSION['config']['gestion_groupes']) && ($select_groupe!=0) )
    			{
    				$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    				$sql1 = $sql1." WHERE u_login!='conges' AND u_login!='admin' ";

    				//recup de la liste des users des groupes dont le user est membre
    				$list_users=get_list_users_du_groupe($select_groupe,  $DEBUG);
    				if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
    				$sql1 = $sql1." AND u_login IN ($list_users) ";

    				$sql1 = $sql1." ORDER BY u_nom, u_prenom";
    			}
    			else
    			{
    				$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    				//$sql1 = $sql1." WHERE u_login!='conges' AND u_resp_login = 'conges' ORDER BY u_nom, u_prenom";
    				$sql1 = $sql1." WHERE u_login!='conges'  AND u_login!='admin' ORDER BY u_nom, u_prenom";
    			}
    		}
    		// sinon (le user n'a pas le droit de voir tout le monde)
    		else
    		{
    			//si gestion des groupes et un groupe a ete selectionne
    			if( ($_SESSION['config']['gestion_groupes']) && ($select_groupe!=0) )
    			{
    				$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    				$sql1 = $sql1." WHERE u_login!='conges' AND u_login!='admin' ";
    				$sql1 = $sql1.' AND ( u_login = "'. \includes\SQL::quote($_SESSION['userlogin']).'" ';

    				//recup de la liste des users des groupes dont le user est membre
    				$list_users=get_list_users_du_groupe($select_groupe,  $DEBUG);
    				if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
    					$sql1 = $sql1." OR u_login IN ($list_users) ";
    					$sql1 = $sql1." ) ";

    				$sql1 = $sql1." ORDER BY u_nom, u_prenom";
    			}
    			// si user n'est pas un responsable
    			else
    			{
    				if( !is_resp($_SESSION['userlogin']) )
    				{
    					$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    					$sql1 = $sql1." WHERE u_login!='conges' AND u_login!='admin' ";

    					//si affichage par groupe : on affiche les membres des groupes du user ($_SESSION['userlogin'])
    					if( ($_SESSION['config']['gestion_groupes']) && ($_SESSION['config']['affiche_groupe_in_calendrier']) )
    					{
    						//recup de la liste des users des groupes dont le user est membre
    						$list_users=get_list_users_des_groupes_du_user($_SESSION['userlogin']);
    						if($list_users!="")  //si la liste n'est pas vide ( serait le cas si n'est membre d'aucun groupe)
    						$sql1 = $sql1." AND u_login IN ($list_users) ";
    					}

    					$sql1 = $sql1." ORDER BY u_nom, u_prenom";
    				}
    				// si user est un responsable
    				else
    				{
    					$sql1 = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
    					$sql1 = $sql1." WHERE u_login!='conges' AND u_login!='admin' ";

    					if($_SESSION['userlogin']!="conges")
    					{
    						$sql1 = $sql1.' AND ( u_login = "'. \includes\SQL::quote($_SESSION['userlogin']).'" ';

    						//si affichage par groupe : on affiche les membres des groupes du user ($_SESSION['userlogin'])
    						if( ($_SESSION['config']['gestion_groupes']) && ($_SESSION['config']['affiche_groupe_in_calendrier']) )
    						{
    							//recup de la liste des users des groupes dont le user est membre
    							$list_users=get_list_users_des_groupes_du_user($_SESSION['userlogin']);
    							if($list_users!="")  //si la liste n'est pas vide ( serait le cas si n'est membre d'aucun groupe)
    								$sql1 = $sql1." OR u_login IN ($list_users) ";

    						}

    						//recup de la liste des users dont le user est responsable
    						$list_users_2=get_list_all_users_du_resp($_SESSION['userlogin'],  $DEBUG);
    						if($list_users_2!="")  //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
    							$sql1 = $sql1." OR u_login IN ($list_users_2) ";

    						if ($_SESSION['config']['double_validation_conges']) {
    							$list_groupes_3 = get_list_login_du_grand_resp($_SESSION['userlogin'] );
    							if (count($list_groupes_3) > 0) {
    								$list_groupes_3 = array_map("\includes\SQL::quote", $list_groupes_3);
    								$list_groupes_3 = '\'' . implode('\', \'', $list_groupes_3).'\'';

    								$sql1 = $sql1." OR u_login IN ( $list_groupes_3 ) ";
    							}
    						}


    						$sql1 = $sql1." ) ";
    					}

    					$sql1 = $sql1." ORDER BY u_nom, u_prenom";

    				}
    			}
    		}
    	}

    	$ReqLog = \includes\SQL::query($sql1);
    	$tab_all_users=array();
    	while ($resultat = $ReqLog->fetch_array())
    	{
    		$tab_user=array();
    		$tab_user['nom']=$resultat["u_nom"];
    		$tab_user['prenom']=$resultat["u_prenom"];
    		$tab_user['quotite']=$resultat["u_quotite"];
    		$sql_login=$resultat["u_login"];
    		$tab_all_users[$sql_login]= $tab_user;
    	}

    	return($tab_all_users);
    }

    // Affichage d'un SELECT de formulaire pour choix d'un groupe
    // affiche les groupes du user OU les groupes du resp (si user est resp) OU tous ls groupes (si option de config ok)
    public static function affiche_select_groupe($select_groupe, $selected, $printable, $year, $mois, $first_jour,  $group_names )
    {

    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();

    	// quelle liste de groupes recuperer ?
    	//if( ($_SESSION['config']['consult_calendrier_sans_auth']) && (!isset($_SESSION['userlogin'])) )
    	if( is_hr($_SESSION['userlogin']) )
    		$list_groupes=get_list_all_groupes( );
    	elseif($_SESSION['config']['calendrier_select_all_groups'])
    		$list_groupes=get_list_all_groupes(  );
    	elseif(is_resp($_SESSION['userlogin'] ))
    	{
    		// on propose la liste des groupes dont user est resp + groupes dont user est membre
    		$list_groupes_1=get_list_groupes_du_resp($_SESSION['userlogin'] );
    		$list_groupes_2=get_list_groupes_du_user($_SESSION['userlogin'] );
    		if ($list_groupes_1 == '' || $list_groupes_2 == '')
    			$list_groupes = $list_groupes_1.$list_groupes_2 ;
    		else
    			$list_groupes = $list_groupes_1.",".$list_groupes_2 ;

    		if ($_SESSION['config']['double_validation_conges']) {
    			$list_groupes_3 = get_list_groupes_du_grand_resp($_SESSION['userlogin'] );
    			if ($list_groupes == '' || $list_groupes_3 == '')
    				$list_groupes = $list_groupes.$list_groupes_3 ;
    			else
    				$list_groupes = $list_groupes.",".$list_groupes_3 ;
    		}
    	}
    	else
    		$list_groupes=get_list_groupes_du_user($_SESSION['userlogin'] );

    	echo "<form id=\"group-select-form\" class=\"form-inline\" action=\"$PHP_SELF?session=$session&printable=$printable&selected=$selected&year=$year&mois=$mois&first_jour=$first_jour\" method=\"POST\">\n";
    	if (trim($list_groupes) == '')
    		$tab_groupes=array();
    	else
    		$tab_groupes=array_unique(explode(",", $list_groupes));

    	echo "<div class=\"form-group\">\n";
    	echo "<label for=\"select_groupe\">" . _('calendrier_afficher_groupe') ."</label>\n";
    	echo "<select class=\"form-control\" name=\"select_groupe\">\n";

    	$tmp = false;
    	foreach($tab_groupes as $grp)
    	{
    		$grp=trim($grp);
    		if($grp == $select_groupe)
    		{
    			echo "<option value=\"$grp\" selected=\"selected\">".$group_names[$grp]."</option>\n";
    			$tmp = true;
    		}
    		else
    			echo "<option value=\"$grp\">".$group_names[$grp]."</option>\n";
    	}
    	//option pour retour a l'affichage normal ...
    	if ($tmp)
    		echo "<option value=\"0\">". _('divers_normal_maj_1') ."</option>\n";
    	else
    		echo "<option value=\"0\" selected=\"selected\">". _('divers_normal_maj_1') ."</option>\n";

    	echo "</select>\n";
    	echo "</div>\n";
    	echo "<input class=\"btn btn-default\" type=\"submit\" value=\"ok\">\n";
    	echo "</form>\n";
    }

    /**************************************************/
    /* recup des info de chaque jour pour tous les users et stockage dans 1 tableau de tableaux */
    /**************************************************/
    public static function recup_tableau_periodes($mois, $first_jour, $year,  $tab_logins = false)
    {

    	$tab_calendrier=array();  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
    						//il contient pour chaque clé : un tableau ($tab_jour) qui contient lui même des
    						// tableaux indexés contenant les infos des periode de conges dont ce jour fait partie
    						// ($tab_periode)


    	$timestamp_deb	= mktime (0,0,0,$mois, $first_jour, $year);
    	$timestamp_fin	= mktime (0,0,0,$mois +1 , $first_jour, $year);

    	$date_deb	= date("Y-m-d", $timestamp_deb );
    	$date_fin	= date("Y-m-d", $timestamp_fin );

    	$sql	= 'SELECT  p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_type, p_etat, p_fermeture_id, p_commentaire
    				FROM conges_periode
    				WHERE ( p_etat=\'ok\' OR  p_etat=\'demande\' OR  p_etat=\'valid\')
    					AND (p_date_fin >= "'. \includes\SQL::quote($date_deb).'" AND p_date_deb <= "'. \includes\SQL::quote($date_fin).'")
    					'.($tab_logins !== false ? 'AND p_login IN (\''.implode('\', \'', $tab_logins).'\')' : '' ).'
    				ORDER BY p_date_deb;';
    	$result = \includes\SQL::query($sql);
    	while($l = $result->fetch_array()) {

    		// on ne stoque les "demandes" que pour le user qui consulte (il ne voit pas celles des autres !)(suivant l'option de config)
    		if( $l['p_etat'] != 'demande' || $_SESSION['config']['affiche_demandes_dans_calendrier'] )
    			$tab_jour	= $l;
    		elseif( isset($_SESSION['userlogin']) && $l['p_login']==$_SESSION['userlogin'] )
    			$tab_jour	= $l;
    		else
    			continue;

    		$p_timestamp_deb = \DateTime::createFromFormat('Y-m-d', $l['p_date_deb']);
    		$p_timestamp_fin = \DateTime::createFromFormat('Y-m-d', $l['p_date_fin']);


    		$deb	= ( $timestamp_deb < $p_timestamp_deb->getTimestamp() ? $p_timestamp_deb : new \DateTime( '@'.$timestamp_deb) );
    		$fin	= ( $timestamp_fin > $p_timestamp_fin->getTimestamp() ? $p_timestamp_fin : new \DateTime( '@'.$timestamp_fin) );

    		$tmp	= $deb;
    		while ( $tmp <= $fin ){
    			$date_j = date('Y-m-d',$tmp->getTimestamp() );
    			if (!isset($tab_calendrier[$date_j]) || !is_array($tab_calendrier[$date_j]))
    				$tab_calendrier[$date_j] = array();
    			$tab_calendrier[$date_j][] = $tab_jour;
    			$tmp->add(new \DateInterval('P1D'));
    		}
    	}
    	return $tab_calendrier;
    }

    // renvoit conges , demande ou autre ....
    public static function get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id, $DEBUG=FALSE)
    {
    	if($sql_p_fermeture_id!="")
    		return "fermeture";
    	elseif ( $tab_type_absence[$sql_p_type] && $tab_type_absence[$sql_p_type]['type']=="absences")
    		return "autre";
    	elseif($sql_p_etat=='ok')
    		return "conges";
    	elseif( ($sql_p_etat=="demande") || ($sql_p_etat=="valid") )
    		return "demande";
    }

    // affichage de la légende explicative des abréviations
    public static function affiche_legende_type_absence($tab_type_absence, $DEBUG=FALSE)
    {
    	$session=session_id();

    //	echo "      <table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">\n" ;
    	echo "      <table cellpadding=\"1\" class=\"tablo-cal\">\n" ;
    	foreach($tab_type_absence as $id_abs => $tab)
    	{
    		echo "      <tr align=\"center\">\n" ;
    		echo "         <td class=\"cal-legende\"> ".$tab['short_libelle']." : </td>\n" ;
    		echo "         <td class=\"cal-legende\"> ".$tab['libelle']." </td>\n" ;
    		echo "      </tr>\n" ;
    	}
    	echo "      </table>\n" ;
    }

    // affichage de la légende des couleurs
    public static function affiche_legende($DEBUG=FALSE)
    {
    	$session=session_id();

    //	echo "      <table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">\n" ;
    	echo "      <table cellpadding=\"1\" class=\"tablo-cal\">\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
    	echo "         <td class=\"cal-legende\"> </td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['week_end_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_we') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['conges_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_conges') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['demande_conges_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_demande') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    //	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_part_time') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['absence_autre_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_abs') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      <tr align=\"center\">\n" ;
    	echo "         <td bgcolor=\"".$_SESSION['config']['fermeture_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
    	echo "         <td class=\"cal-legende\"> ". _('divers_fermeture') ."</td>\n" ;
    	echo "      </tr>\n" ;
    	echo "      </table>\n" ;
    }

    // affichage de la cellule correspondant au jour et au user considéré
    // et renvoit un tableau avec une key / une valeur : key = id type absence / valeur = nb jours pris le jour J
    public static function affiche_cellule_jour_user($sql_login, $j_timestamp, $year_select, $mois_select, $j, $second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence,  $DEBUG=FALSE)
    {

    	$session=session_id();

    	$return = array();

    	// info bulle
    	$j_date_fr=date_fr("d/m/Y", $j_timestamp);
    	$j_num_semaine=date_fr("W", $j_timestamp);
    	$info_bulle=" title=\"$sql_login - $j_date_fr\" ";


    	if($second_class=="weekend")
    	{
    		$class="cal-day_".$second_class ;
    		if($printable!=1)  // si version écran :
    			echo "<td class=\"$class\" $info_bulle>-</td>";
    		else
    			echo "<td class=\"$class\">-</td>";
    	}
    	else
    	{
    		$date_j=date("Y-m-d", $j_timestamp );

    		$class_am="travail_am";
    		$class_pm="travail_pm";
    		$text_am="-";
    		$text_pm="-";

    		$val_matin="";
    		$val_aprem="";
    		// recup des infos ARTT ou Temps Partiel :
    		// la fonction suivante change les valeurs de $val_matin $val_aprem ....
    		recup_infos_artt_du_jour_from_tab($sql_login, $j_timestamp, $val_matin, $val_aprem, $tab_rtt_echange, $tab_rtt_planifiees,  $DEBUG=FALSE);

    		//## AFICHAGE ##
    		if($val_matin=="Y")
    		{
    			$class_am="rtt_am";
    	//		$text_am="a";
    		}
    		if($val_aprem=="Y")
    		{
    			$class_pm = "rtt_pm";
    	//		$text_pm="a";
    		}



    		$text_bulle_type_abs="";

    		if( !(($val_matin=="Y")&&($val_aprem=="Y")) ) //si pas journée complète temps-partiel ou rtt, on regarde les conges)
    		{
    			// Récupération des conges du user
    			if (array_key_exists($date_j, $tab_calendrier))   //verif la clé du jour exite dans $tab_calendrier
    			{
    				$tab_day=$tab_calendrier["$date_j"];  // on recup le tableau ($tab_jour) de la date que l'on affiche
    				//print_r($tab_day);

    				$nb_resultat_periode = count($tab_day);  //
    				if($nb_resultat_periode>0)      // si on est dans une periode de conges
    				{
    					for ($i = 0; $i < $nb_resultat_periode; $i++)
    					{
    						// on regarde chaque periode l'une après l'autre
    						$tab_per=$tab_day[$i];  // on recup le tableau de la periode
    						if(in_array($sql_login, $tab_per))   // si la periode correspond au user que l'on est en train de traiter
    						{
    							//echo "tab_per =<br/>\n"; print_r($tab_per); echo "<br/>\n";

    							$sql_p_type=$tab_per["p_type"];
    							$sql_p_etat=$tab_per["p_etat"];
    							$sql_p_date_deb=$tab_per["p_date_deb"];
    							$sql_p_date_fin=$tab_per["p_date_fin"];
    							$sql_p_demi_jour_deb=$tab_per["p_demi_jour_deb"];
    							$sql_p_demi_jour_fin=$tab_per["p_demi_jour_fin"];
    							$sql_p_fermeture_id=$tab_per["p_fermeture_id"];

    							$sql_p_date_deb_fr=substr($sql_p_date_deb,8,2)."/".substr($sql_p_date_deb,5,2)."/".substr($sql_p_date_deb,0,4);
    							$sql_p_date_fin_fr=substr($sql_p_date_fin,8,2)."/".substr($sql_p_date_fin,5,2)."/".substr($sql_p_date_fin,0,4);

    							//si on est le premier jour ET le dernier jour de conges
    							if( ($sql_p_date_deb==$date_j) && ($sql_p_date_fin==$date_j) )
    							{
    								if($sql_p_demi_jour_deb=="am")
    								{
    									$class_am=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
    									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";

    									if (isset($return[ $tab_type_absence[$sql_p_type]['libelle'] ]))
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] += 0.5;
    									else
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 0.5;
    								}
    								if($sql_p_demi_jour_fin=="pm")
    								{
    									$class_pm=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
    									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";

    									if (isset($return[ $tab_type_absence[$sql_p_type]['libelle'] ]))
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] += 0.5;
    									else
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 0.5;
    								}
    							}
    							elseif($sql_p_date_deb==$date_j) //si on est le premier jour
    							{
    								if($sql_p_demi_jour_deb=="am")
    								{
    									$class_am=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
    									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
    									$class_pm=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
    									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";

    									$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 1;
    								}
    								else
    								{
    									$class_pm=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
    									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";


    									if (isset($return[ $tab_type_absence[$sql_p_type]['libelle'] ]))
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] += 0.5;
    									else
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 0.5;
    								}
    							}
    							elseif($sql_p_date_fin==$date_j) //si on est le dernier jour
    							{
    								if($sql_p_demi_jour_fin=="pm")
    								{
    									$class_am=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
    									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
    									$class_pm=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									$text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";

    									$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 1;
    								}
    								else
    								{
    									$class_am=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
    									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";

    									if (isset($return[ $tab_type_absence[$sql_p_type]['libelle'] ]))
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] += 0.5;
    									else
    										$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 0.5;
    								}
    							}
    							else // si on est ni le premier ni le dernier jour
    							{
    								$class_am=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
    								$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
    								$class_pm=\calendrier\Fonctions::get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
    								$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
    									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
    									        $text_bulle_type_abs='<div class="type-abscence">' . $tab_type_absence[$sql_p_type]['libelle'] ."</div>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";
    									else
    									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br/>$sql_p_date_deb_fr <i class=\"fa fa-long-arrow-right\"></i> $sql_p_date_fin_fr";


    								$return[ $tab_type_absence[$sql_p_type]['libelle'] ] = 1;
    							}
    						}
    					}
    				}
    			}
    		}

    		if(($text_am=="a")&&($text_pm=="a"))
    		{
    			$text_am="abs";
    			$text_pm="";
    		}

    		// on affiche qu'un seule fois le texte si c'est le même le matin et l'aprem :
    		if($text_am==$text_pm)
    			$text_pm="";
    		elseif(($text_am=="-") &&($text_pm!="") ) //on a un "-" le matin et qq chose l'aprem :on affiche que le texte de l'aprem
    			$text_am="";
    		elseif(($text_am!="") && ($text_pm=="-"))  //on a un qq chose le matin et un "-" l'aprem :on affiche que le texte du matin
    			$text_pm="";


    		$class="cal-day cal-day_".$second_class."_".$class_am."_".$class_pm ;


    		if($printable!=1)  // si version écran :
    		{
    			if( ($text_am=="-") && ($text_pm=="") )
    			{
    				echo "<td class=\"$class\"  $info_bulle>";
    				echo "	$text_am $text_pm ";
    			}
    			else
    			{
    				echo "<td class=\"$class\">";
    				echo "	$text_am $text_pm ";

    				// affiche l'info-bulle (affichée grace au javascript)
    				//$texte_info_bulle=" $j_date_fr / ". _('divers_semaine') ." $j_num_semaine <br/>$text_bulle_type_abs<br/>periode";
    				// $texte_info_bulle=" $j_date_fr <br/>$text_bulle_type_abs";
    				echo "	<div class=\"cal-tooltip\" id='$sql_login-$j_timestamp' name='$sql_login-$j_timestamp' >
    							<div class=\"pull-right current-date\">$j_date_fr</div>
    							<strong>$sql_login</strong>
    							<hr/>
    							<div>$text_bulle_type_abs</div>
    						</div> ";
    			}
    		}
    		else
    		{
    			echo "<td class=\"$class\" >";
    			echo "	$text_am $text_pm ";
    		}
    		echo "</td>";
    	}
    	return $return;
    }

    // AFFICHAGE  TABLEAU (CALENDRIER)
    public static function affichage_calendrier($year, $mois, $first_jour, $timestamp_today, $printable, $selected, $tab_type_absence, $select_groupe, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();
    		$nb_day = date('t', mktime(1,1,1,$mois,1,$year));

    		// recup du tableau des types de conges (seulement les conges)
    		$tab_type_cong=recup_tableau_types_conges($DEBUG);
    		if ($_SESSION['config']['gestion_conges_exceptionnels'])
    			$tab_type_cong_excep=recup_tableau_types_conges_exceptionnels($DEBUG);

    		/*****************************************/
    		/** Récupération des users à afficher:  **/

    		$tab_all_users	= \calendrier\Fonctions::recup_tableau_des_users_a_afficher($select_groupe,$DEBUG);


    		if( ($_SESSION['config']['gestion_groupes']) && ($select_groupe!=0) ) {
    			$tab_logins = array_keys( $tab_all_users );
    			$tab_logins = array_map("\includes\SQL::quote", $tab_logins);
    		}
    		else
    			$tab_logins = false;
    		/** FIN de Récupération des users à afficher:  **/
    		/************************************************/


    		/*************************/
    		/**  AFFICHAGE TABLEAU  **/

    		if($printable!=1)  // si version ecran :
    			echo "<table class=\"calendar table table-responsive table-bordered table-stripped\">\n";
    		else               // si version imprimable :
    			echo "<table>\n";

    		echo "<tr><th colspan=\"2\"></th><th colspan=\"$nb_day\">". _('divers_semaine') ."</th><th colspan=\"8\">Solde</th></tr>\n";

    		/*************************************/
    		// affichage premiere ligne (semaines)
    		echo "<tr align=\"center\">\n";

    		// affichage nom prenom quotité
    		$nb_colonnes=3;
    		echo "	<th rowspan=\"2\">Utilisateur</th>\n\n";
    		echo "	<th rowspan=\"2\">Quotité</th>";

    		// affichage des semaines
    		// ... du premier jour voulu à la fin du mois
    		for($j=$first_jour; checkdate($mois, $j, $year); $j++)
    		{
    			$j_timestamp=mktime (0,0,0,$mois, $j, $year);
    			$j_num_semaine=date_fr("W", $j_timestamp);
    			// attention date_fr("w", $j_timestamp) renvoit 0 pour dimanche !
    			if(date_fr("w", $j_timestamp)==0)
    				$j_num_jour_semaine=7;
    			else
    				$j_num_jour_semaine=date_fr("w", $j_timestamp);

    			//DEBUG : echo "<td class=\"cal-day\" >$j_num_jour_semaine / $j_num_semaine</td>";
    			if($j==$first_jour)
    			{
    					$colspan = 8 - $j_num_jour_semaine;

    				echo "<th class=\"cal-day-first\" colspan=\"$colspan\" >$j_num_semaine</th>\n";
    			}
    			else
    			{
    				$month_rest = $nb_day - $j;
    				$colspan = 7;
    				if($month_rest < 6)
    					$colspan = $month_rest + 1;
    				// on affiche que les lundi
    				if($j_num_jour_semaine==1) {
    					echo "<th class=\"cal-day\" colspan=\"$colspan\" >$j_num_semaine</th>\n";
    				}

    			}

    		}

    		// ... si le premier jour voulu n'etait pas le premier du mois, on va jusqu'à la meme date du mois suivant.
    		if($first_jour!=1)
    		{
    			for($j=1; $j<$first_jour; $j++)
    			{
    				if($mois==12)
    				{
    					$mois_select=1;
    					$year_select=$year+1;
    				}
    				else
    				{
    					$mois_select=$mois+1 ;
    					$year_select=$year;
    				}

    				$j_timestamp=mktime (0,0,0,$mois_select, $j, $year_select);
    				$j_num_jour_semaine=date_fr("w", $j_timestamp);

    				$j_num_semaine=date_fr("W", $j_timestamp);
    				// attention date_fr("w", $j_timestamp) renvoit 0 pour dimanche !
    				if(date_fr("w", $j_timestamp)==0)
    					$j_num_jour_semaine=7;
    				else
    					$j_num_jour_semaine=date_fr("w", $j_timestamp);

    				//DEBUG : echo "<td class=\"cal-day\" >$j_num_jour_semaine / $j_num_semaine</td>";
    				if($j==$first_jour)
    				{
    					$colspan=8-$j_num_jour_semaine;
    					echo "<td class=\"cal-day-first\" colspan=\"$colspan\" >$j_num_semaine</td>\n";
    				}
    				else
    				{
    					// on affiche que les lundi
    					if($j_num_jour_semaine==1) {
    						echo "<td class=\"cal-day\" colspan=\"7\" >$j_num_semaine</td>\n";
    					}
    				}
    			}
    		}



    		if( $_SESSION['config']['affiche_soldes_calendrier'] || is_resp($_SESSION['userlogin']) || is_hr($_SESSION['userlogin']) || is_admin($_SESSION['userlogin']) )
    		{
    			// affichage des libellé des conges
    			$abs_libelle = recup_tableau_tout_types_abs();

    			foreach($tab_type_cong as $id => $libelle)
    			{
    					echo "<th rowspan=\"2\">" . $abs_libelle[$id]['short_libelle'] . "</th>\n";
    					$nb_colonnes=$nb_colonnes+1;
    			}


    			if ($_SESSION['config']['gestion_conges_exceptionnels'])
    			{
    				foreach($tab_type_cong_excep as $id => $libelle)
    				{
    					echo "<th rowspan=\"2\">" . $abs_libelle[$id]['short_libelle'] . "</th>\n";
    					$nb_colonnes=$nb_colonnes+1;
    				}
    			}
    		}


    		echo "</tr>\n\n";


    		/*************************************/
    		// affichage 2ieme ligne (dates)
    		echo "<tr>\n";

    		// on affiche pas car on a fait de "rowspan" à la ligne supérieure
    		// affichage d'une cellule vide sous les titres
    		//echo "	<td class=\"cal-user\" colspan=\"$nb_colonnes\">&nbsp;</td>\n";
    		//dernier jour = dimanche ?
    		$last = 7;


    		// affichage des dates
    		// ... du premier jour voulu à la fin du mois
    		for($j=$first_jour; checkdate($mois, $j, $year); $j++)
    		{
    			$j_timestamp=mktime (0,0,0,$mois, $j, $year);
    			// $j_name=date_fr("D", $j_timestamp);
    			$j_name = substr(date_fr("D", $j_timestamp), 0, 1);
    			$last =date("N", $j_timestamp);
    			$j_date_fr=date_fr("d-m-Y", $j_timestamp);
    			$j_num_semaine=date_fr("W", $j_timestamp);
    			$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

    			// on affiche en gras le jour d'aujourd'hui
    			if($j_timestamp==$timestamp_today)
    				$text_titre_date="<b>$j_name <br/>$j</b>";
    			else
    				$text_titre_date="$j_name <br/>$j";

    			// on regarde si c'est la premiere cellule ou non
    			if($j==$first_jour)
    				$cal_day="cal-day-first";
    			else
    				$cal_day="cal-day";

    			// on affiche le titre -date (la date du jour)
    			echo "<td class=\"$cal_day $td_second_class\" title=\"$j_date_fr / ". _('divers_semaine') ." $j_num_semaine\">$text_titre_date</td>";
    		}

    		// ... si le premier jour voulu n'etait pas le premier du mois, on va jusqu'à la meme date du mois suivant.
    		if($first_jour!=1)
    		{
    			for($j=1; $j<$first_jour; $j++)
    			{
    				if($mois==12)
    				{
    					$mois_select=1;
    					$year_select=$year+1;
    				}
    				else
    				{
    					$mois_select=$mois+1 ;
    					$year_select=$year;
    				}

    				$j_timestamp=mktime (0,0,0,$mois_select, $j, $year_select);
    				$last =date("N", $j_timestamp);
    				// $j_name=date_fr("D", $j_timestamp);
    				$j_name = substr(date_fr("D", $j_timestamp), 0, 1);
    				$j_date_fr=date_fr("d-m-Y", $j_timestamp);
    				$j_num_semaine=date_fr("W", $j_timestamp);
    				$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

    				// on affiche en gras le jour d'aujourd'hui
    				if($j_timestamp==$timestamp_today)
    					echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ". _('divers_semaine') ." $j_num_semaine\"><strong>$j_name $j</strong></td>";
    					// echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ". _('divers_semaine') ." $j_num_semaine\"><b>$j_name $j/$mois_select</b></td>";
    				else
    					echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ". _('divers_semaine') ." $j_num_semaine\">$j_name $j</td>";
    					// echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ". _('divers_semaine') ." $j_num_semaine\">$j_name $j/$mois_select</td>";
    			}
    		}

    		// if ($last < 7)
    		// for ($i = $last; $i <7; $i ++)
    		// 	echo '<td></td>';
    		// echo "</tr>\n";


    		/**************************************************/
    		/**************************************************/
    		/* recup des info de chaque jour pour tous les users et stockage dans 1 tableau de tableaux */

    		$tab_calendrier = \calendrier\Fonctions::recup_tableau_periodes($mois, $first_jour, $year,  $tab_logins);



    		/**************************************************/
    		/* recup des rtt de chaque jour pour tous les users et stockage dans 2 tableaux de tableaux */
    		/**************************************************/
    		//$tab_rtt_echange  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
    						//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
    						// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
    						// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )
    		//$tab_rtt_planifiees=array();  //tableau indexé dont la clé est le login_user
    					// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
    					// clé est la date_fin_grille.
    					// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
    					// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
    					// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille


    		$tab_rtt_echange= recup_tableau_rtt_echange($mois, $first_jour, $year  , $tab_logins );
    		$tab_rtt_planifiees= recup_tableau_rtt_planifiees($mois, $first_jour, $year , $tab_logins);

    		$tab_cong_users = recup_tableau_conges_for_users(false, $tab_logins);

    		/**************************************************/
    		/**************************************************/
    		// affichage lignes suivantes (users)

    		// pour chaque user :
    		foreach($tab_all_users as $sql_login => $tab_current_user)
    		{
    			$sql_nom=$tab_current_user["nom"];
    			$sql_prenom=$tab_current_user["prenom"];
    			$sql_quotite=$tab_current_user["quotite"];

    			// nb de jour pris dans le mois en cours (pour un type d'absence donné)
    			$nb_jours_current_month = array();

    			// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
    			$tab_cong_user = isset($tab_cong_users[$sql_login]) ? $tab_cong_users[$sql_login] : [];

    			if($printable==1)
    				echo "<tr align=\"center\" class=\"cal-ligne-user-edit\">\n";
    			elseif($selected==$sql_login)
    				echo "<tr align=\"center\" class=\"cal-ligne-user-selected\">\n";
    			else
    				echo "<tr align=\"center\" class=\"cal-ligne-user\">\n";

    			if($printable==1)
    				$text_nom="<strong>$sql_nom</strong>";
    			else
    				$text_nom="<a href=\"$PHP_SELF?session=$session&selected=$sql_login&year=$year&mois=$mois&first_jour=$first_jour&printable=$printable&select_groupe=$select_groupe\" method=\"GET\">$sql_nom $sql_prenom</a>";

    			// affichage nom prenom quotité
    			echo "<td class=\"cal-user\">$text_nom</td><td class=\"cal-percent\">$sql_quotite %</td>";


    			// pour chaque jour : (du premier jour demandé à la fin du mois ...)
    			for($j=$first_jour; checkdate($mois, $j, $year); $j++)
    			{
    				$j_timestamp=mktime (0,0,0,$mois, $j, $year);
    				$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

    				$mois_select=$mois;
    				$year_select=$year ;

    				// affichage de la cellule correspondant au jour et au user considéré
    				$t_nb_j_type_abs = \calendrier\Fonctions::affiche_cellule_jour_user($sql_login, $j_timestamp, $year, $mois_select, $j , $td_second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence);
    				foreach($t_nb_j_type_abs as $id_type_abs => $nb_j_pris)
    				{
    					if (isset($nb_jours_current_month[ $id_type_abs ]))
    						$nb_jours_current_month[ $id_type_abs ] +=$nb_j_pris;
    					else
    						$nb_jours_current_month[ $id_type_abs ] =$nb_j_pris;
    				}
    			}
    			// si le premier jour demandé n'est pas le 1ier du mois , on va jusqu'à la meme date le mois suivant :
    			if($first_jour!=1)
    			{
    				// pour chaque jour jusqu'a la date voulue : (meme num de jour le mois suivant)
    				for($j=1; $j<$first_jour; $j++)
    				{
    					$j_timestamp=mktime (0,0,0,$mois+1, $j, $year);
    					$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

    					if($mois==12)
    					{
    						$mois_select=1;
    						$year_select=$year+1 ;
    					}
    					else
    					{
    						$mois_select=$mois+1 ;
    						$year_select=$year ;
    					}

    					// affichage de la cellule correspondant au jour et au user considéré
    					$t_nb_j_type_abs = \calendrier\Fonctions::affiche_cellule_jour_user($sql_login, $j_timestamp, $year, $mois_select, $j, $td_second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence);
    					foreach($t_nb_j_type_abs as $id_type_abs => $nb_j_pris)
    					{
    						if (isset($nb_jours_current_month[ $id_type_abs ]))
    							$nb_jours_current_month[ $id_type_abs ] +=$nb_j_pris;
    						else
    							$nb_jours_current_month[ $id_type_abs ] =$nb_j_pris;
    					}
    				}
    			}


    			//if ($last < 7)
    			//for ($i = $last; $i <7; $i ++)
    			//	echo '<td></td>';

    			if( $_SESSION['config']['affiche_soldes_calendrier'] || is_resp($_SESSION['userlogin']) || is_hr($_SESSION['userlogin']) || is_admin($_SESSION['userlogin']) )
    			{
    				// affichage des divers soldes
    				foreach($tab_cong_user as $id => $tab_conges)
    				{
    					// si des jours ont été pris durant le mois affiché, on indique combien :
    					if((isset($nb_jours_current_month[$id])) && ($_SESSION['config']['affiche_jours_current_month_calendrier']) )
    						echo "<td class=\"cal-user\">".$tab_conges['solde']."&nbsp;(".$nb_jours_current_month[$id].")</td>";
    					else
    						echo "<td class=\"cal-user\">".$tab_conges['solde']."</td>";
    				}
    			}


    			echo "\n</tr>\n";
    		}

    		echo "</table>\n\n";
    }

    /******************************/
    /* Boutons de defilement */
    /******************************/
    public static function affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();

    		if($mois==12) $next_mois=1;  else $next_mois=$mois+1 ;
    		if($mois==1) $prev_mois=12;  else $prev_mois=$mois-1 ;

    		if($prev_mois==12) $prev_year=$year-1; else $prev_year=$year;
    		if($next_mois==1) $next_year=$year+1; else $next_year=$year;

    		$prev_first_jour=date("j", \calendrier\Fonctions::jour_precedent($first_jour, $mois, $year))  ;
    			$prev_first_jour_mois=date("n", \calendrier\Fonctions::jour_precedent($first_jour, $mois, $year))  ;
    			$prev_first_jour_year=date("Y", \calendrier\Fonctions::jour_precedent($first_jour, $mois, $year))  ;
    		$next_first_jour=date("j", \calendrier\Fonctions::jour_suivant($first_jour, $mois, $year)) ;
    			$next_first_jour_mois=date("n", \calendrier\Fonctions::jour_suivant($first_jour, $mois, $year)) ;
    			$next_first_jour_year=date("Y", \calendrier\Fonctions::jour_suivant($first_jour, $mois, $year)) ;

    		echo "<ul class=\"pager\">\n";
    		echo "<li><a href=\"$PHP_SELF?session=$session&first_jour=1&mois=$prev_mois&year=$prev_year&select_groupe=$select_groupe\" method=\"POST\"><i class=\"fa fa-angle-double-left\"></i>&nbsp;". _('divers_mois_precedent_maj_1') ." </a></li>\n";
 		echo "<li><a href=\"$PHP_SELF?session=$session&first_jour=$prev_first_jour&mois=$prev_first_jour_mois&year=$prev_first_jour_year&select_groupe=$select_groupe\" method=\"POST\"><i class=\"fa fa-angle-double-left\"></i>&nbsp;". _('calendrier_jour_precedent') ." </a></li>\n";
    		echo "<li><a href=\"$PHP_SELF?session=$session&first_jour=$next_first_jour&mois=$next_first_jour_mois&year=$next_first_jour_year&select_groupe=$select_groupe\" method=\"POST\"> ". _('calendrier_jour_suivant') ."&nbsp;<i class=\"fa fa-angle-double-right\"></i></a></li>\n";
   		echo "<li><a href=\"$PHP_SELF?session=$session&first_jour=1&mois=$next_mois&year=$next_year&select_groupe=$select_groupe\" method=\"POST\">". _('divers_mois_suivant_maj_1') ."&nbsp;<i class=\"fa fa-angle-double-right\"></i></a></li>\n";
    		echo "</ul>\n";
    }

    // retourne le timestamp calculé du jour suivant
    public static function jour_suivant($jour, $mois, $year)
    {
    	return mktime (0,0,0,$mois,$jour +7,$year);
    }

    // retourne le timestamp calculé du jour precedent
    public static function jour_precedent($jour, $mois, $year)
    {
    	return mktime (0,0,0,$mois,$jour -7,$year);
    }

    /**
     * Encapsule le comportement du module calendrier
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function calendrierModule($session, $DEBUG = false)
    {

        if(substr($session, 0, 9)!="phpconges")
        {
        	session_start();
        	$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
        	if($_SESSION['config']['consult_calendrier_sans_auth']==FALSE)
        	{
        		redirect( ROOT_PATH . 'index.php' );
        	}
        }
        else
        	include_once INCLUDE_PATH .'session.php';

        if( $DEBUG ) { echo "lang_file=".$_SESSION['config']['lang_file']."<br/>\n";  echo "_SESSION =<br/>\n"; print_r($_SESSION); echo "<br/><br/>\n"; }

        $script = '<script language=javascript>
        function afficher(id)
        {
        	el = document.getElementById(id);
        	el.style.display = "block";
        }

        function cacher(id)
        {
        	el = document.getElementById(id);
        	el.style.display = "none";
        }
        </script>';


        $add_css = NULL;
        header_menu('', 'Libertempo : '._('calendrier_titre'), $add_css);


        	/*************************************/
        	// recup des parametres reçus :
        	// SERVER
        	$PHP_SELF=$_SERVER['PHP_SELF'];
        	// GET / POST
        	$selected      = getpost_variable('selected') ;
        	$printable     = getpost_variable('printable', 0) ;
        	$year          = getpost_variable('year', date("Y")) ;
        	$mois          = getpost_variable('mois', date("n")) ;
        	$first_jour    = getpost_variable('first_jour', 1) ;
        //	$first_load    = getpost_variable('first_load', "Y") ;
        	$select_groupe = getpost_variable('select_groupe', 0) ;


        	/*************************************/

        	// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        	if(!isset($_SESSION["tab_j_feries"]))
        	{
        		init_tab_jours_feries();
        		//print_r($_SESSION["tab_j_feries"]);   // verif DEBUG
        	}

        	// renvoit un tableau de tableau contenant les infos des types de conges et absences
        	$tab_type_absence=recup_tableau_tout_types_abs($DEBUG);


        //	echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";

        	$jour_today=date("j");
        	$mois_today=date("m");
        	$year_today=date("Y");
        	$timestamp_today = mktime (0,0,0,$mois_today,$jour_today,$year_today);

        	$mois_timestamp = mktime (0,0,0,$mois,1,$year);
        	$nom_mois=date_fr("F", $mois_timestamp);

        	$group_names =get_groups_name();

        	// AFFICHAGE PAGE
        	echo "<div id=\"main-calendar\" class=\"main-content\">\n";
        	if( ($_SESSION['config']['gestion_groupes']) && ($printable!=1) )  // si gestion des groupes active et pas version imprimable
        	{
        	// affiche le select des groupes du user OU les groupes du resp (si user est resp) OU tous les groupes (si option de config ok)
        		echo "<div class=\"pull-right\">\n";
        		\calendrier\Fonctions::affiche_select_groupe($select_groupe, $selected, $printable, $year, $mois, $first_jour, $group_names) ;
        		echo "</div>\n";
        	}

        	echo "   <h1>". _('calendrier_titre') . "</h1>\n";
        	if( ($_SESSION['config']['gestion_groupes']) && ($select_groupe!=0) ) {
        		echo "<h2>". _('divers_groupe') ." : <strong>".$group_names[ $select_groupe ]."</strong></h2>\n";

        	}

        	echo "<hr/>\n";
        	echo "<h3 class=\"current-month\">$nom_mois $year</h3>\n";
        	echo "<hr/>\n";

        	/**********************/
        	/* Boutons de defilement */
        	if($printable!=1)   // si version ecran :
        	{
        		\calendrier\Fonctions::affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG) ;
        	}


        	/***********************************/
        	/* AFFICHAGE  TABLEAU (CALENDRIER) */
        	\calendrier\Fonctions::affichage_calendrier($year, $mois, $first_jour, $timestamp_today, $printable, $selected, $tab_type_absence, $select_groupe, $DEBUG);


        	/**********************/
        	/* Boutons de defilement */
        	if($printable!=1)   // si version ecran :
        	{
        		echo "<tr>\n";
        		echo "   <td align=\"center\">\n";
        		\calendrier\Fonctions::affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG) ;
        		echo "   </td>\n";
        		echo "</tr>\n";
        	}

        	echo "<tr>\n";
        	echo "</tr>\n";
        	echo "</table>\n";

        	if($printable!=1) // si version ecran :
        		{
        			echo "      <br/><a href=\"$PHP_SELF?session=$session&printable=1&year=$year&mois=$mois&first_jour=$first_jour&select_groupe=$select_groupe\" target=\"_blank\" method=\"post\">\n";
        			echo "<i class=\"fa fa-print\"></i>\n";
        			echo "      ". _('calendrier_imprimable') ."\n";
        			echo "      </a>\n";
				echo "      <br><a href=\"calendrier-pdf.php?session=$session&printable=1&year=$year&mois=$mois&first_jour=$first_jour&select_groupe=$select_groupe\" target=\"_blank\" method=\"post\">\n";
				echo "		<img src=\"". TEMPLATE_PATH . "img/pdf_22x22_2.png\" width=\"22\" height=\"22\" border=\"0\" title=\"Version PDF\">\n";
				echo "      PDF\n";
				echo "      </a>\n";


        		}

        	echo "      <br><br><table cellpadding=\"1\" class=\"calendar table-responsive table-bordered table-stripped\">\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#FFFFFF\" class=\"cal-legende\"> - </td>\n" ;
        	echo "         <td class=\"cal-legende\"> </td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#DCDCDC\" class=\"cal-legende\"> - </td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_we') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#8addf2\" class=\"cal-legende\">abs</td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_conges') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#ffc1ff\" class=\"cal-legende\">abs</td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_demande') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#ffffad\" class=\"cal-legende\"> - </td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_part_time') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#C3C3C3\" class=\"cal-legende\">abs</td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('calendrier_legende_abs') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      <tr align=\"center\">\n" ;
        	echo "         <td bgcolor=\"#CEB6FF\" class=\"cal-legende\">abs</td>\n" ;
        	echo "         <td class=\"cal-legende\"> ". _('divers_fermeture') ."</td>\n" ;
        	echo "      </tr>\n" ;
        	echo "      </table>\n" ;
        	echo "</div>";
        	/********************/
        	/* bouton retour */
        	/********************/
        	if($printable==1)   // si version imprimable :
        	{
        	// appel de la fenetre d'impression directe
        ?>
        <script type="text/javascript" language="javascript1.2">
        <!--
        // Do print the page
        if (typeof(window.print) != 'undefined') {
            window.print();
        }
        //-->
        </script>
        <?php
        	}

        	bottom();

    }
}
