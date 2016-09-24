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

        ddd();

        /* Si la gestion des groupes est activée, alors on récupère les utilisateurs associés aux groupes (que l'on demande [intersect] dont on a accès) */
        if($_SESSION['config']['gestion_groupes']) {
            $rechercheUtilisateurs = \App\ProtoControllers\Ajax\Evenement::getListeUtilisateursVisibles($utilisateur);
            $groupesDroits = [];
            if (isRH() || isAdmin()) {
                if (!empty($rechercheCommune['groupe'])) {
                    /* ... On ne prend que le groupe demandé... */
                    $rechercheUtilisateurs = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($rechercheCommune['groupe']);
                /* ... Sinon c'est open bar */
                } else {
                    $rechercheUtilisateurs = \App\ProtoControllers\Utilisateur::getListId();
                }
            } elseif (\App\ProtoControllers\Utilisateur::isResponsable()) {
                $a = [];
            /* C'est forcément un employé normal */
            } else {
                $groupesDroits = \App\ProtoControllers\Utilisateur::getGroupesId($utilisateur);
            }

            /*
            * Si les groupes sont activés
            *   - Si un groupe est effectivement demandé
            *       - Si admin || rh : voir le groupe
            *       - Si responsable || employé : filter sur les droits et groupe
            *   - Sinon :
            *       - Si admin || rh : tout voir
            *       - Si responsable || employé : filter sur les droits
            * Sinon : open bar
            */

            /* - Selon la session qui regarde, évaluer la portée de ce qu'il doit voir :
            - Si c'est employé simple, ne voir que ceux du groupe, s'il n'a pas de groupe, que lui
            - Si c'est un resp, prendre tous les groupes dont il est responsable (ou grand responsable) + comme un employé normal
            - Si c'est un HR (|| admin même si normalement l'admin est hors métier), tout prendre

            $_SESSION['is_resp']"N"
            $_SESSION['is_admin']"Y"
            $_SESSION['is_hr']"N"
            */
            //

            $rechercheGroupe = array_intersect($groupesDroits, [$rechercheCommune['groupe']]);
            unset($rechercheCommune['groupe']);
            $rechercheUtilisateurs = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($rechercheGroupe);
        /* Sinon on prend tous les utilisateurs */
        } else {
            $rechercheUtilisateurs = \App\ProtoControllers\Utilisateur::getListId();
        }
        $rechercheUtilisateurs = [];

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
         * TODO: Pour ce qui est sujet à validation, on ne prend que les acceptés,
         */

        return json_encode($evenements);
    }

    /**
     * Retourne la liste des utilisateurs visibles par un utilisateur
     *
     * @param string $utilisateur
     *
     * @return array
     */
    private function getListeUtilisateursVisibles($utilisateur)
    {
        return [];
        // get role
        // selon le rôle on prend
    }
}
