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
                // protéger les valeurs passées
                $parametresRecherche[$get] = $valeur;
            }
        }
        header('Content-type: application/json');
        $repos = new \App\ProtoControllers\Ajax\Employe\Heure\Repos();
        $lstRepos = $repos->getListe($parametresRecherche);
        $ferie = new \App\ProtoControllers\Ajax\Ferie();
        $lstFeries = $ferie->getListe($parametresRecherche);
        $weekEnd = new \App\ProtoControllers\Ajax\WeekEnd();
        $lstWeekEnd = $weekEnd->getListe($parametresRecherche);
        $fermeture = new \App\ProtoControllers\Ajax\Fermeture();
        $lstFermetures = $fermeture->getListe($parametresRecherche);
        $additionnelle = new \App\ProtoControllers\Ajax\Employe\Heure\Additionnelle();
        $lstAdditionnelles = $additionnelle->getListe($parametresRecherche);
        $conge = new \App\ProtoControllers\Ajax\Employe\Conge();
        $lstConges = $conge->getListe($parametresRecherche);
        $evenements = array_merge(
            $lstRepos,
            $lstFeries,
            $lstWeekEnd,
            $lstFermetures,
            $lstAdditionnelles,
            $lstConges
        );

        /*
        * Pour ce qui est sujet à validation, on ne prend que les acceptés, donc ça implique d'avoir une conscience de la double / simple validation
        * (Voir proposition sur les nouveaux états)
        * À vérifier, voir avec Wouldsmina
        */

        return json_encode($evenements);
    }
}
