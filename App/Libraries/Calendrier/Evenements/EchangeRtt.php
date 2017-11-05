<?php
namespace App\Libraries\Calendrier\Evenements;

/**
 * Evenements des échanges de RTT
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Evenements
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class EchangeRtt
{
    public function __construct(\includes\SQL $db) {
        $this->db = $db;
    }

    /**
    * @var \includes\SQL Objet de DB
    */
    private $db;

    /**
     * Retourne la liste des échanges de RTT relative à la période demandée
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les échanges
     *
     * @return array
     */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver)
    {
        $echanges = [];
        foreach ($this->getListeSQL($dateDebut, $dateFin, $utilisateursATrouver) as $jour) {
            $echanges[$jour['e_date_jour']][] = [
                'employe' => $jour['e_login'],
                'demiJournee' => $this->getTypePeriode($jour['e_absence']),
            ];
        }

        return $echanges;
    }

    private function getTypePeriode($periodeAbsence)
    {
        if ('J' === $periodeAbsence) {
            return '*';
        } elseif ('A' == $periodeAbsence) {
            return 'pm';
        } elseif ('M' == $periodeAbsence) {
            return 'am';
        }
    }

    /*
     * SQL
     */

    /**
     * Retourne la liste des congés du stockage satisfaisant aux critères
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     *
     * @return array
     */
    private function getListeSQL(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver)
    {
        if (empty($utilisateursATrouver)) {
            return [];
        }

        $req = 'SELECT *
                FROM conges_echange_rtt CP
                WHERE e_date_jour >= "' . $dateDebut->format('Y-m-d') . '"
                    AND e_date_jour <= "' . $dateFin->format('Y-m-d') . '"
                    AND e_absence <> "N"
                    AND e_login IN ("' . implode('","', $utilisateursATrouver) . '")';

        return $this->db->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
