<?php
namespace Api\App\Components\Planning\Creneau;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Components\Planning\Creneau\Model
 *
 * Ne devrait être contacté que par le Planning\Creneau\Repository
 * Ne devrait contacter personne
 */
class Model extends \Api\App\Libraries\AModel
{
    public function getPlanningId()
    {
        if (isset($this->dataUpdated['planningId'])) {
            return $this->dataUpdated['planningId'];
        }

        return (int) $this->data['planningId'];
    }

    public function getJourId()
    {
        if (isset($this->dataUpdated['jourId'])) {
            return $this->dataUpdated['jourId'];
        }

        return (int) $this->data['jourId'];
    }

    public function getTypeSemaine()
    {
        if (isset($this->dataUpdated['typeSemaine'])) {
            return $this->dataUpdated['typeSemaine'];
        }

        return (int) $this->data['typeSemaine'];
    }

    public function getTypePeriode()
    {
        if (isset($this->dataUpdated['typePeriode'])) {
            return $this->dataUpdated['typePeriode'];
        }

        return (int) $this->data['typePeriode'];
    }

    public function getDebut()
    {
        if (isset($this->dataUpdated['debut'])) {
            return $this->dataUpdated['debut'];
        }

        return (int) $this->data['debut'];
    }

    public function getFin()
    {
        if (isset($this->dataUpdated['fin'])) {
            return $this->dataUpdated['fin'];
        }

        return (int) $this->data['fin'];
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        $this->setPlanningId($data['planningId']);
        $this->setJourId($data['jourId']);
        $this->setTypeSemaine($data['typeSemaine']);
        $this->setTypePeriode($data['typePeriode']);
        $this->setDebut($data['debut']);
        $this->setFin($data['fin']);

        $erreurs = $this->getErreurs();
        if (!empty($erreurs)) {
            throw new \DomainException(json_encode($erreurs));
        }
    }

    private function setPlanningId($planningId)
    {
        // domaine de planning id ?
        if (empty($planningId)) {
            $this->setErreur('planningId', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['planningId'] = $planningId;
    }

    private function setJourId($jourId)
    {
        // domaine de jour id ?
        if (empty($jourId)) {
            $this->setErreur('jourId', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['jourId'] = $jourId;
    }

    private function setTypeSemaine($typeSemaine)
    {
        // domaine de type semaine ?
        if (false) {
            $this->setErreur('typeSemaine', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['typeSemaine'] = $typeSemaine;
    }

    private function setTypePeriode($typePeriode)
    {
        // domaine de type période ?
        if (empty($typePeriode)) {
            $this->setErreur('typePeriode', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['typePeriode'] = $typePeriode;
    }

    private function setDebut($debut)
    {
        // domaine de debut ?
        if (empty($debut)) {
            $this->setErreur('debut', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['debut'] = $debut;
    }

    private function setFin($fin)
    {
        // domaine de fin ?
        if (empty($fin)) {
            $this->setErreur('fin', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['fin'] = $fin;
    }
}
