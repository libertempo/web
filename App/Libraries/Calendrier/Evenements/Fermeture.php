<?php
namespace App\Libraries\Calendrier\Evenements;

use \App\Libraries\Calendrier\Evenement;

/**
 * Evenements de jours de fermeture de l'entreprise
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit être contacté que par \App\Libraries\Calendrier\Evenements
 *
 * @TODO supprimer le requétage à la migration vers le MVC REST
 */
class Fermeture
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
      * @param array $groupesATrouver Liste des groupes dont on veut voir les fermetures
      *
      * @return array
      */
    public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $groupesATrouver)
    {
        $fermeture = array_map(function ($res) {
            //TODO : se brancher sur le formatter (à modifier d'ailleurs)
            return date('Y-m-d', strtotime($res['jf_date']));
        }, $this->getListeSQL($dateDebut, $dateFin, $groupesATrouver));
        sort($fermeture);

        return $fermeture;
    }


    /*
     * SQL
     */


    /**
     * Retourne les jours de fermeture satisfaisant aux critères de période
     *
     * @return array
     */
    private function getListeSQL(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, $groupesATrouver)
    {
        $reqGroupe = '';
        if (!empty($groupesATrouver)) {
            $reqGroupe = 'AND jf_gid IN (' . implode(',', $groupesATrouver) . ')';
        }
        $req = 'SELECT *
                FROM conges_jours_fermeture
                WHERE jf_date >= "' . $dateDebut->format('Y-m-d') . '"
                    AND jf_date <= "' . $dateFin->format('Y-m-d') . '"
                    ' . $reqGroupe . '
                ORDER BY jf_date ASC';

        return $this->db->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
