<?php
define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

$tz = new DateTimeZone('Europe/Paris');
setlocale(LC_ALL, 'fr_FR', 'fr_FR.utf8');

$factory = new \CalendR\Calendar;
$month = $factory->getMonth(2016, 9);
echo strftime("%A %e %B %Y", mktime(0, 0, 0, 12, 22, 1978));
?>
<h1><?= $month->format('F Y') ?></h1>
<table>

    <?php // Iterate over your month and get weeks ?>
    <?php foreach ($month as $week): ?>
        <tr><td>[<?= $week ?>]</td>
            <?php // Iterate over your month and get days ?>
            <?php foreach ($week as $day): ?>
                <?php //Check days that are out of your month ?>
                <td<?php if (!$month->includes($day)) echo ' class="out-of-month"' ?>>
                    <?php echo $day->getBegin()->format('D') . ' - ' . $day->getBegin()->format('j') .  ' | ';
                    $last = $day->getBegin()->getTimestamp() ?>
                </td>
            <?php endforeach ?>
        </tr>
    <?php endforeach ?>
</table>

<?= date('d-m-Y', $last);
