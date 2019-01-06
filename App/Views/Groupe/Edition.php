<?php declare(strict_types = 1);
/**
 * $message
 * $selectId
 * $divGrandRespId
 * $infosGroupe
 * $data (tmp)
 * $idGroupe
 * $doubleValidationActive
 * $responsables
 * $responsablesGroupe
 * $baseURIApi
 */
?>
<div id="inner-content" onload="showDivGroupeGrandResp('<?= $selectId ?>', '<?= $divGrandRespId ?>');" class="form-group">
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
                        <select class="form-control" name="new_group_double_valid" id="<?= $selectId ?>" onchange="showDivGroupeGrandResp('<?= $selectId ?>','<?= $divGrandRespId ?>');">
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
                        <tr v-for="e in employes">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getEmployeId(e)"
                                 :name="getEmployeName(e)"
                                 :disabled="getEmployeDisabled(e)"
                                 :checked="getEmployeChecked(e)"
                                >
                            </td>
                            <td class="histo">{{ e.nom }} {{ e.prenom }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                        <tr v-for="r in responsables">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getResponsableId(r)"
                                 :name="getResponsableName(r)"
                                 :disabled="getResponsableDisabled(r)"
                                 :checked="getResponsableChecked(r)"
                                 @change="disableCheckboxGroupe($event, '<?= $selectId ?>')"
                                >
                            </td>
                            <td class="histo">{{ r.nom }} {{ r.prenom }}</td>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 hide" id="<?= $divGrandRespId ?>">
                <h2><?= _('admin_gestion_groupe_grand_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                        <tr v-for="r in responsables">
                            <td class="histo">
                                <input type="checkbox"
                                 :id="getGrandResponsableId(r)"
                                 :name="getGrandResponsableName(r)"
                                 :checked="getGrandResponsableChecked(r)"
                                 @change="disableCheckboxGroupe($event, '<?= $selectId ?>')"
                                >
                            </td>
                            <td class="histo">{{ r.nom }} {{ r.prenom }}</td>
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
        getEmployeDisabled : function (employe) {
            if (0 != this.dataForm.length) {
                if (undefined != this.dataForm['responsables'][employe['login']] || undefined != this.dataForm['grandResponsables'][employe['login']]) {
                    return true;
                }
            } else if (undefined != this.responsablesGroupe[employe['login']]) {
                return true;
            }

            return false;
        },
        getEmployeChecked : function (employe) {
            if (0 != this.dataForm.length && this.dataForm['employes'][employe['login']]) {
                return true;
            } else if (employe['isDansGroupe']) {
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
            return (0 != this.dataForm.length && undefined != this.dataForm['grandResponsables'][employe['login']]);
        },
        getResponsableChecked : function (employe) {
            if (0 != this.dataForm.length && this.dataForm['responsables'][employe['login']]) {
                return true;
            } else if (employe['isDansGroupe']) {
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
            if (0 != this.dataForm.length && this.dataForm['grandResponsables'][employe['login']]) {
                return true;
            } else if (employe['isDansGroupe']) {
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
            vm.employes = fullUtilisateurs;
            vm.responsables = responsables;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        })
    }
});
</script>
<?php
