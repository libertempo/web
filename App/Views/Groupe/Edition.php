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
 * $grandResponsables
 * $sql
 */
?>
<div onload="showDivGroupeGrandResp('<?= $selectId ?>', '<?= $DivGrandRespId ?>');" class="form-group">
    <h1><?= $titre ?></h1>
    <?= $message ?>
    <form method="post" action="" role="form">
        <table class="table">
            <thead>
                <tr>
                    <th><b><?= _('Nom du groupe') ?></b></th>
                    <th><?= _('admin_groupes_libelle') ?> / <?= _('divers_comment_maj_1') ?></th>
                    <?php if ($doubleValidationActive) : ?>
                        <th><?= _('admin_groupes_double_valid') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" value="<?= $infosGroupe['nom'] ?>" required>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" value="<?= $infosGroupe['comment'] ?>">
                    </td>
                    <?php if ($doubleValidationActive) : ?>
                        <?php
                        $selectN = $infosGroupe['doubleValidation'] == 'N' ? 'selected="selected"' : '';
                        $selectY = $infosGroupe['doubleValidation'] == 'Y' ? 'selected="selected"' : '';
                        ?>
                        <td>
                            <select class="form-control" name="new_group_double_valid" id="<?= $selectId ?>" onchange="showDivGroupeGrandResp('<?= $selectId ?>','<?= $DivGrandRespId ?>');">
                                <option value="N" <?= $selectN ?>>N</option>
                                <option value="Y" <?= $selectY ?>>Y</option>
                            </select>
                        </td>
                    <?php endif; ?>
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
