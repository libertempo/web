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

    //var pour resp_traite_user.php
    $user_login   = getpost_variable('user_login') ;
    $year_calendrier_saisie_debut = getpost_variable('year_calendrier_saisie_debut', 0) ;
    $mois_calendrier_saisie_debut = getpost_variable('mois_calendrier_saisie_debut', 0) ;
    $year_calendrier_saisie_fin = getpost_variable('year_calendrier_saisie_fin', 0) ;
    $mois_calendrier_saisie_fin = getpost_variable('mois_calendrier_saisie_fin', 0) ;
    $tri_date = getpost_variable('tri_date', "ascendant") ;
    $tab_checkbox_annule = getpost_variable('tab_checkbox_annule') ;
    $tab_radio_traite_demande = getpost_variable('tab_radio_traite_demande') ;
    $tab_text_refus = getpost_variable('tab_text_refus') ;
    $tab_text_annul = getpost_variable('tab_text_annul') ;
    $new_demande_conges = getpost_variable('new_demande_conges', 0) ;
    $new_debut = getpost_variable('new_debut') ;
    $new_demi_jour_deb = getpost_variable('new_demi_jour_deb') ;
    $new_fin = getpost_variable('new_fin') ;
    $new_demi_jour_fin = getpost_variable('new_demi_jour_fin') ;

    if($_SESSION['config']['disable_saise_champ_nb_jours_pris'])  // zone de texte en readonly et grisée
        $new_nb_jours = compter($user_login, '', $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $comment,  $DEBUG);
    else
        $new_nb_jours = getpost_variable('new_nb_jours') ;

    $new_comment = getpost_variable('new_comment') ;
    $new_type = getpost_variable('new_type') ;
    $year_affichage = getpost_variable('year_affichage' , date("Y") );

    /*************************************/

    if ( !is_resp_of_user($_SESSION['userlogin'] , $user_login)) {
        redirect(ROOT_PATH . 'deconnexion.php');
        exit;
    }

    /************************************/


    // si une annulation de conges a été selectionée :
    if($tab_checkbox_annule!="")
    {
        annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul,  $DEBUG);
    }
    // si le traitement des demandes a été selectionée :
    elseif($tab_radio_traite_demande!="")
    {
        traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus,  $DEBUG);
    }
    // si un nouveau conges ou absence a été saisi pour un user :
    elseif($new_demande_conges==1)
    {
        new_conges($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type,  $DEBUG);
    }
    else
    {
        affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date,  $DEBUG);
    }


/*************************************/
/***   FONCTIONS   ***/
/*************************************/

