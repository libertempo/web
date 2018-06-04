<?php declare(strict_types = 1);
namespace LibertAPI\Planning;

use LibertAPI\Tools\Exceptions\MissingArgumentException;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \LibertAPI\Tests\Units\Planning\PlanningEntite
 *
 * Ne devrait être contacté que par le PlanningRepository
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
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }
        $this->setName($data['name']);
        $this->setStatus($data['status']);

        $erreurs = $this->getErreurs();
        if (!empty($erreurs)) {
            throw new \DomainException(json_encode($erreurs));
        }
    }

    /**
     * Vérifie que les données passées possèdent bien tous les champs requis
     *
     * @param array $data
     *
     * @return bool
     */
    private function hasAllRequired(array $data)
    {
        foreach ($this->getListRequired() as $value) {
            if (!isset($data[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne la liste des champs requis
     *
     * @return array
     */
    private function getListRequired()
    {
        return ['name', 'status'];
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
