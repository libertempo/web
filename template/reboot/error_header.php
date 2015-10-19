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
	    
	    <!-- Jquery -->
	    <link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>jquery/css/custom-theme/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
	    
	    <!-- Bootstrap -->
	    <link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" rel="stylesheet" media="screen">

	    <!-- Font Awesome -->
	    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.0/css/font-awesome.css" rel="stylesheet">

	    <!-- Reboot style -->
	    <link type="text/css" href="<?php  echo TEMPLATE_PATH;  ?>/css/reboot.css" rel="stylesheet" rel="stylesheet" media="screen">
	    
	    <!-- scripts -->
		<script type="text/javascript" src="<?php echo TEMPLATE_PATH; ?>jquery/js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="<?php echo TEMPLATE_PATH; ?>jquery/js/jquery-ui-1.8.17.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo  TEMPLATE_PATH ?>jquery/js/jquery.tablesorter.min.js"></script>

		<?php
			include ROOT_PATH .'fonctions_javascript.php' ;
			echo $additional_head;
		?>
  	</head>
  	<body class="error">
  		<header>
			<h1 class="login-heading">Libertempo</h1>
		</header>
  		<div class="container">
  			<div class="icon-header"><i class="fa fa-minus-circle"></i></div>
