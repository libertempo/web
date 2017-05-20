<?php
namespace App\Libraries\Calendrier\Collection;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection de jours de congés
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Facade
 *
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Conge
{
    public function __construct(\includes\SQL $db) {
        $this->db = $db;
    }

    /**
     * Retourne la liste des jours fériés relative à la période demandée
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
            $nomComplet = \App\ProtoControllers\Utilisateur::getNomComplet($jour['u_prenom'], $jour['u_nom'], true);
            /*
             * Par construction, les congés n'ont que le début et la fin.
             * On cherche donc les intersticiels...
             */
            foreach ($this->getListeJoursIntersticiels($jour['p_date_deb'], $jour['p_date_fin']) as $jourIntersticiel) {
                $conges[$jourIntersticiel][] = [
                    'employe' => $nomComplet,
                    'demiJournee' => '*',
                    'statut' => $jour['p_etat'],
                ];
            }

            /* ... Puis on ajoute les bords */
            $conges[$jour['p_date_deb']][] = [
                'employe' => $nomComplet,
                'demiJournee' => $jour['p_demi_jour_deb'],
                'statut' => $jour['p_etat'],
            ];
            $conges[$jour['p_date_fin']][] = [
                'employe' => $nomComplet,
                'demiJournee' => $jour['p_demi_jour_fin'],
                'statut' => $jour['p_etat'],
            ];
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
     * @TODO actuellement, il y a un bug, les rôles autre que utilisateur n'ont pas de valorisation de p_nb_jours en cas de fermeture
     */
    private function getListeSQL(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
    {
        $etats[] = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        if ($canVoirEnTransit) {
            $etats = array_merge($etats, [
                \App\Models\Conge::STATUT_DEMANDE,
                \App\Models\Conge::STATUT_PREMIERE_VALIDATION
            ]);
        }
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                    INNER JOIN conges_users CU ON (CP.p_login = CU.u_login)
                WHERE p_date_deb >= "' . $dateDebut->format('Y-m-d') . '"
                    AND p_date_deb <= "' . $dateFin->format('Y-m-d') . '"
                    AND p_login IN ("' . implode('","', $utilisateursATrouver) . '")
                    AND p_etat IN ("' . implode('","', $etats) . '")';

        return $this->db->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