function affichage($user_login,  $year_affichage, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date,  $DEBUG)
{
    $PHP_SELF=$_SERVER['PHP_SELF']; ;
    $session=session_id();

    // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
    if(!isset($_SESSION["tab_j_feries"]))
    {
        init_tab_jours_feries();
        //print_r($GLOBALS["tab_j_feries"]);   // verif DEBUG
    }

    /********************/
    /* Récupération des informations sur le user : */
    /********************/
    $list_group_dbl_valid_du_resp = get_list_groupes_double_valid_du_resp($_SESSION['userlogin'],  $DEBUG);
    $tab_user=array();
    $tab_user = recup_infos_du_user($user_login, $list_group_dbl_valid_du_resp,  $DEBUG);
    if( $DEBUG ) { echo"tab_user =<br>\n"; print_r($tab_user); echo "<br>\n"; }

    $list_all_users_du_resp=get_list_all_users_du_resp($_SESSION['userlogin'],  $DEBUG);
    if( $DEBUG ) { echo"list_all_users_du_resp = $list_all_users_du_resp<br>\n"; }

    // recup des grd resp du user
    $tab_grd_resp=array();
    if($_SESSION['config']['double_validation_conges'])
    {
        get_tab_grd_resp_du_user($user_login, $tab_grd_resp,  $DEBUG);
        if( $DEBUG ) { echo"tab_grd_resp =<br>\n"; print_r($tab_grd_resp); echo "<br>\n"; }
    }

    /********************/
    /* Titre */
    /********************/
    echo "<h1>".$tab_user['prenom']." ".$tab_user['nom']."</h1>\n\n";


    /********************/
    /* Bilan des Conges */
    /********************/
    // AFFICHAGE TABLEAU
    // affichage du tableau récapitulatif des solde de congés d'un user
    affiche_tableau_bilan_conges_user($user_login);
    echo "<hr/>\n";

    /*************************/
    /* SAISIE NOUVEAU CONGES */
    /*************************/
    // dans le cas ou les users ne peuvent pas saisir de demande, le responsable saisi les congès :
    if( !$_SESSION['config']['user_saisie_demande'] || $_SESSION['config']['resp_saisie_mission'] )
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

        echo "<h2>". _('resp_traite_user_new_conges') ."</h2>\n";

        //affiche le formulaire de saisie d'une nouvelle demande de conges ou d'un  nouveau conges
        $onglet = "traite_user";
        saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);

        echo "<hr/>\n";
    }

    /*********************/
    /* Etat des Demandes */
    /*********************/
    if($_SESSION['config']['user_saisie_demande'])
    {
        //verif si le user est bien un user du resp (et pas seulement du grand resp)
        if(strstr($list_all_users_du_resp, "'$user_login'")!=FALSE)
        {
            echo "<h2>". _('resp_traite_user_etat_demandes') ."</h2>\n";

            //affiche l'état des demandes du user (avec le formulaire pour le responsable)
            affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp,  $DEBUG);

            echo "<hr/>\n";
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
            echo "<h2>". _('resp_traite_user_etat_demandes_2_valid') ."</h2>\n";

            //affiche l'état des demande en attente de 2ieme valid du user (avec le formulaire pour le responsable)
            affiche_etat_demande_2_valid_user_for_resp($user_login,  $DEBUG);

            echo "<hr/>\n";
        }
    }

    /*******************/
    /* Etat des Conges */
    /*******************/
    //affiche l'état des conges du user (avec le formulaire pour le responsable)
    $onglet = "traite_user";
    affiche_etat_conges_user_for_resp($user_login,  $year_affichage, $tri_date, $onglet, $DEBUG);

}



//affiche l'état des demandes du user (avec le formulaire pour le responsable)
function affiche_etat_demande_user_for_resp($user_login, $tab_user, $tab_grd_resp,  $DEBUG=FALSE)
{
    $PHP_SELF=$_SERVER['PHP_SELF']; ;
    $session=session_id();

    // Récupération des informations
    $sql2 = "SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_date_demande, p_date_traitement, p_num " .
            "FROM conges_periode " .
            "WHERE p_login = '$user_login' AND p_etat ='demande' ".
            "ORDER BY p_date_deb";
    $ReqLog2 = \includes\SQL::query($sql2);

    $count2=$ReqLog2->num_rows;
    if($count2==0)
    {
        echo "<p><strong>". _('resp_traite_user_aucune_demande') ."</strong></p>\n";
    }
    else
    {
        // recup dans un tableau des types de conges
        $tab_type_all_abs = recup_tableau_tout_types_abs();

        // AFFICHAGE TABLEAU
        echo " <form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
        //echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\" width=\"80%\">\n";
        echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "<tr align=\"center\">\n";
        echo "<td>". _('divers_debut_maj_1') ."</td>\n";
        echo "<td>". _('divers_fin_maj_1') ."</td>\n";
        echo "<td>". _('divers_nb_jours_pris_maj_1') ."</td>\n";
        echo "<td>". _('divers_comment_maj_1') ."</td>\n";
        echo "<td>". _('divers_type_maj_1') ."</td>\n";
        echo "<td>". _('divers_accepter_maj_1') ."</td>\n";
        echo "<td>". _('divers_refuser_maj_1') ."</td>\n";
        echo "<td>". _('resp_traite_user_motif_refus') ."</td>\n";
        if($_SESSION['config']['affiche_date_traitement'])
        {
            echo "<td>". _('divers_date_traitement') ."</td>\n" ;
        }
        echo "</tr>\n";

        $tab_checkbox=array();
        while ($resultat2 = $ReqLog2->fetch_array())
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

            // si le user fait l'objet d'une double validation on a pas le meme resultat sur le bouton !
            if($tab_user['double_valid'] == "Y")
            {
                /*******************************/
                /* verif si le resp est grand_responsable pour ce user*/
                if(in_array($_SESSION['userlogin'], $tab_grd_resp)) // si user_login est dans le tableau des grand responsable
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";
                else
                    $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--VALID\">";
            }
            else
                $boutonradio1="<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--ACCEPTE\">";

            $boutonradio2 = "<input type=\"radio\" name=\"tab_radio_traite_demande[$sql_num]\" value=\"$chaine_bouton_radio--REFUSE\">";

            $text_refus  = "<input type=\"text\" name=\"tab_text_refus[$sql_num]\" size=\"20\" max=\"100\">";

            echo "<tr align=\"center\">\n";
            echo "<td>$sql_date_deb_fr _ $demi_j_deb</td>\n";
            echo "<td>$sql_date_fin_fr _ $demi_j_fin</td>\n";
            echo "<td>$sql_nb_jours</td>\n";
            echo "<td>$sql_commentaire</td>\n";
            echo "<td>".$tab_type_all_abs[$sql_type]['libelle']."</td>\n";
            echo "<td>$boutonradio1</td>\n";
            echo "<td>$boutonradio2</td>\n";
            echo "<td>$text_refus</td>\n";
            echo "<td>$sql_date_demande</td>\n";

            if($_SESSION['config']['affiche_date_traitement'])
            {
                if(empty($sql_date_traitement))
                    echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                else
                    echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
            }

            echo "</tr>\n";
        }
        echo "</table>\n\n";

        echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
        echo " </form> \n";
    }
}



