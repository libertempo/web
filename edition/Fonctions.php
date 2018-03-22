<?php

namespace edition;

/**
* Regroupement des fonctions liées à l'édition
*/
class Fonctions
{
    public static function affiche_anciennes_editions($login)
    {
        $return = '';

        $return .= '<CENTER>';

        // recup du tableau des types de conges (seulement les conges)
        $tab_type_cong=recup_tableau_types_conges();

        /*************************************/
        /* Historique des éditions           */
        /*************************************/
        // Récupération des informations des editions du user
        $tab_editions_user = \edition\Fonctions::recup_editions_user($login);

        $return .= '<h3>' . _('editions_hitorique_edit') . ' :</h3>';

        if (count($tab_editions_user)==0) {
            $return .= '<b>' . _('editions_aucun_hitorique') . '</b><br>';
        } else {
            // AFFICHAGE TABLEAU
            $return .= '<table cellpadding="2" class="tablo" width="750">';
            $return .= '<thead><tr align="center">';
            $return .= '<th>' . _('editions_numero') . '</th>';
            $return .= '<th>' . _('editions_date') . '</th>';
            foreach($tab_type_cong as $id_abs => $libelle) {
                $return .= '<th>' . _('divers_solde_maj_1') . ' ' . $libelle . '</th>';
            }

            $return .= '<th></th>';
            $return .= '<th></th>';
            $return .= '</tr></thead><tbody>';

            foreach($tab_editions_user as $id_edition => $tab_ed) {
                $text_edit_a_nouveau="<a href=\"edition_papier.php?user_login=$login&edit_id=$id_edition\">" .
                        "<img src=\"". IMG_PATH . "fileprint_16x16_2.png\" width=\"16\" height=\"16\" border=\"0\" title=\"". _('editions_edit_again') ."\" alt=\"". _('editions_edit_again') ."\">" .
                        " ". _('editions_edit_again')  .
                        "</a>\n";
                $text_edit_pdf_a_nouveau="<a href=\"edition_pdf.php?user_login=$login&edit_id=$id_edition\">" .
                        "<img src=\"". IMG_PATH . "pdf_16x16_2.png\" width=\"16\" height=\"16\" border=\"0\" title=\"". _('editions_edit_again_pdf') ."\" alt=\"". _('editions_edit_again_pdf') ."\">" .
                        " ". _('editions_edit_again_pdf')  .
                        "</a>\n";

                $return .= '<tr align="center">';
                $return .= '<td>' . $tab_ed['num_for_user'] . '</td>';
                $return .= '<td class="histo-big">' . $tab_ed['date'] . '</td>';
                foreach($tab_type_cong as $id_abs => $libelle) {
                    $return .= '<td>' . $tab_ed['conges'][$id_abs] . '</td>';
                }

                $return .= '<td>' . $text_edit_a_nouveau . '</td>';
                $return .= '<td>' . $text_edit_pdf_a_nouveau . '</td>';
                $return .= '</tr>';
            }
            $return .= '</tbody></table>';
        }
        $return .= '<br>';

        $return .= '</CENTER>';
        $return .= '<hr align="center" size="2" width="90%">';
        return $return;
    }

    public static function affiche_nouvelle_edition($login)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $return = '';
        $return .= '<CENTER>';

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

        $return .= '<h3>' . _('editions_last_edition') . ' :</h3>';

        $count2=$ReqLog2->num_rows;
        if ($count2==0) {
            $return .= '<b>' . _('editions_aucun_conges') . '</b><br>';
        } else {
            // AFFICHAGE TABLEAU
            if ($config->canAfficheDateTraitement()) {
                $return .= '<table cellpadding="2" class="tablo" width="850">';
            } else {
                $return .= '<table cellpadding="2" class="tablo" width="750">';
            }
            $return .= '<thead><tr align="center">';
            $return .= '<th>' . _('divers_type_maj_1') . '</th>';
            $return .= '<th>' . _('divers_etat_maj_1') . '</th>';
            $return .= '<th>' . _('divers_nb_jours_maj_1') . '</th>';
            $return .= '<th>' . _('divers_debut_maj_1') . '</th>';
            $return .= '<th>' . _('divers_fin_maj_1') . '</th>';
            $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
            if ($config->canAfficheDateTraitement()) {
                $return .= '<th>' . _('divers_date_traitement') . '</td>';
            }
            $return .= '</tr></thead></tbody>';

            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
                $sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
                if ($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
                $sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
                $sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
                if ($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
                $sql_p_nb_jours = $resultat2["p_nb_jours"];
                $sql_p_commentaire = $resultat2["p_commentaire"];
                $sql_p_type = $resultat2["ta_libelle"];
                $sql_p_etat = $resultat2["p_etat"];
                $sql_p_date_demande = $resultat2["p_date_demande"];
                $sql_p_date_traitement = $resultat2["p_date_traitement"];

                $return .= '<tr align="center">';
                $return .= '<td>' . $sql_p_type . '</td>';
                $return .= '<td>';
                if ($sql_p_etat=="refus") {
                    $return .= _('divers_refuse');
                } elseif ($sql_p_etat=="annul") {
                    $return .= _('divers_annule');
                } else {
                    $return .= $sql_p_etat;
                }
                $return .= '</td>';
                if ($sql_p_etat=="ok") {
                    $return .= '<td class="histo-big"> -' . $sql_p_nb_jours . '</td>';
                } elseif ($sql_p_etat=="ajout") {
                    $return .= '<td class="histo-big"> +' . $sql_p_nb_jours . '</td>';
                } else {
                    $return .= '<td>' . $sql_p_nb_jours . '</td>';
                }
                $return .= '<td>' . $sql_p_date_deb . '_' . $demi_j_deb . '</td>';
                $return .= '<td>' . $sql_p_date_fin . '_' . $demi_j_fin . '</td>';
                $return .= '<td>' . $sql_p_commentaire . '</td>';
                if ($config->canAfficheDateTraitement()) {
                    if ($sql_p_date_demande == NULL) {
                        $return .= '<td class="histo-left">' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    }
                    else {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    }
                }
                $return .= '</tr>';
            }
            $return .= '</tbody></table>';
            $return .= '<br>';

            /******************/
            /* bouton editer  */
            /******************/
            $return .= '<table cellpadding="2" width="400">';
            $return .= '<tr align="center">';
            $return .= '<td width="200">';
            $return .= '<a href="edition_papier.php?user_login=' . $login . '&edit_id=0">';
            $return .= '<img src="' . IMG_PATH . 'fileprint_2.png" width="22" height="22" border="0" title="' . _('editions_lance_edition') . '" alt="' . _('editions_lance_edition') . '">';
            $return .= '<b>' . _('editions_lance_edition') . '</b>';
            $return .= '</a>';
            $return .= '</td>';
            $return .= '<td width="200">';
            $return .= '<a href="edition_pdf.php?user_login=' . $login . '&edit_id=0">';
            $return .= '<img src="' . IMG_PATH . 'pdf_22x22_2.png" width="22" height="22" border="0" title="' . _('editions_pdf_edition') . '" alt="' . _('editions_pdf_edition') . '">';
            $return .= '<b>' . _('editions_pdf_edition') . '</b>';
            $return .= '</a>';
            $return .= '</td>';
            $return .= '</tr>';
            $return .= '</table>';

        }
        $return .= '<br>';
        $return .= '</CENTER>';
        $return .= '<hr align="center" size="2" width="90%">';
        return $return;
    }

