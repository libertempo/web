<?php declare(strict_types = 1);
/**
 * $message
 * $selectId
 * $DivGrandRespId
 * $infosGroupe
 * $data (tmp)
 * $idGroupe
 * $doubleValidationActive
 * $employes
 * $responsables
 * $sql
 * $baseURIApi
 */
?>
<div id="inner-content" onload="showDivGroupeGrandResp('<?= $selectId ?>', '<?= $DivGrandRespId ?>');" class="form-group">
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
                        <select class="form-control" name="new_group_double_valid" id="<?= $selectId ?>" onchange="showDivGroupeGrandResp('<?= $selectId ?>','<?= $DivGrandRespId ?>');">
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
                    <?php $i = true ?>
                    <?php foreach ($employes as $info) : ?>
                        <?php
                        $inputOption = '';
                        if (isset($data)) {
                            if (in_array($info['login'], $data['responsables']) || in_array($info['login'], $data['grandResponsables'])) {
                                $inputOption = 'disabled';
                            } elseif (in_array($info['login'], $data['employes'])) {
                                $inputOption = 'checked';
                            }
                        } elseif (\App\ProtoControllers\Groupe::isResponsableGroupe($info['login'], [$idGroupe], $sql)) {
                            $inputOption = 'disabled';
                        } elseif ($info['isDansGroupe']) {
                            $inputOption = 'checked';
                        }
                        ?>
                        <tr class="<?= ($i) ? 'i' : 'p' ?>">
                            <td class="histo">
                                <input type="checkbox" id="Emp_<?= $info['login'] ?>" name="checkbox_group_users['<?= $info['login'] ?>]" <?= $inputOption ?>>
                            </td>
                            <td class="histo"><?= $info['nom'] ?> <?= $info['prenom'] ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                    <?php $i = true ?>
                    <?php foreach ($responsables as $info) : ?>
                        <?php
                        $inputOption = '';

                        if (isset($data)) {
                            if (in_array($info['login'], $data['grandResponsables'])) {
                                $inputOption = 'disabled';
                            } elseif (in_array($info['login'], $data['responsables'])) {
                                $inputOption = 'checked';
                            }
                        } elseif ($info['isDansGroupe']) {
                            $inputOption = 'checked';
                        }
                        ?>
                        <tr class="<?= ($i) ? 'i' : 'p' ?>">
                            <td class="histo">
                                <input type="checkbox" id="Resp_<?= $info['login'] ?>" name="checkbox_group_resps[<?= $info['login'] ?>]" onchange="disableCheckboxGroupe(this,'<?= $selectId ?>');" <?= $inputOption ?>>
                            </td>
                            <td class="histo"><?= $info['nom'] ?> <?= $info['prenom'] ?>
                            </td>
                        </tr>
                    <?php endforeach ; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 hide" id="<?= $DivGrandRespId ?>">
                <h2><?= _('admin_gestion_groupe_grand_resp_responsables') ?></h2>
                <table class="table table-hover table-responsive table-condensed table-striped">
                    <tbody>
                    <?php
                    $i = true;
                    ?>
                    <?php foreach ($responsables as $info) : ?>
                        <?php
                        $inputOption = '';

                        if (isset($data)) {
                            if (in_array($info['login'], $data['grandResponsables'])) {
                                $inputOption = 'checked';
                            }
                        } elseif ($info['isDansGroupe']) {
                            $inputOption = 'checked';
                        }
                        ?>

                        <tr class="<?= ($i) ? 'i' : 'p' ?>">
                            <td class="histo">
                                <input type="checkbox" id="Gres_<?= $info['login'] ?>" name="checkbox_group_grand_resps[<?=  $info['login'] ?>]" onchange="disableCheckboxGroupe(this,'<?= $selectId ?> ');" <?= $inputOption ?>>
                            </td>
                            <td class="histo"><?= $info['nom'] ?> <?= $info['prenom'] ?></td>
                        </tr>
                    <?php endforeach;?>
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
        employes : <?= json_encode($employes) ?>,
        responsables : <?= json_encode($responsables) ?>,
        hasDoubleValidation: 'true' == "<?= $doubleValidationActive ? 'true' : 'false' ?>",
        infosGroupe : <?= json_encode($infosGroupe) ?>,
        axios : instance
    },
    computed: {
    },
    'methods' : {
        hasPlanning : function () {
            return 0 < Object.keys(this.plannings).length;
        },
        selected : function (bool) {
            return bool == this.infosGroupe.doubleValidation;
        },
        linkModification : function (id) {
            return this.lienModification + '?id=' + id;
        }
    },
    created () {
        var vm = this;
    }
});
</script>
<?php
