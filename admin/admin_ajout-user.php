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


	$saisie_user     = getpost_variable('saisie_user') ;

	// si on recupere les users dans ldap et qu'on vient d'en créer un depuis la liste déroulante
	if ($_SESSION['config']['export_users_from_ldap']  && isset($_POST['new_ldap_user']))
	{
		$index = 0;
		// On lance une boucle pour selectionner tous les items
		// traitements : $login contient les valeurs successives
		foreach($_POST['new_ldap_user'] as $login)
		{
			$tab_login[$index]=$login;
			$index++;
			// cnx à l'annuaire ldap :
			$ds = ldap_connect($_SESSION['config']['ldap_server']);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3) ;
			if ($_SESSION['config']['ldap_user'] == "")
				$bound = ldap_bind($ds);
			else
				$bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

			// recherche des entrées :
			$filter = "(".$_SESSION['config']['ldap_login']."=".$login.")";

			$sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
			$data = ldap_get_entries($ds,$sr);

			foreach ($data as $info)
			{
				$tab_new_user[$login]['login']	= $login;
				$ldap_libelle_prenom		=$_SESSION['config']['ldap_prenom'];
				$ldap_libelle_nom		=$_SESSION['config']['ldap_nom'];
				$tab_new_user[$login]['prenom']	= utf8_decode($info[$ldap_libelle_prenom][0]);
				$tab_new_user[$login]['nom']	= utf8_decode($info[$ldap_libelle_nom][0]);

				$ldap_libelle_mail				=$_SESSION['config']['ldap_mail'];
				$tab_new_user[$login]['email']	= $info[$ldap_libelle_mail][0] ;
			}

			$tab_new_user[$login]['quotite']	= getpost_variable('new_quotite') ;
			$tab_new_user[$login]['is_resp']	= getpost_variable('new_is_resp') ;
			$tab_new_user[$login]['resp_login']	= getpost_variable('new_resp_login') ;
			$tab_new_user[$login]['is_admin']	= getpost_variable('new_is_admin') ;
			$tab_new_user[$login]['is_hr']		= getpost_variable('new_is_hr') ;
			$tab_new_user[$login]['see_all']	= getpost_variable('new_see_all') ;

			if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
			{
				$tab_new_user[$login]['password1']= getpost_variable('new_password1') ;
				$tab_new_user[$login]['password2']= getpost_variable('new_password2') ;
			}
			$tab_new_jours_an			= getpost_variable('tab_new_jours_an') ;
			$tab_new_solde				= getpost_variable('tab_new_solde') ;
			$tab_checkbox_sem_imp			= getpost_variable('tab_checkbox_sem_imp') ;
			$tab_checkbox_sem_p			= getpost_variable('tab_checkbox_sem_p') ;
			$tab_new_user[$login]['new_jour']	= getpost_variable('new_jour') ;
			$tab_new_user[$login]['new_mois']	= getpost_variable('new_mois') ;
			$tab_new_user[$login]['new_year']	= getpost_variable('new_year') ;
 		}
	}
	else
	{
		$tab_new_user[0]['login']		= getpost_variable('new_login') ;
		$tab_new_user[0]['nom']			= getpost_variable('new_nom') ;
		$tab_new_user[0]['prenom']		= getpost_variable('new_prenom') ;


		$tab_new_user[0]['quotite']		= getpost_variable('new_quotite') ;
		$tab_new_user[0]['is_resp']		= getpost_variable('new_is_resp') ;
		$tab_new_user[0]['resp_login']	= getpost_variable('new_resp_login') ;
		$tab_new_user[0]['is_admin']	= getpost_variable('new_is_admin') ;
		$tab_new_user[0]['is_hr']		= getpost_variable('new_is_hr') ;
 		$tab_new_user[0]['see_all']		= getpost_variable('new_see_all') ;

		if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
		{
			$tab_new_user[0]['password1']	= getpost_variable('new_password1') ;
			$tab_new_user[0]['password2']	= getpost_variable('new_password2') ;
		}
		$tab_new_user[0]['email']	= getpost_variable('new_email') ;
		$tab_new_jours_an			= getpost_variable('tab_new_jours_an') ;
		$tab_new_solde				= getpost_variable('tab_new_solde') ;
		$tab_checkbox_sem_imp		= getpost_variable('tab_checkbox_sem_imp') ;
		$tab_checkbox_sem_p			= getpost_variable('tab_checkbox_sem_p') ;
		$tab_new_user[0]['new_jour']= getpost_variable('new_jour') ;
		$tab_new_user[0]['new_mois']= getpost_variable('new_mois') ;
		$tab_new_user[0]['new_year']= getpost_variable('new_year') ;
	}


	$checkbox_user_groups= getpost_variable('checkbox_user_groups') ;
	/* FIN de la recup des parametres    */
	/*************************************/



	if($saisie_user=="ok") {
		if($_SESSION['config']['export_users_from_ldap'] ) {
			foreach($tab_login as $login) {
				ajout_user($tab_new_user[$login], $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups, $DEBUG);
			}
		}
		else
			ajout_user($tab_new_user[0], $tab_checkbox_sem_imp, $tab_checkbox_sem_p, $tab_new_jours_an, $tab_new_solde, $checkbox_user_groups, $DEBUG);
	}
	else {
		affiche_formulaire_ajout_user($tab_new_user[0], $tab_new_jours_an, $tab_new_solde, $onglet, $DEBUG);
	}

	
