<?php
namespace App\Libraries\Calendrier\Collection;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements de jours fériés
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
class Ferie extends \App\Libraries\Calendrier\ACollection
{
    /**
     * {@inheritDoc}
     */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        return array_map(function ($res) {
            return date('Y-m-d', strtotime($res['jf_date']));
        }, $this->getListeSQL($dateDebut, $dateFin));
    }


    /*
     * SQL
     */


    /**
     * Retourne tous les jours fériés
     *
     * @return array
     */
    private function getListeSQL(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $req = 'SELECT jf_date
                FROM conges_jours_feries
                WHERE jf_date >= "' . $dateDebut->format('Y-m-d') . '"
                    AND jf_date <= "' . $dateFin->format('Y-m-d') . '"
                ORDER BY jf_date ASC';

        return $this->db->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
