<?php
namespace App\ProtoControllers\Ajax;

/**
 * ProtoContrôleur ajax de jours de fermeture, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Fermeture
{
    /**
     * Retourne la liste des jours de fermeture dans une période donnée utilisable par le calendrier
     *
     * @param array $parametresRecherche Critères de date
     *
     * @return array
     */
    public function getListe(array $parametresRecherche)
    {
        $feries = [];
        $liste = $this->getListeSQL($parametresRecherche);
        foreach ($liste as $jour) {
            $feries[] = [
                'start' => $jour['jf_date'],
                'className' => 'fermeture',
                'title' => 'Fermeture',
            ];
        }

        return $feries;
    }

    /*
     * SQL
     */


    /**
     * Retourne les jours de fermeture satisfaisant aux critères de période
     *
     * @param array $parametresRecherche
     *
     * @return array
     * TODO : faire un filtre sur les groupes en partant des utilisateurs
     */
    private function getListeSQL(array $parametresRecherche)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_jours_fermeture
                WHERE jf_date >= "' . $sql->quote($parametresRecherche['start']) . '"
                    AND jf_date <= "' . $sql->quote($parametresRecherche['end']) . '"
                ORDER BY jf_date ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