/*********************************************************************************/
/*  FONCTIONS   */
/*********************************************************************************/


function ajout_user(&$tab_new_user, $tab_checkbox_sem_imp, $tab_checkbox_sem_p, &$tab_new_jours_an, &$tab_new_solde, $checkbox_user_groups, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if( $DEBUG )
	{
		echo "tab_new_jours_an = "; print_r($tab_new_jours_an) ; echo "<br>\n";
		echo "tab_new_solde = "; print_r($tab_new_solde) ; echo "<br>\n";
	}

	// si pas d'erreur de saisie :
	if( verif_new_param($tab_new_user, $tab_new_jours_an, $tab_new_solde, $DEBUG)==0)
	{
		echo $tab_new_user['login']."---".$tab_new_user['nom']."---".$tab_new_user['prenom']."---".$tab_new_user['quotite']."\n";
		echo "---".$tab_new_user['is_resp']."---".$tab_new_user['resp_login']."---".$tab_new_user['is_admin']."---".$tab_new_user['is_hr']."---".$tab_new_user['see_all']."---".$tab_new_user['email']."<br>\n";

		foreach($tab_new_jours_an as $id_cong => $jours_an)
		{
			echo $tab_new_jours_an[$id_cong]."---".$tab_new_solde[$id_cong]."<br>\n";
		}
		$new_date_deb_grille=$tab_new_user['new_year']."-".$tab_new_user['new_mois']."-".$tab_new_user['new_jour'];
		echo "$new_date_deb_grille<br>\n" ;

		/*****************************/
		/* INSERT dans conges_users  */
		if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
				$motdepasse = md5($tab_new_user['password1']);
		else
			$motdepasse = "none";
			

			
		$sql1 = "INSERT INTO conges_users SET ";
		$sql1=$sql1."u_login='".$tab_new_user['login']."', ";
		$sql1=$sql1."u_nom='".addslashes($tab_new_user['nom'])."', ";
		$sql1=$sql1."u_prenom='".addslashes($tab_new_user['prenom'])."', ";
		$sql1=$sql1."u_is_resp='".$tab_new_user['is_resp']."', ";
		
		if($tab_new_user['resp_login'] == 'no_resp')
			$sql1=$sql1."u_resp_login= NULL , ";
		else
			$sql1=$sql1."u_resp_login='". $tab_new_user['resp_login']."', ";
		
		
		$sql1=$sql1."u_is_admin='".$tab_new_user['is_admin']."', ";
		$sql1=$sql1."u_is_hr='".$tab_new_user['is_hr']."', ";
		$sql1=$sql1."u_see_all='".$tab_new_user['see_all']."', ";
		$sql1=$sql1."u_passwd='$motdepasse', ";
		$sql1=$sql1."u_quotite=".$tab_new_user['quotite'].",";
		$sql1=$sql1." u_email='".$tab_new_user['email']."' ";
		$result1 = \includes\SQL::query($sql1);


		/**********************************/
		/* INSERT dans conges_solde_user  */
		foreach($tab_new_jours_an as $id_cong => $jours_an)
		{
			$sql3 = "INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) ";
			$sql3 = $sql3. "VALUES ('".$tab_new_user['login']."' , $id_cong, ".$tab_new_jours_an[$id_cong].", ".$tab_new_solde[$id_cong].", 0) " ;
			$result3 = \includes\SQL::query($sql3);
		}


		/*****************************/
		/* INSERT dans conges_artt  */
		$list_colums_to_insert="a_login";
		$list_values_to_insert="'".$tab_new_user['login']."'";
		// on parcours le tableau des jours d'absence semaine impaire
		if($tab_checkbox_sem_imp!="") {
			while (list ($key, $val) = each ($tab_checkbox_sem_imp)) {
				//echo "$key => $val<br>\n";
				$list_colums_to_insert="$list_colums_to_insert, $key";
				$list_values_to_insert="$list_values_to_insert, '$val'";
			}
		}
		if($tab_checkbox_sem_p!="") {
			while (list ($key, $val) = each ($tab_checkbox_sem_p)) {
				//echo "$key => $val<br>\n";
				$list_colums_to_insert="$list_colums_to_insert, $key";
				$list_values_to_insert="$list_values_to_insert, '$val'";
			}
		}

		$sql2 = "INSERT INTO conges_artt ($list_colums_to_insert, a_date_debut_grille) VALUES ($list_values_to_insert, '$new_date_deb_grille')" ;
		$result2 = \includes\SQL::query($sql2);


		/***********************************/
		/* ajout du user dans ses groupes  */
		$result4=TRUE;
		if( ($_SESSION['config']['gestion_groupes']) && ($checkbox_user_groups!="") )
		{
			$result4=commit_modif_user_groups($tab_new_user['login'], $checkbox_user_groups, $DEBUG);
		}



		/*****************************/

		if($result1 && $result2 && $result3 && $result4)
			echo  _('form_modif_ok') ."<br><br> \n";
		else
			echo  _('form_modif_not_ok') ."<br><br> \n";

		$comment_log = "ajout_user : ".$tab_new_user['login']." / ".addslashes($tab_new_user['nom'])." ".addslashes($tab_new_user['prenom'])." (".$tab_new_user['quotite']." %)" ;
		log_action(0, "", $tab_new_user['login'], $comment_log, $DEBUG);

		/* APPEL D'UNE AUTRE PAGE */
		echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-users\" method=\"POST\"> \n";
		echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
		echo " </form> \n";
	}
}


