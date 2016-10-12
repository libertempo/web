<?php
namespace App\Libraries\Calendrier\Collection;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements de jours de fermeture de l'entreprise
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
class Fermeture extends \App\Libraries\Calendrier\ACollection
{
    /**
     * @var array $groupesATrouver Liste des groupes dont on veut voir les fermetures
     */
    private $groupesATrouver;

    /**
     * {@inheritDoc}
     * @param array $groupesATrouver Liste des groupes dont on veut voir les fermetures
     */
    public function __construct(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $groupesATrouver)
    {
        parent::__construct($dateDebut, $dateFin);
        $this->groupesATrouver = $groupesATrouver;
    }
    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $fermeture = [];
        $name = 'Fermeture';
        $title = null;
        $class = 'fermeture';
        foreach ($this->getListeSQL() as $jour) {
            $dateJour = new \DateTime($jour['jf_date']);
            $uid = uniqid('fermeture');
            $fermeture[] = new Evenement\Commun($uid, $dateJour, $dateJour, $name, $title, $class);
        }

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
    private function getListeSQL()
    {
        $reqGroupe = '';
        if (!empty($this->groupesATrouver)) {
            $reqGroupe = 'AND jf_gid IN (' . implode(',', $this->groupesATrouver) . ')';
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_jours_fermeture
                WHERE jf_date >= "' . $this->dateDebut->format('Y-m-d') . '"
                    AND jf_date <= "' . $this->dateFin->format('Y-m-d') . '"
                    ' . $reqGroupe . '
                ORDER BY jf_date ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
