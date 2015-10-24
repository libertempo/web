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
namespace edition;

/**
* Regroupement des fonctions liées à l'édition
*/
class Fonctions
{
    public static function affiche_anciennes_editions($login,  $DEBUG=FALSE)
    {
        $session=session_id();

    	echo "<CENTER>\n" ;

    	// recup du tableau des types de conges (seulement les conges)
    	$tab_type_cong=recup_tableau_types_conges();

    	/*************************************/
    	/* Historique des éditions           */
    	/*************************************/
    	// Récupération des informations des editions du user
    	$tab_editions_user = recup_editions_user($login,  $DEBUG);
    	if( $DEBUG ) {echo "tab_editions_user<br>\n"; print_r($tab_editions_user); echo "<br>\n"; }

    	echo "<h3>". _('editions_hitorique_edit') ." :</h3>\n";

    	if(count($tab_editions_user)==0)
    	{
    		echo "<b>". _('editions_aucun_hitorique') ."</b><br>\n";
    	}
    	else
    	{
    		// AFFICHAGE TABLEAU
    		echo "<table cellpadding=\"2\" class=\"tablo\" width=\"750\">\n";
    		echo "<thead><tr align=\"center\">\n";
    		echo " <th>". _('editions_numero') ."</th>\n";
    		echo " <th>". _('editions_date') ."</th>\n";
    		foreach($tab_type_cong as $id_abs => $libelle)
    		{
    			echo " <th>". _('divers_solde_maj_1') ." $libelle</th>\n";
    		}

    		echo " <th></th>\n";
    		echo " <th></th>\n";
    		echo "</tr></thead><tbody>\n";

    		foreach($tab_editions_user as $id_edition => $tab_ed)
    		{
    			//$text_edit_a_nouveau="<a href=\"edition_papier.php?session=$session&user_login=$login&edit_id=$sql_id\">Editer à nouveau</a>" ;
    			$text_edit_a_nouveau="<a href=\"edition_papier.php?session=$session&user_login=$login&edit_id=$id_edition\">" .
    					"<img src=\"". TEMPLATE_PATH . "img/fileprint_16x16_2.png\" width=\"16\" height=\"16\" border=\"0\" title=\"". _('editions_edit_again') ."\" alt=\"". _('editions_edit_again') ."\">" .
    					" ". _('editions_edit_again')  .
    					"</a>\n";
    			$text_edit_pdf_a_nouveau="<a href=\"edition_pdf.php?session=$session&user_login=$login&edit_id=$id_edition\">" .
    					"<img src=\"". TEMPLATE_PATH . "img/pdf_16x16_2.png\" width=\"16\" height=\"16\" border=\"0\" title=\"". _('editions_edit_again_pdf') ."\" alt=\"". _('editions_edit_again_pdf') ."\">" .
    					" ". _('editions_edit_again_pdf')  .
    					"</a>\n";

    			echo "<tr align=\"center\">\n";
    			echo "<td>".$tab_ed['num_for_user']."</td>\n" ;
    			echo "<td class=\"histo-big\">".$tab_ed['date']."</td>";
    			foreach($tab_type_cong as $id_abs => $libelle)
    			{
    				echo "<td>".$tab_ed['conges'][$id_abs]."</td>";
    			}

    			echo "<td>$text_edit_a_nouveau</td>";
    			echo "<td>$text_edit_pdf_a_nouveau</td>";
    			echo "</tr>\n";
    		}
    		echo "</tbody></table>\n";
    	}
    	echo "<br>\n";

    	echo "</CENTER>\n";
    	echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";
    }

