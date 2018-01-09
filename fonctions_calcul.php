<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// calcule le nb de jours de conges à prendre pour un user entre 2 dates
// retourne le nb de jours  (opt_debut et opt_fin ont les valeurs "am" ou "pm"
function compter($user, $num_current_periode, $date_debut, $date_fin, $opt_debut, $opt_fin, &$comment,  $num_update = null)
{
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
	$date_debut = convert_date($date_debut);
	$date_fin = convert_date($date_fin);

    $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
    if (is_null($planningUser)) {
        $comment = _('aucun_planning_associe_utilisateur');
        return 0;
    }

	// verif si date_debut est bien anterieure à date_fin
	// ou si meme jour mais debut l'apres midi et fin le matin
	if( (strtotime($date_debut) > strtotime($date_fin)) || ( ($date_debut==$date_fin) && ($opt_debut=="pm") && ($opt_fin=="am") ) )
	{
		$comment =  _('calcul_nb_jours_commentaire_bad_date') ;
		return 0 ;
	}


	if( ($date_debut!=0) && ($date_fin!=0) )
	{
		// On ne peut pas calculer si, pour l'année considérée, les jours feries ont ete saisis
		if( (verif_jours_feries_saisis($date_debut, $num_update)==FALSE) || (verif_jours_feries_saisis($date_fin, $num_update)==FALSE) )
		{
			$comment =  _('calcul_impossible') ."<br>\n". _('jours_feries_non_saisis') ."<br>\n". _('contacter_rh') ."<br>\n" ;
			return 0 ;
		}


		/************************************************************/
		// 1 : on fabrique un tableau de jours (divisé chacun en 2 demi-jour) de la date_debut à la date_fin
		// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
		// 3 : on affecte à 0 ou 1 chaque demi jour, en fonction de s'il est travaillé ou pas
		// 4 : à la fin , on parcours le tableau en comptant le nb de demi-jour à 1, on multiplie ce total par 0.5, ça donne le nb de jours du conges !

		$nb_jours=0;

		/************************************************************/
		// 1 : fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
		$tab_periode_calcul = make_tab_demi_jours_periode($date_debut, $date_fin, $opt_debut, $opt_fin);


		/************************************************************/
		// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
		if(verif_periode_chevauche_periode_user($date_debut, $date_fin, $user, $num_current_periode, $tab_periode_calcul, $comment, $num_update)) {
			return 0;
        }


		/************************************************************/
		// 3 : on affecte à 0 ou 1 chaque demi jour, en fonction de s'il est travaillé ou pas

		// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
		if(!isset($_SESSION["tab_j_feries"]))
		{
			init_tab_jours_feries();
		}
		// on initialise le tableau global des jours fermés s'il ne l'est pas déjà :
		if(!isset($_SESSION["tab_j_fermeture"]))
		{
			init_tab_jours_fermeture($user);
		}

		$current_day=$date_debut;
		$date_limite=jour_suivant($date_fin);

		// on va avancer jour par jour jusqu'à la date limite et voir si chaque jour est travaillé, férié, rtt, etc ...
		while($current_day!=$date_limite)
		{
			// calcul du timestamp du jour courant
			if(substr_count($current_day,'/')){
				$pieces = explode("/", $current_day);  // date de la forme jj/mm/yyyy
				$y=$pieces[2];
				$m=$pieces[1];
				$j=$pieces[0];
			}else{
				$pieces = explode("-", $current_day);  // date de la forme yyyy-mm-dd
				$y=$pieces[0];
				$m=$pieces[1];
				$j=$pieces[2];
			}
			$timestamp_du_jour=mktime (0,0,0,$m,$j,$y);

			// on regarde si le jour est travaillé ou pas dans la config de l'appli
			$j_name=date("D", $timestamp_du_jour);
			if( (($j_name=="Sat")&&(!$config->isSamediOuvrable())) || (($j_name=="Sun")&&(!$config->isDimancheOuvrable())))
			{
				// on ne compte ce jour à 0
				$tab_periode_calcul[$current_day]['am']=0;
				$tab_periode_calcul[$current_day]['pm']=0;
			}
			elseif(est_chome($timestamp_du_jour)) // verif si jour férié
			{
				// on ne compte ce jour à 0
				$tab_periode_calcul[$current_day]['am']=0;
				$tab_periode_calcul[$current_day]['pm']=0;
			}
			else
			{
				/***************/
				// verif des rtt ou temp partiel (dans la table rtt)
				$val_matin="N";
				$val_aprem="N";
				recup_infos_artt_du_jour($user, $timestamp_du_jour, $val_matin, $val_aprem, $planningUser);

				if($val_matin=="Y")  // rtt le matin
					$tab_periode_calcul[$current_day]['am']=0;

				if($val_aprem=="Y") // rtt l'après midi
					$tab_periode_calcul[$current_day]['pm']=0;
			}

            $nb_jours = $nb_jours + $tab_periode_calcul[$current_day]['am'] + $tab_periode_calcul[$current_day]['pm'];

			$current_day=jour_suivant($current_day);
		}
		$nb_jours = $nb_jours * 0.5;
		$VerifDec = verif_saisie_decimal($nb_jours);
		return $nb_jours;
	}
	else
		return 0;
}

