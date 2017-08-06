<?php
namespace App\Libraries\Calendrier\Evenements;

/**
 * Evenements d'événements des heures
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 */
abstract class AHeure
{
    public function __construct(\includes\SQL $db) {
        $this->db = $db;
    }

    /**
    * @var \includes\SQL Objet de DB
    */
    protected $db;

    /**
     * Retourne la liste des heures additionnelles relative à la période demandée
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les heures
     * @param bool $canVoirEnTransit Si l'utilisateur a la possiblité de voir les événements non encore validés
     *
     * @return array
     */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
    {
        $heures = [];
        $canVoirEnTransit = (bool) $canVoirEnTransit;
        foreach ($this->getListeSQL($this->getListeId($dateDebut, $dateFin, $utilisateursATrouver, $canVoirEnTransit)) as $heure) {
            $date = date('Y-m-d', $heure['debut']);
            $heures[$date][] = [
                'employe' => $heure['login'],
                'statut' => $heure['statut']
            ];
        }

        return $heures;
    }

    /**
     * Retourne une liste d'heures en fonction de ses id
     *
     * @param array $listeId
     *
     * @return array
     */
    abstract protected function getListeSQL(array $listeId);

    /**
     * Retourne la liste des id d'heures satisfaisant aux critères
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les heures
     * @param bool $canVoirEnTransit Si l'utilisateur a la possiblité de voir les événements non encore validés
     *
     * @return array
     */
    abstract protected function getListeId(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit);
}
