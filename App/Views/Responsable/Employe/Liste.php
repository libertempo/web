<?php
/*
 * $typeConges
 * $congesExceptionnels
 * $gestionHeure
 * $gestionEditionPapier
 * $subalternesActifsResponsable
 * $nombreColonnes
 * $subalternesActifsGrandResponsableNonDirect
 */
?>
<h1><?= _('resp_traite_user_etat_conges') ?></h1>
<table class="table table-hover table-responsive table-condensed table-striped">
    <thead>
        <tr>
            <th><?= _('divers_nom_maj') ?></th>
            <th><?= _('divers_prenom_maj') ?></th>
            <th><?= _('divers_quotite_maj_1') ?></th>
        <?php foreach ($typeConges as $libelle) : ?>
            <th><?= $libelle . ' / ' . _('divers_an_maj')?></th>
            <th><?= _('divers_solde_maj') . ' ' . $libelle ?></th>
        <?php endforeach ;?>
        <?php foreach ($congesExceptionnels as $libelle) :?>
            <th><?= _('divers_solde_maj') . ' ' . $libelle ?></th>
        <?php endforeach ;?>
        <?php if ($gestionHeure) : ?>
            <th><?= _('solde_heure') ?></th>
        <?php endif ;?>
        <th></th>
    <?php if ($gestionEditionPapier) : ?>
        <th></th>
    <?php endif ;?>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($subalternesActifsResponsable)) : ?>
        <tr align="center"><td class="histo" colspan="<?= $nombreColonnes ?>"><?= _('resp_etat_aucun_user') ?></td></tr>
    <?php else : ?>
        <?php foreach ($subalternesActifsResponsable as $loginEmploye => $dataEmploye) : ?>
            <?php $i = true; ?>
            <tr class="<?= ($i ? 'i' : 'p') ?>">
                <td><?= $dataEmploye['nom'] ?></td>
                <td><?= $dataEmploye['prenom'] ?></td>
                <td><?= $dataEmploye['quotite'] . '%' ?></td>
            <?php foreach ($typeConges as $libelle) :?>
                <?php if (isset($dataEmploye['conges'][$libelle])) : ?>
                <td><?= $dataEmploye['conges'][$libelle]['nb_an'] ?></td>
                <td><?= $dataEmploye['conges'][$libelle]['solde'] ?></td>
                <?php else : ?>
                    <td>0</td>
                    <td>0</td>
                <?php endif ; ?>
            <?php endforeach ;?>
            <?php foreach ($congesExceptionnels as $libelle) :?>
                <td><?= $dataEmploye['conges'][$libelle]['solde'] ?></td>
            <?php endforeach ;?>
            <?php if ($gestionHeure) : ?>
                <?php $soldeHeure = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($loginEmploye)['u_heure_solde']; ?>
                <td><?= \App\Helpers\Formatter::timestamp2Duree($soldeHeure) ?></td>
            <?php endif ;?>
                <td><a class="action show" href="resp_index.php?onglet=traite_user&amp;user_login=<?= $loginEmploye ?>" title="<?= _('resp_etat_users_afficher') ?>"><i class="fa fa-eye"></i></a></td>
                <?php if ($gestionEditionPapier) : ?>
                    <td><a class="action edit" href="../edition/edit_user.php?user_login=<?= $loginEmploye ?>" target="_blank" title="<?= _('resp_etat_users_imprim') ?>"><i class="fa fa-file-text"></i></a></td>
                <?php endif ;?>
            </tr>
            <?php $i = !$i; ?>
        <?php endforeach ;?>
    <?php endif ;?>
    <?php if (!empty($subalternesActifsGrandResponsableNonDirect)) :?>
        <tr align="center">
            <td class="histo" style="background-color: #CCC;" colspan="<?= $nombreColonnes ?>"><i><?= _('resp_etat_users_titre_double_valid') ?></i></td>
        </tr>
    <?php endif ?>
    <?php foreach ($subalternesActifsGrandResponsableNonDirect as $loginEmploye => $dataEmploye) : ?>
        <?php $i = true; ?>
        <tr class="<?= ($i ? 'i' : 'p') ?>">
            <td><?= $dataEmploye['nom'] ?></td>
            <td><?= $dataEmploye['prenom'] ?></td>
            <td><?= $dataEmploye['quotite'] . '%' ?></td>
        <?php foreach ($typeConges as $libelle) :?>
            <?php if (isset($dataEmploye['conges'][$libelle])) : ?>
            <td><?= $dataEmploye['conges'][$libelle]['nb_an'] ?></td>
            <td><?= $dataEmploye['conges'][$libelle]['solde'] ?></td>
            <?php else : ?>
                <td>0</td>
                <td>0</td>
            <?php endif ; ?>
        <?php endforeach ;?>
        <?php foreach ($congesExceptionnels as $libelle) :?>
            <td><?= $dataEmploye['conges'][$libelle]['solde'] ?></td>
        <?php endforeach ;?>
        <?php if ($gestionHeure) : ?>
            <?php $soldeHeure = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($loginEmploye)['u_heure_solde']; ?>
            <td><?= \App\Helpers\Formatter::timestamp2Duree($soldeHeure) ?></td>
        <?php endif ;?>
            <td><a class="action show" href="resp_index.php?onglet=traite_user&amp;user_login=<?= $loginEmploye ?>" title="<?= _('resp_etat_users_afficher') ?>"><i class="fa fa-eye"></i></a></td>
            <?php if ($gestionEditionPapier) : ?>
                <td><a class="action edit" href="../edition/edit_user.php?user_login=<?= $loginEmploye ?>" target="_blank" title="<?= _('resp_etat_users_imprim') ?>"><i class="fa fa-file-text"></i></a></td>
            <?php endif ;?>
        </tr>
        <?php $i = !$i; ?>
    <?php endforeach ; ?>
    </tbody>
</table>
