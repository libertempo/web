<?php

namespace export;

/**
* Regroupement des fonctions liées à l'export
*/
class Fonctions
{
    public static function form_saisie($user, $date_debut, $date_fin)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    	$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

    	$date_today=date("d-m-Y");
    	if($date_debut=="")
    		$date_debut=$date_today;
    	if($date_fin=="")
    		$date_fin=$date_today;

    	$huser = hash_user($user);

    	header_popup();


    	echo "<center>\n";
    	echo "<h1>". _('export_cal_titre') ."</h1>\n";

    	echo _('button_export_2')."<br>";
    	echo " <a href='".ROOT_PATH."export/ics_export.php?usr=".$huser."'>" . $config->getUrlAccueil() . "/export/ics_export.php?usr=".$huser."<a>";

    	bottom();
    }

    /**
     * Encapsule le comportement du module de l'export VCALENDAR
     *
     * @return void
     * @access public
     * @static
     */
    public static function exportVCalendarModule()
    {
    	/*** initialisation des variables ***/
    	/************************************/

    	/*************************************/
    	// recup des parametres reçus :
    	// SERVER
    	$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    	// GET	/ POST
    	$action     = getpost_variable('action') ;
    	$user_login = getpost_variable('user_login') ;
    	$date_debut = getpost_variable('date_debut') ;
    	$date_fin   = getpost_variable('date_fin') ;
    	$choix_format  = getpost_variable('choix_format') ;
    	/*************************************/


    	\export\Fonctions::form_saisie($user_login, $date_debut, $date_fin);
    }

    public static function remplace_accents($str)
    {
    	$accent        = array("à", "â", "ä", "é", "è", "ê", "ë", "î", "ï", "ô", "ö", "ù", "û", "ü", "ç");
    	$sans_accent   = array("a", "a", "a", "e", "e", "e", "e", "i", "i", "o", "o", "u", "u", "u", "c");
    	return str_replace($accent, $sans_accent, $str) ;
    }

    // export des périodes des conges et d'absences comprise entre les 2 dates , dans un fichier texte au format ICAL
    public static function export_ical($user_login)
    {
    	$good_date_debut = date("Y-m-d", strtotime("-1 year"));
    	$good_date_fin = date("Y-m-d", strtotime('+1 year'));
    		/********************************/
    		// initialisation de variables communes a ttes les periodes

    		// recup des infos du user
    		$tab_infos_user=recup_infos_du_user($user_login, "");

    		$tab_types_abs=recup_tableau_tout_types_abs() ;

    		/********************************/
    		// affichage dans un fichier non html !

    		header("content-type: application/ics");
    		header("Content-disposition: filename=libertempo.ics");


    		echo "BEGIN:VCALENDAR\r\n" .
    				"PRODID:-//Libertempo \r\n" .
    				"VERSION:2.0\r\n\r\n";

    		// SELECT des periodes à exporter .....
    		// on prend toutes les periodes de conges qui chevauchent la periode donnée par les dates demandées
    		$sql_periodes="SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_commentaire, p_type, p_etat, p_date_demande  " .
    				'FROM conges_periode WHERE p_login=\''. \includes\SQL::quote($user_login).'\' AND ((p_date_deb>=\''. \includes\SQL::quote($good_date_debut).'\' AND  p_date_deb<=\''.\includes\SQL::quote($good_date_fin).'\') OR (p_date_fin>=\''. \includes\SQL::quote($good_date_debut).'\' AND p_date_fin<=\''. \includes\SQL::quote($good_date_fin).'\'))';
    		$res_periodes = \includes\SQL::query($sql_periodes);

    		if($num_periodes=$res_periodes->num_rows!=0)
    		{
    			while ($result_periodes = $res_periodes->fetch_array())
    			{
    				$sql_date_debut=$result_periodes['p_date_deb'];
    				$sql_demi_jour_deb=$result_periodes['p_demi_jour_deb'];
    				$sql_date_fin=$result_periodes['p_date_fin'];
    				$sql_demi_jour_fin=$result_periodes['p_demi_jour_fin'];
    				$sql_type=$result_periodes['p_type'];
    				$sql_etat=$result_periodes['p_etat'];
    				$sql_dateh_demande=$result_periodes['p_date_demande'];

    				// PB : les fichiers ical et vcal doivent être encodés en UTF-8, or php ne gère pas l'utf-8
    				// on remplace donc les caractères spéciaux de la chaine de caractères
    				$sql_comment=\export\Fonctions::remplace_accents($result_periodes['p_commentaire']);

    				// même problème
    				$type_abs=\export\Fonctions::remplace_accents($tab_types_abs[$sql_type]['libelle']) ;

    				//conversion format date
    				$replaceThis = Array('-' => '',':' => '',' ' => 'T',);
    				$sql_date_dem=str_replace(array_keys($replaceThis), $replaceThis, $sql_dateh_demande);
    				$DTSTAMP=$sql_date_dem."Z";
    				$tab_date_deb=explode("-", $sql_date_debut);
    				$tab_date_fin=explode("-", $sql_date_fin);

    				//conversion etat demande en status
    				switch ($sql_etat) {
    					case "ok":
    						$status="CONFIRMED";
    						break;
    					case "refus":
    						$status="CANCELLED";
    						break;
    					default:
    						$status="TENTATIVE";
    					}
    				if($sql_demi_jour_deb=="am")
    					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T070000Z";   // .....
    				else
    					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T120000Z";   // .....

    				if($sql_demi_jour_fin=="am")
    					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T120000Z";   // .....
    				else
    					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T210000Z";   // .....

    					echo "BEGIN:VEVENT\r\n" .
    						"DTSTAMP:$DTSTAMP\r\n" .
    						"ORGANIZER:MAILTO:".$tab_infos_user['email']."\r\n" .
    						"CREATED:$DTSTART\r\n" .
    						"STATUS:$status\r\n" .
    						"UID:$user_login@Libertempo-$sql_date_dem\r\n";
    				if($sql_comment!="")
    					echo "DESCRIPTION:$sql_comment\r\n";
    				echo "SUMMARY:$type_abs\r\n" .
    						"CLASS:PUBLIC\r\n" .
    						"PRIORITY:1\r\n" .
    						"DTSTART:$DTSTART\r\n" .
    						"DTEND:$DTEND\r\n" .
    						"TRANSP:OPAQUE\r\n" .
    						"END:VEVENT\r\n\r\n" ;
    			}
    		}

    		echo "END:VCALENDAR\r\n";
    }

    /**
     * Encapsule le comportement du module d'export ics
     *
     * @return void
     * @access public
     * @static
     */
    public static function exportICSModule()
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
        if (!$config->isIcalActive()) {
            header('HTTP/1.0 403 Forbidden');
        	exit('403 Forbidden');
        }

        //on récupère le hash du user
        $usrh = $_GET['usr'];

        //on récupère le nom associé au hash
        $session_username = unhash_user($usrh);

        if ($session_username != "")
        	\export\Fonctions::export_ical($session_username);
    }
}
