/**
 * Ouvre une popup
 *
 * @param string MyFile   nom du fichier contenant le code HTML du pop-up
 * @param string MyWindow nom de la fenêtre (ne pas mettre d'espace)
 * @param int    MyWidth  entier indiquant la largeur de la fenêtre en pixels
 * @param int    MyHeight entier indiquant la hauteur de la fenêtre en pixels
 */
function OpenPopUp(MyFile,MyWindow,MyWidth,MyHeight)
{
    var ns4 = (document.layers)? true:false; //NS 4
    var ie4 = (document.all)? true:false; //IE 4
    var dom = (document.getElementById)? true:false; //DOM
    var xMax, yMax, xOffset, yOffset;;

    if (ie4 || dom) {
        xMax = screen.width;
        yMax = screen.height;
    } else if (ns4) {
        xMax = window.outerWidth;
        yMax = window.outerHeight;
    } else {
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

/**
 * Compte le nombre de jours de congés entre deux dates en fonctions du contexte (temps partiel, jours fériés, fermeture, ...)
 */
function compter_jours()
{
    $(document).ready(function () {
        var login = document.forms["dem_conges"].user_login.value;
        var session = document.forms["dem_conges"].session.value;
        var d_debut = document.forms["dem_conges"].new_debut.value;
        var d_fin = document.forms["dem_conges"].new_fin.value;
        var opt_deb = document.forms["dem_conges"].new_demi_jour_deb.value;
        var opt_fin = document.forms["dem_conges"].new_demi_jour_fin.value;
        var p_num = "";

        if( document.forms["dem_conges"].p_num_to_update ) {
            var p_num = document.forms["dem_conges"].p_num_to_update.value;
        }

        if( (d_debut) && (d_fin)) {
            var page = '../calcul_nb_jours_pris.php?session=' + session + '&date_debut=' + d_debut + '&date_fin=' + d_fin+'&user=' + login + '&opt_debut=' +opt_deb + '&opt_fin=' + opt_fin + '&p_num=' +p_num;

            $.ajax({
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
    });
}

/**
 * Génère le datepicker sur un champ, paramétré par des options précises
 *
 * @param object opts
 */
function generateDatePicker(opts)
{
    var defaultOpts = {
        format             : 'dd/mm/yyyy',
        language           : 'fr',
        autoclose          : true,
        todayHighlight     : true,
        daysOfWeekDisabled : [],
        datesDisabled      : [],
        startDate          : ''
    };
    var toApply = defaultOpts;

    /* On ne peut pas écraser une option qui n'existe en défaut */
    for (var i in defaultOpts) {
        toApply[i] = (undefined !== opts[i]) ? opts[i] : defaultOpts[i];
    }
    $(document).ready(function () {
        $('input.date').datepicker(toApply).on("change", function() {
            compter_jours();
        });
    });
}
