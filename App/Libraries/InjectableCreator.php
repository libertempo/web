<?php
namespace App\Libraries;

/**
 * Gestionnaire minimal d'injection de dépendances
 *
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see Tests\Units\App\Libraries\InjectableCreator
 */
class InjectableCreator
{

    public function __construct(\includes\SQL $db)
    {
        $this->db = $db;
    }

    /**
     * @var \includes\SQL
     */
    private $db;

    /**
     * Retourne un injectable bien construit (avec ses propres dépendances)
     *
     * @param string $classname
     *
     * @return object
     * @throws \LogicException si la construction est interdite
     */
    public function get($classname)
    {
        if (!class_exists($classname) || 'App\\' !== substr($classname, 0, 4)) {
            throw new \LogicException('Class « ' . $classname . ' » loading is forbidden');
        }

        switch ($classname) {
            case 'App\Libraries\Calendrier\Collection\Weekend':
                // TODO: à supprimer quand le mécanisme de l'injection sera opérationnel
                $date = new \DateTimeImmutable('2015-02-13');

                return new $classname($this->db, $date, $date);

            default:
                throw new \LogicException('Unknown « ' . $classname . ' »');
        }
    }
}
