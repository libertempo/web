<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements\Heure;

use App\Libraries\Calendrier\Evenements\Heure\Additionnelle as _Additionnelle;

/**
 * Classe de test des collections d'heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 */
class Additionnelle extends \Tests\Units\App\Libraries\Calendrier\Evenements\AHeure
{
    protected function getTestedClass()
    {
        return _Additionnelle::class;
    }
}