function verif_new_param(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	foreach($tab_new_jours_an as $id_cong => $jours_an)
	{
		$valid=verif_saisie_decimal($tab_new_jours_an[$id_cong], $DEBUG);    //verif la bonne saisie du nombre décimal
		$valid=verif_saisie_decimal($tab_new_solde[$id_cong], $DEBUG);    //verif la bonne saisie du nombre décimal
	}
	if( $DEBUG )
	{
		echo "tab_new_jours_an = "; print_r($tab_new_jours_an) ; echo "<br>\n";
		echo "tab_new_solde = "; print_r($tab_new_solde) ; echo "<br>\n";
	}


	// verif des parametres reçus :
	// si on travaille avec la base dbconges, on teste tout, mais si on travaille avec ldap, on ne teste pas les champs qui viennent de ldap ...
	if( (!$_SESSION['config']['export_users_from_ldap'] &&
		(strlen($tab_new_user['nom'])==0 
			|| strlen($tab_new_user['prenom'])==0
			|| strlen($tab_new_user['password1'])==0 || strlen($tab_new_user['password2'])==0
			|| strcmp($tab_new_user['password1'], $tab_new_user['password2'])!=0 || strlen($tab_new_user['login'])==0
			|| strlen($tab_new_user['quotite'])==0
			|| $tab_new_user['quotite']>100)
			|| !preg_match('/^[a-z.\d_-]{2,20}$/i', $tab_new_user['login'])
			|| !preg_match('/^[a-z\d\sàáâãäåçèéêëìíîïðòóôõöùúûüýÿ-]{2,20}$/i', $tab_new_user['nom'])
			|| !preg_match('/^[a-z\d\sàáâãäåçèéêëìíîïðòóôõöùúûüýÿ-]{2,20}$/i', $tab_new_user['prenom'])
		) || ($_SESSION['config']['export_users_from_ldap']  && (strlen($tab_new_user['login'])==0 || strlen($tab_new_user['quotite'])==0 || $tab_new_user['quotite']>100)))
	{
		echo "<h3><font color=\"red\"> ". _('admin_verif_param_invalides') ." </font></h3>\n"  ;
		// affichage des param :
		echo $tab_new_user['login']."---".$tab_new_user['nom']."---".$tab_new_user['prenom']."---".$tab_new_user['quotite']."---".$tab_new_user['is_resp']."---".$tab_new_user['resp_login']."<br>\n";
		foreach($tab_new_jours_an as $id_cong => $jours_an)
		{
			echo $tab_new_jours_an[$id_cong]."---".$tab_new_solde[$id_cong]."<br>\n";
		}

		echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n"  ;
		echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
		echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
		echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
		echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
		echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
		echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
		echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
		echo "<input type=\"hidden\" name=\"new_see_all\" value=\"".$tab_new_user['see_all']."\">\n";
		echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
		echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";
		foreach($tab_new_jours_an as $id_cong => $jours_an)
		{
			echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
			echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
		}

		echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
		echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo"</form>\n" ;

		return 1;
	}
	else {

		// verif si le login demandé n'existe pas déjà ....
		$sql_verif='SELECT u_login FROM conges_users WHERE u_login="'.\includes\SQL::quote($tab_new_user['login']).'"';
		$ReqLog_verif = \includes\SQL::query($sql_verif);

		$num_verif = $ReqLog_verif->num_rows;
		if ($num_verif!=0)
		{
			echo "<h3><font color=\"red\"> ". _('admin_verif_login_exist') ." </font></h3>\n"  ;
			echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n"  ;
			echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
			echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
			echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
			echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
			echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
			echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";

			foreach($tab_new_jours_an as $id_cong => $jours_an)
			{
				echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
				echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
			}

			echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
			echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n" ;

			return 1;
		}
		elseif($_SESSION['config']['where_to_find_user_email'] == "dbconges" && strrchr($tab_new_user['email'], "@")==FALSE)
		{
			echo "<h3> ". _('admin_verif_bad_mail') ." </h3>\n" ;
			echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout-user\" method=\"POST\">\n" ;
			echo "<input type=\"hidden\" name=\"new_login\" value=\"".$tab_new_user['login']."\">\n";
			echo "<input type=\"hidden\" name=\"new_nom\" value=\"".$tab_new_user['nom']."\">\n";
			echo "<input type=\"hidden\" name=\"new_prenom\" value=\"".$tab_new_user['prenom']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_resp\" value=\"".$tab_new_user['is_resp']."\">\n";
			echo "<input type=\"hidden\" name=\"new_resp_login\" value=\"".$tab_new_user['resp_login']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_admin\" value=\"".$tab_new_user['is_admin']."\">\n";
			echo "<input type=\"hidden\" name=\"new_is_hr\" value=\"".$tab_new_user['is_hr']."\">\n";
			echo "<input type=\"hidden\" name=\"new_quotite\" value=\"".$tab_new_user['quotite']."\">\n";
			echo "<input type=\"hidden\" name=\"new_email\" value=\"".$tab_new_user['email']."\">\n";

			foreach($tab_new_jours_an as $id_cong => $jours_an)
			{
				echo "<input type=\"hidden\" name=\"tab_new_jours_an[$id_cong]\" value=\"".$tab_new_jours_an[$id_cong]."\">\n";
				echo "<input type=\"hidden\" name=\"tab_new_solde[$id_cong]\" value=\"".$tab_new_solde[$id_cong]."\">\n";
			}

			echo "<input type=\"hidden\" name=\"saisie_user\" value=\"faux\">\n";
			echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n" ;

			return 1;
		}
		else
			return 0;
	}
}