    public static function affiche_nouvelle_edition($login,  $DEBUG=FALSE)
    {
    	$session=session_id();

    	echo "<CENTER>\n" ;

    	/*************************************/
    	/* Historique des Conges et demandes */
    	/*************************************/
    	// Récupération des informations
    	// recup de ttes les periodes de type conges du user, sauf les demandes, qui ne sont pas dejà sur une édition papier
    	$sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_date_demande, p_date_traitement, ta_libelle ";
    	$sql2=$sql2."FROM conges_periode as a, conges_type_absence as b ";
    	$sql2=$sql2."WHERE (p_etat!='demande' AND p_etat!='valid') ";
    	$sql2=$sql2."AND p_edition_id IS NULL ";
    	$sql2=$sql2."AND (p_login = '$login') ";
    	$sql2=$sql2."AND (a.p_type=b.ta_id AND  ( (b.ta_type='conges') OR (b.ta_type='conges_exceptionnels') ) )";
    	$sql2=$sql2."ORDER BY p_date_deb ASC ";
    	$ReqLog2 = \includes\SQL::query($sql2);

    	echo "<h3>". _('editions_last_edition') ." :</h3>\n";

    	$count2=$ReqLog2->num_rows;
    	if($count2==0)
    	{
    		echo "<b>". _('editions_aucun_conges') ."</b><br>\n";
    	}
    	else
    	{
    		// AFFICHAGE TABLEAU
    		if($_SESSION['config']['affiche_date_traitement'])
    			echo "<table cellpadding=\"2\" class=\"tablo\" width=\"850\">\n";
    		else
    			echo "<table cellpadding=\"2\" class=\"tablo\" width=\"750\">\n";
    		echo "<thead><tr align=\"center\">\n";
    		echo " <th>". _('divers_type_maj_1') ."</th>\n";
    		echo " <th>". _('divers_etat_maj_1') ."</th>\n";
    		echo " <th>". _('divers_nb_jours_maj_1') ."</th>\n";
    		echo " <th>". _('divers_debut_maj_1') ."</th>\n";
    		echo " <th>". _('divers_fin_maj_1') ."</th>\n";
    		echo " <th>". _('divers_comment_maj_1') ."</th>\n";
    		if($_SESSION['config']['affiche_date_traitement'])
    		{
    			echo "<th>". _('divers_date_traitement') ."</td>\n" ;
    		}
    		echo "</tr></thead></tbody>\n";

    		while ($resultat2 = $ReqLog2->fetch_array()) {
    				$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
    				$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
    				if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
    				$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
    				$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
    				if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
    				$sql_p_nb_jours = $resultat2["p_nb_jours"];
    				$sql_p_commentaire = $resultat2["p_commentaire"];
    				$sql_p_type = $resultat2["ta_libelle"];
    				$sql_p_etat = $resultat2["p_etat"];
    				$sql_p_date_demande = $resultat2["p_date_demande"];
    				$sql_p_date_traitement = $resultat2["p_date_traitement"];

    				echo "<tr align=\"center\">\n";
    				echo "<td>$sql_p_type</td>\n" ;
    				echo "<td>";
    				if($sql_p_etat=="refus")
    					echo  _('divers_refuse')  ;
    				elseif($sql_p_etat=="annul")
    					echo  _('divers_annule')  ;
    				else
    					echo "$sql_p_etat";
    				echo "</td>\n" ;
    				if($sql_p_etat=="ok")
    					echo "<td class=\"histo-big\"> -$sql_p_nb_jours</td>";
    				elseif($sql_p_etat=="ajout")
    					echo "<td class=\"histo-big\"> +$sql_p_nb_jours</td>";
    				else
    					echo "<td> $sql_p_nb_jours</td>";
    				echo "<td>$sql_p_date_deb _ $demi_j_deb</td>";
    				echo "<td>$sql_p_date_fin _ $demi_j_fin</td>";
    				echo "<td>$sql_p_commentaire</td>";
    				if($_SESSION['config']['affiche_date_traitement'])
    				{
    					if($sql_p_date_demande == NULL)
    					 echo "<td class=\"histo-left\">". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
    					else
    						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
    				}
    				echo "</tr>\n";
    		}
    		echo "</tbody></table>\n";
    		echo "<br>\n";

    		/******************/
    		/* bouton editer  */
    		/******************/
    		echo "<table cellpadding=\"2\" width=\"400\">\n";
    		echo "<tr align=\"center\">\n";
    		echo " <td width=\"200\">\n";
    			echo "<a href=\"edition_papier.php?session=$session&user_login=$login&edit_id=0\">\n";
    			echo "<img src=\"". TEMPLATE_PATH . "img/fileprint_2.png\" width=\"22\" height=\"22\" border=\"0\" title=\"". _('editions_lance_edition') ."\" alt=\"". _('editions_lance_edition') ."\">\n";
    			echo "<b> ". _('editions_lance_edition') ." </b>\n";
    			echo "</a>\n";
    		echo "</td>\n";
    		echo " <td width=\"200\">\n";
    			echo "<a href=\"edition_pdf.php?session=$session&user_login=$login&edit_id=0\">\n";
    			echo "<img src=\"". TEMPLATE_PATH . "img/pdf_22x22_2.png\" width=\"22\" height=\"22\" border=\"0\" title=\"". _('editions_pdf_edition') ."\" alt=\"". _('editions_pdf_edition') ."\">\n";
    			echo "<b> ". _('editions_pdf_edition') ." </b>\n";
    			echo "</a>\n";
    		echo "</td>\n";
    		echo "</tr>\n";
    		echo "</table>\n";

    	}
    	echo "<br>\n";

    	echo "</CENTER>\n";
    	echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";
    }

