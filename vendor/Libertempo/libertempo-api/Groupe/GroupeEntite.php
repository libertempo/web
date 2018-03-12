<?php
namespace LibertAPI\Groupe;

use LibertAPI\Tools\Exceptions\MissingArgumentException;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 * @see \LibertAPI\Tests\Units\Groupe\GroupeEntite
 *
 * Ne devrait être contacté que par le GroupeRepository
 * Ne devrait contacter personne
 */
class GroupeEntite extends \LibertAPI\Tools\Libraries\AEntite
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
     * Retourne la donnée la plus à jour du champ comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getFreshData('comment');
    }

    /**
     * Retourne la donnée la plus à jour du champ de double validation
     *
     * @return bool
     */
    public function isDoubleValidated()
    {
        return (bool) $this->getFreshData('double_validation');
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
        $this->setComment($data['comment']);
        $this->withDoubleValidation($data['double_validation']);

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
        return ['name', 'comment', 'double_validation'];
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
     * Tente l'insertion d'une donnée en tant que champ « comment »
     * Il ne s'agit que d'une aide pour l'utilisateur
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @param string $comment
     */
    private function setComment($comment)
    {
        if (empty($comment)) {
            $this->setErreur('comment', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['comment'] = $comment;
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « comment »
     * Il ne s'agit que d'une aide pour l'utilisateur
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @param string $doubleValidation enum (Y, N)
     */
    private function withDoubleValidation($doubleValidation)
    {
        if (empty($doubleValidation)) {
            $this->setErreur('double_validation', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['double_validation'] = 'Y' === $doubleValidation;
    }
}
