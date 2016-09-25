<?php
namespace App\ProtoControllers\Ajax\Employe;

/**
 * ProtoContrôleur ajax de congé, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Conge
{
    /**
     * Retourne la liste des congés satisfaisant aux critères fournis
     *
     * @param array $parametresRecherche Critères de filtres
     *
     * @return array avec offsets utilisables par le calendrier
     */
    public function getListe(array $parametresRecherche)
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
     * @TODO actuellement, il y a un bug, les rôles autre que utilisateur n'ont pas de valorisation de p_nb_jours en cas de fermeture
     */
    private function getListeSQL(array $params)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                    INNER JOIN conges_users CU ON (CP.p_login = CU.u_login)
                WHERE p_date_deb >= "' . $params['start'] . '"
                    AND p_date_deb <= "' . $params['end'] . '"
                    AND p_nb_jours > "0.0"
                    AND p_login IN ("' . implode('","', $params['users']) . '")
                    AND p_etat = "' . \App\Models\Conge::STATUT_VALIDATION_FINALE . '"';
        $res = $sql->query($req);
        $conges = [];
        while ($data = $res->fetch_assoc()) {
            $conges[] = $data;
        }

        return $conges;
    }
}
