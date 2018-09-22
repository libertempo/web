<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Planning;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    final protected function getStorageContent() : array
    {
        return [
            'planning_id' => 42,
            'name' => 'name',
            'status' => 59,
        ];
    }

    protected function getConsumerContent() : array
    {
        return [
            'name' => 'Pomme',
            'status' => 'green',
        ];
    }
}
