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
                'className' => 'conge',
                'title' => '« ' . $conge['p_login'] . ' » - Congé',
            ];
        }

        return $conges;
    }

    /*
     * SQL
     */

    private function getListeSQL(array $params)
    {
        $where = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'start':
                        $where[] = 'p_date_deb >= "' . $value . '"';
                        break;
                    case 'end':
                        $where[] = 'p_date_deb <= "' . $value . '"';
                        break;
                }
            }
        }

        /* TODO actuellement, il y a un bug, les rôles autre que utilisateur n'ont pas de valorisation de p_nb_jours en cas de fermeture */
        $where[] = 'CP.p_nb_jours > "0.0"';
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id) '
                . ((!empty($where)) ? '
                WHERE ' . implode(' AND ', $where) : '');
        $res = $sql->query($req);
        $conges = [];
        while ($data = $res->fetch_assoc()) {
            $conges[] = $data;
        }

        return $conges;
    }
}
