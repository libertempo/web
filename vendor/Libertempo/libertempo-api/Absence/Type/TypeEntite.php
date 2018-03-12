<?php
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
     *
     * @return string
     */
    public function getType()
    {
        return $this->getFreshData('type');
    }

    /**
     * Retourne la donnée la plus à jour du champ libelle
     *
     * @return string
     * @TODO : changer le schema bd, le transcodage ne devrait pas être nécessaire
     */
    public function getLibelle()
    {
        if ('utf-8' !== strtolower(mb_detect_encoding($this->getFreshData('libelle')))) {
            return utf8_encode($this->getFreshData('libelle'));
        }

        return $this->getFreshData('libelle');

    }

    /**
     * Retourne la donnée la plus à jour du champ libelle court
     *
     * @return string
     */
    public function getLibelleCourt()
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
     * @param string $type
     * @todo
     */
    private function setType($type)
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
     * @param string $var
     * @todo
     */
    private function setLibelle($var)
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
     * @param string $var
     * @todo
     */
    private function setLibelleCourt($var)
    {
        // domaine ?
        if (empty($var)) {
            $this->setErreur('libelleCourt', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['libelleCourt'] = $var;
    }

}
