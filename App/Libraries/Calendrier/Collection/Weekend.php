<?php
namespace App\Libraries\Calendrier\Collection;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements de weekend
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
class Weekend extends \App\Libraries\Calendrier\ACollection
{
    /**
     * @var int Identifiant du samedi dans les fonctions de date
     */
    const JOUR_SAMEDI = 6;

    /**
     * @var int Identifiant du dimanche dans les fonctions de date
     */
    const JOUR_DIMANCHE = 0;

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $liste = [];
        if (!$this->isSamediTravaille()) {
            $liste = array_merge($liste, $this->getListeSamedi());
        }
        if (!$this->isDimancheTravaille()) {
            $liste = array_merge($liste, $this->getListeDimanche());
        }

        $weekend = [];
        $name = 'Week-end';
        $title = null;
        $class = 'weekend';
        foreach ($liste as $jour) {
            $dateJour = new \DateTime($jour);
            $uid = uniqid('weekend');
            $weekend[] = new Evenement\Commun($uid, $dateJour, $dateJour, $name, $title, $class);
        }

        return $weekend;
    }

    /**
     * Retourne la liste des occurences des samedi
     *
     * @return array
     */
    private function getListeSamedi()
    {
        return $this->getListeJourSemaine(static::JOUR_SAMEDI);
    }

    /**
     * Retourne la liste des occurences des dimanche
     *
     * @return array
     */
    private function getListeDimanche()
    {
        return $this->getListeJourSemaine(static::JOUR_DIMANCHE);
    }

    /**
     * Retourne la liste des occurences des jours de la semaine dans une période donnée
     *
     * @param int $jourSemaine
     *
     * @return array
     */
    private function getListeJourSemaine($jourSemaine)
    {
        $debut = $this->dateDebut->getTimestamp();
        $fin = $this->dateFin->getTimestamp();
        if ($debut > $fin) {
            throw new \Exception('Date de début supérieure à date de fin');
        }

        $listeJourSemaine = [];
        while ($debut <= $fin) {
            /* Si on est sur le jour clé, on append la liste et on tape une semaine au dessus */
            if (date('w', $debut) == $jourSemaine) {
                $listeJourSemaine[] = date('Y-m-d', $debut);
                $debut = strtotime('+1 week', $debut);
            } else {
                $debut = strtotime('+1 day', $debut);
            }
        }

        return $listeJourSemaine;
    }


    /*
     * SQL
     * @TODO dégager lors de la mise en place de l'objet configuration
     */


    private function isSamediTravaille()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
            FROM conges_config
            WHERE conf_nom = "samedi_travail" LIMIT 1';

        $res = $sql->query($req)->fetch_assoc();

        return 'TRUE' === $res['conf_valeur'];
    }

    private function isDimancheTravaille()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_config
                WHERE conf_nom = "dimanche_travail" LIMIT 1';

        $res = $sql->query($req)->fetch_assoc();

        return 'TRUE' === $res['conf_valeur'];
    }
}
