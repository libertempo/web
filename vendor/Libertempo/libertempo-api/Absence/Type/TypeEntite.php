<?php declare(strict_types = 1);
namespace LibertAPI\Absence\Type;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \LibertAPI\Tests\Units\Absence\Type\TypeEntite
 */
class TypeEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    /**
     * Retourne la donnée la plus à jour du champ type
     */
    public function getType() : string
    {
        return $this->getFreshData('type');
    }

    /**
     * Retourne la donnée la plus à jour du champ libelle
     *
     * @TODO : changer le schema bd, le transcodage ne devrait pas être nécessaire
     */
    public function getLibelle() : string
    {
        if ('utf-8' !== strtolower(mb_detect_encoding($this->getFreshData('libelle')))) {
            return utf8_encode($this->getFreshData('libelle'));
        }

        return $this->getFreshData('libelle');

    }

    /**
     * Retourne la donnée la plus à jour du champ libelle court
     */
    public function getLibelleCourt() : string
    {
        return $this->getFreshData('libelleCourt');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        $this->setType($data['type']);
        $this->setLibelle($data['libelle']);
        $this->setLibelleCourt($data['libelleCourt']);

        $erreurs = $this->getErreurs();
        if (!empty($erreurs)) {
            throw new \DomainException(json_encode($erreurs));
        }
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « type »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @todo
     */
    private function setType(string $type)
    {
        // domaine ?
        if (empty($type)) {
            $this->setErreur('type', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['type'] = $type;
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « libelle »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @todo
     */
    private function setLibelle(string $var)
    {
        // domaine ?
        if (empty($var)) {
            $this->setErreur('libelle', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['libelle'] = $var;
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « libelleCourt »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @todo
     */
    private function setLibelleCourt(string $var)
    {
        // domaine ?
        if (empty($var)) {
            $this->setErreur('libelleCourt', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['libelleCourt'] = $var;
    }

}