//affiche l'état des demande en attente de 2ieme validation du user (avec le formulaire pour le responsable)
function affiche_etat_demande_2_valid_user_for_resp($user_login,  $DEBUG=FALSE)
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
            //echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\" width=\"80%\">\n";
            echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\">\n";
            echo "<thead>";
            echo "<tr align=\"center\">\n";
            echo "<th>". _('divers_debut_maj_1') ."</th>\n";
            echo "<th>". _('divers_fin_maj_1') ."</th>\n";
            echo "<th>". _('divers_nb_jours_pris_maj_1') ."</th>\n";
            echo "<th>". _('divers_comment_maj_1') ."</th>\n";
            echo "<th>". _('divers_type_maj_1') ."</th>\n";
            echo "<th>". _('divers_accepter_maj_1') ."</th>\n";
            echo "<th>". _('divers_refuser_maj_1') ."</th>\n";
            echo "<th>". _('resp_traite_user_motif_refus') ."</th>\n";
            if($_SESSION['config']['affiche_date_traitement'])
            {
                echo "<th>". _('divers_date_traitement') ."</th>\n" ;
            }
            echo "</tr>\n";
            echo "</thead>";
            echo "<tbody>";

            $i = true;
            $tab_checkbox=array();
            while ($resultat2 = $ReqLog2->fetch_array())
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
                echo "<td>".$tab_type_all_abs[$sql_type]['libelle']."</td>\n";
                echo "<td>$casecocher1</td>\n";
                echo "<td>$casecocher2</td>\n";
                echo "<td>$text_refus</td>\n";
                if($_SESSION['config']['affiche_date_traitement'])
                {
                    if(empty($sql_date_traitement))
                        echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                    else
                        echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_date_demande<br>". _('divers_traitement') ." : $sql_date_traitement</td>\n" ;
                }
                echo "</tr>\n";
                $i = !$i;
            }
            echo "</tbody>";
            echo "</table>\n\n";

            echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
            echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">  &nbsp;&nbsp;&nbsp;&nbsp;  <input type=\"reset\" value=\"". _('form_cancel') ."\">\n";
            echo " </form> \n";
        }

}