// affaichage du formulaire de saisie d'un nouveau user
function affiche_formulaire_ajout_user(&$tab_new_user, &$tab_new_jours_an, &$tab_new_solde, $onglet,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// recup du tableau des types de conges (seulement les conges)
	$tab_type_conges=recup_tableau_types_conges($DEBUG);

	// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
	if ($_SESSION['config']['gestion_conges_exceptionnels'])
	{
	  $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);
	}

	if( $DEBUG ) { echo "tab_type_conges = <br>\n"; print_r($tab_type_conges); echo "<br>\n"; }

	/*********************/
	/* Ajout Utilisateur */
	/*********************/

	// TITRE
	echo "<h1>" . _('admin_new_users_titre') . "</h1>\n";

	echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';

	/****************************************/
	// tableau des infos de user

	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
	echo "<thead>\n";
		echo "<tr>\n";
		if ($_SESSION['config']['export_users_from_ldap'] )
			echo "<th>". _('divers_nom_maj_1') ." ". _('divers_prenom_maj_1') ."</th>\n";
		else
		{
			echo "<th>". _('divers_login_maj_1') ."</th>\n";
			echo "<th>". _('divers_nom_maj_1') ."</th>\n";
			echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
		}
		echo "<th>". _('divers_quotite_maj_1') ."</th>\n";
		echo "<th>". _('admin_new_users_is_resp') ."</th>\n";
		echo "<th>". _('divers_responsable_maj_1') ."</th>\n";
		echo "<th>". _('admin_new_users_is_admin') ."</th>\n";
		echo "<th>". _('admin_new_users_is_hr') ."</th>\n";
		echo "<th>". _('admin_new_users_see_all') ."</th>\n";
		if ( !$_SESSION['config']['export_users_from_ldap'] )
			echo "<th>". _('admin_users_mail') ."</th>\n";
		if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
		{
			echo "<th>". _('admin_new_users_password') ."</th>\n";
			echo "<th>". _('admin_new_users_password') ."</th>\n";
		}
		echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	$text_nom="<input class=\"form-control\" type=\"text\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['nom']."\">" ;
	$text_prenom="<input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$tab_new_user['prenom']."\">" ;
	if( (!isset($tab_new_user['quotite'])) || ($tab_new_user['quotite']=="") )
		$tab_new_user['quotite']=100;
	$text_quotite="<input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$tab_new_user['quotite']."\">" ;
	$text_is_resp="<select class=\"form-control\" name=\"new_is_resp\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;

	// PREPARATION DES OPTIONS DU SELECT du resp_login
	$text_resp_login="<select class=\"form-control\" name=\"new_resp_login\" id=\"resp_login_id\" ><option value=\"no_resp\">". _('admin_users_no_resp') ."</option>" ;
	$sql2 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_is_resp = \"Y\" ORDER BY u_nom, u_prenom"  ;
	$ReqLog2 = \includes\SQL::query($sql2);

	while ($resultat2 = $ReqLog2->fetch_array()) {
		$current_resp_login=$resultat2["u_login"];
		if($tab_new_user['resp_login']==$current_resp_login)
			$text_resp_login=$text_resp_login."<option value=\"$current_resp_login\" selected>".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
		else
			$text_resp_login=$text_resp_login."<option value=\"$current_resp_login\">".$resultat2["u_nom"]." ".$resultat2["u_prenom"]."</option>";
	}
	$text_resp_login=$text_resp_login."</select>" ;

	$text_is_admin="<select class=\"form-control\" name=\"new_is_admin\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
	$text_is_hr="<select class=\"form-control\" name=\"new_is_hr\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
	$text_see_all="<select class=\"form-control\" name=\"new_see_all\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
	$text_email="<input class=\"form-control\" type=\"text\" name=\"new_email\" size=\"10\" maxlength=\"99\" value=\"".$tab_new_user['email']."\">" ;
	$text_password1="<input class=\"form-control\" type=\"password\" name=\"new_password1\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" >" ;
	$text_password2="<input class=\"form-control\" type=\"password\" name=\"new_password2\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" >" ;
	$text_login="<input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"98\" value=\"".$tab_new_user['login']."\">" ;


	// AFFICHAGE DE LA LIGNE DE SAISIE D'UN NOUVEAU USER

	echo "<tr class=\"update-line\">\n";
	// Aj. D.Chabaud - Université d'Auvergne - Sept. 2005
	if ($_SESSION['config']['export_users_from_ldap'] )
	{
		// Récupération de la liste des utilisateurs via un ldap :

		// on crée 2 tableaux (1 avec les noms + prénoms, 1 avec les login)
		// afin de pouvoir construire une liste déroulante dans le formulaire qui suit...
		$tab_ldap  = array();
		$tab_login = array();
		recup_users_from_ldap($tab_ldap, $tab_login, $DEBUG);

		// construction de la liste des users récupérés du ldap ...
		array_multisort($tab_ldap, $tab_login); // on trie les utilisateurs par le nom

		$lst_users = "<select multiple size=9 name=new_ldap_user[]><option>------------------</option>\n";
		$i = 0;

		foreach ($tab_login as $login)
		{
			$lst_users .= "<option value=$tab_login[$i]>$tab_ldap[$i]</option>\n";
			$i++;
		}
		$lst_users .= "</select>\n";
		echo "<td>$lst_users</td>\n";
	}
	else
	{
		echo "<td>$text_login</td>\n";
		echo "<td>$text_nom</td>\n";
		echo "<td>$text_prenom</td>\n";
	}

	echo "<td>$text_quotite</td>\n";
	echo "<td>$text_is_resp</td>\n";
	echo "<td>$text_resp_login</td>\n";
	echo "<td>$text_is_admin</td>\n";
	echo "<td>$text_is_hr</td>\n";
	echo "<td>$text_see_all</td>\n";
	if ( !$_SESSION['config']['export_users_from_ldap'] )
		echo "<td>$text_email</td>\n";
	if ($_SESSION['config']['how_to_connect_user'] == "dbconges")
	{
		echo "<td>$text_password1</td>\n";
		echo "<td>$text_password2</td>\n";
	}
	echo "</tr>\n";
	echo "</tbody>\n";
	echo "</table>\n";

	echo "<br>\n";


	/****************************************/
	//tableau des conges annuels et soldes

	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
	// ligne de titres
	echo "<thead>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th>". _('admin_new_users_nb_par_an') ."</th>\n";
		echo "<th>". _('divers_solde') ."</th>\n";
		echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";
	
	$i = true;
	// ligne de saisie des valeurs
	foreach($tab_type_conges as $id_type_cong => $libelle)
	{
		echo '<tr class="'.($i?'i':'p').'">';
		$value_jours_an = ( isset($tab_new_jours_an[$id_type_cong]) ? $tab_new_jours_an[$id_type_cong] : 0 );
		$value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
		$text_jours_an="<input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_jours_an\">" ;
		$text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
		echo "<td>$libelle</td>\n";
		echo "<td>$text_jours_an</td>\n";
		echo "<td>$text_solde_jours</td>\n";
		echo "</tr>\n";
		$i = !$i;
	}
	if ($_SESSION['config']['gestion_conges_exceptionnels']) {
	  foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
	  {
		echo '<tr class="'.($i?'i':'p').'">';
	    $value_solde_jours = ( isset($tab_new_solde[$id_type_cong]) ? $tab_new_solde[$id_type_cong] : 0 );
		$text_jours_an="<input type=\"hidden\" name=\"tab_new_jours_an[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"0\"> &nbsp; " ;
	    $text_solde_jours="<input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$id_type_cong]\" size=\"5\" maxlength=\"5\" value=\"$value_solde_jours\">" ;
	    echo "<td>$libelle</td>\n";
		echo "<td>$text_jours_an</td>\n";
	    echo "<td>$text_solde_jours</td>\n";
	    echo "</tr>\n";
		$i = !$i;
	  }
	}
	echo "</tbody>\n";
	echo "</table>\n";

	echo "<br>\n\n";

	// saisie de la grille des jours d'abscence ARTT ou temps partiel:
	saisie_jours_absence_temps_partiel($tab_new_user['login'],  $DEBUG);


    // si gestion des groupes :  affichage des groupe pour y affecter le user
    if($_SESSION['config']['gestion_groupes'])
    {
		echo "<br>\n";
		affiche_tableau_affectation_user_groupes("",  $DEBUG);
    }

	echo "<hr>\n";
	echo "<input type=\"hidden\" name=\"saisie_user\" value=\"ok\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;
}