// renvoit le jour suivant de la date passée en paramètre sous la forme jj/mm/yyyy
function jour_suivant($date)
{
	if(substr_count($date,'/')){
		$pieces = explode("/", $date);  // date de la forme jj/mm/yyyy
		$y=$pieces[2];
		$m=$pieces[1];
		$j=$pieces[0];
		$lendemain = date("d/m/Y", mktime(0, 0, 0, $m , $j+1, $y) );
	}else{
		$pieces = explode("-", $date);  // date de la forme yyyy-mm-dd
		$y=$pieces[0];
		$m=$pieces[1];
		$j=$pieces[2];
		$lendemain = date("Y-m-d", mktime(0, 0, 0, $m , $j+1, $y) );
	}

	return $lendemain;
}

// verifie si les jours fériés de l'annee de la date donnée sont enregistrés
// retourne TRUE ou FALSE
function verif_jours_feries_saisis($date)
{
	// on calcule le premier de l'an et le dernier de l'an de l'année de la date passee en parametre
	if(substr_count($date,'/'))
	{
		$tab_date=explode("/", $date); // date est de la forme dd/mm/YYYY
		$an=$tab_date[2];
	}
	if(substr_count($date,'-'))
	{
		$tab_date=explode("-", $date); // date est de la forme yyyy-mm-dd
		$an=$tab_date[0];
	}
	$premier_an="$an-01-01";
	$dernier_an="$an-12-31";

	$sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date >= "'.\includes\SQL::quote($premier_an).'" AND jf_date <= "'. \includes\SQL::quote($dernier_an).'" ';
	$res_select = \includes\SQL::query($sql_select);

	return ($res_select->num_rows != 0);
}

// fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin d'une periode
function make_tab_demi_jours_periode($date_debut, $date_fin, $opt_debut, $opt_fin)
{
	$tab_periode_calcul=array();
	$nb_jours_entre_date = (((strtotime(str_replace('/','-',$date_fin)) - strtotime(str_replace('/','-',$date_debut)))/3600)/24)+1 ;

	// on va avancer jour par jour jusqu'à la date limite
	$current_day=$date_debut;
	$date_limite=jour_suivant($date_fin);
	while($current_day!=$date_limite)
	{
		$jour['am']=1;
		$jour['pm']=1;
		$tab_periode_calcul[$current_day]=$jour;
		$current_day=jour_suivant($current_day);
	}
	// attention au premier et dernier jour :
	if($opt_debut=="pm")
		$tab_periode_calcul[$date_debut]['am']=0;
	if($opt_fin=="am")
		$tab_periode_calcul[$date_fin]['pm']=0;
	return $tab_periode_calcul;
}

