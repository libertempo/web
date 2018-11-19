<?php declare(strict_types = 1);
/**
 * $titre
 * $tab_all_users_du_hr
 * $tab_all_users_du_grand_resp
 * $list_group
 * $groupes
 * $tab_type_cong
 * $tab_type_conges_exceptionnels
 */
?>

<h1><?= $titre ?></h1>
<?php if (empty($tab_all_users_du_hr) && empty($tab_all_users_du_grand_resp)) : ?>
    <?= _('resp_etat_aucun_user') ?><br>
<?php else : ?>
    <h2><?= _('resp_ajout_conges_ajout_all') ?></h2>
    <form action="<?= $PHP_SELF ?>" method="POST">
        <fieldset class="cal_saisie">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            <th colspan="2"><?= _('resp_ajout_conges_nb_jours_all_1') ?> <?= _('resp_ajout_conges_nb_jours_all_2') ?></th>
                            <th><?= _('resp_ajout_conges_calcul_prop') ?></th>
                            <th><?= _('divers_comment_maj_1') ?></th>
                        </tr>
                    </thead>
                <?php foreach($tab_type_cong as $id_conges => $libelle) : ?>
                    <tr>
                        <td><strong><?= $libelle ?><strong></td>
                        <td><input class="form-control" type="text" name="tab_new_nb_conges_all[<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                        <td><?= _('resp_ajout_conges_oui') ?>
                            <input type="checkbox" name="tab_calcul_proportionnel[<?= $id_conges ?>]" value="TRUE" checked>
                        </td>
                        <td><input class="form-control" type="text" name="tab_new_comment_all[<?= $id_conges ?>]" size="30" maxlength="200"></td>
                    </tr>
                <?php endforeach ; ?>
                </table>
            </div>
            <p><?= _('resp_ajout_conges_calcul_prop_arondi') ?> !</p>
            <input class="btn" type="submit" value="<?= _('form_valid_global') ?>">
        </fieldset>
        <input type="hidden" name="ajout_global" value="true">
    </form>
    <br>
    <?php if (!empty($list_group)) : ?>
    <h2><?= _('resp_ajout_conges_ajout_groupe') ?></h2>
    <form action="<?= $PHP_SELF ?>" method="POST">
        <fieldset class="cal_saisie">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <tr>
                        <td class="big"><?= _('resp_ajout_conges_choix_groupe') ?> : </td>
                        <td colspan="3">
                            <select name="choix_groupe">
                            <?php foreach ($groupes as $groupe) : ?>
                                <option value="<?= $groupe['g_gid'] ?>"><?= $groupe['g_groupename'] ?></option>
                            <?php endforeach ; ?>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><?= _('resp_ajout_conges_nb_jours_all_1') ?> <?= _('resp_ajout_conges_nb_jours_all_2') ?></th>
                        <th><?= _('resp_ajout_conges_calcul_prop') ?></th>
                        <th><?= _('divers_comment_maj_1') ?></th>
                    </tr>
                <?php foreach($tab_type_cong as $id_conges => $libelle) : ?>
                    <tr>
                        <td><strong><?= $libelle ?><strong></td>
                        <td><input class="form-control" type="text" name="tab_new_nb_conges_all[<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                        <td><?= _('resp_ajout_conges_oui') ?>
                            <input type="checkbox" name="tab_calcul_proportionnel[<?= $id_conges ?>]" value="TRUE" checked>
                        </td>
                        <td>
                            <input class="form-control" type="text" name="tab_new_comment_all[<?= $id_conges ?>]" size="30" maxlength="200">
                        </td>
                    </tr>
                <?php endforeach ; ?>
                </table>
            </div>
            <p><?= _('resp_ajout_conges_calcul_prop_arondi') ?> ! </p>
            <input class="btn" type="submit" value="<?= _('form_valid_groupe') ?>">
        </fieldset>
        <input type="hidden" name="ajout_groupe" value="true">
    </form>
    <br>
<?php endif ; ?>

    <h2>Ajout par utilisateur</h2>
    <form action="<?= $PHP_SELF ?>" method="POST">
        <div class="table-responsive">
            <table class="table table-hover table-condensed table-striped">
                <thead>
                    <tr align="center">
                        <th><?= _('divers_nom_maj_1') ?></th>
                        <th><?= _('divers_prenom_maj_1') ?></th>
                        <th><?= _('divers_quotite_maj_1') ?></th>
                    <?php foreach($tab_type_cong as $libelle) : ?>
                        <th><?= $libelle ?><br><i>(<?= _('divers_solde') ?>)</i></th>
                        <th><?= $libelle ?><br><?= _('resp_ajout_conges_nb_jours_ajout') ?></th>
                    <?php endforeach ; ?>
                    <?php foreach($tab_type_conges_exceptionnels as $libelle) : ?>
                        <th><?= $libelle ?><br><i>(<?= _('divers_solde') ?>)</i></th>
                        <th><?= $libelle ?><br><?= _('resp_ajout_conges_nb_jours_ajout') ?></th>
                    <?php endforeach ; ?>
                        <th><?= _('divers_comment_maj_1') ?><br></th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = true; ?>
                <?php foreach ($tab_all_users_du_hr as $current_login => $tab_current_user) : ?>
                    <?php if ($tab_current_user['is_active'] != "Y") : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <tr class="<?= ($i ? 'i' : 'p') ?>">
                        <?php $tab_conges = $tab_current_user['conges']; ?>
                        <td><?= $tab_current_user['nom'] ?></td>
                        <td><?= $tab_current_user['prenom'] ?></td>
                        <td><?= $tab_current_user['quotite'] ?>%</td>
                    <?php foreach($tab_type_cong as $id_conges => $libelle) : ?>
                        <td><?= $tab_conges[$libelle]['nb_an'] ?? 0 ?> <i>(<?= $tab_conges[$libelle]['solde'] ?? 0 ?>)</i></td>
                        <td align="center" class="histo">
                            <input class="form-control" type="text" name="tab_champ_saisie[<?= $current_login ?>][<?= $id_conges ?>]" size="6" maxlength="6" value="0">
                        </td>
                    <?php endforeach ; ?>
                    <?php foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) : ?>
                        <td><i>(<?= $tab_conges[$libelle]['solde'] ?>)</i></td>
                        <td align="center" class="histo">
                            <input class="form-control" type="text" name="tab_champ_saisie[<?= $current_login ?>][ <?= $id_conges ?>]" size="6" maxlength="6" value="0">
                        </td>
                    <?php endforeach ; ?>
                        <td align="center" class="histo">
                            <input class="form-control" type="text" name="tab_commentaire_saisie[<?= $current_login ?>]" size="30" maxlength="200" value="">
                        </td>
                    </tr>
                    <?php $i = !$i; ?>
                <?php endforeach ; ?>
                </tbody>
            </table>
        <input type="hidden" name="ajout_conges" value="true">
        <input class="btn" type="submit" value="<?= _('form_submit') ?>">
    </form>
    <br>
<?php endif ; ?>
