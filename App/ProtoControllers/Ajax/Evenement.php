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

     * @return string
     */
    public function getListe(array $filtres)
    {
        $rechercheCommune = [];
        $rechercheAutorise = ['start', 'end', 'groupe'];
        foreach ($filtres as $k => $valeur) {
            if (in_array($k, $rechercheAutorise, true)) {
                // protéger les valeurs passées
                $rechercheCommune[$k] = $valeur;
            }
        }

        if($_SESSION['config']['gestion_groupes']) {
            $groupesDroits = [];
            $rechercheGroupe = array_intersect($groupesDroits, [$rechercheCommune['groupe']]);
            unset($rechercheCommune['groupe']);
            //$rechercheUtilisateurs = getAllUsersInGroupes(['groupe'  => $rechercheGroupe]);
            // get all utilisateurs dans ces groupes
        } else {
            //$rechercheUtilisateurs = getAllUsers();
            // get all utilisateurs
        }
        $rechercheUtilisateurs = [];
        /*
        * Si gestion des groupe activée :
        *   - récuperer tous les groupes auxquels l'utilisateur a droit
        *   - si un groupe est demandé, array_intersect()
        * Sinon :
        *   - Tout rôle : comme existant (ça devrait être « open bar » normalement)
        */


        /* - Selon la session qui regarde, évaluer la portée de ce qu'il doit voir :
        - Si c'est employé simple, ne voir que ceux du groupe, s'il n'a pas de groupe, que lui
        - Si c'est un resp, prendre tous les groupes dont il est responsable (ou grand responsable) + comme un employé normal
        - Si c'est un HR, tout prendre

        $_SESSION['is_resp']"N"
        $_SESSION['is_admin']"Y"
        $_SESSION['is_hr']"N"
        */

        $repos = new \App\ProtoControllers\Ajax\Employe\Heure\Repos();
        $lstRepos = $repos->getListe($rechercheCommune + $rechercheUtilisateurs);
        $ferie = new \App\ProtoControllers\Ajax\Ferie();
        $lstFeries = $ferie->getListe($rechercheCommune);
        $weekEnd = new \App\ProtoControllers\Ajax\WeekEnd();
        $lstWeekEnd = $weekEnd->getListe($rechercheCommune);
        $fermeture = new \App\ProtoControllers\Ajax\Fermeture();
        $lstFermetures = $fermeture->getListe($rechercheCommune + $rechercheUtilisateurs);
        $additionnelle = new \App\ProtoControllers\Ajax\Employe\Heure\Additionnelle();
        $lstAdditionnelles = $additionnelle->getListe($rechercheCommune + $rechercheUtilisateurs);
        $conge = new \App\ProtoControllers\Ajax\Employe\Conge();
        $lstConges = $conge->getListe($rechercheCommune + $rechercheUtilisateurs);
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
