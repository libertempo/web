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
        $repos = new \App\ProtoControllers\Ajax\Employe\Heure\Repos();
        $lstRepos = $repos->getListe($parametresRecherche);
        $feries = new \App\ProtoControllers\Ajax\Ferie();
        $lstFeries = $feries->getListe($parametresRecherche);
        $weekEnd = new \App\ProtoControllers\Ajax\WeekEnd();
        $lstWeekEnd = $weekEnd->getListe($parametresRecherche);
        $fermeture = new \App\ProtoControllers\Ajax\Fermeture();
        $lstFermeture = $fermeture->getListe($parametresRecherche);
        $additionnelle = new \App\ProtoControllers\Ajax\Employe\Heure\Additionnelle();
        $lstAdditionnelle = $additionnelle->getListe($parametresRecherche);
        $evenements = array_merge(
            $lstRepos,
            $lstFeries,
            $lstWeekEnd,
            $lstFermeture,
            $lstAdditionnelle
        );

        //ddd($evenements);

        // congés acceptés (rtt compris)
        // congés en cours de validation
        // absences

        return json_encode($evenements);
    }
}
