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
namespace hr;

/**
 * Regroupement des fonctions liées au haut responsable
 */
class Fonctions
{
    /**
     * Encapsule le comportement du module de page principale
     *
     * @param array  $tab_type_cong
     * @param array  $tab_type_conges_exceptionnels
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pagePrincipaleModule(array $tab_type_cong, array $tab_type_conges_exceptionnels, $session, $DEBUG = false)
    {
        /***********************************/
        // AFFICHAGE ETAT CONGES TOUS USERS
        /***********************************/
        // AFFICHAGE TABLEAU (premiere ligne)
        echo '<h2>'. _('hr_traite_user_etat_conges') ."</H2>\n\n";
        echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
        echo '<thead>';
        echo '<tr>';
        echo '<th>'. _('divers_nom_maj') .'</th>';
        echo '<th>'. _('divers_prenom_maj') .'</th>';
        echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
        $nb_colonnes = 3;
        foreach($tab_type_cong as $id_conges => $libelle)
        {
            // cas d'une absence ou d'un congé
            echo "<th> $libelle"." / ". _('divers_an_maj') .'</th>';
            echo '<th>'. _('divers_solde_maj') ." ".$libelle .'</th>';
            $nb_colonnes += 2;
        }
        // conges exceptionnels
        if ($_SESSION['config']['gestion_conges_exceptionnels'])
        {
            foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
            {
                echo '<th>'. _('divers_solde_maj') ." $libelle</th>\n";
                $nb_colonnes += 1;
            }
        }
        echo '<th></th>';
        $nb_colonnes += 1;
        if($_SESSION['config']['editions_papier'])
        {
            echo '<th></th>';
            $nb_colonnes += 1;
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        /***********************************/
        // AFFICHAGE USERS
        /***********************************/
        // AFFICHAGE DE USERS DIRECTS DU RESP

        // Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
        $tab_all_users=recup_infos_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
        if( $DEBUG ) {echo "tab_all_users :<br>\n";  print_r($tab_all_users); echo "<br>\n"; }

        if(count($tab_all_users)==0) // si le tableau est vide (resp sans user !!) on affiche une alerte !
            echo "<tr><td class=\"histo\" colspan=\"".$nb_colonnes."\">". _('resp_etat_aucun_user') ."</td></tr>\n" ;
        else
        {
            //$i = true;
            foreach($tab_all_users as $current_login => $tab_current_user)
            {
                //tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
                $tab_conges=$tab_current_user['conges'];
                $text_affich_user="<a href=\"hr_index.php?session=$session&onglet=traite_user&user_login=$current_login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a>" ;
                $text_edit_papier="<a href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a>";
                if($tab_current_user['is_active'] == "Y" || $_SESSION['config']['print_disable_users'] == 'TRUE')
                    { echo '<tr>'; }
                else
                    { echo '<tr class="hidden">'; }
                echo '<td>'.$tab_current_user['nom']."</td><td>".$tab_current_user['prenom']."</td><td>".$tab_current_user['quotite']."%</td>";
                foreach($tab_type_cong as $id_conges => $libelle)
                {
                    $nbAn = isset($tab_conges[$libelle]['nb_an'])
                        ? $tab_conges[$libelle]['nb_an']
                        : 0;
                    $solde = isset($tab_conges[$libelle]['solde'])
                        ? $tab_conges[$libelle]['solde']
                        : 0;
                    echo '<td>'.$nbAn.'</td>';
                    echo '<td>'. $solde .'</td>';
                }
                if ($_SESSION['config']['gestion_conges_exceptionnels'])
                {
                    foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
                    {
                        $solde = isset($tab_conges[$libelle]['solde'])
                            ? $tab_conges[$libelle]['solde']
                            : 0;
                        echo '<td>' . $solde .'</td>';
                    }
                }
                echo "<td>$text_affich_user</td>\n";
                if($_SESSION['config']['editions_papier'])
                echo "<td>$text_edit_papier</td>";
                echo '</tr>';
                //$i = !$i;
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '<script>
        $(document).ready(function()
            {
            $("tr:not(.hidden):odd").css("background-color", "#F4F4F4");
            $("#display_hidden").click(function () {
                $(".hidden").slideToggle();
                });
            });
        </script>';
    }

    public static function traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();

    	while($elem_tableau = each($tab_bt_radio))
    	{
    		$champs             = explode("--", $elem_tableau['value']);
    		$user_login         = $champs[0];
    		$user_nb_jours_pris = $champs[1];
    		$type_abs           = $champs[2];   // id du type de conges demandé
    		$date_deb           = $champs[3];
    		$demi_jour_deb      = $champs[4];
    		$date_fin           = $champs[5];
    		$demi_jour_fin      = $champs[6];
    		$reponse            = $champs[7];

    		$numero             = $elem_tableau['key'];
    		$numero_int         = (int) $numero;
    		echo "$numero---$user_login---$user_nb_jours_pris---$reponse<br>\n";

    		/* Modification de la table conges_periode */
    		if(strcmp($reponse, "OK")==0)
    		{
    			/* UPDATE table "conges_periode" */
    			$sql1 = 'UPDATE conges_periode SET p_etat=\'ok\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );' ;
    			/* On valide l'UPDATE dans la table "conges_periode" ! */
    			$ReqLog1 = \includes\SQL::query($sql1) ;
    			if ($ReqLog1 && \includes\SQL::getVar('affected_rows') ) {

    				// Log de l'action
    				log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $reponse",  $DEBUG);

    				/* UPDATE table "conges_solde_user" (jours restants) */
    				soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);

    				//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
    				if($_SESSION['config']['mail_valid_conges_alerte_user'])
    					alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges",  $DEBUG);
    			}
    		}
    		elseif(strcmp($reponse, "not_OK")==0)
    		{
    			// recup du motif de refus
    			$motif_refus=addslashes($tab_text_refus[$numero_int]);
    			$sql1 = 'UPDATE conges_periode SET p_etat=\'refus\', p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'" AND ( p_etat=\'valid\' OR p_etat=\'demande\' );';

    			/* On valide l'UPDATE dans la table ! */
    			$ReqLog1 = \includes\SQL::query($sql1) ;
    			if ($ReqLog1 && \includes\SQL::getVar('affected_rows')) {

    				// Log de l'action
    				log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : refus",  $DEBUG);


    				//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
    				if($_SESSION['config']['mail_refus_conges_alerte_user'])
    					alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges",  $DEBUG);
    			}
    		}
    	}

    	if( $DEBUG )
    	{
    		echo "<form action=\"$PHP_SELF?sesssion=$session&onglet=traitement_demande\" method=\"POST\">\n" ;
    		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
    		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
    		echo "</form>\n" ;
    	}
    	else
    	{
    		echo  _('form_modif_ok') ."<br><br> \n";
    		/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
    		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&onglet=traitement_demandes\">";
    	}
    			//envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
    			if($_SESSION['config']['mail_refus_conges_alerte_user'])
    				alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges", $DEBUG);

    }

