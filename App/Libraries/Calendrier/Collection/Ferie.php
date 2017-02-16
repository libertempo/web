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
    public function getListe()
    {
        $feries = [];
        $name = 'Jour férié';
        $title = null;
        $class = 'ferie';
        foreach ($this->getListeSQL() as $jour) {
            $dateJour = new \DateTime($jour['jf_date']);
            $uid = uniqid('ferie');
            $feries[] = new Evenement\Commun($uid, $dateJour, $dateJour, $name, $title, $class);
        }

        return $feries;
    }


    /*
     * SQL
     */


    /**
     * Retourne tous les jours fériés
     *
     * @return array
     */
    private function getListeSQL()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_jours_feries
                WHERE jf_date >= "' . $this->dateDebut->format('Y-m-d') . '"
                    AND jf_date <= "' . $this->dateFin->format('Y-m-d') . '"
                ORDER BY jf_date ASC';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
