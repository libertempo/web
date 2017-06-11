<?php
namespace App\Libraries\Calendrier\Evenements;

/**
 * Evenements de jours fériés
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Evenements
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Ferie
{
    public function __construct(\includes\SQL $db)
    {
        $this->db = $db;
    }

    /**
    * @var \includes\SQL Objet de DB
    */
    private $db;

    /**
     * Retourne la liste des jours fériés relative à la période demandée
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     *
     * @return array
     */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $feries = array_map(function ($res) {
            //TODO : se brancher sur le formatter (à modifier d'ailleurs)
            return date('Y-m-d', strtotime($res['jf_date']));
        }, $this->getListeSQL($dateDebut, $dateFin));
        sort($feries);

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
