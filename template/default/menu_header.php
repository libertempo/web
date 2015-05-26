<?php
	
	defined( '_PHP_CONGES' ) or die( 'Restricted access' );
	include TEMPLATE_PATH . 'template_define.php';

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
	echo "<head>\n";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
		echo "<title> ".$title." </TITLE>\n";
		echo "<link href=\"". TEMPLATE_PATH ."style.css\" rel=\"stylesheet\" type=\"text/css\" />";
		echo "<link href=\"". TEMPLATE_PATH .$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
		echo '<link type="text/css" href="'. TEMPLATE_PATH .'jquery/css/custom-theme/jquery-ui-1.8.17.custom.css" rel="stylesheet" />';
		echo '<script type="text/javascript" src="'. TEMPLATE_PATH .'jquery/js/jquery-1.7.1.min.js"></script>';
		echo '<script type="text/javascript" src="'. TEMPLATE_PATH .'jquery/js/jquery-ui-1.8.17.custom.min.js"></script>';
		echo '<script type="text/javascript" src="'. TEMPLATE_PATH .'jquery/js/jquery.tablesorter.min.js"></script>';
		include ROOT_PATH .'fonctions_javascript.php' ;
		echo $additional_head;
	echo "</head>\n";
	echo '<body id="top">';
	echo '<script>
		$(document).ready(function() { 
			$(".tablo").tablesorter();
						
			$(window).scroll(function () {
				$("#back-top").fadeIn("slow");
				});
			
			});
	</script>';

	
	
	/*****************************************************************************/
	// DEBUT AFFICHAGE DU MENU
	echo '<div id="header" class="ui-widget-header ui-helper-clearfix ui-corner-all">';

			/*****************************************************************************/
			// DEBUT AFFICHAGE DES BOUTONS ...
	
				echo '<div id=\"header_menu\" style="overflow: hidden;">';
				
					if ( is_resp($_SESSION['userlogin']) ) {
						$home = 'responsable/resp_index.php?session='.$session;
					}
					else {
						$home = 'utilisateur/user_index.php?session='.$session;
					}
					
					$user_mode = '';
					$tmp = dirname($_SERVER['PHP_SELF']);
					$tmp = explode('/',$tmp);
					$tmp = array_pop($tmp);
					if (in_array($tmp, array('utilisateur','admin','responsable','hr')))
						$user_mode = $tmp;
					else
						$user_mode = '';
					
					echo '<div style="float: left;"><a href="'. ROOT_PATH . $home .'"><img src="'. TEMPLATE_PATH . LOGO .'"/></a></div>';	
					
					
					function bouton($name, $icon ,$link, $active = false)
					{
						$name = str_replace('"','\\"',$name);
						$icon = str_replace('"','\\"',$icon);
						$link = str_replace('"','\\"',$link);
						echo '<div class="button_div'.($active?' active':'').'">
								<a href="'. $link .'">
									<img src="'. TEMPLATE_PATH .'img/'.$icon.'" title="'.$name.'" alt="'.$name.'">
									<span>'.$name.'</span>
								</a>
							</div>';
					}
					
					function bouton_popup($name, $icon ,$link, $popup_name, $size_x, $size_y, $active = false)
					{
						$name = str_replace('"','\\"',$name);
						
						echo '<div class="button_div'.($active?' active':'').'">
								<a href="javascript:void(0);" onClick="javascript:OpenPopUp(\''. $link .'\',\''.$popup_name.'\','.$size_x.','.$size_y.');">
									<img src="'. TEMPLATE_PATH .'img/'.$icon.'" title="'.$name.'" alt="'.$name.'">
									<span>'.$name.'</span>
								</a>
							</div>';
					}
					
					
					if (is_admin($_SESSION['userlogin']))
						bouton('Administration'	,'tools.png'			,ROOT_PATH .'admin/admin_index.php?session='.$session, $user_mode == 'admin');
					if (is_hr($_SESSION['userlogin']))
						bouton('RH'				,'user-rh.png'			,ROOT_PATH .'hr/hr_index.php?session='.$session, $user_mode == 'hr');
					if (is_resp($_SESSION['userlogin']))
						bouton(_('button_responsable_mode')	,'user-responsable.png'	,ROOT_PATH .'responsable/resp_index.php?session='.$session, $user_mode == 'responsable');
				bouton(_('user')	,'user.png'				,ROOT_PATH .'utilisateur/user_index.php?session='.$session, $user_mode == 'utilisateur');
					
					
							
					echo '<div class="mode_and_user_info" >Mode '.$user_mode.': '.$_SESSION['u_prenom'].' '.$_SESSION['u_nom'].' ('.$_SESSION['userlogin'].')</div>';
					echo '<div style="clear: right; margin : 0;"></div>';



					
					if($_SESSION['config']['auth'])
						bouton(_('button_deconnect')		,'exit.png'		,ROOT_PATH .'deconnexion.php?session='.$session);
					
					$PHP_SELF=$_SERVER['PHP_SELF'];
					$session=session_id();
					if (is_active($_SESSION['userlogin']))
						$_SESSION['is_active'] = "Y";
					else
						$_SESSION['is_active'] = "N";
					verif_droits_user($_SESSION['is_active'], "is_active", FALSE);
					
					$onglet = getpost_variable('onglet');
					bouton('Actualiser'		,'refresh.png'	,$PHP_SELF.'?session='.$session.'&onglet='.$onglet);
		
					if($_SESSION['config']['user_affiche_calendrier'])
						bouton_popup('Calendrier','calendar.png',ROOT_PATH . 'calendrier.php?session='.$session , 'calendrier', 1280, 1024);
					


					
					echo '<div style="float: left;">';
					
					switch($user_mode)
					{
						case 'admin':
						
							bouton_popup( _('admin_button_save_db_2') ,'floppy_22x22.png',ROOT_PATH . 'admin/admin_db_sauve.php?session='.$session , 'sauvedb', 400, 300);
							bouton_popup( _('admin_button_jours_fermeture_2') ,'jours_fermeture_22x22.png',ROOT_PATH . 'admin/admin_jours_fermeture.php?session='.$session , 'fermeture', 1080, 690);
							bouton_popup( _('admin_button_jours_chomes_2') ,'jours_feries_22x22.png',ROOT_PATH . 'admin/admin_jours_chomes.php?session='.$session , 'jourschomes', 1080, 610);
							
							if (false)
							{
								if($_SESSION['config']['affiche_bouton_config_mail_pour_admin'])
									bouton_popup( _('admin_button_config_mail_2') ,'tux_config_22x22.png',ROOT_PATH . 'config/config_mail.php?session='.$session , 'configmail', 800, 600);
									
								if($_SESSION['config']['affiche_bouton_config_absence_pour_admin'])
									bouton_popup( _('admin_button_config_abs_2') ,'tux_config_22x22.png',ROOT_PATH . 'config/config_type_absence.php?session='.$session , 'configabs', 800, 600);

								if($_SESSION['config']['affiche_bouton_config_pour_admin'] )
									bouton_popup( _('admin_button_config_2') ,'tux_config_22x22.png',ROOT_PATH . 'config/configure.php?session='.$session , 'config', 800, 600);
							}
							
							bouton( 'All config' ,'tux_config_22x22.png',ROOT_PATH . 'config/index.php?session='.$session );
							
							break;
						case 'utilisateur':
							if($_SESSION['config']['export_ical_vcal']) 
								bouton_popup( _('Exporter cal') ,'export-22x22.png',ROOT_PATH . 'export/export_vcalendar.php?session='.$session.'&user_login='.$_SESSION['userlogin'] , 'icalvcal', 457, 280);
					
					
							if($_SESSION['config']['editions_papier'])
								bouton(_('button_editions')	,'edition-22x22.png'	,ROOT_PATH .'edition/edit_user.php?session='.$session );
							break;
					}
					
					echo '</div>';
				
				if (false)
				{
					if($info=="responsable")
					{
						if($_SESSION['config']['resp_affiche_calendrier'])
						{
							// bouton calendrier
							if($_SESSION['config']['resp_affiche_calendrier'])
							{
								echo '<div style="float: right;">';
									echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../calendrier.php?session=$session','calendrier',1050,600);\">" .
									//echo "<a href=\"../calendrier.php?session=$session\">" .
									 "<img src=\"". TEMPLATE_PATH ."img/rebuild.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_calendar') ."\" alt=\"". _('button_calendar') ."\">" .
									  _('button_calendar') ."</a>\n";
								echo '</div>';
							}
							
							// bouton imprim calendrier
							echo '<div style="float: right;">';
								echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../imprim_calendrier.php?session=$session','imprimcal',300,210);\">" .
								//echo "<a href=\"../calendrier.php?session=$session\">" .
								 "<img src=\"". TEMPLATE_PATH ."img/fileprint_4_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_imprim_calendar') ."\" alt=\"". _('button_imprim_calendar') ."\">" .
								  _('button_imprim_calendar') ."</a>\n";
							echo '</div>';
							
						}
						
						/*** bouton changement exercice ***/ 
						echo '<div style="float: right;">';
							echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('resp_cloture_year.php?session=$session','cloture_exercice',800,600);\">" .
							 "<img src=\"". TEMPLATE_PATH ."img/reload3.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_cloture') ."\" alt=\"". _('button_calendar') ."\">" .
							  _('button_cloture') ."</a>\n";
						echo '</div>';
						
					}
					
					if($info=="admin")
					{
				
						/* bouton db_sauvegarde  ***/
						echo '<div style="float: right;">';
						echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('admin_db_sauve.php?session=$session','sauvedb',400,300);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/floppy_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_save_db_1') ."\" alt=\"". _('admin_button_save_db_1') ."\">\n";
							echo " ". _('admin_button_save_db_2') ."\n";
						echo '</a></div>';
				
						/* bouton jours fermeture  ***/
						echo '<div style="float: right;">';
						echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('admin_jours_fermeture.php?session=$session','fermeture',1080,690);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/jours_fermeture_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_jours_fermeture_1') ."\" alt=\"". _('admin_button_jours_fermeture_1') ."\">\n";
							echo " ". _('admin_button_jours_fermeture_2') ."\n";
						echo '</a></div>';
						
						/* bouton jours chômés  ***/
						echo '<div style="float: right;">';
						echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('admin_jours_chomes.php?session=$session','jourschomes',1080,610);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/jours_feries_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_jours_chomes_1') ."\" alt=\"". _('admin_button_jours_chomes_1') ."\">\n";
							echo " ". _('admin_button_jours_chomes_2') ."\n";
						echo '</a></div>';
				
						/* bouton config des mails php_conges  */
						if($_SESSION['config']['affiche_bouton_config_mail_pour_admin'])
						{
							echo '<div style="float: right;">';
							echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../config/config_mail.php?session=$session','configmail',800,600);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/tux_config_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_config_mail_1') ."\" alt=\"". _('admin_button_config_mail_1') ."\">\n";
							echo " ". _('admin_button_config_mail_2') ."\n";
							echo '</a></div>';
						}
						
						/* bouton config types absence php_conges  */
						if($_SESSION['config']['affiche_bouton_config_absence_pour_admin'])
						{
							echo '<div style="float: right;">';
							echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../config/config_type_absence.php?session=$session','configabs',800,600);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/tux_config_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_config_abs_1') ."\" alt=\"". _('admin_button_config_abs_1') ."\">\n";
							echo " ". _('admin_button_config_abs_2') ."\n";
							echo '</a></div>';
						}
				
				
						/* bouton config php_conges  */
						if($_SESSION['config']['affiche_bouton_config_pour_admin'] && $_SESSION['is_admin']=="Y")
						{
							echo '<div style="float: right;">';
							echo " <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../config/configure.php?session=$session','config',800,600);\">\n";
							echo " <img src=\"". TEMPLATE_PATH ."img/tux_config_22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('admin_button_config_1') ."\" alt=\"". _('admin_button_config_1') ."\">\n";
							echo " ". _('admin_button_config_2') ."\n";
							echo '</a></div>';
						}
					}
				
					if($info=="user")
					{
					
						/*** bouton calendrier  ***/
						if($_SESSION['config']['user_affiche_calendrier'])
						{
							echo '<div style="float: right;">';
							// affichage dans un popup
							echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../calendrier.php?session=$session','calendrier',1450,550);\">" .
									"<img src=\"". TEMPLATE_PATH ."img/rebuild.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_calendar') ."\" alt=\"". _('button_calendar') ."\">" .
									 _('button_calendar') ."</a>\n";
							echo '</div>';
						}
						
						/*** bouton export calendar  ***/
						if($_SESSION['config']['export_ical_vcal'])
						{
							echo '<div style="float: right;">';
							echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../export/export_vcalendar.php?session=$session&user_login=".$_SESSION['userlogin']."','icalvcal',457,280);\">" .
							// echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../export/export_vcalendar.php?session=$session&&user_login=".$_SESSION['userlogin']."','icalvcal',457,280);\">" .
									"<img src=\"". TEMPLATE_PATH ."img/export-22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_export_2') ."\" alt=\"". _('button_export_2') ."\">" .
									 _('button_export_1') ."</a>\n";
							echo '</div>';
						}
				
						/*** bouton éditions papier  ***/
						if($_SESSION['config']['editions_papier'])
						{
							echo '<div style="float: right;">';
							echo "<a href=\"../edition/edit_user.php?session=$session&user_login=".$_SESSION['userlogin']."\" target=\"_blank\">" .
									"<img src=\"". TEMPLATE_PATH ."img/edition-22x22.png\" width=\"17\" height=\"17\" border=\"0\" title=\"". _('button_editions') ."\" alt=\"". _('button_editions') ."\">" .
									 _('button_editions') ."</a>\n";
							echo '</div>';
						}
					}
					
				}
					
					
				echo "</div>";
			
			// FIN AFFICHAGE DES BOUTONS ...
			/*****************************************************************************/
			// echo "</div>";
		// echo "</div>";
	echo "</div>";
	
	// FIN AFFICHAGE DU MENU
	/*****************************************************************************/
	
	
	
	echo "<div id=\"content\">";
		echo "<center>\n";
