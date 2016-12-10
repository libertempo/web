<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur de planning, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class APlanning
{
    /**
     * Retourne la liste des utilisateurs associés au planning
     *
     * @param int $idPlanning
     *
     * @return array ['login', 'nom', 'prenom', 'planningId']
     */
    public static function getListeUtilisateursAssocies($idPlanning)
    {
        $utilisateursAssocies = \App\ProtoControllers\Utilisateur::getListByPlanning(0);

        $idPlanning = (int) $idPlanning;
        if (NIL_INT !== $idPlanning) {
            $utilisateursAssocies = array_merge($utilisateursAssocies, \App\ProtoControllers\Utilisateur::getListByPlanning($idPlanning));
        }
        $utilisateursAssocies = array_map(
            function ($utilisateur) {
                return [
                    'login' => $utilisateur['u_login'],
                    'nom' => $utilisateur['u_nom'],
                    'prenom' => $utilisateur['u_prenom'],
                    'planningId' => (int) $utilisateur['planning_id'],
                ];
            },
            $utilisateursAssocies
        );

        return $utilisateursAssocies;
    }
}