function affiche_tableau_affectation_user_groupes($choix_user,  $DEBUG=FALSE)
{

	//AFFICHAGE DU TABLEAU DES GROUPES DU USER
	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";

	// affichage TITRE
	echo "<thead>\n";
	echo "<tr>\n";
	if($choix_user=="")
		echo "	<th colspan=3><h3>". _('admin_gestion_groupe_users_group_of_new_user') ." :</h3></th>\n";
	else
		echo "	<th colspan=3><h3>". _('admin_gestion_groupe_users_group_of_user') ." <b> $choix_user </b> :</h3></th>\n";

	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<th>&nbsp;</th>\n";
	echo "	<th>&nbsp;". _('admin_groupes_groupe') ."&nbsp;:</th>\n";
	echo "	<th>&nbsp;". _('admin_groupes_libelle') ."&nbsp;:</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	// affichage des groupes

	//on rempli un tableau de tous les groupes avec le nom et libellé (tableau de tableau à 3 cellules)
	$tab_groups=array();
	$sql_g = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename "  ;
	$ReqLog_g = \includes\SQL::query($sql_g);

	while($resultat_g=$ReqLog_g->fetch_array())
	{
		$tab_gg=array();
		$tab_gg["gid"]=$resultat_g["g_gid"];
		$tab_gg["groupename"]=$resultat_g["g_groupename"];
		$tab_gg["comment"]=$resultat_g["g_comment"];
		$tab_groups[]=$tab_gg;
	}

	$tab_user="";
	// si le user est connu
	// on rempli un autre tableau des groupes du user
	if($choix_user!="")
	{
		$tab_user=array();
		$sql_gu = 'SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'.\includes\SQL::quote($choix_user).'" ORDER BY gu_gid ';
		$ReqLog_gu = \includes\SQL::query($sql_gu);

		while($resultat_gu=$ReqLog_gu->fetch_array())
		{
			$tab_user[]=$resultat_gu["gu_gid"];
		}
	}

	// ensuite on affiche tous les groupes avec une case cochée si existe le gid dans le 2ieme tableau
	$count = count($tab_groups);
	for ($i = 0; $i < $count; $i++)
	{
		$gid=$tab_groups[$i]["gid"] ;
		$group=$tab_groups[$i]["groupename"] ;
		$libelle=$tab_groups[$i]["comment"] ;

		if ( ($tab_user!="") && (in_array ($gid, $tab_user)) )
		{
			$case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\" checked>";
			$class="histo-big";
		}
		else
		{
			$case_a_cocher="<input type=\"checkbox\" name=\"checkbox_user_groups[$gid]\" value=\"$gid\">";
			$class="histo";
		}

		echo '<tr class="'.(!($i%2)?'i':'p').'">';
		echo "	<td>$case_a_cocher</td>\n";
		echo "	<td class=\"$class\">&nbsp;$group&nbsp</td>\n";
		echo "	<td class=\"$class\">&nbsp;$libelle&nbsp;</td>\n";
		echo "</tr>\n";
	}

	echo "<tbody>\n";
	echo "</table>\n\n";
}

