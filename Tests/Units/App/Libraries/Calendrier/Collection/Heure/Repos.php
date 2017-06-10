<?php
namespace Tests\Units\App\Libraries\Calendrier\Collection\Heure;

use App\Libraries\Calendrier\Collection\Heure\Repos as _Repos;

/**
 * Classe de test des collections d'heures de repos
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 */
class Repos extends \Tests\Units\App\Libraries\Calendrier\Collection\AHeure
{
    protected function getTestedClass()
    {
        return _Repos::class;
    }
}
