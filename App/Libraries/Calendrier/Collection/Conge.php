<?php
namespace App\Libraries\Calendrier\Collection;

use \App\Libraries\Calendrier\Evenement;

/**
 * Collection d'événements de jours de congés
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
class Conge extends \App\Libraries\Calendrier\ACollection
{
    /**
     * @var array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     */
    private $utilisateursATrouver;

    /**
     * {@inheritDoc}
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     */
    public function __construct(\DateTime $dateDebut, \DateTime $dateFin, array $utilisateursATrouver)
    {
        parent::__construct($dateDebut, $dateFin);
        $this->utilisateursATrouver = $utilisateursATrouver;
    }

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $conges = [];
        $longueurMax = 10;
        foreach ($this->getListeSQL() as $jour) {
            $class = $jour['ta_type'];
            /* TODO: unescape_string ? */
            $identite = $jour['u_prenom'] . ' ' . $jour['u_nom'];
            $userName = ($longueurMax < mb_strlen($identite))
                ? substr($identite, 0, $longueurMax) . ['...']
                : $identite;
            $name = $userName . ' - ' . $jour['ta_libelle'];

            $dateDebut = $this->getDebutPeriode($jour['p_date_deb'], $jour['p_demi_jour_deb']);
            $dateFin = $this->getFinPeriode($jour['p_date_fin'], $jour['p_demi_jour_fin']);

            /* afficher le format long que si l'heure est != 00:00 || 23:59 (?) */
            $title = '[' . $jour['ta_type'] . '] ' . $jour['ta_libelle'] . ' de ' . $jour['u_login'] . ' du ' . $dateDebut->format('d/m/Y à H\:i') . ' au ' . $dateFin->format('d/m/Y à H\:i');
            $uid = uniqid('ferie');
            $conges[] = new Evenement\Commun($uid, $dateDebut, $dateFin, $name, $title, $class);
        }

        return $conges;
    }

    private function getDebutPeriode($dateDebut, $demiJournee)
    {
        // TODO pour le moment, si c'est un creneau de matin, on le place à 00:00 -> 11:59 / Creneaux du soir 12:00 -> 23:59
        // Il faudra préciser davantage avec le planning
        $debut = ('am' === $demiJournee)
            ? $dateDebut . ' 00:00'
            : $dateDebut . ' 11:59';
        return new \DateTime($debut);
    }

    private function getFinPeriode($dateFin, $demiJournee)
    {
        // TODO pour le moment, si c'est un creneau de matin, on le place à 00:00 -> 11:59 / Creneaux du soir 12:00 -> 23:59
        // Il faudra préciser davantage avec le planning
        $fin = ('am' === $demiJournee)
            ? $dateFin . ' 11:59'
            : $dateFin . ' 23:59';
        return new \DateTime($fin);
    }


    /*
     * SQL
     */


    /**
     * Retourne la liste des congés du stockage satisfaisant aux critères
     *
     * @return array
     * @TODO actuellement, il y a un bug, les rôles autre que utilisateur n'ont as de valorisation de p_nb_jours en cas de fermeture
     */
    private function getListeSQL()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                    INNER JOIN conges_users CU ON (CP.p_login = CU.u_login)
                WHERE p_date_deb >= "' . $this->dateDebut->format('Y-m-d') . '"
                    AND p_date_deb <= "' . $this->dateFin->format('Y-m-d') . '"
                    AND p_login IN ("' . implode('","', $this->utilisateursATrouver) . '")
                    AND p_etat = "' . \App\Models\Conge::STATUT_VALIDATION_FINALE . '"';
        $res = $sql->query($req);
        $conges = [];
        while ($data = $res->fetch_assoc()) {
            $conges[] = $data;
        }

        return $conges;
    }
}