    public static function affichage($login,  $DEBUG=FALSE)
    {

    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();


    	$sql1 = 'SELECT u_nom, u_prenom, u_quotite FROM conges_users where u_login = "'. \includes\SQL::quote($login).'"';
    	$ReqLog1 = \includes\SQL::query($sql1);

    	while ($resultat1 = $ReqLog1->fetch_array()) {
    		$sql_nom=$resultat1["u_nom"];
    		$sql_prenom=$resultat1["u_prenom"];
    		$sql_quotite=$resultat1["u_quotite"];
    	}

    	// TITRE
    	echo "<H1>$sql_prenom  $sql_nom  ($login)</H1>\n\n";

    	/********************/
    	/* Bilan des Conges */
    	/********************/
    	// affichage du tableau récapitulatif des solde de congés d'un user
    	affiche_tableau_bilan_conges_user($login,  $DEBUG);
    	echo "<br><br><br>\n";

    	\edition\Fonctions::affiche_nouvelle_edition($login,  $DEBUG);

    	\edition\Fonctions::affiche_anciennes_editions($login,  $DEBUG);
    }

    /**
     * Encapsule le comportement du module d'édition des utilisateurs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function editUserModule($session, $DEBUG = false)
    {
        /*************************************/
    	// recup des parametres reçus :
    	// SERVER
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	// GET / POST
    	$user_login = getpost_variable('user_login', $_SESSION['userlogin']) ;

    	/*************************************/

    	if ($user_login != $_SESSION['userlogin'] && !is_hr($_SESSION['userlogin']) && !is_resp_of_user($_SESSION['userlogin'] , $user_login)) {
    		redirect(ROOT_PATH . 'deconnexion.php');
    		exit;
    	}

    	/************************************/

    	header_popup( _('editions_titre') .' : '.$user_login);

    	\edition\Fonctions::affichage($user_login, $DEBUG);

