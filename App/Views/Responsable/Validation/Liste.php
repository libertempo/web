<?php
/*
 * $formResponsable
 * $formGrandResponsable
 * $formDelegation
 * $errorsLst
 * $notice
 * $titre
 */
?>
<h1><?= $titre ?></h1>
<?php if (!empty($errorsLst)) : ?>
    <div class="alert alert-danger"><?= _('erreur_recommencer') ?> :<ul>
    <?php foreach ($errorsLst as $error) : ?>
        <?php if (is_array($value)) : ?>
            <?php $error = implode(' / ', $error); ?>
        <?php endif ; ?>
        <li><?= $error ?></li>
    <?php endforeach; ?>
    </ul></div>
<?php endif; ?>
<?php if (!empty($notice)) : ?>
    <div class="alert alert-info"><?= $notice ?>.</div>
<?php endif; ?>

<form action="" method="post" class="form-group">
    <table class="table table-hover table-responsive table-condensed table-striped">
        <thead>
            <tr>
                <th><?= _('divers_nom_maj_1') ?><br><?= _('divers_prenom_maj_1') ?></th>
                <th><?= _('jour') ?></th>
                <th><?= _('divers_debut_maj_1') ?></th>
                <th><?= _('divers_fin_maj_1') ?></th>
                <th><?= _('divers_type_maj_1') ?></th>
                <th><?= _('duree') ?></th>
                <th><?= _('divers_solde') ?></th>
                <th><?= _('divers_comment_maj_1') ?></th>
                <th><?= _('divers_accepter_maj_1') ?></th>
                <th><?= _('divers_refuser_maj_1') ?></th>
                <th><?= _('resp_traite_demandes_attente') ?></th>
                <th></th>
                <th><?= _('resp_traite_demandes_motif_refus') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($formResponsable) && empty($formGrandResponsable) && empty($formDelegation)) : ?>
            <tr><td colspan="13"><center><?= _('aucune_demande') ?></center></td></tr>
        <?php else : ?>
            <?php if (!empty($formResponsable)) : ?>
                <?= $formResponsable ?>
            <?php endif ; ?>
            <?php if (!empty($formGrandResponsable)) : ?>
                <tr align="center"><td class="histo" style="background-color: #CCC;" colspan="13"><i>'._('resp_etat_users_titre_double_valid').'</i></td></tr>
                <?= $formGrandResponsable ?>
            <?php endif ; ?>
            <?php if (!empty($formDelegation)) : ?>
                <tr align="center"><td class="histo" style="background-color: #CCC;" colspan="13"><i>'._('traitement_demande_par_delegation').'</i></td></tr>
                <?= $formDelegation ?>
            <?php endif ; ?>
        <?php endif ; ?>
        </tbody>
    </table>
    <?php if (!empty($formResponsable) || !empty($formGrandResponsable) || !empty($formDelegation)) : ?>
        <div class="form-group"><input type="submit" class="btn btn-success" value="<?= _('form_submit') ?>" /></div>
    <?php endif ; ?>
</form>
