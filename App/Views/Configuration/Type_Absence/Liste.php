<?php
/*
 * $listeTypeConges
 * $nouveauLibelle
 * $nouveauLibelleCourt
 * $nouveauType
 * $traductions
 * $lienModif
 * $lienSuppr
 */
?>
<div id="inner-content">
    <h1><?= _('config_abs_titre') ?></h1>
    <div v-for="(value, key) in in absenceTypes">
        <h2>{{ titre(key) }}</h2>
        <p>{{ commentaire(key) }}</p>
        <table class="table table-hover table-responsive table-condensed table-striped">
            <tr>
                <th><?= _('config_abs_libelle') ?></th>
                <th><?= _('config_abs_libelle_short') ?></th>
                <th></th>
            </tr>
            <tr v-if="!hasAbsences(key)"><td colspan="2"><center><?= _('aucun_resultat') ?></center></td></tr>
            <tr v-for="absenceType in value">
                <td><strong>{{ absenceType.libelle }}</strong></td>
                <td>{{ absenceType.libelleCourt }}</td>
                <td class="action">
                    <div v-if="{{ absenceType.typeNatif }}">
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
                        <option v-for="type in typesGeneraux" :selected="isSelected(type)">{{ type }}</option>
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
axios.defaults.headers.get = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Token': '<?= $_SESSION['token'] ?>',
};

const instance = axios.create({
  baseURL: '<?= $baseURIApi ?>',
  timeout: 1500
});

var vm = new Vue({
    el: '#inner-content',
    data: {
        absenceTypes : '',
        listIdUsed : <?= json_encode($listIdUsed) ?>,
        isHr: 'true' == "<?= $isHr ? 'true' : 'false' ?>",
        lienModification : "<?= $lienModif ?>",
        lienSuppression : "<?= $lienSuppr ?>",
        statusActive : <?= \App\Models\Planning::STATUS_ACTIVE ?>,
        nouveauLibelle : '<?= $nouveauLibelle ?>',
        nouveauLibelleCourt : '<?= $nouveauLibelleCourt ?>',
        nouveauType : '<?= $nouveauType ?>',
        traductions : '<?= $traductions ?>',
        axios : instance
    },
    computed: {
        typesGeneraux : function () {
            return Object.keys(this.absenceType);
        }
    },
    'methods' : {
        hasAbsences: function (type) {
            return 0 > this.absenceType[type].length;
        },
        titre: function (type) {
            return this.traductions.titres[type];
        },
        commentaire: function (type) {
            return this.traductions.titres[type];
        },
        linkModification : function (id) {
            return this.lienModification + id;
        }
        linkSuppression : function (id) {
            return this.lienSuppression + id;
        },
        isSelected : function (type) {
            return type == this.nouveauType;
        }
    },
    created () {
    var vm = this;
    this.axios.get('/absence/type')
        .then((response) => {
            const absenceTypes = response.data.data;
            var organisedTypes = {};
            for (var i = 0; i < absenceTypes.length; ++i) {
                var absenceType = absenceTypes[i];
                if (undefined == organisedTypes[absenceType.type]) {
                    organisedTypes[absenceType.type] = new Array();
                }
                organisedTypes[absenceType.type].push(absenceType);
            }
            vm.absenceType = organisedTypes;
            /*
            var exemple = {
                'conges' : [
                    {
                        id: 1,
                        name: 'foo',
                    },
                    {
                        id: 2,
                        name: 'bar',
                    }
                ]
            };
            vm.types = activePlannings;
            */

            /*
            var obj = {
                key1: value1,
                key2: value2
            };

            Using dot notation:

            obj.key3 = "value3";

            Using square bracket notation:

            obj["key3"] = "value3";

            The first form is used when you know the name of the property. The second form is used when the name of the property is dynamically determined. Like in this example:

            var getProperty = function (propertyName) {
                return obj[propertyName];
            };

            getProperty("key1");
            getProperty("key2");
            getProperty("key3");

            A real JavaScript array can be constructed using either:
            The Array literal notation:

            var arr = [];

            The Array constructor notation:

            var arr = new Array();
            */
        })
        .catch((error) => {
            console.log(error);
        })
  }
});
</script>
<?php
