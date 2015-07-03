<script language=javascript>
    <!--
    function OpenPopUp(MyFile,MyWindow,MyWidth,MyHeight)
    //MyFile :    nom du fichier contenant le code HTML du pop-up
    //MyWindow :      nom de la fenêtre (ne pas mettre d'espace)
    //MyWidth :   entier indiquant la largeur de la fenêtre en pixels
    //MyHeight :      entier indiquant la hauteur de la fenêtre en pixels

    {
    var ns4 = (document.layers)? true:false;      //NS 4
    var ie4 = (document.all)? true:false;     //IE 4
    var dom = (document.getElementById)? true:false;      //DOM
    var xMax, yMax, xOffset, yOffset;;

        if (ie4 || dom)
            {
            xMax = screen.width;
            yMax = screen.height;
            }
        else if (ns4)
            {
            xMax = window.outerWidth;
            yMax = window.outerHeight;
            }
        else
            {
            xMax = 800;
            yMax = 600;
            }
        xOffset = (xMax - MyWidth)/2;
        yOffset = (yMax - MyHeight)/2;
        window.open(MyFile,MyWindow,'width='+MyWidth
    +',height='+MyHeight
    +',screenX='+xOffset
    +',screenY='+yOffset
    +',top='+yOffset
    +',left='+xOffset
    +',scrollbars=yes,resizable=yes');
        }
    //-->
</script>

<script language=javascript>

function compter_jours()
{
    var login = document.forms["dem_conges"].user_login.value;
    var session = document.forms["dem_conges"].session.value;
	var d_debut = document.forms["dem_conges"].new_debut.value;
	var d_fin = document.forms["dem_conges"].new_fin.value;
	var opt_deb = document.forms["dem_conges"].new_demi_jour_deb.value;
	var opt_fin = document.forms["dem_conges"].new_demi_jour_fin.value;
	var p_num = "";

    if( document.forms["dem_conges"].p_num_to_update ) 
    {
        var p_num = document.forms["dem_conges"].p_num_to_update.value;
    }

    if( (d_debut) && (d_fin))
    {
        var page ='../calcul_nb_jours_pris.php?session='+session+'&date_debut='+d_debut+'&date_fin='+d_fin+'&user='+login+'&opt_debut='+opt_deb+'&opt_fin='+opt_fin+'&p_num='+p_num;

    $.ajax(
    {
        type : 'GET',
        url : page,
        dataType : 'text', // expected returned data format.
        success : function(data)
        {
		var arr = new Array();
	arr = JSON.parse(data);
	document.forms["dem_conges"].new_nb_jours.value=arr["nb"];
	document.getElementById('comment_nbj').innerHTML = arr["comm"];

        },
    });

    }

}

jQuery( document ).ready(function($) { 

var jdesac = [];
<?php

function convert_jf($date)
{
		$pieces = explode("-", $date);  // date de la forme yyyy-mm-dd
		$y=$pieces[0];
		$m=$pieces[1];
		$j=$pieces[2];
		$date = date("d/m/Y", mktime(0, 0, 0, $m , $j, $y) );

	return $date;
}

$tabferm = isset($_SESSION["tab_j_fermeture"]) ? $_SESSION["tab_j_fermeture"] : NULL;
$tabjferies = isset($_SESSION["tab_j_feries"]) ? $_SESSION["tab_j_feries"] : NULL;
$js_tab= "[";

if(isset($tabjferies)) {
	foreach($tabjferies as $key => $jf){
		$js_tab=$js_tab.'"'.convert_jf($jf).'",';
	}
}

if(isset($tabferm)) {
	foreach($tabferm as $key => $jf){
		$js_tab=$js_tab.'"'.convert_jf($jf).'",';
	}
}
if (strlen($js_tab) > 1)
	$js_tab=substr($js_tab, 0, -1);

print "var feries=".$js_tab."];\n";


if (($_SESSION['config']['dimanche_travail']==FALSE)&&($_SESSION['config']['samedi_travail']==FALSE)){
	echo "jdesac = [0,6];";
} else {
	if ($_SESSION['config']['dimanche_travail']==FALSE)
		echo "jdesac = [0];";
	if ($_SESSION['config']['dimanche_travail']==FALSE)
		echo "jdesac = [6];";
}
?>
    // datepicker	            
    $('input.date').datepicker({
	    format: "dd/mm/yyyy",

	    language: "fr",
	    autoclose: true,
	    todayHighlight: true,
<?php
	echo "daysOfWeekDisabled: jdesac,\n";
?>
	    datesDisabled: feries,

	}).on("change", function() {
compter_jours();
  });

});

</script>
