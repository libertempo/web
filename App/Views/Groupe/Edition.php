<?php declare(strict_types = 1);
/**
 * $message
 * $infosGroupe
 * $data (tmp)
 * $idGroupe
 * $doubleValidationActive
 * $responsablesGroupe
 * $baseURIApi
 */
?>
<div id="inner-content" class="form-group">
    <h1><?= $titre ?></h1>
    <?= $message ?>
    <form method="post" action="" role="form">
        <table class="table">
            <thead>
                <tr>
                    <th><b><?= _('Nom du groupe') ?></b></th>
                    <th><?= _('admin_groupes_libelle') ?> / <?= _('divers_comment_maj_1') ?></th>
                    <th v-if="hasDoubleValidation"><?= _('admin_groupes_double_valid') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" :value="infosGroupe.nom" required>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" :value="infosGroupe.comment">
                    </td>
                    <td v-if="hasDoubleValidation">
                        <select class="form-control" name="new_group_double_valid" id="select-double-validation" @change="showDivGroupeGrandResp('select-double-validation', 'groupe-grands-responsables');">
                            <option value="N" :selected="selected('N')">N</option>
                            <option value="Y" :selected="selected('Y')">Y</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_users_membres') ?></h2>
                <table class="table table-hover table-condensed table-striped"/>
                    <tbody>
                        <tr>
                            <div id="loader-bar-employe" class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar"
                                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                                </div>
                            </div>
                        </tr>
                        <tr v-for="e in employes">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getEmployeId(e)"
                                 :name="getEmployeName(e)"
                                 :disabled="getEmployeDisabled(e)"
                                 :checked="getEmployeChecked(e)"
                                >
                            </td>
                            <td class="histo"><label :for="getEmployeId(e)">{{ e.nom }} {{ e.prenom }}</label></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6" id="groupe-responsables">
                <h2><?= _('admin_gestion_groupe_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                        <tr>
                            <div id="loader-bar-responsable" class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar"
                                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                                </div>
                            </div>
                        </tr>
                        <tr v-for="r in responsables">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getResponsableId(r)"
                                 :name="getResponsableName(r)"
                                 :disabled="getResponsableDisabled(r)"
                                 :checked="getResponsableChecked(r)"
                                 @change="disableCheckboxGroupe($event, 'select-double-validation')"
                                >
                            </td>
                            <td class="histo"><label :for="getResponsableId(r)">{{ r.nom }} {{ r.prenom }}</label></td>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 hide" id="groupe-grands-responsables">
                <h2><?= _('admin_gestion_groupe_grand_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                        <tr v-for="r in responsables">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getGrandResponsableId(r)"
                                 :name="getGrandResponsableName(r)"
                                 :checked="getGrandResponsableChecked(r)"
                                 @change="disableCheckboxGroupe($event, 'select-double-validation')"
                                >
                            </td>
                            <td class="histo"><label :for="getGrandResponsableId(r)">{{ r.nom }} {{ r.prenom }}</label></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-group">
        <?php if (NIL_INT !== $idGroupe) : ?>
            <input type="hidden" name="_METHOD" value="PUT" />
            <input type="hidden" name="group" value="<?= $idGroupe ?>" />
        <?php endif; ?>
        <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
        </div>
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
        employes : {},
        responsables : {},
        responsablesGroupe : <?= json_encode($responsablesGroupe) ?>,

        hasDoubleValidation: 'true' == "<?= $doubleValidationActive ? 'true' : 'false' ?>",
        infosGroupe : <?= json_encode($infosGroupe) ?>,
        dataForm : <?= json_encode($data) ?>,
        axios : instance
    },
    computed: {
    },
    'methods' : {
        objectContains : function (object, needle) {
            return undefined != object[needle];
        },
        arrayContains : function (array, needle) {
            return -1 != array.indexOf(needle);
        },
        dataEmployeContains : function (employe) {
            var login = employe['login'];

            return this.objectContains(this.dataForm, 'employes') && this.arrayContains(this.dataForm['employes'], login);
        },
        dataResponsableContains : function (employe) {
            var login = employe['login'];

            return this.objectContains(this.dataForm, 'responsables') && this.arrayContains(this.dataForm['responsables'], login);
        },
        dataGrandResponsableContains : function (employe) {
            var login = employe['login'];

            return this.objectContains(this.dataForm, 'grandResponsables') && this.arrayContains(this.dataForm['grandResponsables'], login);
        },
        getEmployeDisabled : function (employe) {
            if (this.dataResponsableContains(employe) || this.dataGrandResponsableContains(employe)) {
                    return true;
            } else if (undefined != this.responsablesGroupe[employe['login']]) {
                return true;
            }

            return false;
        },
        getEmployeChecked : function (employe) {
            if (this.dataEmployeContains(employe) || employe['isDansGroupe']) {
                return true;
            }

            return false;
        },
        getEmployeId : function (employe) {
            return 'Emp_' + employe['login'];
        },
        getEmployeName : function (employe) {
            return 'checkbox_group_users[' + employe['login'] + ']';
        },
        selected : function (bool) {
            return bool == this.infosGroupe.doubleValidation;
        },
        getResponsableId : function (employe) {
            return 'Resp_' + employe['login'];
        },
        getResponsableName : function (employe) {
            return 'checkbox_group_resps[' + employe['login'] + ']';
        },
        getResponsableDisabled : function (employe) {
            return (this.dataGrandResponsableContains(employe));
        },
        getResponsableChecked : function (employe) {
            if (this.dataResponsableContains(employe) || employe['isDansGroupe']) {
                return true;
            }

            return false;
        },
        getGrandResponsableId : function (employe) {
            return 'Gres_' + employe['login'];
        },
        getGrandResponsableName : function (employe) {
            return 'checkbox_group_grand_resps[' + employe['login'] + ']';
        },
        getGrandResponsableChecked : function (employe) {
            if (this.dataGrandResponsableContains(employe) || employe['isDansGroupe']) {
                return true;
            }

            return false;
        },
        disableCheckboxGroupe : function (event, selectId) {
            var target = event.target;
            var select = document.getElementById(selectId);
            var login = target.id.substring(5);
            var employe = document.getElementById('Emp_' + login);
            var responsable = document.getElementById('Resp_' + login);
            var grandResponsable = document.getElementById('Gres_' + login);

            if (target.checked) {
                employe.disabled = true;
                employe.checked = false;
                if (target.id.substring(0, 4) == 'Gres') {
                    responsable.disabled = true;
                    responsable.checked = false;
                } else if (select.value == 'Y') {
                    grandResponsable.disabled = true;
                    grandResponsable.checked = false;
                }
            } else {
                employe.disabled = false;
                if (target.id.substring(0, 4) == 'Gres') {
                    responsable.disabled = false;
                } else if (select.value == 'Y') {
                    grandResponsable.disabled = false;
                }
            }
        },
        showDivGroupeGrandResp : function (selectId, DivGrandRespId) {
            var select = document.getElementById(selectId);
            var groupeGrandsResponsables = document.getElementById(DivGrandRespId);
            if (select.value == 'Y') {
                groupeGrandsResponsables.classList.remove('hide');
            } else {
                groupeGrandsResponsables.classList.add('hide');
            }
        }
    },
    created () {
        var vm = this;
        this.axios.get('/utilisateur')
        .then((response) => {
            if (typeof response.data != 'object') {
                return;
            }
            const employes = response.data.data;
            var fullUtilisateurs = new Array();
            var responsables = new Array();
            for (var i = 0; i < employes.length; ++i) {
                var employe = employes[i];
                employe.isDansGroupe = false;
                fullUtilisateurs.push(employe);

                if (employe.is_haut_responsable) {
                    responsables.push(employe);
                }
            }
            // Finally hide loaders and show vars
            document.getElementById('loader-bar-employe').classList.add('hidden');
            document.getElementById('loader-bar-responsable').classList.add('hidden');
            vm.employes = fullUtilisateurs;
            vm.responsables = responsables;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        })
    },
    updated () {
        // Shows grand responsable groupe
        this.showDivGroupeGrandResp('select-double-validation', 'groupe-grands-responsables');
        // Update checkboxes and lock them
        var event = new Event('change');
        var responsables = document.querySelectorAll('#groupe-responsables input[type=checkbox]');
        for (var i in responsables) {
            if (!responsables.hasOwnProperty(i)) {
                continue;
            }
            responsables[i].dispatchEvent(event);
        }
    }
});
</script>
<?php