//affiche l'état des conges du user (avec le formulaire pour le responsable)
function affiche_etat_conges_user_for_resp($user_login, $year_affichage, $tri_date, $onglet ,$DEBUG=FALSE)
{
    $PHP_SELF=$_SERVER['PHP_SELF']; ;
    $session=session_id() ;

    // affichage de l'année et des boutons de défilement
    $year_affichage_prec = $year_affichage-1 ;
    $year_affichage_suiv = $year_affichage+1 ;
    echo "<div class=\"calendar-nav\">\n";
    echo "<ul>\n";
    echo "<li><a class=\"action previous\" href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_prec\"><i class=\"fa fa-chevron-left\"></i></a></li>\n";
    echo "<li class=\"current-year\">$year_affichage</li>";
    echo "<li><a class=\"action next\" href=\"$PHP_SELF?session=$session&onglet=traite_user&user_login=$user_login&year_affichage=$year_affichage_suiv\"><i class=\"fa fa-chevron-right\"></i></a></li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<h2>". _('resp_traite_user_etat_conges') ." $year_affichage</h2>\n";

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
        $tab_types_abs = recup_tableau_tout_types_abs( $DEBUG) ;

        // AFFICHAGE TABLEAU
        echo "<form action=\"$PHP_SELF?session=$session&onglet=traite_user\" method=\"POST\"> \n";
        //echo "<table cellpadding=\"2\" class=\"table table-hover table-responsive table-condensed table-striped\" width=\"80%\">\n";
        echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "<thead>";
        echo "<tr align=\"center\">\n";
        echo " <th>\n";
        // echo " <a href=\"$PHP_SELF?session=$session&user_login=$user_login&onglet=$onglet&tri_date=descendant\"><img src=\"". TEMPLATE_PATH ."img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
        echo " ". _('divers_debut_maj_1') ." \n";
        // echo " <a href=\"$PHP_SELF?session=$session&user_login=$user_login&onglet=$onglet&tri_date=ascendant\"><img src=\"". TEMPLATE_PATH ."img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
        echo " </th>\n";
        echo " <th>". _('divers_fin_maj_1') ."</th>\n";
        echo " <th>". _('divers_nb_jours_pris_maj_1') ."</th>\n";
        echo " <th>". _('divers_comment_maj_1') ."</th>\n";
        echo " <th>". _('divers_type_maj_1') ."</th>\n";
        echo " <th>". _('divers_etat_maj_1') ."</th>\n";
        echo " <th>". _('resp_traite_user_annul') ."</th>\n";
        echo " <th>". _('resp_traite_user_motif_annul') ."</th>\n";
        if($_SESSION['config']['affiche_date_traitement'])
        {
            echo "<th>". _('divers_date_traitement') ."</th>\n" ;
        }
        echo "</tr>\n";
        echo "</thead>";
        echo "<tbody>";
        $tab_checkbox=array();
        $i = true;
        while ($resultat3 = $ReqLog3->fetch_array())
        {
                $sql_login=$resultat3["p_login"] ;
                $sql_date_deb=eng_date_to_fr($resultat3["p_date_deb"]) ;
                $sql_demi_jour_deb=$resultat3["p_demi_jour_deb"] ;
                if($sql_demi_jour_deb=="am")
                    $demi_j_deb =  _('divers_am_short') ;
                else
                    $demi_j_deb =  _('divers_pm_short') ;
                $sql_date_fin=eng_date_to_fr($resultat3["p_date_fin"]) ;
                $sql_demi_jour_fin=$resultat3["p_demi_jour_fin"] ;
                if($sql_demi_jour_fin=="am")
                    $demi_j_fin =  _('divers_am_short') ;
                else
                    $demi_j_fin =  _('divers_pm_short') ;
                $sql_nb_jours=affiche_decimal($resultat3["p_nb_jours"]) ;
                $sql_commentaire=$resultat3["p_commentaire"] ;
                $sql_type=$resultat3["p_type"] ;
                $sql_etat=$resultat3["p_etat"] ;
                $sql_motif_refus=$resultat3["p_motif_refus"] ;
                $sql_p_date_demande = $resultat3["p_date_demande"];
                $sql_p_date_traitement = $resultat3["p_date_traitement"];
                $sql_num=$resultat3["p_num"] ;

                if(($sql_etat=="annul") || ($sql_etat=="refus") || ($sql_etat=="ajout"))
                {
                    $casecocher1="";
                    if($sql_etat=="refus")
                    {
                        if($sql_motif_refus=="")
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        //$text_annul="<i>motif du refus : $sql_motif_refus</i>";
                        $text_annul="<i>". _('resp_traite_user_motif') ." : $sql_motif_refus</i>";
                    }
                    elseif($sql_etat=="annul")
                    {
                        if($sql_motif_refus=="")
                            $sql_motif_refus =  _('divers_inconnu')  ;
                        //$text_annul="<i>motif de l'annulation : $sql_motif_refus</i>";
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
                    echo "<td>".$tab_types_abs[$sql_type]['libelle']."</td>\n";
                    echo "<td>";
                    if($sql_etat=="refus")
                        echo  _('divers_refuse') ;
                    elseif($sql_etat=="annul")
                        echo  _('divers_annule') ;
                    else
                        echo "$sql_etat";
                    echo "</td>\n";
                    echo "<td>$casecocher1</td>\n";
                    echo "<td>$text_annul</td>\n";
                    if($_SESSION['config']['affiche_date_traitement'])
                    {
                        if(empty($sql_p_date_traitement))
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : pas traité</td>\n" ;
                        else
                            echo "<td class=\"histo-left\">". _('divers_demande') ." : $sql_p_date_demande<br>". _('divers_traitement') ." : $sql_p_date_traitement</td>\n" ;
                    }
                    echo "</tr>\n";
                $i = !$i;
            }
        echo "</tbody>";
        echo "</table>\n\n";

        echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
        echo "</form> \n";
    }
}



function annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul,  $DEBUG=FALSE)
{
    $PHP_SELF=$_SERVER['PHP_SELF']; ;
    $session=session_id() ;

    // recup dans un tableau de tableau les infos des types de conges et absences
    $tab_tout_type_abs = recup_tableau_tout_types_abs( $DEBUG);

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
        $sql1 = 'UPDATE conges_periode SET p_etat="annul", p_motif_refus="'. \includes\SQL::quote($motif_annul).'", p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'" ';
        $ReqLog1 = \includes\SQL::query($sql1);

        // Log de l'action
        log_action($numero_int,"annul", $user_login, "annulation conges $numero ($user_login) ($user_nb_jours_pris jours)",  $DEBUG);

        /* UPDATE table "conges_solde_user" (jours restants) */
        // on re-crédite les jours seulement pour des conges pris (pas pour les absences)
        // donc seulement si le type de l'absence qu'on annule est un "conges"
        if($tab_tout_type_abs[$user_type_abs_id]['type']=="conges")
        {
            $sql2 = 'UPDATE conges_solde_user SET su_solde = su_solde+"'. \includes\SQL::quote($user_nb_jours_pris_float).'" WHERE su_login="'. \includes\SQL::quote($user_login).'" AND su_abs_idi="'. \includes\SQL::quote($user_type_abs_id).'";';
            //echo($sql2."<br>");
            $ReqLog2 = \includes\SQL::query($sql2);
        }

        //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
        if($_SESSION['config']['mail_annul_conges_alerte_user'])
            alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "annul_conges",  $DEBUG);
    }

    if( $DEBUG )
    {
        echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
        echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
        echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
        echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
        echo "</form>\n" ;
    }
    else
    {
        echo  _('form_modif_ok') ."<br><br> \n";
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
    }

}



function traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus,  $DEBUG=FALSE)
{
    $PHP_SELF=$_SERVER['PHP_SELF']; ;
    $session=session_id();

    // recup dans un tableau de tableau les infos des types de conges et absences
    $tab_tout_type_abs = recup_tableau_tout_types_abs( $DEBUG);

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
//      $value_traite=$champs[3];

        $numero=$elem_tableau['key'];
        $numero_int=(int) $numero;
        if( $DEBUG ) { echo "<br><br>conges numero :$numero --- User_login : $user_login --- nb de jours : $user_nb_jours_pris --->$value_traite<br>" ; }

        if($reponse == "ACCEPTE") // acceptation definitive d'un conges
        {
            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="ok", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'";';
            $ReqLog1 = \includes\SQL::query($sql1);

            // Log de l'action
            log_action($numero_int,"ok", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite",  $DEBUG);

            /* UPDATE table "conges_solde_user" (jours restants) */
            // on retranche les jours seulement pour des conges pris (pas pour les absences)
            // donc seulement si le type de l'absence qu'on accepte est un "conges"
            if( $DEBUG ) { echo "type_abs = ".$tab_tout_type_abs[$value_type_abs_id]['type']."<br>\n" ; }
            if(($tab_tout_type_abs[$value_type_abs_id]['type']=="conges")||($tab_tout_type_abs[$value_type_abs_id]['type']=="conges_exceptionnels"))
            {
                soustrait_solde_et_reliquat_user($user_login, $numero_int, $user_nb_jours_pris_float, $value_type_abs_id, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $DEBUG);
            }

            //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
            if($_SESSION['config']['mail_valid_conges_alerte_user'])
                alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "accept_conges",  $DEBUG);
        }
        elseif($reponse == "VALID") // première validation dans le cas d'une double validation
        {
            /* UPDATE table "conges_periode" */
            $sql1 = 'UPDATE conges_periode SET p_etat="valid", p_date_traitement=NOW() WHERE p_num="'.\includes\SQL::quote($numero_int).'";';
            $ReqLog1 = \inclusionSQL::query($sql1);

            // Log de l'action
            log_action($numero_int,"valid", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite",  $DEBUG);

            //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
            if($_SESSION['config']['mail_valid_conges_alerte_user'])
                alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "valid_conges",  $DEBUG);
        }
        elseif($reponse == "REFUSE") // refus d'un conges
        {
            // recup di motif de refus
            $motif_refus=addslashes($tab_text_refus[$numero_int]);
            //$sql3 = "UPDATE conges_periode SET p_etat=\"refus\" WHERE p_num=$numero_int" ;
            $sql3 = 'UPDATE conges_periode SET p_etat="refus", p_motif_refus=\''.$motif_refus.'\', p_date_traitement=NOW() WHERE p_num="'. \includes\SQL::quote($numero_int).'";';
            $ReqLog3 = \includes\SQL::query($sql3);

            // Log de l'action
            log_action($numero_int,"refus", $user_login, "traite demande $numero ($user_login) ($user_nb_jours_pris jours) : $value_traite",  $DEBUG);

            //envoi d'un mail d'alerte au user (si demandé dans config de php_conges)
            if($_SESSION['config']['mail_refus_conges_alerte_user'])
                alerte_mail($_SESSION['userlogin'], $user_login, $numero_int, "refus_conges",  $DEBUG);
        }
    }

    if( $DEBUG )
    {
        echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
        echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
        echo "<input type=\"hidden\" name=\"onglet\" value=\"traite_user\">\n";
        echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
        echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_ok') ."\">\n";
        echo "</form>\n" ;
    }
    else
    {
        echo  _('form_modif_ok') ."<br><br> \n";
        /* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$PHP_SELF?session=$session&user_login=$user_login\">";
    }

}

function new_conges($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	//conversion des dates
	$new_debut = convert_date($new_debut);
	$new_fin = convert_date($new_fin);
	
	// verif validité des valeurs saisies
	$valid=verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);
	
	if( $DEBUG ) { echo "verif_saisie_new_demande resp_traite_user : $valid<br>\n"; }
	if($valid)
	{
        	echo "$user_login---$new_debut _ $new_demi_jour_deb---$new_fin _ $new_demi_jour_fin---$new_nb_jours---$new_comment---$new_type_id<br>\n";

        	// recup dans un tableau de tableau les infos des types de conges et absences
        	$tab_tout_type_abs = recup_tableau_tout_types_abs( $DEBUG);

        	/**********************************/
        	/* insert dans conges_periode     */
        	/**********************************/
        	$new_etat="ok";
        	$result=insert_dans_periode($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type_id, $new_etat, 0, $DEBUG);

        	/************************************************/
        	/* UPDATE table "conges_solde_user" (jours restants) */
        	// on retranche les jours seulement pour des conges pris (pas pour les absences)
        	// donc seulement si le type de l'absence qu'on annule est un "conges"
        	if(isset($tab_tout_type_abs[$new_type_id]['type']) && $tab_tout_type_abs[$new_type_id]['type']=="conges")
        	{
        		$user_nb_jours_pris_float=(float) $new_nb_jours ;
        		soustrait_solde_et_reliquat_user($user_login, "", $user_nb_jours_pris_float, $new_type_id, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin , $DEBUG);
	        }
	        $comment_log = "saisie conges par le responsable pour $user_login ($new_nb_jours jour(s)) type_conges = $new_type_id ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
        	log_action(0, "", $user_login, $comment_log,  $DEBUG);

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
	echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_retour') ."\">\n";
	echo "</form> \n";
}

