<?php
namespace App\Libraries\Calendrier\Evenements;

/**
 * Evenements de jours de congés
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Evenements
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Conge
{
    public function __construct(\includes\SQL $db) {
        $this->db = $db;
    }

    /**
    * @var \includes\SQL Objet de DB
    */
    private $db;

    /**
     * Retourne la liste des congés relative à la période demandée
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     * @param bool $canVoirEnTransit Si l'utilisateur a la possiblité de voir les événements non encore validés
     *
     * @return array
     */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
    {
        $conges = [];
        $canVoirEnTransit = (bool) $canVoirEnTransit;
        foreach ($this->getListeSQL($dateDebut, $dateFin, $utilisateursATrouver, $canVoirEnTransit) as $jour) {
            /*
             * Par construction, les congés n'ont que le début et la fin.
             * On cherche donc les intersticiels...
             */
            foreach ($this->getListeJoursIntersticiels($jour['p_date_deb'], $jour['p_date_fin']) as $jourIntersticiel) {
                $conges[$jourIntersticiel][] = [
                    'employe' => $jour['p_login'],
                    'demiJournee' => '*',
                    'statut' => $jour['p_etat'],
                ];
            }

            /* ... Puis on ajoute les bords */
            $conges[$jour['p_date_deb']][] = [
                'employe' => $jour['p_login'],
                'demiJournee' => $this->getDemiJourneeDebut($jour['p_date_deb'], $jour['p_demi_jour_deb'], $jour['p_date_fin'], $jour['p_demi_jour_fin']),
                'statut' => $jour['p_etat'],
            ];
            if ($jour['p_date_fin'] !== $jour['p_date_deb']) {
                $conges[$jour['p_date_fin']][] = [
                    'employe' => $jour['p_login'],
                    'demiJournee' => $this->getDemiJourneeFin($jour['p_date_deb'], $jour['p_demi_jour_deb'], $jour['p_date_fin'], $jour['p_demi_jour_fin']),
                    'statut' => $jour['p_etat'],
                ];
            }
        }
        ksort($conges);

        return $conges;
    }

    private function getListeJoursIntersticiels($debut, $fin)
    {
        if ($debut === $fin || $debut > $fin) {
            return [];
        }
        $debutComptage = strtotime('+1 day', strtotime($debut));
        $finComptage = strtotime('-1 day', strtotime($fin));
        $listeJours = [];
        while ($debutComptage <= $finComptage) {
            $listeJours[] = date('Y-m-d', $debutComptage);
            $debutComptage = strtotime('+1 day', $debutComptage);
        }

        return $listeJours;
    }

    private function getDemiJourneeDebut($debut, $demiJourneeDebut, $fin, $demiJourneeFin)
    {
        if ($debut < $fin) {
            return 'am' === $demiJourneeDebut
            ? '*'
            : $demiJourneeDebut;
        }

        return $demiJourneeDebut === $demiJourneeFin
            ? $demiJourneeDebut
            : '*';
    }

    private function getDemiJourneeFin($debut, $demiJourneeDebut, $fin, $demiJourneeFin)
    {
        if ($debut < $fin) {
            return 'pm' === $demiJourneeFin
            ? '*'
            : $demiJourneeFin;
        }

        return $demiJourneeDebut === $demiJourneeFin
            ? $demiJourneeDebut
            : '*';
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
     * @param bool $canVoirEnTransit Si l'utilisateur a la possiblité de voir les événements non encore validés
     *
     * @return array
     */
    private function getListeSQL(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
    {
        if (empty($utilisateursATrouver)) {
            return [];
        }
        $etats[] = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        if ($canVoirEnTransit) {
            $etats = array_merge($etats, [
                \App\Models\Conge::STATUT_DEMANDE,
                \App\Models\Conge::STATUT_PREMIERE_VALIDATION
            ]);
        }
        /* On prend plus pour le début en cas de congés débordant sur deux mois */
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                WHERE p_date_deb >= "' . $dateDebut->modify('-1 month')->format('Y-m-d') . '"
                    AND p_date_deb <= "' . $dateFin->format('Y-m-d') . '"
                    AND p_login IN ("' . implode('","', $utilisateursATrouver) . '")
                    AND p_etat IN ("' . implode('","', $etats) . '")
                    AND p_fermeture_id IS NULL';

        return $this->db->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