    public static function affichage($login)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';

        $sql1 = 'SELECT u_nom, u_prenom, u_quotite FROM conges_users where u_login = "'. \includes\SQL::quote($login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array()) {
            $sql_nom=$resultat1["u_nom"];
            $sql_prenom=$resultat1["u_prenom"];
            $sql_quotite=$resultat1["u_quotite"];
        }

        // TITRE
        $return .= '<H1>' . $sql_prenom . ' ' . $sql_nom . ' (' . $login . ')</H1>';

        /********************/
        /* Bilan des Conges */
        /********************/
        // affichage du tableau récapitulatif des solde de congés d'un user
        $return .= affiche_tableau_bilan_conges_user($login);
        $return .= '<br><br><br>';

        $return .= \edition\Fonctions::affiche_nouvelle_edition($login);

        $return .= \edition\Fonctions::affiche_anciennes_editions($login);
        return $return;
    }

    /**
     * Encapsule le comportement du module d'édition des utilisateurs
     *
     * @return void
     * @access public
     * @static
     */
    public static function editUserModule()
    {
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $user_login = htmlentities(getpost_variable('user_login', $_SESSION['userlogin']), ENT_QUOTES | ENT_HTML401);
        $return = '';
        header_menu( _('editions_titre') . ' : ' . $user_login);


        /*************************************/

        if ($user_login != $_SESSION['userlogin'] && !is_hr($_SESSION['userlogin']) && !\App\ProtoControllers\Responsable::isRespDeUtilisateur($_SESSION['userlogin'] , $user_login)) {
            redirect(ROOT_PATH . 'deconnexion.php');
            exit;
        }

        /************************************/
        $return .= '<div class="main-content">';
        $return .= \edition\Fonctions::affichage($user_login);
        $return .= '</div>';

        return $return;
    }

    // affichage du tableau récapitulatif des solde de congés d'un user d'une edition donnée !
    public static function affiche_tableau_bilan_conges_user_edition($tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels)
    {
        $return = '';

        $return .= '<table cellpadding="2" width="250" class="tablo">';
        //    echo "<tr align=\"center\"><td class=\"titre\" colspan=\"3\"> quotité &nbsp; : &nbsp; $quotite % </td></tr>\n" ;
        $return .= '<thead><tr>';
        $return .= '<th></th>';
        $return .= '<th>' . _('editions_jours_an') . '</th>';
        $return .= '<th>' . _('divers_solde_maj') . '</th>';
        $return .= '</tr></thead><tbody>';

        foreach($tab_type_cong as $id_abs => $libelle) {
            $return .= '<tr><td>' . $libelle . '</td><td>' . $tab_info_user['conges'][$libelle]['nb_an']. '</td><td align="center" bgcolor="#FF9191"><b>' . $tab_info_edition['conges'][$id_abs] . '</b></td>';
        }
        foreach($tab_type_conges_exceptionnels as $id_abs => $libelle) {
            $return .= '<tr><td>' . $libelle . '</td><td>' . $tab_info_user['conges'][$libelle]['nb_an'] . '</td><td align="center" bgcolor="#FF9191"><b>'. $tab_info_edition['conges'][$id_abs] . '</b></td>';
        }
        $return .= '</tr>';
        $return .= '</tbody></table>';

        return $return;
    }

    public static function edition_papier($login, $edit_id)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $return = '';

        // recup infos du user
        $tab_info_user = \edition\Fonctions::recup_info_user_pour_edition($login);

        // recup infos de l'édition
        $tab_info_edition = \edition\Fonctions::recup_info_edition($edit_id);

        // recup du tableau des types de conges exceptionnels (seulement les conge sexceptionnels )
        $tab_type_cong=recup_tableau_types_conges();
        // recup du tableau des types de conges (seulement les conges)
        if ($config->isCongesExceptionnelsActive()) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        } else {
            $tab_type_conges_exceptionnels=array();
        }
        // recup du tableau de tous les types de conges
        $tab_type_all_cong=recup_tableau_tout_types_abs();

        /**************************************/
        /* affichage du texte en haut de page */
        /**************************************/
        $return .= '<table cellpadding="0" cellspacing="0" border="0" width="770">';
        $return .= '<tr align="center">';
        $return .= '<td>' . $config->getTextHaut() . '<br><br></td>';
        $return .= '</tr>';
        $return .= '</table>';

        /**************************************/
        /* affichage du TITRE                 */
        /**************************************/
        $return .= '<H1>' . $tab_info_user['nom'] . ' ' . $tab_info_user['prenom'] . '</H1>';
        $tab_date=explode("-", $tab_info_edition['date']);
        $return .= '<H2>' . _('editions_bilan_au') . ' ' . $tab_date[2] . '/' . $tab_date[1] . '/' . $tab_date[0] . '</H2>';


        /****************************/
        /* tableau Bilan des Conges */
        /****************************/
        // affichage du tableau récapitulatif des solde de congés d'un user DE cette edition !
        $return .= \edition\Fonctions::affiche_tableau_bilan_conges_user_edition($tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels);

        $quotite=$tab_info_user['quotite'];
        $return .= '<h3>' . _('divers_quotite') . '&nbsp; : &nbsp;' . $quotite . ' %</h3>';
        $return .= '<br><br><br>';


        if ($config->canAfficheDateTraitement()) {
            $return .= '<table cellpadding="0" cellspacing="0" border="1" width="870">';
        } else {
            $return .= '<table cellpadding="0" cellspacing="0" border="1" width="770">';
        }
        $return .= '<tr align="center">';
        $return .= '<td><h3>' . _('editions_historique') . ' :</h3></td>';
        $return .= '</tr>';

        /*********************************************/
        /* Tableau Historique des Conges et demandes */
        /*********************************************/
        $return .= '<!-- Tableau Historique des Conges et demandes -->';
        $return .= '<tr align="center">';
        $return .= '<td>';

        // Récupération des informations
        // on ne recup QUE les periodes de l'edition choisie
        $sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_date_demande, p_date_traitement ";
        $sql2=$sql2."FROM conges_periode ";
        $sql2=$sql2."WHERE p_edition_id = $edit_id ";
        $sql2=$sql2."ORDER BY p_date_deb ASC ";
        $ReqLog2 = \includes\SQL::query($sql2);

        $count2=$ReqLog2->num_rows;
        if ($count2==0) {
            $return .= '<b>' . _('editions_aucun_conges') . '</b><br>';
        } else {
            // AFFICHAGE TABLEAU
            if ($config->canAfficheDateTraitement()) {
                $return .= '<table cellpadding="2" class="tablo-edit" width="850">';
            } else {
                $return .= '<table cellpadding="2" class="tablo-edit" width="750">';
            }

            /*************************************/
            /* affichage anciens soldes          */
            /*************************************/
            $return .= '<!-- affichage anciens soldes -->';
            $return .= '<tr>';
            $return .= '<td colspan="5">';
            $edition_precedente_id = \edition\Fonctions::get_id_edition_precedente_user($login, $edit_id);
            if ($edition_precedente_id==0) {
                $return .= '<b>' . _('editions_soldes_precedents_inconnus') . '!... ';
            } else {
                $tab_edition_precedente = \edition\Fonctions::recup_info_edition($edition_precedente_id);
                foreach($tab_type_cong as $id_abs => $libelle) {
                    $return .= _('editions_solde_precedent') . ' <b>' . $libelle . ' : ' . $tab_edition_precedente['conges'][$id_abs] . '</b><br>';
                }
                foreach($tab_type_conges_exceptionnels as $id_abs => $libelle) {
                    $return .= _('editions_solde_precedent') . ' <b>' . $libelle . ' : ' . $tab_edition_precedente['conges'][$id_abs] . '</b><br>';
                }
            }

            $return .= '<td>';
            $return .= '</tr>';


            /*************************************/
            /* affichage lignes de l'edition     */
            /*************************************/
            $return .= '<!-- affichage lignes de l\'edition -->';
            $return .= '<tr>';
            $return .= '<td class="titre-edit">' . _('divers_type_maj_1') . '</td>';
            $return .= '<td class="titre-edit">' . _('divers_etat_maj_1') . '</td>';
            $return .= '<td class="titre-edit">' . _('divers_nb_jours_maj_1') . '</td>';
            $return .= '<td class="titre-edit">' . _('divers_debut_maj_1') . '</td>';
            $return .= '<td class="titre-edit">' . _('divers_fin_maj_1') . '</td>';
            $return .= '<td class="titre-edit">' . _('divers_comment_maj_1') . '</td>';
            if ($config->canAfficheDateTraitement()) {
                $return .= '<td class="titre-edit">' . _('divers_date_traitement') . '</td>';
            }
            $return .= '</tr>';

            while ($resultat2 = $ReqLog2->fetch_array()) {
                $sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
                $sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
                if ($sql_p_demi_jour_deb=="am") {
                    $demi_j_deb =  _('divers_am_short') ;
                } else {
                    $demi_j_deb =  _('divers_pm_short') ;
                }
                $sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
                $sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
                if ($sql_p_demi_jour_fin=="am") {
                    $demi_j_fin =  _('divers_am_short') ;
                } else {
                    $demi_j_fin =  _('divers_pm_short') ;
                }
                $sql_p_nb_jours = $resultat2["p_nb_jours"];
                $sql_p_commentaire = $resultat2["p_commentaire"];
                $sql_p_type = $resultat2["p_type"];
                $sql_p_etat = $resultat2["p_etat"];
                $sql_p_date_demande = $resultat2["p_date_demande"];
                $sql_p_date_traitement = $resultat2["p_date_traitement"];

                $return .= '<tr>';
                $return .= '<td class="histo-edit">' . $tab_type_all_cong[$sql_p_type]['libelle'] . '</td>';
                $return .= '<td class="histo-edit">';
                if ($sql_p_etat=="refus") {
                    $return .= _('divers_refuse') ;
                } elseif ($sql_p_etat=="annul") {
                    $return .= _('divers_annule') ;
                } else {
                    $return .= '"' . $sql_p_etat . '"';
                }
                $return .= '</td>';
                if ($sql_p_etat=="ok") {
                    $return .= '<td class="histo-big"> -' . $sql_p_nb_jours . '</td>';
                } elseif ($sql_p_etat=="ajout") {
                    $return .= '<td class="histo-big"> +' . $sql_p_nb_jours . '</td>';
                } else {
                    $return .= '<td> ' . $sql_p_nb_jours . '</td>';
                }
                $return .= '<td class="histo-edit">' . $sql_p_date_deb . '_' .  $demi_j_deb . '</td>';
                $return .= '<td class="histo-edit">' . $sql_p_date_fin . '_' .  $demi_j_fin . '</td>';
                $return .= '<td class="histo-edit">' . $sql_p_commentaire . '</td>';

                if ($config->canAfficheDateTraitement()) {
                    if ($sql_p_date_demande == NULL) {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : ' . $sql_p_date_traitement . '</td>';
                    } else {
                        $return .= '<td class="histo-left">' . _('divers_demande') . ' : ' . $sql_p_date_demande . '<br>' . _('divers_traitement') . ' : pas traité</td>';
                    }
                }
                $return .= '</tr>';
            }

            /*************************************/
            /* affichage nouveaux soldes         */
            /*************************************/
            $return .= '<!-- affichage nouveaux soldes -->';
            $return .= '<tr>';
            $return .= '<td colspan="5">';
            foreach($tab_type_cong as $id_abs => $libelle) {
                $return .= _('editions_nouveau_solde') . ' <b>' . $libelle . ': ' . $tab_info_edition['conges'][$id_abs] . '</b><br>';
            }
            $return .= '<td>';
            $return .= '</tr>';
            $return .= '</table>';
        }
        $return .= '<br><br>';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';

        /*************************************/
        /* affichage des zones de signature  */
        /*************************************/
        $return .= '<!-- affichage des zones de signature -->';
        $return .= '<br>';
        $return .= '<table cellpadding="0" cellspacing="0" border="0" width="770">';
        $return .= '<tr align="center">';
        $return .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $return .= '<td align="left">';
        $return .= '<b>' . _('editions_date') . ' : <br>' . _('editions_signature_1') . ' :</b><br><br><br><br><br><br><br><br><br><br>';
        $return .= '</td>';
        $return .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $return .= '<td align="left">';
        $return .= '<b>' . _('editions_date') . ' : <br>' . _('editions_signature_2') . ' :</b><br><i>(' . _('editions_cachet_etab') . ')</i><br><br><br><br><br><br><br><br><br>';
        $return .= '</td>';
        $return .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $return .= '</tr>';
        $return .= '</table>';


        /*************************************/
        /* affichage du texte en bas de page */
        /*************************************/
        $return .= '<!-- affichage du texte en bas de page -->';
        $return .= '<table cellpadding="0" cellspacing="0" border="0" width="770">';
        $return .= '<tr align="center">';
        $return .= '<td><br>' . $config->getTextBas() . '</td>';
        $return .= '</tr>';
        $return .= '</table>';

        return $return;
    }

    /**
     * Encapsule le comportement du module de l'édition papier
     *
     * @return void
     * @access public
     * @static
     */
    public static function editPapierModule()
    {
        /*************************************/
        // recup des parametres reçus :
        // GET / POST
        $user_login = getpost_variable('user_login') ;
        $edit_id = getpost_variable('edit_id', 0) ;
        $return = '';
        /*************************************/

        if ($user_login != $_SESSION['userlogin'] && !is_hr($_SESSION['userlogin']) && !\App\ProtoControllers\Responsable::isRespDeUtilisateur($_SESSION['userlogin'] , $user_login)) {
            redirect(ROOT_PATH . 'deconnexion.php');
            exit;
        }

        /************************************/

        $css = '<link href="'. CSS_PATH .'style_calendar_edition.css" rel="stylesheet" type="text/css">';

        header_popup(_('editions_etat_conges').' : '.$user_login , $css);

        if ($edit_id==0) {  // si c'est une nouvelle édition, on insert dans la base avant d'éditer et on renvoit l'id de l'édition
            $edit_id = \edition\Fonctions::enregistrement_edition($user_login);
        }

        $return .= \edition\Fonctions::edition_papier($user_login, $edit_id);

        $comment_log = "edition papier (num_edition = $edit_id) ($user_login) ";
        log_action(0, "", $user_login, $comment_log);

        $return .= '<br><script type="text/javascript" language="javascript1.2">
        if (typeof(window.print) != \'undefined\') {
            window.print();
        }
        </script>';
        return $return;
    }

    public static function affiche_tableau_conges_normal(&$pdf, $ReqLog2, $decalage, $tab_type_all_cong)
    {

        // (largeur totale page = 210 ( - 2x10 de marge))
        // tailles des cellules du tableau
        $size_cell_type = 30;
        $size_cell_etat = 15;
        $size_cell_nb_jours = 20;  //90
        $size_cell_debut = 30;
        $size_cell_fin = 30;
        $size_cell_comment = 60;
        //          total = 190 (185+decalage)

        /*************************************/
        /* affichage lignes de l'edition     */
        /*************************************/
        // decalage pour centrer
        $pdf->Cell($decalage);

        //$pdf->SetFont('Times', 'B', 10);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell($size_cell_type, 5,  _('divers_type_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_etat, 5,  _('divers_etat_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_nb_jours, 5,  _('divers_nb_jours_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_debut, 5,  _('divers_debut_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_fin, 5,  _('divers_fin_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_comment, 5,  _('divers_comment_maj_1') , 1, 1, 'C', 1);

        while ($resultat2 = $ReqLog2->fetch_array()) {
            $sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
            $sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
            if ($sql_p_demi_jour_deb=="am") {
                $demi_j_deb =  _('divers_am_short') ;
            } else {
                $demi_j_deb =  _('divers_pm_short') ;
            }
            $sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
            $sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
            if ($sql_p_demi_jour_fin=="am") {
                $demi_j_fin =  _('divers_am_short') ;
            } else {
                $demi_j_fin =  _('divers_pm_short') ;
            }
            $sql_p_nb_jours = $resultat2["p_nb_jours"];
            $sql_p_commentaire = html_entity_decode($resultat2["p_commentaire"], ENT_QUOTES);
            $sql_p_type = $resultat2["p_type"];
            $sql_p_etat = $resultat2["p_etat"];
            $sql_p_date_demande = $resultat2["p_date_demande"];
            $sql_p_date_traitement = $resultat2["p_date_traitement"];

            // decalage pour centrer
            $pdf->Cell($decalage);
            $hauteur_cellule=5;

            $taille_font=10;

            $pdf->SetFont('Times', '', $taille_font);

            $pdf->Cell($size_cell_type, $hauteur_cellule, $tab_type_all_cong[$sql_p_type]['libelle'], 1, 0, 'C');

            if ($sql_p_etat=="refus") {
                $text_etat =  _('divers_refuse') ;
            } elseif ($sql_p_etat=="annul") {
                $text_etat =  _('divers_annule') ;
            } else {
                $text_etat=$sql_p_etat;
            }
            $pdf->Cell($size_cell_etat, $hauteur_cellule, $text_etat, 1, 0, 'C');

            if ( ($sql_p_etat=="refus") || ($sql_p_etat=="annul") ) {
                $pdf->SetFont('Times', '', $taille_font);
            } else {
                $pdf->SetFont('Times', 'B', $taille_font);
            }

            if ($sql_p_etat=="ok") {
                $text_nb_jours="-".$sql_p_nb_jours;
            } elseif ($sql_p_etat=="ajout") {
                $text_nb_jours="+".$sql_p_nb_jours;
            } else {
                $text_nb_jours=$sql_p_nb_jours;
            }
            $pdf->Cell($size_cell_nb_jours, $hauteur_cellule, $text_nb_jours, 1, 0, 'C');

            $pdf->SetFont('Times', '', $taille_font);
            $pdf->Cell($size_cell_debut, $hauteur_cellule, $sql_p_date_deb." _ ".$demi_j_deb, 1, 0, 'C');
            $pdf->Cell($size_cell_fin, $hauteur_cellule, $sql_p_date_fin." _ ".$demi_j_fin, 1, 0, 'C');
            // reduction de la taille du commentaire pour rentrer dans la cellule
            if (strlen($sql_p_commentaire)>39) {
                $sql_p_commentaire = substr($sql_p_commentaire, 0, 35)." ..." ;
            }
            $pdf->Cell($size_cell_comment, $hauteur_cellule, $sql_p_commentaire, 1, 1, 'C');
        }
    }

    public static function affiche_tableau_conges_avec_date_traitement(&$pdf, $ReqLog2, $decalage, $tab_type_all_cong)
    {
        // (largeur totale page = 210 ( - 2x10 de marge))
        // tailles des cellules du tableau
        $size_cell_type = 25;
        $size_cell_etat = 15;
        $size_cell_nb_jours = 15;  //90
        $size_cell_debut = 25;
        $size_cell_fin = 25;
        $size_cell_comment = 40;
        $size_cell_date_traitement = 40;
        //          total = 190 (185+decalage)

        /*************************************/
        /* affichage lignes de l'edition     */
        /*************************************/
        // decalage pour centrer
        $pdf->Cell($decalage);

        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell($size_cell_type, 5,  _('divers_type_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_etat, 5,  _('divers_etat_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_nb_jours, 5,  _('divers_nb_jours_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_debut, 5,  _('divers_debut_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_fin, 5,  _('divers_fin_maj_1') , 1, 0, 'C', 1);
        $pdf->Cell($size_cell_comment, 5,  _('divers_comment_maj_1') , 1, 1, 'C', 1);

        while ($resultat2 = $ReqLog2->fetch_array()) {
            $sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
            $sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
            if ($sql_p_demi_jour_deb=="am") {
                $demi_j_deb =  _('divers_am_short') ;
            } else {
                $demi_j_deb =  _('divers_pm_short') ;
            }
            $sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
            $sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
            if ($sql_p_demi_jour_fin=="am") {
                $demi_j_fin =  _('divers_am_short') ;
            } else {
                $demi_j_fin =  _('divers_pm_short') ;
            }
            $sql_p_nb_jours = $resultat2["p_nb_jours"];
            $sql_p_commentaire = html_entity_decode($resultat2["p_commentaire"], ENT_QUOTES);
            $sql_p_type = $resultat2["p_type"];
            $sql_p_etat = $resultat2["p_etat"];
            $sql_p_date_demande = $resultat2["p_date_demande"];
            $sql_p_date_traitement = $resultat2["p_date_traitement"];

            // decalage pour centrer
            $pdf->Cell($decalage);
            $hauteur_cellule=5;

            $taille_font=8;

            $pdf->SetFont('Times', '', $taille_font);

            $pdf->Cell($size_cell_type, $hauteur_cellule*2, $tab_type_all_cong[$sql_p_type]['libelle'], 1, 0, 'C');

            if ($sql_p_etat=="refus")
                $text_etat =  _('divers_refuse') ;
            elseif ($sql_p_etat=="annul")
                $text_etat =  _('divers_annule') ;
            else
                $text_etat=$sql_p_etat;
            $pdf->Cell($size_cell_etat, $hauteur_cellule*2, $text_etat, 1, 0, 'C');

            if ( ($sql_p_etat=="refus") || ($sql_p_etat=="annul") )
                $pdf->SetFont('Times', '', $taille_font);
            else
                $pdf->SetFont('Times', 'B', $taille_font);

            if ($sql_p_etat=="ok")
                $text_nb_jours="-".$sql_p_nb_jours;
            elseif ($sql_p_etat=="ajout")
                $text_nb_jours="+".$sql_p_nb_jours;
            else
                $text_nb_jours=$sql_p_nb_jours;
            $pdf->Cell($size_cell_nb_jours, $hauteur_cellule*2, $text_nb_jours, 1, 0, 'C');

            $pdf->SetFont('Times', '', $taille_font);
            $pdf->Cell($size_cell_debut, $hauteur_cellule*2, $sql_p_date_deb." _ ".$demi_j_deb, 1, 0, 'C');
            $pdf->Cell($size_cell_fin, $hauteur_cellule*2, $sql_p_date_fin." _ ".$demi_j_fin, 1, 0, 'C');
            // reduction de la taille du commentaire pour rentrer dans la cellule
            if (strlen($sql_p_commentaire)>39)
                $sql_p_commentaire = substr($sql_p_commentaire, 0, 35)." ..." ;

            $pdf->Cell($size_cell_comment, $hauteur_cellule*2, $sql_p_commentaire."\n ", 1, 'L');
            $pdf->MultiCell($size_cell_date_traitement, $hauteur_cellule, "demande : ".$sql_p_date_demande."\ntraitement : ".$sql_p_date_traitement , 1, 'L' );
        }
    }

    // affichage en pdf des nouveaux soldes de congés d'un user
    public static function affiche_pdf_nouveau_solde(&$pdf, $login, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage)
    {

        foreach($tab_type_cong as $id_abs => $libelle)
        {
            $pdf->Cell($decalage);
            $pdf->SetFont('Times', '', 10);
            $pdf->Cell(24, 5,  _('editions_nouveau_solde') ." ",0,0);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->Cell(40, 5, $libelle." : ".$tab_info_edition['conges'][$id_abs], 0, 1);
        }
        foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
        {
            $pdf->Cell($decalage);
            $pdf->SetFont('Times', '', 10);
            $pdf->Cell(24, 5,  _('editions_nouveau_solde') ." ",0,0);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->Cell(40, 5, $libelle." : ".$tab_info_edition['conges'][$id_abs], 0, 1);
        }
    }

    // affichage en pdf des anciens soldes de congés d'un user
    public static function affiche_pdf_ancien_solde(&$pdf, $login, $edit_id, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage)
    {
        //    $pdf->SetFont('Times', 'B', 10);

        $edition_precedente_id = \edition\Fonctions::get_id_edition_precedente_user($login, $edit_id);
        if ($edition_precedente_id==0)
        {
            $pdf->Cell($decalage);
            $pdf->SetFont('Times', '', 10);
            $pdf->Cell(50, 5,  _('editions_soldes_precedents_inconnus') ." !...",0,1);
        }
        else
        {
            $tab_edition_precedente = \edition\Fonctions::recup_info_edition($edition_precedente_id);

            foreach($tab_type_cong as $id_abs => $libelle)
            {
                $pdf->Cell($decalage);
                $pdf->SetFont('Times', '', 10);
                $pdf->Cell(26, 5,  _('editions_solde_precedent') ." ",0,0);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->Cell(10, 5, $libelle." : ".$tab_edition_precedente['conges'][$id_abs], 0, 1);
            }
            foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
            {
                $pdf->Cell($decalage);
                $pdf->SetFont('Times', '', 10);
                $pdf->Cell(26, 5,  _('editions_solde_precedent') ." ",0,0);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->Cell(10, 5, $libelle." : ".$tab_edition_precedente['conges'][$id_abs], 0, 1);
            }
        }
    }

    // affichage en pdf du tableau récapitulatif des solde de congés d'un user
    public static function affiche_pdf_tableau_bilan_conges_user_edtion(&$pdf, $tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels)
    {
        // calcul du décalage pour centrer ( = (21cm - (marges x 2) - (sommes des cell définies en dessous) )/2  ) (marges=10mm)
        $decalage = 55 ;

        // affichage :
        $pdf->SetFont('Times', 'B', 11);

        $pdf->Cell($decalage);
        $pdf->Cell(40, 5, " ", 1, 0, 'C');
        $pdf->Cell(20, 5, " ". _('editions_jours_an') , 1, 0, 'C');
        $pdf->Cell(20, 5,  _('divers_solde_maj_1') ." ", 1, 1, 'C');

        foreach($tab_type_cong as $id_abs => $libelle) {
            $pdf->Cell($decalage);
            $pdf->Cell(40, 5, " $libelle ", 1, 0, 'C');
            $pdf->Cell(20, 5, $tab_info_user['conges'][$libelle]['nb_an'], 1, 0, 'C');
            $pdf->Cell(20, 5, $tab_info_edition['conges'][$id_abs], 1, 1, 'C', 1);
        }
        foreach($tab_type_conges_exceptionnels as $id_abs => $libelle) {
            $pdf->Cell($decalage);
            $pdf->Cell(40, 5, " $libelle ", 1, 0, 'C');
            $pdf->Cell(20, 5, $tab_info_user['conges'][$libelle]['nb_an'], 1, 0, 'C');
            $pdf->Cell(20, 5, $tab_info_edition['conges'][$id_abs], 1, 1, 'C', 1);
        }
        // passage à la ligne
        $pdf->Ln();

    }

    public static function edition_pdf($login, $edit_id)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        // recup du tableau des types de conges (seulement les conges)
        $tab_type_cong=recup_tableau_types_conges();
        // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
        if ($config->isCongesExceptionnelsActive()) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
        } else {
            $tab_type_conges_exceptionnels=array();
        }
        // recup du tableau de tous les types de conges
        $tab_type_all_cong=recup_tableau_tout_types_abs();

        // recup infos du user
        $tab_info_user = \edition\Fonctions::recup_info_user_pour_edition($login);

        // recup infos de l'édition
        $tab_info_edition = \edition\Fonctions::recup_info_edition($edit_id);


        /**************************************/
        /* on commence l'affichage ...        */
        /**************************************/
        header('content-type: application/pdf');
        //header('content-Disposition: attachement; filename="downloaded.pdf"');    // pour IE

        $pdf=new \edition\PDF( 'P', 'mm', 'A4', true, "UTF-8");
        $pdf->AddPage();
        $pdf->SetFillColor(200);

        /**************************************/
        /* affichage du texte en haut de page */
        /**************************************/
        // fait dans le header de la classe (cf + haut)

        /**************************************/
        /* affichage du TITRE                 */
        /**************************************/
        $pdf->SetFont('Times', 'B', 18);
        $pdf->Cell(0, 5, $tab_info_user['nom']." ".  $tab_info_user['prenom'],0,1,'C');
        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 13);
        $tab_date=explode("-", $tab_info_edition['date']);
        $pdf->Cell(0, 5,  _('editions_bilan_au') ." ".$tab_date[2]." / ".$tab_date[1]." / ".$tab_date[0],0,1,'C');
        $pdf->Ln(4);

        /****************************/
        /* tableau Bilan des Conges */
        /****************************/
        // affichage en pdf du tableau récapitulatif des solde de congés d'un user
        \edition\Fonctions::affiche_pdf_tableau_bilan_conges_user_edtion($pdf, $tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels) ;

        // affichage de la quotité
        $pdf->SetFont('Times', 'B', 13);
        $quotite=$tab_info_user['quotite'];
        $pdf->Cell(0, 5,  _('divers_quotite') ."  :  $quotite % ",0,1,'C');
        $pdf->Ln(4);
        $pdf->Ln(8);


        $pdf->SetFont('Times', 'BU', 11);
        $pdf->Cell(0, 5,  _('editions_historique') ." :",0,1,'C');
        $pdf->Ln(5);
        /*********************************************/
        /* Tableau Historique des Conges et demandes */
        /*********************************************/
        $pdf->SetFont('Times', 'B', 10);

        //test d'une ligne à 120 caractères
        //$ligne120="123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890";
        //$pdf->Cell(0, 5, $ligne120 ,0,1,'C');

        // Récupération des informations
        // on ne recup QUE les periodes de l'edition choisie
        $sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_date_demande, p_date_traitement ";
        $sql2=$sql2."FROM conges_periode ";
        $sql2=$sql2."WHERE p_edition_id = $edit_id ";
        $sql2=$sql2."ORDER BY p_date_deb ASC ";
        $ReqLog2 = \includes\SQL::query($sql2) ;

        $count2=$ReqLog2->num_rows;
        if ($count2==0) {
            $pdf->Cell(0, 5,  _('editions_aucun_conges') ." ...",0,1,'C');
            $pdf->Ln(5);
        } else {
            // AFFICHAGE TABLEAU
            // decalage pour centrer
            $decalage = 5;

            /*************************************/
            /* affichage anciens soldes          */
            /*************************************/
            // affichage en pdf des anciens soldes de congés d'un user
            \edition\Fonctions::affiche_pdf_ancien_solde($pdf, $login, $edit_id, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage) ;

            $pdf->Ln(2);

            // (largeur totale page = 210 ( - 2x10 de marge))
            // tailles des cellules du tableau
            if ($config->canAfficheDateTraitement()) {
                \edition\Fonctions::affiche_tableau_conges_avec_date_traitement($pdf, $ReqLog2, $decalage, $tab_type_all_cong);
            } else {
                \edition\Fonctions::affiche_tableau_conges_normal($pdf, $ReqLog2, $decalage, $tab_type_all_cong);
            }

            $pdf->Ln(2);

            /*************************************/
            /* affichage nouveaux soldes         */
            /*************************************/
            // affichage en pdf des nouveaux soldes de congés d'un user
            \edition\Fonctions::affiche_pdf_nouveau_solde($pdf, $login, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage) ;
        }

        $pdf->Ln(8);

        /*************************************/
        /* affichage des zones de signature  */
        /*************************************/
        $pdf->SetFont('Times', 'B', 10);
        // decalage pour centrer
        $pdf->Cell(20);
        $pdf->Cell(70, 5,  _('editions_date') ." :",0,0);
        $pdf->Cell(70, 5,  _('editions_date') ." :",0,1);
        // decalage pour centrer
        $pdf->Cell(20);
        $pdf->Cell(70, 5,  _('editions_signature_1') ." :",0,0);
        $pdf->Cell(70, 5,  _('editions_signature_2') ." :",0,1);

        $pdf->SetFont('Times', 'I', 10);
        // decalage pour centrer
        $pdf->Cell(20);
        $pdf->Cell(70, 5, "",0,0);
        $pdf->Cell(70, 5, "(". _('editions_cachet_etab') .")",0,1);

        $pdf->Ln(30);

        /*************************************/
        /* affichage du texte en bas de page */
        /*************************************/
        // fait dans le footer de la classe (cf + haut)

        $pdf->Output();
    }

    /**
     * Encapsule le comportement du module d'édition PDF
     *
     * @return void
     * @access public
     * @static
     */
    public static function editPDFModule()
    {
        /*************************************/
        // recup des parametres reçus :
        // GET / POST
        $user_login = getpost_variable('user_login') ;
        $edit_id = getpost_variable('edit_id', 0) ;
        /*************************************/

        if ($user_login != $_SESSION['userlogin'] && !is_hr($_SESSION['userlogin']) && !\App\ProtoControllers\Responsable::isRespDeUtilisateur($_SESSION['userlogin'] , $user_login)) {
            redirect(ROOT_PATH . 'deconnexion.php');
            exit;
        }

        /************************************/
        if ($edit_id==0) {  // si c'est une nouvelle édition, on insert dans la base avant d'éditer et on renvoit l'id de l'édition
            $edit_id = \edition\Fonctions::enregistrement_edition($user_login);
        }

        \edition\Fonctions::edition_pdf($user_login, $edit_id);

        $comment_log = "edition PDF (num_edition = $edit_id) ($user_login) ";
        log_action(0, "", $user_login, $comment_log);
    }

    // Récupération des informations des editions du user
    // renvoit un tableau vide si pas de'edition pour le user
    public static function recup_editions_user($login)
    {
        $tab_ed=array();

        $sql2 = "SELECT ep_id, ep_date, ep_num_for_user ";
        $sql2=$sql2."FROM conges_edition_papier WHERE ep_login = '$login' ";
        $sql2=$sql2."ORDER BY ep_num_for_user DESC ";
        $ReqLog2 = \includes\SQL::query($sql2);

        if ($ReqLog2->num_rows != 0)
        {
            while ($resultat2 = $ReqLog2->fetch_array())
            {
                $tab=array();
                $sql_id = $resultat2["ep_id"];
                $tab['date'] = eng_date_to_fr($resultat2["ep_date"]);
                $tab['num_for_user'] = $resultat2["ep_num_for_user"];
                // recup du tab des soldes des conges pour cette edition
                $tab['conges'] = \edition\Fonctions::recup_solde_conges_of_edition($sql_id);

                $tab_ed[$sql_id]=$tab;
            }
        }
        return $tab_ed ;
    }

    // recup infos de l'édition
    // renvoit un tableau vide si pas de'edition pour le user
    public static function recup_info_edition($edit_id)
    {

        $tab=array();

        $sql_edition= 'SELECT ep_date, ep_num_for_user FROM conges_edition_papier where ep_id = '.\includes\SQL::quote($edit_id);
        $ReqLog_edition = \includes\SQL::query($sql_edition);

        if ($resultat_edition = $ReqLog_edition->fetch_array())
        {
            $tab['date']=$resultat_edition["ep_date"];
            $tab['num_for_user'] = $resultat_edition["ep_num_for_user"];
            // recup du tab des soldes des conges pour cette edition
            $tab['conges'] = \edition\Fonctions::recup_solde_conges_of_edition($edit_id);
        }
        return $tab ;
    }

    // recup infos du user
    public static function recup_info_user_pour_edition($login)
    {
        $tab=array();
        $sql_user = 'SELECT u_nom, u_prenom, u_quotite FROM conges_users where u_login = "'. \includes\SQL::quote($login).'"';
        $ReqLog_user = \includes\SQL::query($sql_user);

        while ($resultat_user = $ReqLog_user->fetch_array()) {
            $tab['nom']=$resultat_user["u_nom"];
            $tab['prenom']=$resultat_user["u_prenom"];
            $tab['quotite']=$sql_quotite=$resultat_user["u_quotite"];
        }

        // recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
        $tab['conges']=recup_tableau_conges_for_user($login, false) ;

        return $tab;
    }

    // recup du tab des soldes des conges pour cette edition
    public static function recup_solde_conges_of_edition($edition_id)
    {

        $tab=array();
        $sql_ed = 'SELECT se_id_absence, se_solde FROM conges_solde_edition where se_id_edition = '.\includes\SQL::quote($edition_id);
        $ReqLog_ed = \includes\SQL::query($sql_ed);

        $tab=array();
        while ($resultat_ed = $ReqLog_ed->fetch_array())
        {
            $id_absence=$resultat_ed["se_id_absence"];
            $tab[$id_absence]=$resultat_ed["se_solde"];
        }
        return $tab;
    }

    // renvoi le id de la table edition_papier de l'edition précédente pour un user donné et un edition_id donnée.
    public static function get_id_edition_precedente_user($login, $edition_id)
    {

        // verif si le user n'a pas une seule edition
        $sql1 = 'SELECT * FROM conges_edition_papier WHERE ep_login="'.\includes\SQL::quote($login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        $resultat1 = $ReqLog1->num_rows ;
        if ($resultat1<=1)    // une seule edition pour ce user
            return 0;
        else
        {
            $sql2 = 'SELECT MAX(ep_id) FROM conges_edition_papier WHERE ep_login="'. \includes\SQL::quote($login).'" AND ep_id<'.\includes\SQL::quote($edition_id);
            $ReqLog2 = \includes\SQL::query($sql2);
            $tmp = $ReqLog2->fetch_row();
            return $tmp[0];
        }
    }

    // renvoi le + grand num_par_user de la table edition_papier pour un user donné (le num de la derniere edition du user)
    public static function get_num_last_edition_user($login)
    {

        // verif si le user a une edition
        $sql1 = 'SELECT ep_num_for_user FROM conges_edition_papier WHERE ep_login="'. \includes\SQL::quote($login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        if ($ReqLog1->num_rows==0)
            return 0;    // c'est qu'il n'y a pas encore d'edition pour ce user
        else
        {
            $sql2 = 'SELECT MAX(ep_num_for_user) FROM conges_edition_papier WHERE ep_login="'. \includes\SQL::quote($login).'"';
            $ReqLog2 = \includes\SQL::query($sql2);
            $tmp = $ReqLog2->fetch_row();
            return $tmp[0];
        }
    }

    // renvoi le + grand id de la table edition_papier (l'id de la derniere edition)
    public static function get_last_edition_id()
    {
        // verif si table edition pas vide
        $sql1 = "SELECT ep_id FROM conges_edition_papier ";
        $ReqLog1 = \includes\SQL::query($sql1);

        if ($ReqLog1->num_rows==0)
            return 0;    // c'est qu'il n'y a pas encore d'edition
        else
        {
            $sql2 = 'SELECT MAX(ep_id) FROM conges_edition_papier ';
            $ReqLog2 = \includes\SQL::query($sql2);
            $tmp = $ReqLog2->fetch_row();
            return $tmp[0];
        }
    }

    public static function enregistrement_edition($login)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        $tab_solde_user=array();
        $sql1 = 'SELECT su_abs_id, su_solde FROM conges_solde_user where su_login = "'. \includes\SQL::quote($login).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array())
        {
            $sql_id=$resultat1["su_abs_id"];
            $tab_solde_user[$sql_id]=$resultat1["su_solde"];
        }
        $new_edition_id = \edition\Fonctions::get_last_edition_id()+1;
        $aujourdhui = date("Y-m-d");
        $num_for_user = \edition\Fonctions::get_num_last_edition_user($login)+1;

        /*************************************************/
        /* Insertion dans le table conges_edition_papier */
        /*************************************************/
        $sql_insert = "INSERT INTO conges_edition_papier
                SET ep_id=$new_edition_id, ep_login='$login', ep_date='$aujourdhui', ep_num_for_user=$num_for_user ";
        $result_insert = \includes\SQL::query($sql_insert);


        /*************************************************/
        /* Insertion dans le table conges_solde_edition  */
        /*************************************************/
        // recup du tableau des types de conges (seulement les conges)
        $tab_type_cong=recup_tableau_types_conges();
        foreach($tab_type_cong as $id_abs => $libelle)
        {
            $sql_insert_2 = "INSERT INTO conges_solde_edition
                    SET se_id_edition=$new_edition_id, se_id_absence=$id_abs, se_solde=$tab_solde_user[$id_abs] ";
            $result_insert_2 = \includes\SQL::query($sql_insert_2);
        }
        if ($config->isCongesExceptionnelsActive()) {
            $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
            foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
            {
                $sql_insert_3 = "INSERT INTO conges_solde_edition SET se_id_edition=$new_edition_id, se_id_absence=$id_abs, se_solde=$tab_solde_user[$id_abs] ";
                $result_insert_3 = \includes\SQL::query($sql_insert_3);
            }
        }

        /********************************************************************************************/
        /* Update du num edition dans la table periode pour les Conges et demandes de cette edition */
        /********************************************************************************************/
        // recup de la liste des id des absence de type conges !
        $sql_list="SELECT ta_id FROM conges_type_absence WHERE ta_type='conges' OR ta_type='conges_exceptionnels'";
        $ReqLog_list = \includes\SQL::query($sql_list);

        $list_abs_id="";
        while($resultat_list = $ReqLog_list->fetch_array())
        {
            if ($list_abs_id=="")
                $list_abs_id=$resultat_list['ta_id'] ;
            else
                $list_abs_id=$list_abs_id.", ".$resultat_list['ta_id'] ;
        }

        $sql_update = 'UPDATE conges_periode SET p_edition_id=\''.$new_edition_id.'\'
                WHERE p_login = \''.$login.'\'
                AND p_edition_id IS NULL
                AND (p_type IN ('.$list_abs_id.') )
                AND (p_etat!=\'demande\') ';
        $ReqLog_update = \includes\SQL::query($sql_update);

        return $new_edition_id;
    }
}
