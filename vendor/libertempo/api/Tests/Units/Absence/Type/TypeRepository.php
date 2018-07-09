<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Absence\Type;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class TypeRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    protected function getStorageContent() : array
    {
        return [
            'ta_id' => 38,
            'ta_type' => 81,
            'ta_libelle' => 'libelle',
            'ta_short_libelle' => 'li',
        ];
    }
}
