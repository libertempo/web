<?php declare(strict_types = 1);
/*
 * $listeMois
 * $joursFeries
 * $annee
 */
?>
<h1> <?= $title ?></h1>
<div class="pager">
    <div class="calendar-nav">
        <ul>
            <li><a href="<?= $prev_link ?>" class="calendar-prev"><i class="fa fa-chevron-left"></i><span>année précédente</span></a></li>
            &nbsp;<li class="current-year"><?= $annee ?></li>
            &nbsp;<li><a href="<?= $next_link ?>" class="calendar-next"><i class="fa fa-chevron-right"></i><span>année suivante</span></a></li>
        </ul>
    </div>
</div>
<?php if (null !== $commitSuccess) : ?>
    <div class="alert <?= $commitSuccess ? 'alert-success' : 'alert-danger' ?>"><?= $commitSuccess ? _('form_modif_ok') : _('form_modif_not_ok') ?>
    </div>
<?php endif; ?>
<div class="wrapper">
    <form action="<?= $PHP_SELF ?>?year_calendrier_saisie=<?= $annee ?>" method="POST">
        <div class="calendar">
        <?php foreach ($listeMois as $ligneMois) : ?>
            <div class="row">
                <?php foreach ($ligneMois as $mois) : ?>
                    <?php require VIEW_PATH . 'JourFerie/Mois.php'; ?>
                <?php endforeach ; ?>
            </div>
        <?php endforeach ;?>
        </div>
        <div class="actions">
            <input type="hidden" name="choix_action" value="commit">
            <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
        </div>
    </form>
</div>