// verifie si la periode donnee chevauche une periode de conges d'un user donné
// attention à ne pas verifer le chevauchement avec la periode qu on est en train de traiter (si celle ci a déjà un num_periode)
// retourne TRUE si chevauchement et FALSE sinon !
function verif_periode_chevauche_periode_user($date_debut, $date_fin, $user, $num_current_periode='', $tab_periode_calcul, &$comment, $num_update = null)
{

	/************************************************************/
	// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
	// -> on recupere les periodes par rapport aux dates, on en fait une tableau de 1/2 journees, et on compare par 1/2 journee
	$tab_periode_deja_prise=array();
	$current_day=$date_debut;
	$date_limite=jour_suivant($date_fin);

	// on va avancer jour par jour jusqu'à la date limite et recupere les periodes qui contiennent ce jour...
	// on construit un tableau par date et 1/2 jour avec l'état de la periode
	while($current_day!=$date_limite)
	{
		$tab_periode_deja_prise[$current_day]['am']="no" ;
		$tab_periode_deja_prise[$current_day]['pm']="no" ;

		if ($num_update === null)
		{
			// verif si c'est deja un conges
			$user_periode_sql = 'SELECT  p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_etat
						FROM conges_periode
						WHERE p_login = "'.\includes\SQL::quote($user).'" AND ( p_etat=\'ok\' OR p_etat=\'valid\' OR p_etat=\'demande\' )
						'.(!empty($num_current_periode) ? 'AND p_num != '.intval($num_current_periode).' ' : '') .'
						AND p_date_deb<="'.\includes\SQL::quote($current_day).'" AND p_date_fin>="'.\includes\SQL::quote($current_day).'" ';
		}
		else
		{
			// verif si c'est deja un conges
			$user_periode_sql = 'SELECT  p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_etat
						FROM conges_periode
						WHERE p_login = "'.\includes\SQL::quote($user).'" AND ( p_etat=\'ok\' OR p_etat=\'valid\' OR p_etat=\'demande\' )
						'.(!empty($num_current_periode) ? 'AND p_num != '.intval($num_current_periode).' ' : '') .'
						AND p_date_deb<="'.\includes\SQL::quote($current_day).'" AND p_date_fin>="'. \includes\SQL::quote($current_day).'"
						AND p_num != \''.intval($num_update).'\' ';
		}

		$user_periode_request = \includes\SQL::query($user_periode_sql);

		if($user_periode_request->num_rows !=0)  // le jour courant est dans une periode de conges du user
		{
			while($resultat_periode=$user_periode_request->fetch_array())
			{
				$sql_p_date_deb=$resultat_periode["p_date_deb"];
				$sql_p_date_fin=$resultat_periode["p_date_fin"];
				$sql_p_demi_jour_deb=$resultat_periode["p_demi_jour_deb"];
				$sql_p_demi_jour_fin=$resultat_periode["p_demi_jour_fin"];
				$sql_p_etat=$resultat_periode["p_etat"];

				if( ($current_day!=$sql_p_date_deb) && ($current_day!=$sql_p_date_fin) )
				{
					// pas la peine d'aller + loin, on chevauche une periode de conges !!!
					if($sql_p_etat=="demande")
							$comment =  _('calcul_nb_jours_commentaire_impossible') ;
						else
							$comment =  _('calcul_nb_jours_commentaire') ;

					return TRUE ;
				}
				elseif( ($current_day==$sql_p_date_deb) && ($current_day==$sql_p_date_fin) ) // periode sur une seule journee
				{
					if($sql_p_demi_jour_deb=="am")
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
					if($sql_p_demi_jour_fin=="pm")
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
				}
				elseif($current_day==$sql_p_date_deb)
				{
					if($sql_p_demi_jour_deb=="am")
					{
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
					}
					else // alors ($sql_p_demi_jour_deb=="pm")
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
				}
				else // alors ($current_day==$sql_p_date_fin)
				{
					if($sql_p_demi_jour_fin=="pm")
					{
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
					}
					else // alors ($sql_p_demi_jour_fin=="am")
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
				}
			}
		}
		$current_day=jour_suivant($current_day);
	}// fin du while
	/**********************************************/
	// Ensuite verifie en parcourant le tableau qu'on vient de crée (s'il n'est pas vide)
    $donneesPeriodeDebut = $tab_periode_calcul[$date_debut];
    $donneesPeriodeFin = $tab_periode_calcul[$date_fin];
    if (1 == $donneesPeriodeDebut['am']) {
        $periodeDebut = (1 == $donneesPeriodeDebut['pm'])
            ? \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN_APRES_MIDI
            : \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    } elseif (1 == $donneesPeriodeDebut['pm']) {
        $periodeDebut = \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    }

    if (1 == $donneesPeriodeFin['pm']) {
        $periodeFin = (1 == $donneesPeriodeFin['am'])
            ? \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN_APRES_MIDI
            : \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    } elseif (1 == $donneesPeriodeFin['am']) {
        $periodeFin = \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    }

    $conge = new \App\ProtoControllers\Employe\Conge();
    if ($conge->isChevauchement($user, $date_debut, $periodeDebut, $date_fin, $periodeFin)) {
        $comment =  _('demande_heure_chevauche_demande');
        return true;
    }

	if(count($tab_periode_deja_prise)!=0)
	{
		$current_day=$date_debut;
		$date_limite=jour_suivant($date_fin);
		// on va avancer jour par jour jusqu'à la date limite et recupere les periodes qui contiennent ce jour...
		// on construit un tableau par date et 1/2 jour avec l'état de la periode
		while($current_day!=$date_limite)
		{
			if( ($tab_periode_calcul[$current_day]['am']==1) && ($tab_periode_deja_prise[$current_day]['am']!="no") )
			{
				// pas la peine d'aller + loin, on chevauche une periode de conges !!!
				if($tab_periode_deja_prise[$current_day]['am']=="demande")
					$comment =  _('calcul_nb_jours_commentaire_impossible') ;
				else
					$comment =  _('calcul_nb_jours_commentaire') ;
				return TRUE ;
			}
			if( ($tab_periode_calcul[$current_day]['pm']==1) && ($tab_periode_deja_prise[$current_day]['pm']!="no") )
			{
				// pas la peine d'aller + loin, on chevauche une periode de conges !!!
				if($tab_periode_deja_prise[$current_day]['pm']=="demande")
					$comment =  _('calcul_nb_jours_commentaire_impossible') ;
				else
					$comment =  _('calcul_nb_jours_commentaire') ;

				return TRUE ;
			}
			$current_day=jour_suivant($current_day);
		}// fin du while
	}


	return FALSE ;

	/************************************************************/
	// Fin de le verif de chevauchement d'une période déja saisie
}

//retourne un nombre arrondit à 0.5 près à partir d'un nombre décimal
function round_to_half($num)
{
	if($num >= ($half = ($ceil = ceil($num))- 0.5) + 0.25) return $ceil;
	else if($num < $half - 0.25) return floor($num);
	else return $half;
}
