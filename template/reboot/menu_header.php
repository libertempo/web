<?php
	defined( '_PHP_CONGES' ) or die( 'Restricted access' );
	include TEMPLATE_PATH . 'template_define.php';
?>

<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- fonts -->
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,700" type="text/css" />
    
    <!-- Bootstrap -->
    <link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" rel="stylesheet" media="screen,print">

    <!-- Font Awesome -->
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.0/css/font-awesome.css" rel="stylesheet">
    
	<!-- Reboot style -->
	<link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>css/reboot.css" rel="stylesheet" rel="stylesheet" media="screen,print">
    <link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>css/datepicker.css" rel="stylesheet" rel="stylesheet" media="screen">
    
    <!-- scripts -->
    <!-- Jquery -->
    <script src="//code.jquery.com/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="<?php echo  TEMPLATE_PATH ?>bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo  TEMPLATE_PATH ?>js/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="<?php echo  TEMPLATE_PATH ?>js/bootstrap-datepicker/locales/bootstrap-datepicker.fr.js"></script>
	<script type="text/javascript" src="<?php echo  TEMPLATE_PATH ?>js/reboot.js"></script>

	<?php
		include ROOT_PATH .'fonctions_javascript.php' ;
		echo $additional_head;
	?>
  </head>
  <?php $printable = getpost_variable('printable'); ?>
  <body id="top" class="hbox connected<?php echo ($printable) ? ' printable' : ''; ?>" >
  	<aside id="toolbar">
	   	<section>
	   		<header class="main-header">
	   			<?php
	   				if ( is_resp($_SESSION['userlogin']) ) {
						$home = 'responsable/resp_index.php?session='.$session;
					}
					else {
						$home = 'utilisateur/user_index.php?session='.$session;
					}
	   			?>
	   			<i class="icon-ellipsis-vertical toolbar-toggle"></i>
	   			<h2 class="brand"><a href="<?php echo ROOT_PATH . $home; ?>" title="Accueil">Libertempo</a></h2>
	   		</header>
	   		<div class="tools">
	   			<?php
		   			//user mode
					$user_mode = '';
					$tmp = dirname($_SERVER['PHP_SELF']);
					$tmp = explode('/',$tmp);
					$tmp = array_pop($tmp);
					switch ($tmp) {
						case "utilisateur":
							$user_mode = _('user');
							break;
						case "admin":
						case "config":
							$user_mode = _('button_admin_mode');
							break;
						case "responsable":
							$user_mode = _('button_responsable_mode');
							break;
						case "hr":
							$user_mode = _('button_hr_mode');
							break;
						default :
							$user_mode = _('button_calendar');
					}
				?>
		   		<div class="profil-info">
		   			<i class="fa fa-smile-o"></i>
		   			<div class="wrapper">
		   				<div class="user-info">
			   				<!-- <i class="icon-smile"></i> -->
			   				<div class="user-login">
			   					<?php echo $_SESSION['userlogin']; ?>
			   				</div>
			   				<div class="user-name">
			   					<span class="firstname"><?php echo $_SESSION['u_prenom']; ?></span>
			   					<span class="name"><?php echo $_SESSION['u_nom']; ?></span>
			   				</div>	
			   			</div>
		   			</div>
				</div>
	        	<?php if( ($_SESSION['config']['user_affiche_calendrier'] && $tmp=='utilisateur') || ($_SESSION['config']['resp_affiche_calendrier'] && $tmp=='responsable') || $tmp=='hr' ): ?>
	        	<div class="calendar-link">
	        		<a title="<?php echo _('button_calendar'); ?>" href="<?php echo ROOT_PATH  . "calendrier.php?session=$session";?>">
	        			<i class="fa fa-calendar"></i>
	        			<?php echo _('button_calendar'); ?>
	        		</a>
	        	</div>
	        	<?php endif; ?>
	        	<ul class="nav">
	        		<li class="bottom-links">
	        			<?php $onglet = getpost_variable('onglet'); ?>
	        			<a class="refresh-link" href="<?php echo $_SERVER['PHP_SELF'].'?session=' . $session . '&onglet=' . $onglet;?>" title ="Actualiser">
	        				<i class="fa fa-refresh"></i>
	        			</a>
	        		</li>
	        		<?php if($_SESSION['config']['auth']): ?>
	        		<li class="bottom-links">
	        			<a class="disconnect-link" href="<?php echo ROOT_PATH  . 'deconnexion.php?session=' . $session;?>" title="Se déconnecter">
	        				<i class="fa fa-power-off"></i>
	        			</a>
					</li>
					<?php endif; ?>
		   		</ul>
	   		</div>
	   	</section>
   </aside>
	<section id="content">
		<section class="vbox">
			<header class="header bg-white">
				<div class="mod-info">
					<ul class="nav pull-left">
  						<li class="dropdown">
							<a id="dropdown-mode" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
								Mode <strong><?php echo $user_mode; ?></strong>&nbsp;<b class="caret"></b>
	   						</a>
							<ul class="dropdown-menu dropdown-select" role="menu" aria-labelledby="dropdown-mode">
					        	<?php if (is_admin($_SESSION['userlogin'])): ?>
					        		<li>
					        			<a href="<?php echo ROOT_PATH . 'admin/admin_index.php?session=' . $session; ?>" <?php print ($tmp == 'admin' || $tmp == 'config') ? 'active' : '' ;?>>Administration</a>
					        		</li>
					      		<?php endif; ?>
					      		<?php if (is_hr($_SESSION['userlogin'])): ?>
					        		<li>
					        			<a href="<?php echo ROOT_PATH . 'hr/hr_index.php?session=' . $session; ?>" <?php print ($tmp == 'hr') ? 'active' : '' ;?>>RH</a>
					        		</li>
					      		<?php endif; ?>
					      		<?php if (is_resp($_SESSION['userlogin'])): ?>
					        		<li>
					        			<a href="<?php echo ROOT_PATH  . 'responsable/resp_index.php?session=' . $session; ?>" <?php print ($tmp == 'utilisateur') ? 'active' : '' ;?>><?php echo _('button_responsable_mode');?></a>
					        		</li>
					      		<?php endif; ?>
								<li>
									<a href="<?php echo ROOT_PATH . 'utilisateur/user_index.php?session=' . $session; ?>" <?php print ($tmp == 'utilisateur') ? 'active' : '' ;?>><?php echo _('user');?></a>
								</li>
					        </ul>
				        </li>
				    </ul>
				</div>
				<?php 
				// toolbar contextuelle au mode
				$mod_toolbar = NULL;
				
				switch($tmp) {
					case 'admin':
						$mod_toolbar[] = "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('". ROOT_PATH ."/admin/admin_db_sauve.php?session=$session','400,300');\"><i class=\"fa fa-save\"></i><span>" . _('admin_button_save_db_2') . "</span></a>";
						if($_SESSION['config']['affiche_bouton_config_pour_admin'] || $_SESSION['config']['affiche_bouton_config_absence_pour_admin'] || $_SESSION['config']['affiche_bouton_config_mail_pour_admin'] || $_SESSION['userlogin']=="admin" )
							$mod_toolbar[] = "<a href=\"" . ROOT_PATH . "config/index.php?session=$session\"" . ($tmp == 'config' ? 'class="active"' : '') . "><i class=\"fa fa-th-list\"></i><span>" . _('admin_button_config_2') . "</span></a>";
					break;
					case 'hr':
						$mod_toolbar[] = "<a href=\"" . ROOT_PATH . "hr/hr_jours_fermeture.php?session=$session\"><i class=\"fa fa-calendar\"></i><span>" . _('admin_button_jours_fermeture_2') . "</span></a>";
					break;
					case 'utilisateur':
						$mod_toolbar[] = "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('". ROOT_PATH . "export/export_vcalendar.php?session=$session&user_login=" . $_SESSION['userlogin'] . "','457,280');\"><i class=\"fa fa-download\"></i><span>" . _('Exporter cal') . "</span></a>";
						if($_SESSION['config']['editions_papier'])
							$mod_toolbar[] = "<a href=\"" . ROOT_PATH . "edition/edit_user.php?session=$session\"><i class=\"fa fa-file-text\"></i><span>"._('button_editions')."</span></a>";
					break;
				}
				?>
				<?php if($mod_toolbar) : ?>
					<ul id="mod-toolbar" class="pull-right">
			 		<?php foreach ($mod_toolbar as $key => $link) : ?>
			 			<li><?php echo $link; ?></li>
			 		<?php endforeach;?> 
			 		</ul>
				<?php endif; ?>
			</header>
			<section id="scrollable">
				<div class="wrapper bg-white">
