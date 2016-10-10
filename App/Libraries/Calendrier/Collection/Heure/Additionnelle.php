<?php
namespace App\Libraries\Calendrier\Collection\Heure;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements des heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit contacter que Evenement\Commun
 * Ne doit être contacté que par \App\Libraries\Calendrier\BusinessCollection
 *
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Additionnelle extends \App\Libraries\Calendrier\ACollection
{
    /**
     * @var array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les heures additionnelles
     */
    private $utilisateursATrouver;

    /**
     * {@inheritDoc}
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les heures additionnelles
     */
    public function __construct(\DateTime $dateDebut, \DateTime $dateFin, array $utilisateursATrouver)
    {
        parent::__construct($dateDebut, $dateFin);
        $this->utilisateursATrouver = $utilisateursATrouver;
    }

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $heures = [];
        $longueurMax = 10;
        $class = 'heure';
        foreach ($this->getListeSQL($this->getListeId()) as $heure) {
            $identite = $heure['u_prenom'] . ' ' . $heure['u_nom'];
            $userName = ($longueurMax < mb_strlen($identite))
                ? substr($identite, 0, $longueurMax) . ['...']
                : $identite;
            $name = $userName . ' - Heure(s) additionnelles';
            $dateDebut = new \DateTime();
            $dateDebut->setTimestamp($heure['debut']);
            $dateFin = new \DateTime();
            $dateFin->setTimestamp($heure['fin']);
            $statut = ' statut_' . $heure['statut'];

            $title = 'Heure(s) additionnelles de ' . $heure['u_login'] . ' le ' . $dateDebut->format('d/m/Y') . ' de ' . $dateDebut->format('H\:i') . ' à ' . $dateFin->format('H\:i');
            $uid = uniqid('additionnelle');
            $heures[] = new Evenement\Commun($uid, $dateDebut, $dateFin, $name, $title, $class);
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
    private function getListeId()
    {
        $ids = [];
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle
                WHERE debut >= "' . $this->dateDebut->getTimestamp() . '"
                    AND debut <= "' . $this->dateFin->getTimestamp() . '"
                    AND duree > 0
                    AND login IN ("' . implode('","', $this->utilisateursATrouver) . '")
                    AND statut = ' . \App\Models\AHeure::STATUT_VALIDATION_FINALE;
        $sql = \includes\SQL::singleton();
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
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
                    INNER JOIN conges_users CU ON (HA.login = CU.u_login)
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';
        $sql = \includes\SQL::singleton();

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
