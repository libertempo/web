<?php
/*
 * $hasSubalternes
 * $tab_type_cong
 * $groupes
 * $subalternesActifsResponsable
 * $subalternesActifsGrandResponsable
 */
?>
<h1><?= _('resp_ajout_conges_titre') ?></h1>
<?php if (!$hasSubalternes) : ?>
    <?= _('resp_etat_aucun_user') ?><br>
<?php else : ?>
    <h2><?= _('resp_ajout_conges_ajout_all') ?></h2>
    <form action="?onglet=ajout_conges" method="POST">
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
                <?php foreach ($tab_type_cong as $id_conges => $libelle) : ?>
                    <tr>
                        <td><strong><?= $libelle ?><strong></td>
                        <td><input class="form-control" type="text" name="tab_new_nb_conges_all[<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                        <td><?= _('resp_ajout_conges_oui') ?><input type="checkbox" name="tab_calcul_proportionnel[<?= $id_conges ?>]" value="TRUE" checked></td>
                        <td><input class="form-control" type="text" name="tab_new_comment_all[<?= $id_conges ?>]" size="30" maxlength="200" value=""></td>
                    </tr>
                <?php endforeach ; ?>
                </table>
            </div>
            <p><?= _('resp_ajout_conges_calcul_prop_arondi') ?> !</p>
            <input class="btn" type="submit" value="<?= _('form_valid_global') ?>">
        </fieldset>
        <input type="hidden" name="ajout_global" value="TRUE">
    </form>
    <br>
    <?php if (!empty($groupes)) : ?>
        <h2><?= _('resp_ajout_conges_ajout_groupe') ?></h2>
        <form action="?onglet=ajout_conges" method="POST">
            <fieldset class="cal_saisie">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-striped">
                        <tr>
                            <td class="big"><?= _('resp_ajout_conges_choix_groupe') ?> : </td>
                            <td colspan="3">
                                <select name="choix_groupe">
                                    <?php foreach ($groupes as $groupeId => $groupeName) : ?>
                                        <option value="<?= $groupeId ?>" ><?= $groupeName ?></option>
                                    <?php endforeach ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2"><?= _('resp_ajout_conges_nb_jours_all_1') ?> <?= _('resp_ajout_conges_nb_jours_all_2') ?></th>
                            <th><?= _('resp_ajout_conges_calcul_prop') ?></th>
                            <th><?= _('divers_comment_maj_1') ?></th>
                        </tr>
                    <?php foreach ($tab_type_cong as $id_conges => $libelle) : ?>
                        <tr>
                            <td><strong><?= $libelle ?><strong></td>
                            <td><input class="form-control" type="text" name="tab_new_nb_conges_all[<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                            <td><?= _('resp_ajout_conges_oui') ?><input type="checkbox" name="tab_calcul_proportionnel[<?= $id_conges ?>]" value="TRUE" checked></td>
                            <td><input class="form-control" type="text" name="tab_new_comment_all[<?= $id_conges ?>]" size="30" maxlength="200" value=""></td>
                        </tr>
                    <?php endforeach ; ?>
                    </table>
                </div>
                <p><?= _('resp_ajout_conges_calcul_prop_arondi') ?> !</p>
                <input class="btn" type="submit" value="<?= _('form_valid_groupe') ?>">
            </fieldset>
            <input type="hidden" name="ajout_groupe" value="TRUE">
        </form>
    <?php endif ; ?>
    <h2>Ajout par utilisateur</h2>
    <form action="?onglet=ajout_conges" method="POST">
        <div class="table-responsive">
            <table class="table table-hover table-condensed table-striped">
                <thead>
                    <tr align="center">
                        <th><?= _('divers_nom_maj_1') ?></th>
                        <th><?= _('divers_prenom_maj_1') ?></th>
                        <th><?= _('divers_quotite_maj_1') ?></td>
                        <?php foreach ($tab_type_cong as $id_conges => $libelle) : ?>
                            <th><?= $libelle ?><br><i>(<?= _('divers_solde') ?>)</i></th>
                            <th><?= $libelle ?><br><?= _('resp_ajout_conges_nb_jours_ajout') ?></th>
                        <?php endforeach ?>
                        <?php foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) :?>
                            <th><?= $libelle ?><br><i>(<?= _('divers_solde') ?>)</i></th>
                            <th><?= $libelle ?><br><?= _('resp_ajout_conges_nb_jours_ajout') ?></th>
                        <?php endforeach ?>
                        <th><?= _('divers_comment_maj_1') ?><br></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($subalternesActifsResponsable as $login => $donneesSubalterne) :?>
                        <tr>
                            <td><?= $donneesSubalterne['nom'] ?></td>
                            <td><?= $donneesSubalterne['prenom'] ?></td>
                            <td><?= $donneesSubalterne['quotite'] ?>%</td>
                            <?php foreach($tab_type_cong as $id_conges => $libelle) : ?>
                                <td><?= $donneesSubalterne['conges'][$libelle]['nb_an'] ?> <i>(<?= $donneesSubalterne['conges'][$libelle]['solde'] ?>)</i></td>
                                <td align="center" class="histo"><input class="form-control" type="text" name="tab_champ_saisie[<?= $login ?>][<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                            <?php endforeach ?>
                            <?php foreach ($tab_type_conges_exceptionnels as $id_conges => $libelle) : ?>
                                <td><i>(<?= $donneesSubalterne['conges'][$libelle]['solde'] ?>)</i></td>
                                <td align="center" class="histo"><input class="form-control" type="text" name="tab_champ_saisie[<?= $login ?>][<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                            <?php endforeach ?>
                            <td align="center" class="histo"><input class="form-control" type="text" name="tab_commentaire_saisie[<?= $login ?>]" size="30" maxlength="200" value=""></td>
                        </tr>
                    <?php endforeach ?>
                    <tr align="center"><td class="histo" style="background-color: #CCC;" colspan="50"><i><?= _('resp_etat_users_titre_double_valid') ?></i></td></tr>
                    <?php foreach($subalternesActifsGrandResponsable as $login => $donneesSubalterne) : ?>
                        <tr>
                            <td><?= $tab_current_user['nom'] ?></td>
                            <td><?= $tab_current_user['prenom'] ?></td>
                            <td><?= $tab_current_user['quotite'] ?>%</td>
                            <?php foreach($tab_type_cong as $id_conges => $libelle) : ?>
                                <td><?= $tab_conges[$libelle]['nb_an'] ?> <i>(<?= $tab_conges[$libelle]['solde'] ?>)</i></td>
                                <td align="center" class="histo"><input type="text" name="tab_champ_saisie[<?= $current_login ?>][<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                            <?php endforeach ?>
                            <?php foreach($tab_type_conges_exceptionnels as $id_conges => $libelle) : ?>
                                <td><i>(<?= $tab_conges[$libelle]['solde'] ?>)</i></td>
                                <td align="center" class="histo"><input type="text" name="tab_champ_saisie[<?= $current_login ?>][<?= $id_conges ?>]" size="6" maxlength="6" value="0"></td>
                            <?php endforeach ?>
                            <td align="center" class="histo"><input type="text" name="tab_commentaire_saisie[<?= $current_login ?>]" size="30" maxlength="200" value=""></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="ajout_conges" value="TRUE">
        <input class="btn" type="submit" value="<?= _('form_submit') ?>">
    </form>
<?php endif ; ?>
