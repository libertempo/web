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
class Ferie // extends or implements ?
{
    /**
     * Retourne la collection de jours feriés
     *
     * @return \CalendR\Event\EventInterface[]
     */
    public function getListe()
    {
        $feries = [];
        foreach ($this->getListeSQL() as $jour) {
            $dateJour = new \DateTime($jour['jf_date']);
            $uid = uniqid('ferie');
            $class = $uid;

            $feries[] = new Evenement\Commun($uid, $dateJour, $dateJour, $class);
            /*$feries[] = [
                'start' => $jour['jf_date'],
                'className' => 'ferie',
                'title' => 'Ferié',
            ];*/
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
                ORDER BY jf_date ASC';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
