<?php
namespace App\ProtoControllers\Ajax;

/**
 * ProtoContrôleur ajax d'événement, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Evenement extends \App\ProtoControllers\Ajax
{
    /**
     * Retourne une liste d'événements
     *
     * @param array $filtres
     * @param string $utilisateur

     * @return string
     */
    public function getListe(array $filtres, $utilisateur)
    {
        $rechercheCommune = [];
        $rechercheAutorise = ['start', 'end', 'groupe'];
        foreach ($filtres as $k => $valeur) {
            if (in_array($k, $rechercheAutorise, true)) {
                // protéger les valeurs passées
                $rechercheCommune[$k] = $valeur;
            }
        }

        if (!empty($rechercheUtilisateurs)) {
            $repos = new \App\ProtoControllers\Ajax\Employe\Heure\Repos();
            $lstRepos = $repos->getListe($rechercheCommune + ['users' => $rechercheUtilisateurs]);
            $fermeture = new \App\ProtoControllers\Ajax\Fermeture();
            $lstFermetures = $fermeture->getListe($rechercheCommune + ['users' => $rechercheUtilisateurs]);
            $additionnelle = new \App\ProtoControllers\Ajax\Employe\Heure\Additionnelle();
            $lstAdditionnelles = $additionnelle->getListe($rechercheCommune + ['users' => $rechercheUtilisateurs]);
            $conge = new \App\ProtoControllers\Ajax\Employe\Conge();
            $lstConges = $conge->getListe($rechercheCommune + ['users' => $rechercheUtilisateurs]);
            $evenements = array_merge(
                $evenements,
                $lstRepos,
                $lstFermetures,
                $lstAdditionnelles,
                $lstConges
            );
        }

        return json_encode($evenements);
    }
}