    public static function affiche_all_demandes_en_cours($tab_type_conges, $DEBUG=FALSE)
    {

    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;
    	$count1=0;
    	$count2=0;

    	$tab_type_all_abs = recup_tableau_tout_types_abs();

    	// recup du tableau des types de conges (seulement les conges exceptionnels)
    	$tab_type_conges_exceptionnels=array();
    	if ($_SESSION['config']['gestion_conges_exceptionnels'])
    		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($DEBUG);

    	/*********************************/
    	// Récupération des informations
    	/*********************************/

    	// Récup dans un tableau de tableau des informations de tous les users
    	$tab_all_users=recup_infos_all_users($DEBUG);
    	if( $DEBUG ) { echo "tab_all_users :<br>\n"; print_r($tab_all_users); echo "<br><br>\n";}

    	// si tableau des users du resp n'est pas vide
    	if( count($tab_all_users)!=0 )
    	{
    		// constitution de la liste (séparé par des virgules) des logins ...
    		$list_users="";
    		foreach($tab_all_users as $current_login => $tab_current_user)
    		{
    			if($list_users=="")
    				$list_users= "'$current_login'" ;
    			else
    				$list_users=$list_users.", '$current_login'" ;
    		}
    	}

    	/*********************************/




    	echo " <form action=\"$PHP_SELF?session=$session&onglet=traitement_demandes\" method=\"POST\"> \n" ;

    	/*********************************/
    	/* TABLEAU DES DEMANDES DES USERS*/
    	/*********************************/

    	// si tableau des users n'est pas vide :)
    	if( count($tab_all_users)!=0 )
    	{

    		// Récup des demandes en cours pour les users :
    		$sql1 = "SELECT p_num, p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement FROM conges_periode ";
    		$sql1=$sql1." WHERE p_etat =\"demande\" ";
    		$sql1=$sql1." AND p_login IN ($list_users) ";
    		$sql1=$sql1." ORDER BY p_num";

    		$ReqLog1 = \includes\SQL::query($sql1) ;

    		$count1 = $ReqLog1->num_rows;
    		if($count1!=0)
    		{
    			// AFFICHAGE TABLEAU DES DEMANDES EN COURS

    			echo "<h3>". _('resp_traite_demandes_titre_tableau_1') ."</h3>\n" ;
    			echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\">\n" ;
    			echo '<thead>' ;
    			echo '<tr>' ;
    			echo '<th>'. _('divers_nom_maj_1') ."<br>". _('divers_prenom_maj_1') .'</th>' ;
    			echo '<th>'. _('divers_quotite_maj_1') .'</th>' ;
    			echo "<th>". _('divers_type_maj_1') ."</th>\n" ;
    			echo '<th>'. _('divers_debut_maj_1') .'</th>' ;
    			echo '<th>'. _('divers_fin_maj_1') .'</th>' ;
    			echo '<th>'. _('divers_comment_maj_1') .'</th>' ;
    			echo '<th>'. _('resp_traite_demandes_nb_jours') .'</th>';
    			echo "<th>". _('divers_solde') ."</th>\n" ;
    			echo '<th>'. _('divers_accepter_maj_1') .'</th>' ;
    			echo '<th>'. _('divers_refuser_maj_1') .'</th>' ;
    			echo '<th>'. _('resp_traite_demandes_attente') .'</th>' ;
    			echo '<th>'. _('resp_traite_demandes_motif_refus') .'</th>' ;
    			if( $_SESSION['config']['affiche_date_traitement'] )
    			echo '<th>'. _('divers_date_traitement') .'</th>' ;
    			echo '</tr>';
    			echo '</thead>' ;
    			echo '<tbody>' ;
    			$i = true;
    			$tab_bt_radio=array();
    			while ($resultat1 = $ReqLog1->fetch_array())
    			{
    				/** sur la ligne ,   **/
    				/** le 1er bouton radio est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--OK"> */
    				/**  et le 2ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--not_OK"> */
    				/**  et le 3ieme est <input type="radio" name="tab_bt_radio[valeur de p_num]" value="[valeur de p_login]--[valeur p_nb_jours]--$type--RIEN"> */

    				$sql_p_date_deb         = $resultat1["p_date_deb"];
    				$sql_p_date_fin         = $resultat1["p_date_fin"];
    				$sql_p_date_deb_fr      = eng_date_to_fr($resultat1["p_date_deb"]);
    				$sql_p_date_fin_fr      = eng_date_to_fr($resultat1["p_date_fin"]);
    				$sql_p_demi_jour_deb    = $resultat1["p_demi_jour_deb"] ;
    				$sql_p_demi_jour_fin    = $resultat1["p_demi_jour_fin"] ;
    				$sql_p_commentaire      = $resultat1["p_commentaire"];
    				$sql_p_num              = $resultat1["p_num"];
    				$sql_p_login            = $resultat1["p_login"];
    				$sql_p_nb_jours         = affiche_decimal($resultat1["p_nb_jours"]);
    				$sql_p_type             = $resultat1["p_type"];
    				$sql_p_date_demande     = $resultat1["p_date_demande"];
    				$sql_p_date_traitement  = $resultat1["p_date_traitement"];

    				if($sql_p_demi_jour_deb=="am")
    					$demi_j_deb="mat";
    				else
    					$demi_j_deb="aprm";

    				if($sql_p_demi_jour_fin=="am")
    					$demi_j_fin="mat";
    				else
    					$demi_j_fin="aprm";

    				// on construit la chaine qui servira de valeur à passer dans les boutons-radio
    				$chaine_bouton_radio = "$sql_p_login--$sql_p_nb_jours--$sql_p_type--$sql_p_date_deb--$sql_p_demi_jour_deb--$sql_p_date_fin--$sql_p_demi_jour_fin";
    				$boutonradio1="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--OK\">";
    				$boutonradio2="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--not_OK\">";
    				$boutonradio3="<input type=\"radio\" name=\"tab_bt_radio[$sql_p_num]\" value=\"$chaine_bouton_radio--RIEN\" checked>";
    				$text_refus="<input class=\"form-control\" type=\"text\" name=\"tab_text_refus[$sql_p_num]\" size=\"20\" max=\"100\">";

    				echo '<tr class="'.($i?'i':'p').'">';
    				echo "<td><b>".$tab_all_users[$sql_p_login]['nom']."</b><br>".$tab_all_users[$sql_p_login]['prenom']."</td><td>".$tab_all_users[$sql_p_login]['quotite']."%</td>";
    				echo "<td>".$tab_type_all_abs[$sql_p_type]['libelle']."</td>\n";
    				echo "<td>$sql_p_date_deb_fr <span class=\"demi\">$demi_j_deb</span></td><td>$sql_p_date_fin_fr <span class=\"demi\">$demi_j_fin</span></td><td>$sql_p_commentaire</td><td><b>$sql_p_nb_jours</b></td>";
    				$tab_conges=$tab_all_users[$sql_p_login]['conges'];
    				echo "<td>".$tab_conges[$tab_type_all_abs[$sql_p_type]['libelle']]['solde']."</td>";
    				// foreach($tab_type_conges as $id_conges => $libelle)
    				// {
    				// 	echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
    				// }

    				// if ($_SESSION['config']['gestion_conges_exceptionnels'])
    				// 	foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
    				// 	{
    				// 		echo '<td>'.$tab_conges[$libelle]['solde'].'</td>';
    				// 	}
    				// echo '<td>'.$tab_type_all_abs[$sql_p_type]['libelle'].'</td>';
    				echo "<td>$boutonradio1</td><td>$boutonradio2</td><td>$boutonradio3</td><td>$text_refus</td>\n";
    				if($_SESSION['config']['affiche_date_traitement'])
    				{
    					if($sql_p_date_demande == NULL)
    						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
    					else
    						echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
    				}
    				echo '</tr>' ;
    				$i = !$i;
    			} // while
    			echo '</tbody>' ;
    			echo '</table>' ;
    		} //if($count1!=0)
    	} //if( count($tab_all_users)!=0 )

    	echo "<br>\n";

    	if(($count1==0) && ($count2==0))
    		echo "<strong>". _('resp_traite_demandes_aucune_demande') ."</strong>\n";
    	else {
    		echo "<hr/>\n";
    		echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n" ;
    	}
    	echo " </form> \n" ;
    }

