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
abstract class AModel
{
    /**
     * @var int $id Identifiant unique de l'élément dans la liste
     */
    protected $id;

    /**
     * @var array $data Données de l'objet
     */
    protected $data;

    public function __construct(array $data, $id = -1)
    {
        if (-1 !== $id) {
            $this->id = (int) $id;
        }
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Insère massivement des nouvelles données dans le modèle
     *
     * @param array $data Données à insérer / mettre à jour
     *
     * @throws \DomainException Si une ou plusieurs données ne sont pas dans les bons domaines de définition
     */
    abstract public function populate(array $data);

    // populate pour le set massif (private) avec un retour d'erreur collectif, sinon dans dataUpdated
}
