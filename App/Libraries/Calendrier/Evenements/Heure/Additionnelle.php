<?php
namespace App\Libraries\Calendrier\Evenements\Heure;

/**
 * Evenements d'événements des heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Evenements
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
final class Additionnelle extends \App\Libraries\Calendrier\Evenements\AHeure
{
    /*
     * SQL
     */

    /**
     * @inheritDoc
     */
    protected function getListeId(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $utilisateursATrouver, $canVoirEnTransit)
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
     * @inheritDoc
     */
    protected function getListeSQL(array $listeId)
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