    /**
     * Encapsule le comportement du module de traitement des demandes
     *
     * @param array  $tab_type_cong
     * @param string $onglet
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraitementDemandeModule(array $tab_type_cong, $onglet, $session, $DEBUG = false)
    {


        //var pour resp_traite_demande_all.php
        $tab_bt_radio   = getpost_variable('tab_bt_radio');
        $tab_text_refus = getpost_variable('tab_text_refus');

        // titre
        echo '<h2>'. _('resp_traite_demandes_titre') .'</h2>';

        // si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
        if( $tab_bt_radio == '' ) {
            \hr\Fonctions::affiche_all_demandes_en_cours($tab_type_cong, $DEBUG);
        } else
    	{
    		\hr\Fonctions::traite_all_demande_en_cours($tab_bt_radio, $tab_text_refus, $DEBUG);
    		redirect( ROOT_PATH .'hr/hr_index.php?session='.$session.'&onglet='.$onglet, false);
    		exit;
    	}
    }

    public static function new_conges($user_login, $numero_int, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $session=session_id();

    	$new_debut = convert_date($new_debut);
    	$new_fin = convert_date($new_fin);

        // verif validité des valeurs saisies
        $valid=verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

        if($valid)
        {
            echo "$user_login---$new_debut _ $new_demi_jour_deb---$new_fin _ $new_demi_jour_fin---$new_nb_jours---$new_comment---$new_type_id<br>\n";

            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

            /**********************************/
            /* insert dans conges_periode     */
            /**********************************/
            $new_etat="ok";
            $result=insert_dans_periode($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $new_etat, 0,$DEBUG);

