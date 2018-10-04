<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe;

/**
 * Classe de test du repository de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class GroupeRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    final protected function getStorageContent() : array
    {
        return [
            'g_gid' => 42,
            'g_groupename' => 'name',
            'g_comment' => 'this is a storage comment',
            'g_double_valid' => 'Y'
        ];
    }

    protected function getConsumerContent() : array
    {
        return [
            'name' => 'Spartan',
            'comment' => 'Mario',
            'double_validation' => 'N',
        ];
    }
}
