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
     * @var bool Si l'utilisateur a la possiblité de voir les événements non encore validés
     */
    private $canVoirEnTransit;

    /**
     * {@inheritDoc}
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les heures additionnelles
     */
    public function __construct(
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        array $utilisateursATrouver,
        $canVoirEnTransit
    ) {
        parent::__construct($dateDebut, $dateFin);
        $this->utilisateursATrouver = $utilisateursATrouver;
        $this->canVoirEnTransit = (bool) $canVoirEnTransit;
    }

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $heures = [];
        foreach ($this->getListeSQL($this->getListeId()) as $heure) {
            $class = 'heure heure_' . $heure['statut'];
            $nomComplet = \App\ProtoControllers\Utilisateur::getNomComplet($heure['u_prenom'],  $heure['u_nom'], true);
            $name = $nomComplet . ' - Heure(s) additionnelles';
            if (\App\Models\AHeure::STATUT_VALIDATION_FINALE !== $heure['statut']) {
                $name = '[En demande]  ' . $name;
            }
            $dateDebut = new \DateTime();
            $dateDebut->setTimestamp($heure['debut']);
            $dateFin = new \DateTime();
            $dateFin->setTimestamp($heure['fin']);
            $statut = ' statut_' . $heure['statut'];

            $title = 'Heure(s) additionnelles de ' . $nomComplet . ' le ' . $dateDebut->format('d/m/Y') . ' de ' . $dateDebut->format('H\:i') . ' à ' . $dateFin->format('H\:i');
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
        $etats[] = \App\Models\AHeure::STATUT_VALIDATION_FINALE;
        if ($this->canVoirEnTransit) {
            $etats = array_merge($etats, [
                \App\Models\AHeure::STATUT_DEMANDE,
                \App\Models\AHeure::STATUT_PREMIERE_VALIDATION
            ]);
        }
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle
                WHERE debut >= "' . $this->dateDebut->getTimestamp() . '"
                    AND debut <= "' . $this->dateFin->getTimestamp() . '"
                    AND duree > 0
                    AND login IN ("' . implode('","', $this->utilisateursATrouver) . '")
                    AND statut IN ("' . implode('","', $etats) . '")';
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
