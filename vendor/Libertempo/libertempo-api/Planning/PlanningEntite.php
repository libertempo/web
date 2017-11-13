<?php
namespace LibertAPI\Planning;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \LibertAPI\Tests\Units\Planning\Entite
 *
 * Ne devrait être contacté que par le Planning\Repository
 * Ne devrait contacter personne
 */
class PlanningEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    /**
     * Retourne la donnée la plus à jour du champ name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFreshData('name');
    }

    /**
     * Retourne la donnée la plus à jour du champ status
     *
     * @return int
     */
    public function getStatus()
    {
        return (int) $this->getFreshData('status');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        $this->setName($data['name']);
        $this->setStatus($data['status']);

        $erreurs = $this->getErreurs();
        if (!empty($erreurs)) {
            throw new \DomainException(json_encode($erreurs));
        }
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « name »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @param string $name
     */
    private function setName($name)
    {
        // domaine de name ?
        if (empty($name)) {
            $this->setErreur('name', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['name'] = $name;
    }


    /**
     * Tente l'insertion d'une donnée en tant que champ « status »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @param string $status
     */
    private function setStatus($status)
    {
        // domaine de status ?
        if (empty($status)) {
            $this->setErreur('status', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['status'] = $status;
    }
}
