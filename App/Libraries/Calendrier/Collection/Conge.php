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
     * @var bool Si l'utilisateur a la possiblité de voir les événements non encore validés
     */
    private $canVoirEnTransit;

    /**
     * {@inheritDoc}
     * @param array $utilisateursATrouver Liste d'utilisateurs dont on veut voir les congés
     */
    public function __construct(
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        array $utilisateursATrouver,
        $canVoirEnTransit
    ) {
        parent::__construct($dateDebut, $dateFin);
        $this->utilisateursATrouver = $utilisateursATrouver;
        $this->canVoirEnTransit = (bool) $canVoirEnTransit;
    }

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $conges = [];
        foreach ($this->getListeSQL() as $jour) {
            $class = $jour['ta_type'] . ' ' . $jour['ta_type'] . '_' . $jour['p_etat'];
            /* TODO: unescape_string ? */
            $nomComplet = \App\ProtoControllers\Utilisateur::getNomComplet($jour['u_prenom'], $jour['u_nom'], true);
            $name = $nomComplet . ' - ' . $jour['ta_libelle'];
            if (\App\Models\Conge::STATUT_VALIDATION_FINALE !== $jour['p_etat']) {
                $name = '[En demande]  ' . $name;
            }

            $dateDebut = $this->getDebutPeriode($jour['p_date_deb'], $jour['p_demi_jour_deb']);
            $dateFin = $this->getFinPeriode($jour['p_date_fin'], $jour['p_demi_jour_fin']);

            $title = '[' . $jour['ta_type'] . '] ' . $jour['ta_libelle'] . ' de ' . $nomComplet . ' du ' . $dateDebut['date']->format('d/m/Y') . ' ' . $dateDebut['creneau'] . ' au ' . $dateFin['date']->format('d/m/Y') . ' ' . $dateFin['creneau'];
            $uid = uniqid('ferie');
            $conges[] = new Evenement\Commun($uid, $dateDebut['date'], $dateFin['date'], $name, $title, $class);
        }

        return $conges;
    }

    private function getDebutPeriode($dateDebut, $demiJournee)
    {
        $debut = ('am' === $demiJournee)
            ? $dateDebut
            : $dateDebut . ' 11:59';
        $creneau = ('am' === $demiJournee)
            ? _('debut_matin')
            : _('debut_apres-midi');

        return ['date' => new \DateTime($debut), 'creneau' => $creneau];
    }

    private function getFinPeriode($dateFin, $demiJournee)
    {
        $fin = ('am' === $demiJournee)
            ? $dateFin . ' 11:59'
            : $dateFin . ' 23:59';
        $creneau = ('am' === $demiJournee)
            ? _('debut_apres-midi')
            : _('fin_apres-midi');

            return ['date' => new \DateTime($fin), 'creneau' => $creneau];
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
        $etats[] = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        if ($this->canVoirEnTransit) {
            $etats = array_merge($etats, [
                \App\Models\Conge::STATUT_DEMANDE,
                \App\Models\Conge::STATUT_PREMIERE_VALIDATION
            ]);
        }
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                    INNER JOIN conges_users CU ON (CP.p_login = CU.u_login)
                WHERE p_date_deb >= "' . $this->dateDebut->format('Y-m-d') . '"
                    AND p_date_deb <= "' . $this->dateFin->format('Y-m-d') . '"
                    AND p_login IN ("' . implode('","', $this->utilisateursATrouver) . '")
                    AND p_etat IN ("' . implode('","', $etats) . '")';
        $res = $sql->query($req);
        $conges = [];
        while ($data = $res->fetch_assoc()) {
            $conges[] = $data;
        }

        return $conges;
    }
}
