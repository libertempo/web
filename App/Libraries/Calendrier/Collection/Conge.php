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
            $userName = ($longueurMax < mb_strlen($jour['u_login']))
                ? substr($jour['u_login'], 0, $longueurMax) . ['...']
                : $jour['u_login'];
            $name = $userName . ' - ' . $jour['ta_libelle'];
            $dateDebut = new \DateTime($jour['p_date_deb']);
            $dateFin = new \DateTime($jour['p_date_fin']);

            $title = '[' . $jour['ta_type'] . '] ' . $jour['ta_libelle'] . ' de ' . $jour['u_login'] . ' du ' . $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
            $uid = uniqid('ferie');
            $conges[] = new Evenement\Commun($uid, $dateDebut, $dateFin, $name, $title, $class);
        }

        return $conges;
    }

    /**
     * Retourne la liste des congés satisfaisant aux critères fournis
     *
     * @param array $parametresRecherche Critères de filtres
     *
     * @return array avec offsets utilisables par le calendrier
     */
    public function getListeyy(array $parametresRecherche)
    {
        $conges = [];
        $liste = $this->getListeSQL($parametresRecherche);

        foreach ($liste as $conge) {
            // TODO pour le moment, si c'est un creneau de matin, on le place à 00:00 -> 11:59 / Creneaux du soir 12:00 -> 23:59
            // Il faudra préciser davantage avec le planning
            $debut = $conge['p_date_deb'];
            $strDebut = 'am' === $conge['p_demi_jour_deb'] ?
                $conge['p_date_deb'] . ' 00:00' :
                $conge['p_date_deb'] . ' 11:59';
            $strFin = 'am' === $conge['p_demi_jour_fin'] ?
                $conge['p_date_fin'] . ' 12:00' :
                $conge['p_date_fin'] . ' 23:59';
            $conges[] = [
                'start' => date('c', strtotime($strDebut)),
                'end' => date('c', strtotime($strFin)),
                'className' => $conge['ta_type'] . ' ' . $conge['p_etat'],
                'title' => $conge['ta_libelle'] . ' - ' . $conge['u_prenom'] . ' ' . $conge['u_nom'],
            ];
        }

        return $conges;
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