            /************************************************/
            /* UPDATE table "conges_solde_user" (jours restants) */
            // on retranche les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on annule est un "conges"
            if($tab_tout_type_abs[$new_type_id]['type']=="conges")
            {
                $user_nb_jours_pris_float=(float) $new_nb_jours ;
                soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $new_type_id, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin , $DEBUG);
            }

            $comment_log = "saisie conges par le responsable pour $user_login ($new_nb_jours jour(s)) type_conges = $new_type_id ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
            log_action(0, "", $user_login, $comment_log, $DEBUG);

            if($result)
                echo  _('form_modif_ok') ."<br><br> \n";
            else
                echo  _('form_modif_not_ok') ."<br><br> \n";
        }
        else
        {
                echo  _('resp_traite_user_valeurs_not_ok') ."<br><br> \n";
        }

        /* APPEL D'UNE AUTRE PAGE */
        echo "<form action=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
        echo "</form> \n";

    }

    public static function traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

        while($elem_tableau = each($tab_radio_traite_demande))
        {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $user_nb_jours_pris_float=(float) $user_nb_jours_pris ;
            $value_type_abs_id=$champs[2];
            $date_deb=$champs[3];
            $demi_jour_deb=$champs[4];
            $date_fin=$champs[5];
            $demi_jour_fin=$champs[6];
            $reponse=$champs[7];
            $value_traite=$champs[3];

            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            if( $DEBUG ) { echo "<br><br>conges numero :$numero --- User_login : $user_login --- nb de jours : $user_nb_jours_pris --->$value_traite<br>" ; }

            if($reponse == "ACCEPTE") // acceptation definitive d'un conges
            {
                /* UPDATE table "conges_periode" */
                $sql1 = "UPDATE conges_periode SET p_etat=\"ok\", p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog1 = \includes\SQL::query($sql1);

                // Log de l'action
                log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                /* UPDATE table "conges_solde_user" (jours restants) */
                // on retranche les jours seulement pour des conges pris (pas pour les absences)
                // donc seulement si le type de l'absence qu'on annule est un "conges"
                if( $DEBUG ) { echo "type_abs = ".$tab_tout_type_abs[$value_type_abs_id]['type']."<br>\n" ; }
                if(($tab_tout_type_abs[$value_type_abs_id]['type']=="conges")||($tab_tout_type_abs[$value_type_abs_id]['type']=="conges_exceptionnels"))
                {
                    soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $value_type_abs_id, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);
                }

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_valid_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges", $DEBUG);
            }
            elseif($reponse == "VALID") // première validation dans le cas d'une double validation
            {
                /* UPDATE table "conges_periode" */
                $sql1 = "UPDATE conges_periode SET p_etat=\"valid\", p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog1 = \includes\SQL::query($sql1);

                // Log de l'action
                log_action($numero_int,"valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_valid_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges", $DEBUG);
            }
            elseif($reponse == "REFUSE") // refus d'un conges
            {
                // recup di motif de refus
                $motif_refus=addslashes($tab_text_refus[$numero_int]);
                $sql3 = "UPDATE conges_periode SET p_etat=\"refus\", p_motif_refus='$motif_refus', p_date_traitement=NOW() WHERE p_num=$numero_int" ;
                $ReqLog3 = \includes\SQL::query($sql3);

                // Log de l'action
                log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite", $DEBUG);

                //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
                if($_SESSION['config']['mail_refus_conges_alerte_user'])
                    alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges", $DEBUG);
            }
        }

        if( $DEBUG )
        {
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
            echo "</form>\n" ;
        }
        else
        {
            echo  _('form_modif_ok') ."<br><br> \n";
            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
        }
    }

    public static function annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // recup dans un tableau de tableau les infos des types de conges et absences
        $tab_tout_type_abs = recup_tableau_tout_types_abs($DEBUG);

        while($elem_tableau = each($tab_checkbox_annule))
        {
            $champs = explode("--", $elem_tableau['value']);
            $user_login=$champs[0];
            $user_nb_jours_pris=$champs[1];
            $user_nb_jours_pris_float=(float) $user_nb_jours_pris ;
            $numero=$elem_tableau['key'];
            $numero_int=(int) $numero;
            $user_type_abs_id=$champs[2];

            $motif_annul=addslashes($tab_text_annul[$numero_int]);

            if( $DEBUG ) { echo "<br><br>conges numero :$numero ---> login : $user_login --- nb de jours : $user_nb_jours_pris_float --- type : $user_type_abs_id ---> ANNULER <br>"; }

            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="annul", p_motif_refus="'.\includes\SQL::quote($motif_annul).'", p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" ';
            $ReqLog1 = \includes\SQL::query($sql1);

            // Log de l'action
            log_action($numero_int,"annul", $user_login, "annulation conges $numero ($user_login) ($user_nb_jours_pris jours)", $DEBUG);

            /* UPDATE table "conges_solde_user" (jours restants) */
            // on re-crédite les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on annule est un "conges"
            if($tab_tout_type_abs[$user_type_abs_id]['type']=="conges")
            {
                $sql2 = 'UPDATE conges_solde_user SET su_solde = su_solde+"'. \includes\SQL::quote($user_nb_jours_pris_float).'" WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_id="'. \includes\SQL::quote($user_type_abs_id).'";';
                $ReqLog2 = \includes\SQL::query($sql2);
            }

            //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
            if($_SESSION['config']['mail_annul_conges_alerte_user'])
                alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "annul_conges", $DEBUG);
        }

        if( $DEBUG )
        {
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
            echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
            echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
            echo "</form>\n" ;
        }
        else
        {
            echo  _('form_modif_ok') ."<br><br> \n";
            /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
        }
    }

    //affiche l'état des conges du user (avec le formulaire pour le responsable)
    public static function affiche_etat_conges_user_for_resp($user_login, $year_affichage, $tri_date, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // affichage de l'année et des boutons de défilement
        $year_affichage_prec = $year_affichage-1 ;
        $year_affichage_suiv = $year_affichage+1 ;

        echo "<b>";
        echo "<a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_prec\"><<</a>";
        echo "&nbsp&nbsp&nbsp  $year_affichage &nbsp&nbsp&nbsp";
        echo "<a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_suiv\">>></a>";
        echo "</b><br><br>\n";


        // Récupération des informations de speriodes de conges/absences
        $sql3 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num FROM conges_periode " .
                "WHERE p_login = '$user_login' " .
                "AND p_etat !='demande' " .
                "AND p_etat !='valid' " .
                "AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";
        if($tri_date=="descendant")
            $sql3=$sql3." ORDER BY p_date_deb DESC ";
        else
            $sql3=$sql3." ORDER BY p_date_deb ASC ";

        $ReqLog3 = \includes\SQL::query($sql3);

        $count3=$ReqLog3->num_rows;
        if($count3==0)
        {
            echo "<b>". _('resp_traite_user_aucun_conges') ."</b><br><br>\n";
        }
        else
        {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_types_abs = recup_tableau_tout_types_abs($DEBUG) ;

            // AFFICHAGE TABLEAU
            echo "<form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo '<thead>';
                echo '<tr>';
                    echo " <th>\n";
                    echo " <a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&tri_date=descendant\"><img src=\"". TEMPLATE_PATH ."img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
                    echo " ". _('divers_debut_maj_1') ." \n";
                    echo " <a href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&tri_date=ascendant\"><img src=\"". TEMPLATE_PATH ."img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
                    echo " </th>\n";
                    echo " <th>". _('divers_fin_maj_1') .'</th>';
                    echo " <th>". _('divers_nb_jours_pris_maj_1') .'</th>';
                    echo " <th>". _('divers_comment_maj_1') ."<br><i>". _('resp_traite_user_motif_possible') ."</i></th>\n";
                    echo " <th>". _('divers_type_maj_1') .'</th>';
                    echo " <th>". _('divers_etat_maj_1') .'</th>';
                    echo " <th>". _('resp_traite_user_annul') .'</th>';
                    echo " <th>". _('resp_traite_user_motif_annul') .'</th>';
                    if( $_SESSION['config']['affiche_date_traitement'] )
                        echo '<th>'. _('divers_date_traitement') .'</th>' ;
                echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat3 = $ReqLog3->fetch_array() )
            {
                    $sql_date_deb           = eng_date_to_fr($resultat3["p_date_deb"]) ;
                    $sql_date_fin           = eng_date_to_fr($resultat3["p_date_fin"]) ;
                    $sql_demi_jour_deb      = $resultat3["p_demi_jour_deb"] ;
                    $sql_demi_jour_fin      = $resultat3["p_demi_jour_fin"] ;

                    $sql_login              = $resultat3["p_login"] ;
                    $sql_nb_jours           = affiche_decimal($resultat3["p_nb_jours"]) ;
                    $sql_commentaire        = $resultat3["p_commentaire"] ;
                    $sql_type               = $resultat3["p_type"] ;
                    $sql_etat               = $resultat3["p_etat"] ;
                    $sql_motif_refus        = $resultat3["p_motif_refus"] ;
                    $sql_p_date_demande     = $resultat3["p_date_demande"];
                    $sql_p_date_traitement  = $resultat3["p_date_traitement"];
                    $sql_num                = $resultat3["p_num"] ;

                    if($sql_demi_jour_deb=="am")
                        $demi_j_deb =  _('divers_am_short') ;
                    else
                        $demi_j_deb =  _('divers_pm_short') ;

                    if($sql_demi_jour_fin=="am")
                        $demi_j_fin =  _('divers_am_short') ;
                    else
                        $demi_j_fin =  _('divers_pm_short') ;

                    if(($sql_etat=="annul") || ($sql_etat=="refus") || ($sql_etat=="ajout"))
                    {
                        $casecocher1="";
                        if($sql_etat=="refus")
                        {
                            if($sql_motif_refus=="")
                                $sql_motif_refus =  _('divers_inconnu')  ;
                            $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                        }
                        elseif($sql_etat=="annul")
                        {
                            if($sql_motif_refus=="")
                                $sql_motif_refus =  _('divers_inconnu')  ;
                            $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                        }
                        elseif($sql_etat=="ajout")
                        {
                            $text_annul="&nbsp;";
                        }
                    }
                    else
                    {
                        $casecocher1=sprintf("<input type=\"checkbox\" name=\"tab_checkbox_annule[$sql_num]\" value=\"$sql_login--$sql_nb_jours--$sql_type--ANNULE\">");
                        $text_annul="<input type=\"text\" name=\"tab_text_annul[$sql_num]\" size=\"20\" max=\"100\">";
                    }

                    echo '<tr class="'.($i?'i':'p').'">';
                        echo "<td>$sql_date_deb _ $demi_j_deb</td>\n";
                        echo "<td>$sql_date_fin _ $demi_j_fin</td>\n";
                        echo "<td>$sql_nb_jours</td>\n";
                        echo "<td>$sql_commentaire</td>\n";
                        echo '<td>'.$tab_types_abs[$sql_type]['libelle'].'</td>';
                        echo '<td>';
                        if($sql_etat=="refus")
                            echo  _('divers_refuse') ;
                        elseif($sql_etat=="annul")
                            echo  _('divers_annule') ;
                        else
                            echo "$sql_etat";
                        echo '</td>';
                        echo "<td>$casecocher1</td>\n";
                        echo "<td>$text_annul</td>\n";

                        if($_SESSION['config']['affiche_date_traitement'])
                        {
                            if(empty($sql_p_date_demande))
                             echo "<td class=\"histo-left\">". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                            else
                                echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                        }
                        echo '</tr>';
                        $i = !$i;
                }
            echo '</tbody>';
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">\n";
            echo " </form> \n";
        }
    }

    //affiche l'état des demande en attente de 2ieme validation du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_2_valid_user_for_resp($user_login, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='valid' ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0)
        {
            echo "<b>". _('resp_traite_user_aucune_demande') ."</b><br><br>\n";
        }
        else
        {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            echo " <form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo "<thead>\n";
            echo '<tr>';
            echo '<th>'. _('divers_debut_maj_1') .'</th>';
            echo '<th>'. _('divers_fin_maj_1') .'</th>';
            echo '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
            echo '<th>'. _('divers_comment_maj_1') .'</th>';
            echo '<th>'. _('divers_type_maj_1') .'</th>';
            echo '<th>'. _('divers_accepter_maj_1') .'</th>';
            echo '<th>'. _('divers_refuser_maj_1') .'</th>';
            echo '<th>'. _('resp_traite_user_motif_refus') .'</th>';
            if($_SESSION['config']['affiche_date_traitement'])
            {
                echo '<th>'. _('divers_date_traitement') .'</th>' ;
            }
            echo '</tr>';
            echo "</thead>\n";
            echo "<tbody>\n";

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array() )
            {
                $sql_date_deb = $resultat2["p_date_deb"];
                $sql_date_deb_fr = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat2["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am")
                    $demi_j_deb =  _('divers_am_short') ;
                else
                    $demi_j_deb =  _('divers_pm_short') ;
                $sql_date_fin = $resultat2["p_date_fin"];
                $sql_date_fin_fr = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat2["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am")
                    $demi_j_fin =  _('divers_am_short') ;
                else
                    $demi_j_fin =  _('divers_pm_short') ;
                $sql_nb_jours=affiche_decimal($resultat2["p_nb_jours"]) ;
                $sql_commentaire=$resultat2["p_commentaire"] ;
                $sql_type=$resultat2["p_type"] ;
                $sql_date_demande = $resultat2["p_date_demande"];
                $sql_date_traitement = $resultat2["p_date_traitement"];
                $sql_num=$resultat2["p_num"] ;

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";


                $casecocher1 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                $casecocher2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";
                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                echo '<tr class="'.($i?'i':'p').'">';
                echo "<td>$sql_date_deb_fr _ $demi_j_deb</td>\n";
                echo "<td>$sql_date_fin_fr _ $demi_j_fin</td>\n";
                echo "<td>$sql_nb_jours</td>\n";
                echo "<td>$sql_commentaire</td>\n";
                echo '<td>'.$tab_type_all_abs[$sql_type]['libelle'].'</td>';
                echo "<td>$casecocher1</td>\n";
                echo "<td>$casecocher2</td>\n";
                echo "<td>$text_refus</td>\n";
                if($_SESSION['config']['affiche_date_traitement'])
                {
                    echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
                }

                echo '</tr>';
                $i = !$i;
            }
            echo "</tbody>\n";
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
            echo " </form> \n";
        }
    }

    //affiche l'état des demande du user (avec le formulaire pour le responsable)
    public static function affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id() ;

        // Récupération des informations
        $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
                "FROM conges_periode " .
                "WHERE p_login = '$user_login' AND p_etat ='demande' ".
                "ORDER BY p_date_deb";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if($count2==0)
        {
            echo "<b>". _('resp_traite_user_aucune_demande') ."</b><br><br>\n";
        }
        else
        {
            // recup dans un tableau des types de conges
            $tab_type_all_abs = recup_tableau_tout_types_abs();

            // AFFICHAGE TABLEAU
            echo " <form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
            echo "<table cellpadding=\"2\" class=\"tablo\">\n";
            echo '<thead>';
                echo '<tr>';
                    echo '<th>'. _('divers_debut_maj_1') .'</th>';
                    echo '<th>'. _('divers_fin_maj_1') .'</th>';
                    echo '<th>'. _('divers_nb_jours_pris_maj_1') .'</th>';
                    echo '<th>'. _('divers_comment_maj_1') .'</th>';
                    echo '<th>'. _('divers_type_maj_1') .'</th>';
                    echo '<th>'. _('divers_accepter_maj_1') .'</th>';
                    echo '<th>'. _('divers_refuser_maj_1') .'</th>';
                    echo '<th>'. _('resp_traite_user_motif_refus') .'</th>';
                    if( $_SESSION['config']['affiche_date_traitement'] )
                        echo '<th>'. _('divers_date_traitement') .'</th>' ;
                echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array())
            {
                $sql_date_deb       = $resultat2["p_date_deb"];
                $sql_date_fin       = $resultat2["p_date_fin"];
                $sql_date_deb_fr    = eng_date_to_fr($resultat2["p_date_deb"]) ;
                $sql_date_fin_fr    = eng_date_to_fr($resultat2["p_date_fin"]) ;
                $sql_demi_jour_deb  = $resultat2["p_demi_jour_deb"] ;
                $sql_demi_jour_fin  = $resultat2["p_demi_jour_fin"] ;

                $sql_nb_jours       = affiche_decimal($resultat2["p_nb_jours"]) ;
                $sql_commentaire    = $resultat2["p_commentaire"] ;
                $sql_type           = $resultat2["p_type"] ;
                $sql_date_demande   = $resultat2["p_date_demande"];
                $sql_date_traitement= $resultat2["p_date_traitement"];
                $sql_num            = $resultat2["p_num"] ;


                if($sql_demi_jour_deb=="am")
                    $demi_j_deb =  _('divers_am_short') ;
                else
                    $demi_j_deb =  _('divers_pm_short') ;
                if($sql_demi_jour_fin=="am")
                    $demi_j_fin =  _('divers_am_short') ;
                else
                    $demi_j_fin =  _('divers_pm_short') ;

                // on construit la chaine qui servira de valeur à passer dans les boutons-radio
                $chaine_bouton_radio = "$user_login--$sql_nb_jours--$sql_type--$sql_date_deb--$sql_demi_jour_deb--$sql_date_fin--$sql_demi_jour_fin";

                // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
                if($tab_user['double_valid'] == "Y")
                {
                    /*******************************/
                    /* verif si le resp est grand_responsable pour ce user*/
                    if(in_array($_SESSION['userlogin'], $tab_grd_resp)) // si resp_login est dans le tableau
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--VALID\">";
                    else
                        $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                }
                else
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";

                $boutonradio2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";

                $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

                echo '<tr class="'.($i?'i':'p').'">';
                    echo "<td>$sql_date_deb_fr _ $demi_j_deb</td>\n";
                    echo "<td>$sql_date_fin_fr _ $demi_j_fin</td>\n";
                    echo "<td>$sql_nb_jours</td>\n";
                    echo "<td>$sql_commentaire</td>\n";
                    echo '<td>'.$tab_type_all_abs[$sql_type]['libelle'].'</td>';
                    echo "<td>$boutonradio1</td>\n";
                    echo "<td>$boutonradio2</td>\n";
                    echo "<td>$text_refus</td>\n";
                    if( $_SESSION['config']['affiche_date_traitement'] )
                    {
                        if($sql_date_traitement==NULL)
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                        else
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
                    }

                echo '</tr>';
                $i = !$i;
            }
            echo '</tbody>';
            echo '</table>';

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<br><input type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
            echo " </form> \n";
        }
    }

    public static function affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet, $DEBUG)
    {
        $PHP_SELF=$_SERVER['PHP_SELF']; ;
        $session=session_id();

        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        if(!isset($_SESSION["tab_j_feries"]))
        {
            init_tab_jours_feries();
        }

        /********************/
        /* Récupération des informations sur le user : */
        /********************/
        $list_group_dbl_valid_du_resp = get_list_groupes_double_valid_du_resp($_SESSION['userlogin'], $DEBUG);
        $tab_user=array();
        $tab_user = recup_infos_du_user($user_login, $list_group_dbl_valid_du_resp, $DEBUG);
        if( $DEBUG ) { echo"tab_user =<br>\n"; print_r($tab_user); echo "<br>\n"; }

        $list_all_users_du_hr=get_list_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
        if( $DEBUG ) { echo"list_all_users_du_hr = $list_all_users_du_hr<br>\n"; }

        // recup des grd resp du user
        $tab_grd_resp=array();
        if($_SESSION['config']['double_validation_conges'])
        {
            get_tab_grd_resp_du_user($user_login, $tab_grd_resp, $DEBUG);
            if( $DEBUG ) { echo"tab_grd_resp =<br>\n"; print_r($tab_grd_resp); echo "<br>\n"; }
        }

    	include ROOT_PATH .'fonctions_javascript.php' ;
        /********************/
        /* Titre */
        /********************/
        echo '<h2>'. _('resp_traite_user_titre') ." ".$tab_user['prenom']." ".$tab_user['nom'].".</H2>\n\n";


        /********************/
        /* Bilan des Conges */
        /********************/
        // AFFICHAGE TABLEAU
        // affichage du tableau récapitulatif des solde de congés d'un user
        affiche_tableau_bilan_conges_user($user_login);
        echo "<br><br>\n";

        /*************************/
        /* SAISIE NOUVEAU CONGES */
        /*************************/
        // dans le cas ou les users ne peuvent pas saisir de demande, le responsable saisi les congès :
        if(($_SESSION['config']['user_saisie_demande']==FALSE)||($_SESSION['config']['resp_saisie_mission']))
        {

            // si les mois et année ne sont pas renseignés, on prend ceux du jour
            if($year_calendrier_saisie_debut==0)
                $year_calendrier_saisie_debut=date("Y");
            if($mois_calendrier_saisie_debut==0)
                $mois_calendrier_saisie_debut=date("m");
            if($year_calendrier_saisie_fin==0)
                $year_calendrier_saisie_fin=date("Y");
            if($mois_calendrier_saisie_fin==0)
                $mois_calendrier_saisie_fin=date("m");
            if( $DEBUG ) { echo "$mois_calendrier_saisie_debut  $year_calendrier_saisie_debut  -  $mois_calendrier_saisie_fin  $year_calendrier_saisie_fin<br>\n"; }

            echo "<H3>". _('resp_traite_user_new_conges') ."</H3>\n\n";

            saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);

            echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
        }

        /*********************/
        /* Etat des Demandes */
        /*********************/
        if($_SESSION['config']['user_saisie_demande'])
        {
            //verif si le user est bien un user du resp (et pas seulement du grad resp)
            if(strstr($list_all_users_du_hr, "'$user_login'")!=FALSE)
            {
                echo "<h3>". _('resp_traite_user_etat_demandes') ."</h3>\n";

                //affiche l'état des demande du user (avec le formulaire pour le responsable)
                \hr\Fonctions::affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp, $DEBUG);

                echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
            }
        }

        /*********************/
        /* Etat des Demandes en attente de 2ieme validation */
        /*********************/
        if($_SESSION['config']['double_validation_conges'])
        {
            /*******************************/
            /* verif si le resp est grand_responsable pour ce user*/

            if(in_array($_SESSION['userlogin'], $tab_grd_resp)) // si resp_login est dans le tableau
            {
                echo "<h3>". _('resp_traite_user_etat_demandes_2_valid') ."</h3>\n";

                //affiche l'état des demande en attente de 2ieme valid du user (avec le formulaire pour le responsable)
                affiche_etat_demande_2_valid_user_for_resp($user_login, $DEBUG);

                echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";
            }
        }

        /*******************/
        /* Etat des Conges */
        /*******************/
        echo "<h3>". _('resp_traite_user_etat_conges') ."</h3>\n";

        //affiche l'état des conges du user (avec le formulaire pour le responsable)
        \hr\Fonctions::affiche_etat_conges_user_for_resp($user_login,  $year_affichage, $tri_date, $DEBUG);

        //echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";


        echo "<td valign=\"middle\">\n";
        echo "</td></tr></table>\n";
        echo "<center>\n";
    }

    /**
     * Encapsule le comportement du module de traitement des utilisateurs
     *
     * @param string $onglet
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageTraiteUserModule($onglet, $DEBUG = false)
    {
        //var pour hr_traite_user.php
        $user_login                 = getpost_variable('user_login') ;
        $tab_checkbox_annule        = getpost_variable('tab_checkbox_annule') ;
        $tab_radio_traite_demande   = getpost_variable('tab_radio_traite_demande') ;
        $new_demande_conges         = getpost_variable('new_demande_conges', 0) ;

        // si une annulation de conges a été selectionée :
        if( $tab_checkbox_annule != '' ) {
        	$tab_text_annul         = getpost_variable('tab_text_annul') ;
        	\hr\Fonctions::annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul, $DEBUG);
        }
        // si le traitement des demandes a été selectionée :
        elseif( $tab_radio_traite_demande != '' ) {
        	$tab_text_refus         = getpost_variable('tab_text_refus') ;
        	\hr\Fonctions::traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus, $DEBUG);
        }
        // si un nouveau conges ou absence a été saisi pour un user :
        elseif( $new_demande_conges == 1 ) {
        	$new_debut          = getpost_variable('new_debut') ;
        	$new_demi_jour_deb  = getpost_variable('new_demi_jour_deb') ;
        	$new_fin            = getpost_variable('new_fin') ;
        	$new_demi_jour_fin  = getpost_variable('new_demi_jour_fin') ;
        	$new_comment        = getpost_variable('new_comment') ;
        	$new_type           = getpost_variable('new_type') ;

        	if( $_SESSION['config']['disable_saise_champ_nb_jours_pris'] ) {
        		$new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $comment,  $DEBUG);
        		if ($new_nb_jours <= 0 )
        			$new_nb_jours      = getpost_variable('new_nb_jours');
        	}
        	else {
        		$new_nb_jours   = getpost_variable('new_nb_jours') ;
        	}

        	\hr\Fonctions::new_conges($user_login, $numero_int, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $DEBUG);
        }
        else {
        	$year_calendrier_saisie_debut   = getpost_variable('year_calendrier_saisie_debut', 0) ;
        	$mois_calendrier_saisie_debut   = getpost_variable('mois_calendrier_saisie_debut', 0) ;
        	$year_calendrier_saisie_fin     = getpost_variable('year_calendrier_saisie_fin', 0) ;
        	$mois_calendrier_saisie_fin     = getpost_variable('mois_calendrier_saisie_fin', 0) ;
        	$tri_date                       = getpost_variable('tri_date', "ascendant") ;
        	$year_affichage                 = getpost_variable('year_affichage' , date("Y") );

        	\hr\Fonctions::affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $onglet, $DEBUG);
        }
    }

    // recup de la liste de tous les groupes pour le mode RH
    public static function get_list_groupes_pour_rh($user_login, $DEBUG=FALSE)
    {
    	$list_group="";

    	$sql1="SELECT g_gid FROM conges_groupe ORDER BY g_gid";
    	$ReqLog1 = \includes\SQL::query($sql1);

    	if($ReqLog1->num_rows != 0)
    	{
    		while ($resultat1 = $ReqLog1->fetch_array())
    		{
    			$current_group=$resultat1["g_gid"];
    			if($list_group=="")
    				$list_group="$current_group";
    			else
    				$list_group=$list_group.", $current_group";
    		}
    	}
    	if( $DEBUG ) { echo "list_group = $list_group<br>\n" ;}

    	return $list_group;
    }

    // on insert l'ajout de conges dans la table periode
    public static function insert_ajout_dans_periode($DEBUG, $login, $nb_jours, $id_type_abs, $commentaire)
    {
    	$date_today=date("Y-m-d");

    	$result=insert_dans_periode($login, $date_today, "am", $date_today, "am", $nb_jours, $commentaire, $id_type_abs, "ajout", 0, $DEBUG);
    }

    public static function ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG=FALSE)
    {

    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	// recup de la liste des users d'un groupe donné
    	$list_users = get_list_users_du_groupe($choix_groupe, $DEBUG);


    	foreach($tab_new_nb_conges_all as $id_conges => $nb_jours)
    	{
    		if($nb_jours!=0)
    		{
    			$comment = $tab_new_comment_all[$id_conges];

    			$sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users) ORDER BY u_login ";
    			$ReqLog1 = \includes\SQL::query($sql1);

    			while ($resultat1 = $ReqLog1->fetch_array())
    			{
    				$current_login  =$resultat1["u_login"];
    				$current_quotite=$resultat1["u_quotite"];

    				if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
    					$nb_conges=$nb_jours;
    				else
    					// pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
    					$nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;

    				$valid=verif_saisie_decimal($nb_conges, $DEBUG);
    				if($valid){
    					// 1 : on update conges_solde_user
    					$req_update = 'UPDATE conges_solde_user SET su_solde = su_solde+ '.intval($nb_conges).'
    							WHERE  su_login = "'. \includes\SQL::quote($current_login).'" AND su_abs_id = '.intval($id_conges).';';
    					$ReqLog_update = \includes\SQL::query($req_update);

    					// 2 : on insert l'ajout de conges dans la table periode
    					// recup du nom du groupe
    					$groupename= get_group_name_from_id($choix_groupe, $DEBUG);
    					$commentaire =  _('resp_ajout_conges_comment_periode_groupe') ." $groupename";

    					// ajout conges
    					\hr\Fonctions::insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
    				}
    			}

    			$group_name = get_group_name_from_id($choix_groupe, $DEBUG);
    			if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
    				$comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
    			else
    				$comment_log = "ajout conges pour groupe $group_name ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
    			log_action(0, "ajout", "groupe", $comment_log, $DEBUG);
    		}
    	}
    }

    public static function ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	// recup de la liste de TOUS les users dont $resp_login est responsable
    	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
    	// renvoit une liste de login entre quotes et séparés par des virgules
    	$list_users_du_resp = get_list_all_users_du_hr($_SESSION['userlogin'], $DEBUG);
    	if( $DEBUG ) { echo "list_all_users_du_hr = $list_users_du_resp<br>\n";}

    	if( $DEBUG ) { echo "tab_new_nb_conges_all = <br>"; print_r($tab_new_nb_conges_all); echo "<br>\n" ;}
    	if( $DEBUG ) { echo "tab_calcul_proportionnel = <br>"; print_r($tab_calcul_proportionnel); echo "<br>\n" ;}

    	foreach($tab_new_nb_conges_all as $id_conges => $nb_jours)
    	{
    		if($nb_jours!=0)
    		{
    			$comment = $tab_new_comment_all[$id_conges];

    			$sql1="SELECT u_login, u_quotite FROM conges_users WHERE u_login IN ($list_users_du_resp) ORDER BY u_login ";
    			$ReqLog1 = \includes\SQL::query($sql1);

    			while($resultat1 = $ReqLog1->fetch_array())
    			{
    				$current_login  =$resultat1["u_login"];
    				$current_quotite=$resultat1["u_quotite"];

    				if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
    					$nb_conges=$nb_jours;
    				else
    					// pour arrondir au 1/2 le + proche on  fait x 2, on arrondit, puis on divise par 2
    					$nb_conges = (ROUND(($nb_jours*($current_quotite/100))*2))/2  ;
    	   			$valid=verif_saisie_decimal($nb_conges, $DEBUG);
    				if($valid) {
    					// 1 : update de la table conges_solde_user
    					$req_update = 'UPDATE conges_solde_user SET su_solde = su_solde + '.floatval($nb_conges).'
    							WHERE  su_login = "'. \includes\SQL::quote($current_login).'"  AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
    					$ReqLog_update = \includes\SQL::query($req_update);

    					// 2 : on insert l'ajout de conges GLOBAL (pour tous les users) dans la table periode
    					$commentaire =  _('resp_ajout_conges_comment_periode_all') ;
    					// ajout conges
    					\hr\Fonctions::insert_ajout_dans_periode($DEBUG, $current_login, $nb_conges, $id_conges, $commentaire);
    				}
    			}

    			if( (!isset($tab_calcul_proportionnel[$id_conges])) || ($tab_calcul_proportionnel[$id_conges]!=TRUE) )
    				$comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : No)";
    			else
    				$comment_log = "ajout conges global ($nb_jours jour(s)) ($comment) (calcul proportionnel : Yes)";
    			log_action(0, "ajout", "tous", $comment_log, $DEBUG);
    		}
    	}
    }


    public static function ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id();

    	foreach($tab_champ_saisie as $user_name => $tab_conges)   // tab_champ_saisie[$current_login][$id_conges]=valeur du nb de jours ajouté saisi
    	{
    	  foreach($tab_conges as $id_conges => $user_nb_jours_ajout)
    	  {
    	    $user_nb_jours_ajout_float =(float) $user_nb_jours_ajout ;
    	    $valid=verif_saisie_decimal($user_nb_jours_ajout_float, $DEBUG);   //verif la bonne saisie du nombre décimal
    	    if($valid)
    	    {
    	      if( $DEBUG ) {echo "$user_name --- $id_conges --- $user_nb_jours_ajout_float<br>\n";}

    	      if($user_nb_jours_ajout_float!=0)
    	      {
    			/* Modification de la table conges_users */
    			$sql1 = 'UPDATE conges_solde_user SET su_solde = su_solde+'.floatval($user_nb_jours_ajout_float).' WHERE su_login="'. \includes\SQL::quote($user_name).'" AND su_abs_id = "'. \includes\SQL::quote($id_conges).'";';
    			/* On valide l'UPDATE dans la table ! */
    			$ReqLog1 = \includes\SQL::query($sql1) ;

    			// on insert l'ajout de conges dans la table periode
    			$commentaire =  _('resp_ajout_conges_comment_periode_user') ;
    			\hr\Fonctions::insert_ajout_dans_periode($DEBUG, $user_name, $user_nb_jours_ajout_float, $id_conges, $commentaire);
    	      }
    	    }
    	  }
    	}
    }

    public static function affichage_saisie_globale_groupe($tab_type_conges, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	/***********************************************************************/
    	/* SAISIE GROUPE pour tous les utilisateurs */

    	// on établi la liste complète des groupes pour le mode RH
    	$list_group = \hr\Fonctions::get_list_groupes_pour_rh($_SESSION['userlogin']);

    	if($list_group!="") //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
    	{
    		echo "<h2>". _('resp_ajout_conges_ajout_groupe') ."</h2>\n";
    		echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
    		echo "	<fieldset class=\"cal_saisie\">\n";
    		echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
    		echo "	<tr>\n";
    		echo "		<td class=\"big\">". _('resp_ajout_conges_choix_groupe') ." : </td>\n";
    			// création du select pour le choix du groupe
    			$text_choix_group="<select name=\"choix_groupe\" >";
    			$sql_group = "SELECT g_gid, g_groupename FROM conges_groupe WHERE g_gid IN ($list_group) ORDER BY g_groupename "  ;
    			$ReqLog_group = \includes\SQL::query($sql_group) ;

    			while ($resultat_group = $ReqLog_group->fetch_array())
    			{
    				$current_group_id=$resultat_group["g_gid"];
    				$current_group_name=$resultat_group["g_groupename"];
    				$text_choix_group=$text_choix_group."<option value=\"$current_group_id\" >$current_group_name</option>";
    			}
    			$text_choix_group=$text_choix_group."</select>" ;

    		echo "		<td colspan=\"3\">$text_choix_group</td>\n";
    		echo "	</tr>\n";
    	echo "<tr>\n";
    	echo "<th colspan=\"2\">" . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . "</th>\n";
    	echo "<th>" ._('resp_ajout_conges_calcul_prop') . "</th>\n";
    	echo "<th>" . _('divers_comment_maj_1') . "</th>\n";
    	echo "</tr>\n";
    		foreach($tab_type_conges as $id_conges => $libelle)
    		{
    			echo "	<tr>\n";
    			echo "		<td><strong>$libelle<strong></td>\n";
    			echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
    			echo "		<td>". _('resp_ajout_conges_oui') ." <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked></td>\n";
    			echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
    			echo "	</tr>\n";
    		}
    		echo "	</table></div>\n";
    		echo "<p>" . _('resp_ajout_conges_calcul_prop_arondi') . "! </p>\n";
    		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_groupe') ."\">\n";
    		echo "	</fieldset>\n";
    		echo "<input type=\"hidden\" name=\"ajout_groupe\" value=\"TRUE\">\n";
    		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
    		echo "</form> \n";
    	}
    }

    public static function affichage_saisie_globale_pour_tous($tab_type_conges, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	/************************************************************/
    	/* SAISIE GLOBALE pour tous les utilisateurs du responsable */
    	echo "<h2>". _('resp_ajout_conges_ajout_all') ."</h2>\n";
    	echo "<form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";
    	echo "	<fieldset class=\"cal_saisie\">\n";
    	echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
    	echo "<thead>\n";
    	echo "<tr>\n";
    	echo "<th colspan=\"2\">" . _('resp_ajout_conges_nb_jours_all_1') . ' ' . _('resp_ajout_conges_nb_jours_all_2') . "</th>\n";
    	echo "<th>" ._('resp_ajout_conges_calcul_prop') . "</th>\n";
    	echo "<th>" . _('divers_comment_maj_1') . "</th>\n";
    	echo "</tr>\n";
    	echo "</thead>\n";
    	foreach($tab_type_conges as $id_conges => $libelle)
    	{
    		echo "	<tr>\n";
    		echo "		<td><strong>$libelle<strong></td>\n";
    		echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_nb_conges_all[$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\"></td>\n";
    		echo "		<td>". _('resp_ajout_conges_oui') ." <input type=\"checkbox\" name=\"tab_calcul_proportionnel[$id_conges]\" value=\"TRUE\" checked></td>\n";
    		echo "		<td><input class=\"form-control\" type=\"text\" name=\"tab_new_comment_all[$id_conges]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
    		echo "	</tr>\n";
    	}
    	echo "</table></div>\n";
    	// texte sur l'arrondi du calcul proportionnel
    	echo "<p>" . _('resp_ajout_conges_calcul_prop_arondi') . "!</p>\n";
    	// bouton valider
    	echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_valid_global') ."\">\n";
    	echo "</fieldset>\n";
    	echo "<input type=\"hidden\" name=\"ajout_global\" value=\"TRUE\">\n";
    	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
    	echo "</form> \n";
    }

    public static function affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG=FALSE)
    {
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	/************************************************************/
    	/* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
    	echo "<h2>Ajout par utilisateur</h2>\n";
    	echo " <form action=\"$PHP_SELF?session=$session&onglet=ajout_conges\" method=\"POST\"> \n";

    	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
    	{
    		// AFFICHAGE TITRES TABLEAU
    		echo "<div class=\"table-responsive\"><table class=\"table table-hover table-condensed table-striped\">\n";
    		echo '<thead>';
    			echo '<tr align="center">';
    				echo '<th>'. _('divers_nom_maj_1') .'</th>';
    				echo '<th>'. _('divers_prenom_maj_1') .'</th>';
    				echo '<th>'. _('divers_quotite_maj_1') .'</th>';
    				foreach($tab_type_conges as $id_conges => $libelle)
    				{
    					echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
    					echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') .'</th>' ;
    				}
    				if ($_SESSION['config']['gestion_conges_exceptionnels'])
    				{
    					foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
    					{
    						echo "<th>$libelle<br><i>(". _('divers_solde') .")</i></th>\n";
    						echo "<th>$libelle<br>". _('resp_ajout_conges_nb_jours_ajout') .'</th>' ;
    					}
    				}
    				echo '<th>'. _('divers_comment_maj_1') ."<br></th>\n" ;
    			echo"</tr>\n";
    		echo '</thead>';
    		echo '<tbody>';

    		// AFFICHAGE LIGNES TABLEAU
    		$cpt_lignes=0 ;
    		$tab_champ_saisie_conges=array();

    		$i = true;
    		// affichage des users dont on est responsable :
    		foreach($tab_all_users_du_hr as $current_login => $tab_current_user)
    		{
    			echo '<tr class="'.($i?'i':'p').'">';
    			//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
    			$tab_conges=$tab_current_user['conges'];

    			/** sur la ligne ,   **/
    			echo '<td>'.$tab_current_user['nom'].'</td>';
    			echo '<td>'.$tab_current_user['prenom'].'</td>';
    			echo '<td>'.$tab_current_user['quotite']."%</td>\n";

    			foreach($tab_type_conges as $id_conges => $libelle)
    			{
    				/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
    				$champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
    				echo '<td>'.$tab_conges[$libelle]['nb_an']." <i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
    				echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
    			}
    			if ($_SESSION['config']['gestion_conges_exceptionnels'])
    			{
    				foreach($tab_type_conges_exceptionnels as $id_conges => $libelle)
    				{
    					/** le champ de saisie est <input type="text" name="tab_champ_saisie[valeur de u_login][id_du_type_de_conges]" value="[valeur du nb de jours ajouté saisi]"> */
    					$champ_saisie_conges="<input class=\"form-control\" type=\"text\" name=\"tab_champ_saisie[$current_login][$id_conges]\" size=\"6\" maxlength=\"6\" value=\"0\">";
    					echo "<td><i>(".$tab_conges[$libelle]['solde'].")</i></td>\n";
    					echo "<td align=\"center\" class=\"histo\">$champ_saisie_conges</td>\n" ;
    				}
    			}
    			echo "<td align=\"center\" class=\"histo\"><input class=\"form-control\" type=\"text\" name=\"tab_commentaire_saisie[$current_login]\" size=\"30\" maxlength=\"200\" value=\"\"></td>\n";
    			echo '</tr>';
    			$cpt_lignes++ ;
    			$i = !$i;
    		}

    		echo '</tbody>';
    		echo '</table>';

    		echo "<input type=\"hidden\" name=\"ajout_conges\" value=\"TRUE\">\n";
    		echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
    		echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
    		echo " </form> \n";
    	}
    }

    public static function saisie_ajout( $tab_type_conges, $DEBUG)
    {
    //$DEBUG;
    	$PHP_SELF=$_SERVER['PHP_SELF'];
    	$session=session_id() ;

    	// recup du tableau des types de conges (seulement les congesexceptionnels )
    	if ($_SESSION['config']['gestion_conges_exceptionnels'])
    	{
    	  $tab_type_conges_exceptionnels = recup_tableau_types_conges_exceptionnels();
    	  if( $DEBUG ) { echo "tab_type_conges_exceptionnels = "; print_r($tab_type_conges_exceptionnels); echo "<br><br>\n";}
    	}
    	else
    	  $tab_type_conges_exceptionnels = array();

    	// recup de la liste de TOUS les users pour le RH
    	// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
    	// renvoit une liste de login entre quotes et séparés par des virgules
    	$tab_all_users_du_hr=recup_infos_all_users_du_hr($_SESSION['userlogin']);
    	$tab_all_users_du_grand_resp=recup_infos_all_users_du_grand_resp($_SESSION['userlogin']);
    	if( $DEBUG ) { echo "tab_all_users_du_hr =<br>\n"; print_r($tab_all_users_du_hr); echo "<br>\n"; }
    	if( $DEBUG ) { echo "tab_all_users_du_grand_resp =<br>\n"; print_r($tab_all_users_du_grand_resp); echo "<br>\n"; }

    	if( (count($tab_all_users_du_hr)!=0) || (count($tab_all_users_du_grand_resp)!=0) )
    	{
    		/************************************************************/
    		/* SAISIE GLOBALE pour tous les utilisateurs du responsable */
    		\hr\Fonctions::affichage_saisie_globale_pour_tous($tab_type_conges, $DEBUG);
    		echo "<br>\n";

    		/***********************************************************************/
    		/* SAISIE GROUPE pour tous les utilisateurs d'un groupe du responsable */
    		if( $_SESSION['config']['gestion_groupes'] )
    			\hr\Fonctions::affichage_saisie_globale_groupe($tab_type_conges, $DEBUG);
    		echo "<br>\n";

    		/************************************************************/
    		/* SAISIE USER PAR USER pour tous les utilisateurs du responsable */
    		\hr\Fonctions::affichage_saisie_user_par_user($tab_type_conges, $tab_type_conges_exceptionnels, $tab_all_users_du_hr, $tab_all_users_du_grand_resp, $DEBUG);
    		echo "<br>\n";

    	}
    	else
    		echo  _('resp_etat_aucun_user') ."<br>\n";

    }

    /**
     * Encapsule le comportement du module d'ajout de congés
     *
     * @param array  $tab_type_cong
     * @param string $session
     * @param bool   $DEBUG          Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function pageAjoutCongesModule($tab_type_cong, $session, $DEBUG = false)
    {
        //var pour resp_ajout_conges_all.php
        $ajout_conges            = getpost_variable('ajout_conges');
        $ajout_global            = getpost_variable('ajout_global');
        $ajout_groupe            = getpost_variable('ajout_groupe');
        $choix_groupe            = getpost_variable('choix_groupe');

        // titre
        echo '<h1>'. _('resp_ajout_conges_titre') ."</h1>\n\n";

        if( $ajout_conges == "TRUE" ) {

            $tab_champ_saisie			= getpost_variable('tab_champ_saisie');
            $tab_commentaire_saisie		= getpost_variable('tab_commentaire_saisie');

            \hr\Fonctions::ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        }
        elseif( $ajout_global == "TRUE" ) {

            $tab_new_nb_conges_all   	= getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel	= getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all     	= getpost_variable('tab_new_comment_all');

            \hr\Fonctions::ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        }
        elseif( $ajout_groupe == "TRUE" ) {

            $tab_new_nb_conges_all   	= getpost_variable('tab_new_nb_conges_all');
            $tab_calcul_proportionnel	= getpost_variable('tab_calcul_proportionnel');
            $tab_new_comment_all     	= getpost_variable('tab_new_comment_all');
            $choix_groupe            	= getpost_variable('choix_groupe');

            \hr\Fonctions::ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $DEBUG);
            redirect( ROOT_PATH .'hr/hr_index.php?session='.$session, false);
            exit;
        }
        else {
            \hr\Fonctions::saisie_ajout($tab_type_cong,$DEBUG);
        }
    }
}
