<?php
namespace App\Libraries\Calendrier\Collection\Heure;

/**
 * Collection d'événements des heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Facade
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Additionnelle
{
    public function __construct(\includes\SQL $db) {
        $this->db = $db;
    }

    /**
    * @var \includes\SQL Objet de DB
    */
    private $db;

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


    /*
     * SQL
     */


    /**
     * Retourne la liste des id d'heures additionnelles satisfaisant aux critères
     *
     * @return array
     */
    private function getListeId(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
    {
        $ids = [];
        $etats[] = \App\Models\AHeure::STATUT_VALIDATION_FINALE;
        if ($canVoirEnTransit) {
            $etats = array_merge($etats, [
                \App\Models\AHeure::STATUT_DEMANDE,
                \App\Models\AHeure::STATUT_PREMIERE_VALIDATION
            ]);
        }
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle
                WHERE debut >= "' . $dateDebut->getTimestamp() . '"
                    AND debut <= "' . $dateFin->getTimestamp() . '"
                    AND duree > 0
                    AND login IN ("' . implode('","', $utilisateursATrouver) . '")
                    AND statut IN ("' . implode('","', $etats) . '")';
        $res = $this->db->query($req);
        foreach ($res->fetch_all(\MYSQLI_ASSOC) as $data) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne une liste d'heures additionnelles en fonction de ses id
     *
     * @param array $listeId
     *
     * @return array
     */
    private function getListeSQL(array $listeId)
    {
        if (empty($listeId)) {
            return [];
        }

        $listeId = array_map('intval', $listeId);
        $req = 'SELECT *
                FROM heure_additionnelle HA
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';

        return $this->db->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