    	bottom();
    }

    // affichage du tableau récapitulatif des solde de congés d'un user d'une edition donnée !
    public static function affiche_tableau_bilan_conges_user_edition($tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels,  $DEBUG=FALSE)
    {

    	echo "<table cellpadding=\"2\" width=\"250\" class=\"tablo\">\n";
    //	echo "<tr align=\"center\"><td class=\"titre\" colspan=\"3\"> quotité &nbsp; : &nbsp; $quotite % </td></tr>\n" ;
    	echo "<thead><tr>\n";
    	echo "	<th></th>\n";
    	echo "	<th> ". _('editions_jours_an') ." </th>\n";
    	echo "	<th> ". _('divers_solde_maj') ."</th>\n";
    	echo "	</tr></thead><tbody>\n" ;

    	foreach($tab_type_cong as $id_abs => $libelle)
    	{
    		echo "<tr><td> $libelle </td>
    				<td>".$tab_info_user['conges'][$libelle]['nb_an']."</td>
    				<td align=\"center\" bgcolor=\"#FF9191\"><b>".$tab_info_edition['conges'][$id_abs]."</b></td>";
    	}
    	foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
    	{
    		echo "<tr><td> $libelle </td>
    				<td>".$tab_info_user['conges'][$libelle]['nb_an']."</td>
    				<td align=\"center\" bgcolor=\"#FF9191\"><b>".$tab_info_edition['conges'][$id_abs]."</b></td>";
    	}
    	echo "</tr>\n";

    	echo "</tbody></table>\n";
    }

    public static function edition_papier($login, $edit_id,  $DEBUG=FALSE)
    {
    //$DEBUG = TRUE ;
    	$session=session_id();

    	// recup infos du user
    	$tab_info_user=recup_info_user_pour_edition($login,  $DEBUG);

    	// recup infos de l'édition
    	$tab_info_edition=recup_info_edition($edit_id,  $DEBUG);

    	// recup du tableau des types de conges exceptionnels (seulement les conge sexceptionnels )
    	$tab_type_cong=recup_tableau_types_conges( $DEBUG);
    	// recup du tableau des types de conges (seulement les conges)
    	if ($_SESSION['config']['gestion_conges_exceptionnels'])
    		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels( $DEBUG);
    	else
    		$tab_type_conges_exceptionnels=array();
    	// recup du tableau de tous les types de conges
    	$tab_type_all_cong=recup_tableau_tout_types_abs( $DEBUG);

    	if( $DEBUG )
    	{
    		echo "tab_info_user :<br>\n" ; print_r($tab_info_user) ; echo "<br><br>\n" ;
    		echo "tab_info_edition :<br>\n" ; print_r($tab_info_edition) ; echo "<br><br>\n" ;
    		echo "tab_type_cong :<br>\n" ; print_r($tab_type_cong) ; echo "<br><br>\n" ;
    		echo "tab_type_conges_exceptionnels :<br>\n" ; print_r($tab_type_conges_exceptionnels) ; echo "<br><br>\n" ;
    		echo "tab_type_all_cong :<br>\n" ; print_r($tab_type_all_cong) ; echo "<br><br>\n" ;
    		echo "numero edition = $edit_id<br>\n" ;
    	}


    	/**************************************/
    	/* affichage du texte en haut de page */
    	/**************************************/
    	echo "\n<!-- affichage du texte en haut de page -->\n";
    	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"770\">\n" ;
    	echo "<tr align=\"center\">\n";
    	echo "<td>".$_SESSION['config']['texte_haut_edition_papier']."<br><br></td>\n";
    	echo "</tr>\n";
    	echo "</table>\n";

    	/**************************************/
    	/* affichage du TITRE                 */
    	/**************************************/
    	echo "\n<!-- affichage du TITRE -->\n";
    	echo "<H1>".$tab_info_user['nom']."  ".$tab_info_user['prenom']."</H1>\n\n";
    	$tab_date=explode("-", $tab_info_edition['date']);
    	echo "<H2>". _('editions_bilan_au') ." $tab_date[2] / $tab_date[1] / $tab_date[0]</H2>\n\n";


    	/****************************/
    	/* tableau Bilan des Conges */
    	/****************************/
    	// affichage du tableau récapitulatif des solde de congés d'un user DE cette edition !
    	\edition\Fonctions::affiche_tableau_bilan_conges_user_edition($tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels,  $DEBUG);

    	$quotite=$tab_info_user['quotite'];
    	echo "<h3> ". _('divers_quotite') ."&nbsp; : &nbsp;$quotite % </h3>\n" ;
    	echo "<br><br><br>\n";


    	if($_SESSION['config']['affiche_date_traitement'])
    		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"870\">\n" ;
    	else
    		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"770\">\n" ;
    	echo "<tr align=\"center\">\n";
    	echo "<td><h3>". _('editions_historique') ." :</h3></td>\n";
    	echo "</tr>\n";

    	/*********************************************/
    	/* Tableau Historique des Conges et demandes */
    	/*********************************************/
    	echo "\n<!-- Tableau Historique des Conges et demandes -->\n";
    	echo "<tr align=\"center\">\n";
    	echo "<td>\n";

    		// Récupération des informations
    		// on ne recup QUE les periodes de l'edition choisie
    		$sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_date_demande, p_date_traitement ";
    		$sql2=$sql2."FROM conges_periode ";
    		$sql2=$sql2."WHERE p_edition_id = $edit_id ";
    		$sql2=$sql2."ORDER BY p_date_deb ASC ";
    		$ReqLog2 = \includes\SQL::query($sql2);

    		$count2=$ReqLog2->num_rows;
    		if($count2==0)
    		{
    			echo "<b>". _('editions_aucun_conges') ."</b><br>\n";
    		}
    		else
    		{
    			// AFFICHAGE TABLEAU
    			if($_SESSION['config']['affiche_date_traitement'])
    				echo "<table cellpadding=\"2\" class=\"tablo-edit\" width=\"850\">\n";
    			else
    				echo "<table cellpadding=\"2\" class=\"tablo-edit\" width=\"750\">\n";

    			/*************************************/
    			/* affichage anciens soldes          */
    			/*************************************/
    			echo "\n<!-- affichage anciens soldes -->\n";
    			echo "<tr>\n";
    			echo "<td colspan=\"5\">\n";
    			$edition_precedente_id=get_id_edition_precedente_user($login, $edit_id,  $DEBUG);
    			if($edition_precedente_id==0)
    				echo "<b>". _('editions_soldes_precedents_inconnus') ." !... ";
    			else
    			{
    				$tab_edition_precedente=recup_info_edition($edition_precedente_id,  $DEBUG);
    				foreach($tab_type_cong as $id_abs => $libelle)
    				{
    					echo  _('editions_solde_precedent') ." <b>$libelle : ".$tab_edition_precedente['conges'][$id_abs]."</b><br>\n";
    				}
    				foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
    				{
    					echo  _('editions_solde_precedent') ." <b>$libelle : ".$tab_edition_precedente['conges'][$id_abs]."</b><br>\n";
    				}
    			}

    			echo "<td>\n";
    			echo "</tr>\n";


    			/*************************************/
    			/* affichage lignes de l'edition     */
    			/*************************************/
    			echo "\n<!-- affichage lignes de l'edition -->\n";
    			echo "<tr>\n";
    			echo " <td class=\"titre-edit\">". _('divers_type_maj_1') ."</td>\n";
    			echo " <td class=\"titre-edit\">". _('divers_etat_maj_1') ."</td>\n";
    			echo " <td class=\"titre-edit\">". _('divers_nb_jours_maj_1') ."</td>\n";
    			echo " <td class=\"titre-edit\">". _('divers_debut_maj_1') ."</td>\n";
    			echo " <td class=\"titre-edit\">". _('divers_fin_maj_1') ."</td>\n";
    			echo " <td class=\"titre-edit\">". _('divers_comment_maj_1') ."</td>\n";
    			if($_SESSION['config']['affiche_date_traitement'])
    			{
    				echo "<td class=\"titre-edit\">". _('divers_date_traitement') ."</td>\n" ;
    			}
    			echo "</tr>\n";

    			while ($resultat2 = $ReqLog2->fetch_array()) {
    					$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
    					$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
    					if($sql_p_demi_jour_deb=="am")
    						$demi_j_deb =  _('divers_am_short') ;
    					else
    						$demi_j_deb =  _('divers_pm_short') ;
    					$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
    					$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
    					if($sql_p_demi_jour_fin=="am")
    						$demi_j_fin =  _('divers_am_short') ;
    					else
    						$demi_j_fin =  _('divers_pm_short') ;
    					$sql_p_nb_jours = $resultat2["p_nb_jours"];
    					$sql_p_commentaire = $resultat2["p_commentaire"];
    					$sql_p_type = $resultat2["p_type"];
    					$sql_p_etat = $resultat2["p_etat"];
    					$sql_p_date_demande = $resultat2["p_date_demande"];
    					$sql_p_date_traitement = $resultat2["p_date_traitement"];

    					echo "<tr>\n";
    					echo "<td class=\"histo-edit\">".$tab_type_all_cong[$sql_p_type]['libelle']."</td>\n" ;
    					echo "<td class=\"histo-edit\">";
    					if($sql_p_etat=="refus")
    						echo  _('divers_refuse') ;
    					elseif($sql_p_etat=="annul")
    						echo  _('divers_annule') ;
    					else
    						echo "$sql_p_etat";
    					echo "</td>\n" ;
    					if($sql_p_etat=="ok")
    						echo "<td class=\"histo-big\"> -$sql_p_nb_jours</td>";
    					elseif($sql_p_etat=="ajout")
    						echo "<td class=\"histo-big\"> +$sql_p_nb_jours</td>";
    					else
    						echo "<td> $sql_p_nb_jours</td>";
    					echo "<td class=\"histo-edit\">$sql_p_date_deb _ $demi_j_deb</td>";
    					echo "<td class=\"histo-edit\">$sql_p_date_fin _ $demi_j_fin</td>";
    					echo "<td class=\"histo-edit\">$sql_p_commentaire</td>";

    				if($_SESSION['config']['affiche_date_traitement'])
    				{
    					if($sql_p_date_demande == NULL)
    						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
    					else
    						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
    				}
    					echo "</tr>\n";
    			}

    			/*************************************/
    			/* affichage nouveaux soldes         */
    			/*************************************/
    			echo "\n<!-- affichage nouveaux soldes -->\n";
    			echo "<tr>\n";
    			echo "<td colspan=\"5\">\n";
    				foreach($tab_type_cong as $id_abs => $libelle)
    				{
    					echo  _('editions_nouveau_solde') ." <b>$libelle : ".$tab_info_edition['conges'][$id_abs]."</b><br>\n";
    				}
    			echo "<td>\n";
    			echo "</tr>\n";

    			echo "</table>\n\n";
    		}
    	echo "<br><br>\n";
    	echo "</td>\n";

    	echo "</tr>\n";

    	echo "</table>\n";


    	/*************************************/
    	/* affichage des zones de signature  */
    	/*************************************/
    	echo "\n<!-- affichage des zones de signature -->\n";
    	echo "<br>\n" ;
    	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"770\">\n" ;
    	echo "<tr align=\"center\">\n";
    	echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
    	echo "<td align=\"left\">\n" ;
    		echo "<b>". _('editions_date') ." : <br>". _('editions_signature_1') ." :</b><br><br><br><br><br><br><br><br><br><br>\n" ;
    	echo "</td>\n";
    	echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
    	echo "<td align=\"left\">\n" ;
    		echo "<b>". _('editions_date') ." : <br>". _('editions_signature_2') ." :</b><br><i>(". _('editions_cachet_etab') .")</i><br><br><br><br><br><br><br><br><br>\n" ;
    	echo "</td>\n";
    	echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
    	echo "</tr>\n";
    	echo "</table>\n";


    	/*************************************/
    	/* affichage du texte en bas de page */
    	/*************************************/
    	echo "\n<!-- affichage du texte en bas de page -->\n";
    	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"770\">\n" ;
    	echo "<tr align=\"center\">\n";
    	echo "<td><br>".$_SESSION['config']['texte_bas_edition_papier']."</td>\n";
    	echo "</tr>\n";
    	echo "</table>\n";
    }

    /**
     * Encapsule le comportement du module de l'édition papier
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function editPapierModule($session, $DEBUG = false)
    {
        /*************************************/
        // recup des parametres reçus :
        // GET / POST
        $user_login = getpost_variable('user_login') ;
        $edit_id = getpost_variable('edit_id', 0) ;
        /*************************************/

        if ($user_login != $_SESSION['userlogin'] && !is_hr($_SESSION['userlogin']) && !is_resp_of_user($_SESSION['userlogin'] , $user_login)) {
            redirect(ROOT_PATH . 'deconnexion.php');
            exit;
        }

        /************************************/

        $css = '<link href="'. TEMPLATE_PATH .'style_calendar_edition.css" rel="stylesheet" type="text/css">';

        header_popup(_('editions_etat_conges').' : '.$user_login , $css);



        if($edit_id==0)   // si c'est une nouvelle édition, on insert dans la base avant d'éditer et on renvoit l'id de l'édition
            $edit_id=enregistrement_edition($user_login,  $DEBUG);

        \edition\Fonctions::edition_papier($user_login, $edit_id,  $DEBUG);

        $comment_log = "edition papier (num_edition = $edit_id) ($user_login) ";
        log_action(0, "", $user_login, $comment_log,  $DEBUG);

    ?>
    <br>
    <script type="text/javascript" language="javascript1.2">
    <!--
    // Do print the page
    if (typeof(window.print) != 'undefined') {
        window.print();
    }
    //-->
    </script>
    <?php

    bottom();
    }
}
