<?php
namespace App\ProtoControllers\Ajax;

/**
 * ProtoContrôleur ajax d'événement, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Evenement
{
    /**
     *
     */
    public function get()
    {
        $parametresRecherche = [];
        $rechercheAuthorise = ['start', 'end'];
        foreach ($_GET as $get => $valeur) {
            if (in_array($get, $rechercheAuthorise)) {
                $parametresRecherche[$get] = $valeur;
            }
        }
        header('Content-type: application/json');
        $repos = new \App\ProtoControllers\Ajax\Heure\Repos();
        $evenements = $repos->getListe($parametresRecherche);

        // week end / feries
        // fermeture
        // congés acceptés (rtt compris)
        // heure repos
        // congés en cours de validation
        // absences

        return json_encode($evenements);
    }
}
