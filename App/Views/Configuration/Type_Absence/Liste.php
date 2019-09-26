<?php
/*
 * $nouveauLibelle
 * $nouveauLibelleCourt
 * $nouveauType
 * $traductions
 * $url
 * $isCongesExceptionnelsActive
 * $classesConges
 */
?>
<div id="inner-content">
    <h1><?= _('config_abs_titre') ?></h1>
    <div id="loader-bar" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar"
        aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
        </div>
    </div>
    <div v-for="classe in classesConges">
        <h2>{{ titre(classe) }}</h2>
        <p>{{ commentaire(classe) }}</p>
        <table class="table table-hover table-responsive table-condensed table-striped">
            <tr>
                <th><?= _('config_abs_libelle') ?></th>
                <th><?= _('config_abs_libelle_short') ?></th>
                <th></th>
            </tr>
            <tr v-if="!hasAbsences(classe)"><td colspan="2"><center><?= _('aucun_resultat') ?></center></td></tr>
            <tr v-for="absenceType in absenceTypes[classe]">
                <td><strong>{{ absenceType.libelle }}</strong></td>
                <td>{{ absenceType.libelleCourt }}</td>
                <td class="action">
                    <div v-if="absenceType.typeNatif">
                        <i class="fa fa-pencil disabled" title="Type d'absence natif"></i>
                        &nbsp;
                        <i class="fa fa-times-circle disabled" title="Type d'absence natif"></i>
                    </div>
                    <div v-else>
                        <a :href="linkModification(absenceType.id)" title=" <?= _('form_modif') ?>"><i class="fa fa-pencil"></i></a>
                        &nbsp;
                        <a :href="linkSuppression(absenceType.id)" title=" <?= _('form_supprim') ?>"><i class="fa fa-times-circle"></i></a>
                    </div>
                </td>
            </tr>
        </table>
        <hr/>
    </div>

    <h2><?= _('config_abs_add_type_abs') ?></h2>
    <p><?= _('config_abs_add_type_abs_comment') ?></p>
    <form :action="url" method="POST">
        <table class="table table-hover table-responsive table-condensed table-striped">
            <tr>
                <th><?= _('config_abs_libelle') ?></th>
                <th><?= _('config_abs_libelle_short') ?></th>
                <th><?= _('divers_type') ?></th>
            </tr><tr>
                <td><input class="form-control" type="text" name="tab_new_values[libelle]" size="20" maxlength="20" :value="nouveauLibelle"></td>
                <td><input class="form-control" type="text" name="tab_new_values[short_libelle]" size="3" maxlength="3" :value="nouveauLibelleCourt"></td>
                <td>
                    <select class="form-control" name=tab_new_values[type]>
                        <option v-for="classe in classesConges" :selected="isSelected(classe)">{{ titre(classe) }}</option>
                    </select>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="new">
        <hr/>
        <input type="submit" class="btn btn-success" value="<?= _('form_ajout') ?>"><br>
    </form>
</div>

<script>
var optionsVue = {
    el: '#inner-content',
    data: {
        absenceTypes : {},
        url : '<?= $url ?>',
        nouveauLibelle : '<?= $nouveauLibelle ?>',
        nouveauLibelleCourt : '<?= $nouveauLibelleCourt ?>',
        nouveauType : '<?= $nouveauType ?>',
        traductions : <?= json_encode($traductions) ?>,
        isCongesExceptionnelsActive: 'true' == "<?= $isCongesExceptionnelsActive ? 'true' : 'false' ?>",
        axios : instance,
        classesConges : <?= json_encode($classesConges) ?>
    },
    computed: {
    },
    'methods' : {
        hasAbsences: function (type) {
            return undefined != this.absenceTypes[type] && 0 < this.absenceTypes[type].length;
        },
        titre: function (type) {
            return this.traductions.titres[type];
        },
        commentaire: function (type) {
            return this.traductions.commentaires[type];
        },
        linkModification : function (id) {
            return this.url + '?action=modif&id_to_update=' + id;
        },
        linkSuppression : function (id) {
            return this.url + '?action=suppr&id_to_update=' + id;
        },
        isSelected : function (type) {
            return type == this.nouveauType;
        }
    },
    created () {
        var vm = this;
        this.axios.get('/absence/type')
        .then((response) => {
            if (typeof response.data != 'object') {
                return;
            }
            const absenceTypes = response.data.data;
            console.log(response.data);
            var organisedTypes = {};
            for (var i = 0; i < absenceTypes.length; ++i) {
                var absenceType = absenceTypes[i];
                if (undefined == organisedTypes[absenceType.type]) {
                    organisedTypes[absenceType.type] = new Array();
                }
                organisedTypes[absenceType.type].push(absenceType);
            }
            if (!vm.isCongesExceptionnelsActive && undefined != organisedTypes['conges_exceptionnels']) {
                delete organisedTypes['conges_exceptionnels'];
            }

            // Finally hide loader and show var
            document.getElementById('loader-bar').classList.add('hidden');
            vm.absenceTypes = organisedTypes;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        })
    }
});
</script>
<?php
