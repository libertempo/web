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
        $utilisateurs = \App\ProtoControllers\Utilisateur::getListByPlanning($id);
        foreach ($utilisateurs as $utilisateur) {
            if (\App\ProtoControllers\Utilisateur::hasSortiesEnCours($utilisateur['u_login'])) {
                $errors['Planning'] = _('demande_en_cours_sur_planning');
                return NIL_INT;
            }
        }

        $subalternes =  \App\ProtoControllers\Responsable::getUsersRespDirect($_SESSION['userlogin']);
        \App\ProtoControllers\Utilisateur::deleteListAssociationPlanning($id, $subalternes);
        $utilisateursAssocies = array_intersect($put['utilisateurs'], $subalternes);
        if (!empty($utilisateursAssocies)) {
            // on ne peut pas supprimer par erreur des employés associés && en cours
            // Vu qu'on ne peut pas modifier le planning
            $hasUtilisateursAffectes = \App\ProtoControllers\Utilisateur::putListAssociationPlanning($utilisateursAssocies, $id);
            if (!$hasUtilisateursAffectes) {
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
        return \App\ProtoControllers\Responsable::canAssociatePLanning() && parent::isVisible($id);
    }

}
