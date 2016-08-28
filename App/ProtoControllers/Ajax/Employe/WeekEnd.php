<?php
namespace App\ProtoControllers\Ajax\Employe;

/**
 * ProtoContrôleur ajax des jours de congés, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class WeekEnd
{

    const JOUR_SAMEDI = 6;
    const JOUR_DIMANCHE = 0;

    /**
     *
     */
    public function getListe(array $parametresRecherche)
    {
        $liste = [];

        if (!$this->isSamediTravaille()) {
            $liste = array_merge($liste, $this->getListeSamediDansPeriode($parametresRecherche['start'], $parametresRecherche['end']));
        }
        if (!$this->isDimancheTravaille()) {
            $liste = array_merge($liste, $this->getListeDimancheDansPeriode($parametresRecherche['start'], $parametresRecherche['end']));
        }
        $weekEnd = [];

        foreach ($liste as $jour) {
            $weekEnd[] = [
                'start' => $jour,
                'className' => 'week-end',
                'title' => 'WeekEnd',
            ];
        }

        return $weekEnd;
    }

    private function getListeSamediDansPeriode($dateDebut, $dateFin)
    {
        return $this->getListeJourSemaineDansPeriode($dateDebut, $dateFin, self::JOUR_SAMEDI);
    }

    private function getListeDimancheDansPeriode($dateDebut, $dateFin)
    {
        return $this->getListeJourSemaineDansPeriode($dateDebut, $dateFin, self::JOUR_DIMANCHE);
    }

    private function getListeJourSemaineDansPeriode($dateDebut, $dateFin, $jourSemaine)
    {
        $debut = strtotime($dateDebut);
        $fin = strtotime($dateFin);
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
