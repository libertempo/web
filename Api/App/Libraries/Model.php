<?php
namespace Api\App\Libraries;

/**
 * Domain Model
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * @see https://en.wikipedia.org/wiki/Domain_model
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
class Model
{
    /**
     * @var int $id Identifiant unique de l'élément dans la liste
     */
    protected $id;

    /**
     * @var array $data Données de l'objet
     */
    protected $data;

    public function __construct($id, array $data)
    {
        $this->id = (int) $id;
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    // populate pour le set massif (private) avec un retour d'erreur collectif, sinon dans dataUpdated
}