function recup_users_from_ldap(&$tab_ldap, &$tab_login, $DEBUG=FALSE)
{
	// cnx à l'annuaire ldap :
	$ds = ldap_connect($_SESSION['config']['ldap_server']);
	if($_SESSION['config']['ldap_protocol_version'] != 0)
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
	if ($_SESSION['config']['ldap_user'] == "")
		$bound = ldap_bind($ds);  // connexion anonyme au serveur
	else
		$bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

	// recherche des entrées :
	if ($_SESSION['config']['ldap_filtre_complet'] != "")
		$filter = $_SESSION['config']['ldap_filtre_complet'];
	else
		$filter = "(&(".$_SESSION['config']['ldap_nomaff']."=*)(".$_SESSION['config']['ldap_filtre']."=".$_SESSION['config']['ldap_filrech']."))";

	$sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
	$data = ldap_get_entries($ds,$sr);

	foreach ($data as $info)
	{
		$ldap_libelle_login=$_SESSION['config']['ldap_login'];
		$ldap_libelle_nom=$_SESSION['config']['ldap_nom'];
		$ldap_libelle_prenom=$_SESSION['config']['ldap_prenom'];
		$login = $info[$ldap_libelle_login][0];
		// concaténation NOM Prénom
		// utf8_decode permet de supprimer les caractères accentués mal interprêtés...
		$nom = ( isset($info[$ldap_libelle_nom]) ? strtoupper($info[$ldap_libelle_nom][0]): '' )." ". (isset($info[$ldap_libelle_prenom])?$info[$ldap_libelle_prenom][0]:'');
		array_push($tab_ldap, $nom);
		array_push($tab_login, $login);
	}
}
