<?php
namespace App\ProtoControllers\Ajax\Employe\Heure;

/**
 * ProtoContrôleur ajax d'heure additionnelles, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Additionnelle
{
    /**
     * Retourne la liste des heures additionnelles satisfaisant aux critères fournis
     *
     * @param array $parametresRecherche Critères de filtres
     *
     * @return array utilisables par le calendrier
     */
    public function getListe(array $parametresRecherche)
    {
        $additionnelle = [];
        $liste = $this->getListeSQL($this->getListeId($parametresRecherche));

        foreach ($liste as $heureAdditionnelle) {
            $additionnelle[] = [
                'start' => date('c', $heureAdditionnelle['debut']),
                'end' => date('c', $heureAdditionnelle['fin']),
                'className' => 'heureAdditionnelle',
                'title' => '« ' . $heureAdditionnelle['login'] . ' » - Additionnelle',
            ];
        }

        return $additionnelle;
    }

    /*
     * SQL
     */

    protected function getListeId(array $params)
    {
        $where = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'start':
                        $where[] = 'debut >= ' . strtotime($value);
                        break;
                    case 'end':
                        $where[] = 'debut <= ' . strtotime($value);
                        break;
                }
            }
        }
        /* TODO voir si c'est vraiment utile, si on est capable de l'empêcher en amont */
        $where[] = 'duree > 0';
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle '
                . ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     *
     */
    private function getListeSQL(array $listeId)
    {
        if (empty($listeId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_additionnelle
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
