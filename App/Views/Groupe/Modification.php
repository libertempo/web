<?php declare(strict_types = 1);
/**
 * $titre
 * $message
 * $idGroupe
 * $data
 * $selectId
 * $DivGrandRespId
 * $infosGroupe
 */
?>
<div onload="showDivGroupeGrandResp(<?= $selectId ?>','<?= $DivGrandRespId ?>');" class="form-group">
    <h1><?= $titre ?></h1>
    <?php $message ?>
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
                    <td><input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" value="<?= $infosGroupe['nom'] ?>" required></td>
                    <td><input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" value="<?= $infosGroupe['comment'] ?>"></td>
                    <?php if ($doubleValidationActive) : ?>
                        <?php
                        $selectN = $infosGroupe['doubleValidation'] === 'N' ? 'selected="selected"' : '';
                        $selectY = $infosGroupe['doubleValidation'] === 'Y' ? 'selected="selected"' : '';
                        ?>
                        <td><select class="form-control" name="new_group_double_valid" id="<?= $selectId ?>" onchange="showDivGroupeGrandResp('<?= $selectId ?>',' <?= $DivGrandRespId ?>');"><option value="N" <?= $selectN ?>>N</option><option value="Y" <?= $selectY ?>>Y</option></select></td>
                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_users_membres') ?></h2>
                <table class="table table-hover table-condensed table-striped"/>
                    <thead>
                        <tr>';
                $childTable .= '<th></th>';
                $childTable .= '<th>' . _('divers_personne_maj_1') . '</th>';
                $childTable .= '<th>' . _('divers_login') . '</th>';
                $childTable .= '</tr>';
                $childTable .= '</thead>';
                $childTable .= '<tbody>';
                $i = true;
                foreach (getEmployes($idGroupe) as $login => $info) {
                    $inputOption = '';

                    if (!empty($data)) {
                        if (in_array($login, $data['responsables']) || in_array($login, $data['grandResponsables'])) {
                            $inputOption = 'disabled';
                        } elseif (in_array($login, $data['employes'])) {
                            $inputOption = 'checked';
                        }
                    } elseif (\App\ProtoControllers\Groupe::isResponsableGroupe($login, [$idGroupe], \includes\SQL::singleton())) {
                        $inputOption = 'disabled';
                    } elseif ($info['isDansGroupe']) {
                        $inputOption = 'checked';
                    }

                    $childTable .= '<tr class="' . (($i) ? 'i' : 'p') . '">';
                    $childTable .='<td class="histo"><input type="checkbox" id="Emp_' . $login . '" name="checkbox_group_users[' . $login . '] "' . $inputOption . '></td>';
                    $childTable .= '<td class="histo">' . $info['nom'] . ' ' . $info['prenom'] . '</td>';
                    $childTable .= '<td class="histo">' . $login . '</td>';
                    $childTable .= '</tr>';
                }
                $childTable .= '</tbody>';
                $table->addChild($childTable);
                ob_start();
                $table->render();
                $return = ob_get_clean();

                return $return;





                <?= getFormChoixEmploye($idGroupe, $data); ?>
            </div>
            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_resp_responsables') ?></h2>
                <?= getFormChoixResponsable($idGroupe, $selectId, $data); ?>
            </div>
            <div class="col-md-6 hide" id="<?= $DivGrandRespId ?>">
                <h2><?= _('admin_gestion_groupe_grand_resp_responsables') ?></h2>
                <?= getFormChoixGrandResponsable($idGroupe, $selectId, $data) ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" name="_METHOD" value="PUT" />
        <input type="hidden" name="group" value="<?= $idGroupe ?>" />
        <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
    </div>
</form>
