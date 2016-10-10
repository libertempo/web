<?php
namespace App\Libraries\Calendrier\Collection\Heure;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements des heures de repos
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
class Repos extends \App\Libraries\Calendrier\ACollection
{
    /**
     * @var array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     */
    private $utilisateursATrouver;

    /**
     * {@inheritDoc}
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
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
            /* c'est pas le login qu'il nous faut, mais le nom de l'employé (pareil pour congés) */
            $userName = ($longueurMax < mb_strlen($heure['login']))
                ? substr($heure['u_login'], 0, $longueurMax) . ['...']
                : $heure['u_login'];
            $name = $userName . ' - Heure de repos';
            $dateDebut = new \DateTime();
            $dateDebut->setTimestamp($heure['debut']);
            $dateFin = new \DateTime();
            $dateFin->setTimestamp($heure['fin']);
            $statut = ' statut_' . $heure['statut'];

            /* afficher le format long que si l'heure est != 00:00 || 23:59 (?) */
            $title = 'Heure(s) de repos de ' . $heure['u_login'] . ' du ' . $dateDebut->format('d/m/Y à H\:i') . ' au ' . $dateFin->format('d/m/Y à H\:i');
            $uid = uniqid('ferie');
            $heures[] = new Evenement\Commun($uid, $dateDebut, $dateFin, $name, $title, $class);
        }

        return $heures;
    }


    /*
     * SQL
     */


    /**
     * Retourne la liste des id d'heures de repos satisfaisant aux critères
     *
     * @return array
     */
    private function getListeId()
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE debut >= "' . $this->dateDebut->getTimestamp() . '"
                    AND debut <= "' . $this->dateFin->getTimestamp() . '"
                    AND duree > 0
                    AND login IN ("' . implode('","', $this->utilisateursATrouver) . '")
                    AND statut = ' . \App\Models\AHeure::STATUT_VALIDATION_FINALE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne une liste d'heures de repos en fonction de ses id
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
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos HR
                    INNER JOIN conges_users CU ON (HR.login = CU.u_login)
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
