<?php
namespace App\ProtoControllers\Ajax;

/**
 * ProtoContrôleur ajax de jours feriés, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Ferie
{
    /**
     * Retourne la liste des jours feriés dans une période donnée utilisable par le calendrier
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
                'className' => 'ferie',
                'title' => 'Ferié',
            ];
        }

        return $feries;
    }

    /*
     * SQL
     */


    /**
     * Retourne les jours fériés satisfaisant aux critères de période
     *
     * @param array $parametresRecherche
     *
     * @return array
     */
    private function getListeSQL(array $parametresRecherche)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_jours_feries
                WHERE jf_date >= "' . $sql->quote($parametresRecherche['start']) . '"
                    AND jf_date <= "' . $sql->quote($parametresRecherche['end']) . '"
                ORDER BY jf_date ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
