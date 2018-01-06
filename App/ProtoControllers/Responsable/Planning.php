<?php
namespace App\ProtoControllers\Responsable;

/**
 * ProtoContrôleur de planning du responsable, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Planning extends \App\ProtoControllers\APlanning
{
    /**
     * Met à jour un planning
     *
     * @param int   $id
     * @param array $put
     * @param array &$errors
     *
     * @return int
     */
    public static function putPlanning($id, array $put, array &$errors)
    {
        $id = (int) $id;
        $subalternes = \App\ProtoControllers\Responsable::getUsersRespDirect($_SESSION['userlogin']);
        $utilisateursPlannings = array_map(function (array $u) {
            return $u['u_login'];
        }, \App\ProtoControllers\Utilisateur::getListByPlanning($id));

        $subalternesAvecPlanning = array_intersect(
            $utilisateursPlannings,
            $subalternes
        );

        $subalternesSansSortie = [];
        foreach ($subalternesAvecPlanning as $u) {
            if (!\App\ProtoControllers\Utilisateur::hasSortiesEnCours($u)) {
                $subalternesSansSortie[] = $u;
            }
        }

        if (!empty($subalternesSansSortie)) {
            \App\ProtoControllers\Utilisateur::deleteListAssociationPlanning($id, $subalternesSansSortie);
        }

        if (empty($put['utilisateurs'])) {
            return $id;
        }

        $subalternesSelectionnes = !empty($subalternesSansSortie)
            ? array_intersect($put['utilisateurs'], $subalternesSansSortie)
            : $put['utilisateurs'];

        if (!empty($subalternesSelectionnes)) {
            $hasSubalternesAffectes = \App\ProtoControllers\Utilisateur::putListAssociationPlanning($subalternesSelectionnes, $id);
            if (!$hasSubalternesAffectes) {
                return false;
            }
        }

        return $id;
    }

    /**
     * Retourne la liste des utilisateurs associés au planning disponibles pour le responsable
     *
     * @param int $idPlanning
     *
     * @return array ['login', 'nom', 'prenom', 'planningId']
     */
    public static function getListeUtilisateursAssocies($idPlanning)
    {
        $utilisateursAssocies = parent::getListeUtilisateursAssocies($idPlanning);

        $subalternes = \App\ProtoControllers\Responsable::getUsersRespDirect($_SESSION['userlogin']);

        return $utilisateursAssocies = array_filter(
            $utilisateursAssocies,
            function ($utilisateurs) use ($subalternes) {
                return in_array($utilisateurs['login'], $subalternes);
            }
        );
    }

    /**
     * Vérifie qu'un planning est visible dans l'application au sens du responsable
     *
     * {@inheritDoc}
     */
    public static function isVisible($id)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        return $config->canResponsablesAssociatePlanning() && parent::isVisible($id);
    }

}
