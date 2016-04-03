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

/**
 * Objet de manipulation du planning
 */
var planningController = function (idElement, options, creneaux)
{
    this.element = document.getElementById(idElement);
    this.options = options;
    this.typeSemaine = this.options['typeSemaine'];
    this.debut = this.options['typeHeureDebut'];
    this.fin   = this.options['typeHeureFin'];
    this.incrementMatin = 0;
    this.incrementApresMidi = 0;
    this.init = function ()
    {
        this.element.addEventListener('click', function () {
            var select = document.getElementById(this.options['selectJourId']);
            var jourSelectionne = select.options[select.selectedIndex].value;
            var radios = document.body.querySelectorAll('input[type="radio"]');
            var typePeriodeSelected = 0;
            for (var i = 0; i < radios.length; ++i) {
                if (radios[i].checked) {
                    typePeriodeSelected = radios[i].value;
                }
            }
            var debutVal = document.getElementById(this.options['debutId']).value;
            var finVal = document.getElementById(this.options['finId']).value;
            if (-1 != jourSelectionne && 0 != typePeriodeSelected && '' != debutVal && '' != finVal) {
                if (this._checkTimeValue(debutVal) || this._checkTimeValue(finVal)) {
                    this._addPeriod(jourSelectionne, typePeriodeSelected, debutVal, finVal);
                    // else heure inconnue
                    // TODO: reorganisation dynamique de l'ordre
                }
            }
            // Else option manquante
        }.bind(this));

        /* Remplissage des valeurs prééxistantes */
        this._addPeriods(creneaux);
    }

    /**
     * Vérifie que l'heure respecte bien un format donné
     *
     * @param string timeValue
     *
     * @return bool
     */
    this._checkTimeValue = function (timeValue)
    {
        timeValue   = timeValue.trim();
        var pattern = new RegExp("^(([0-1][0-9])|(2[0-3])):[0-5]|0-9]");

        return pattern.test(timeValue);
    }

    /**
     * Ajoute une liste de périodes au planning
     *
     * @param Object creneaux
     *
     * @return void
     */
    this._addPeriods = function (creneaux)
    {
        for (var jour in creneaux) {
            var dataJour = creneaux[jour];
            for (var periode in dataJour) {
                var dataPeriode = dataJour[periode];
                if (!dataPeriode.hasOwnProperty(this.debut) || !dataPeriode.hasOwnProperty(this.fin)) {
                    return;
                }
                this._addPeriod(jour, periode, dataPeriode[this.debut], dataPeriode[this.fin]);
            }
        }
    }

    /**
     * Ajoute une nouvelle période au planning
     *
     * @param int    jourSelectionne
     * @param int    typePeriodeSelected
     * @param string debutVal
     * @param string finVal
     *
     * @return void
     */
    this._addPeriod = function (jourSelectionne, typePeriodeSelected, debutVal, finVal)
    {
        var table = document.getElementById(this.options['tableId']);
        var ligneCible = table.querySelector('tr[data-id-jour="' +  jourSelectionne + '"]');
        var cellCible = ligneCible.getElementsByClassName('creneaux')[0];

        cellCible.appendChild(this._getVisiblePeriod(jourSelectionne, typePeriodeSelected, debutVal, finVal));
    }

    /**
     * Retourne la structure DOM d'une période à afficher
     *
     * @param int    jourSelectionne
     * @param int    typePeriodeSelected
     * @param string debutVal
     * @param string finVal
     *
     * @return HTMLSpanElement
     */
    this._getVisiblePeriod = function (jourSelectionne, typePeriodeSelected, debutVal, finVal) {
        var span = document.createElement('span');
        var iBaseTag = document.createElement('i');
        var iTo = iBaseTag.cloneNode(false);
        var buttonTag = document.createElement('button');
        buttonTag.className = 'btn btn-default btn-xs';
        buttonTag.type = 'button';
        buttonTag.addEventListener('click', function (e) {
            this._removePeriod(e.target.parentNode);
        }.bind(this));
        iTo.className = 'fa fa-caret-right';
        var debut = document.createTextNode(' ' + debutVal + ' ');
        var labelTypePeriode = (this.options['typePeriodeMatin'] == typePeriodeSelected ) ? 'am' : 'pm';
        var fin   = document.createTextNode(' ' + finVal + ' (' + labelTypePeriode + ') ');
        var iMinus = iBaseTag.cloneNode(false);
        iMinus.className = 'fa fa-minus';
        span.appendChild(debut);
        span.appendChild(iTo);
        span.appendChild(fin);
        buttonTag.appendChild(iMinus);
        span.appendChild(buttonTag);
        span.appendChild(this._getHiddenFieldPeriod(jourSelectionne, typePeriodeSelected, debutVal, finVal));

        return span;
    }

    /**
     * Retourne la structure DOM des champs cachés d'une période à afficher
     *
     * @param int    jourSelectionne
     * @param int    typePeriodeSelected
     * @param string debutVal
     * @param string finVal
     *
     * @return HTMLSpanElement
     */
    this._getHiddenFieldPeriod = function (jourSelectionne, typePeriodeSelected, debutVal, finVal) {
        var span = document.createElement('span');
        var input = document.createElement('input');
        var typeSemaine = this.typeSemaine;
        var debut = input.cloneNode(false);
        var prefixName = 'creneaux[' + typeSemaine + '][' + jourSelectionne + '][' + typePeriodeSelected + ']';
        if (this.options['typePeriodeMatin'] == typePeriodeSelected) {
            var prefixName = prefixName + '[' + (++this.incrementMatin) + ']';
        } else {
            var prefixName = prefixName + '[' + (++this.incrementMatin) + ']';
        }
        debut.type  = 'hidden';
        debut.name  = prefixName + '[' + this.debut + ']';
        debut.value = debutVal;
        var fin     = input.cloneNode(false);
        fin.type    = 'hidden';
        fin.name    = prefixName + '[' + this.fin + ']';
        fin.value   = finVal;
        span.appendChild(debut);
        span.appendChild(fin);

        return span;
    }

    /**
     * Supprime une période du planning
     *
     * @return void
     */
    this._removePeriod = function (period)
    {
        period.parentNode.removeChild(period);
    }
}
