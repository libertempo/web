<?php
namespace App\ProtoControllers\HautResponsable;

/**
 * ProtoContrôleur de planning de haut responsable, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Planning extends \App\ProtoControllers\APlanning
{
    /**
     * Poste un nouveau planning
     *
     * @param array $post
     * @param array &$errors
     * @param string $notice
     *
     * @return int
     */
    public static function postPlanning(array $post, array &$errors, &$notice)
    {
        /* On fait la distinction entre les verbes post / put (début REST) */
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    return static::deletePlanning($post['planning_id'], $errors, $notice);
                    break;
                case 'PUT':
                    if (!empty($post['planning_id']) && 0 < (int) $post['planning_id']) {
                        return static::putPlanning($post['planning_id'], $post, $errors);
                    }
                    break;
                case 'PATCH':
                    if (!empty($post['planning_id']) && 0 < (int) $post['planning_id']) {
                        return static::patchPlanning($post['planning_id'], $post);
                    }
                    break;
            }
        } else {
            if (empty($post['name'])) {
                $errors['Nom'] = _('champ_necessaire');
                return NIL_INT;
            }
            if (static::existPlanningName($post['name'])) {
                $errors['Nom'] = _('nom_existe_deja');
                return NIL_INT;
            }

            $sql = \includes\SQL::singleton();
            $sql->getPdoObj()->begin_transaction();
            $rollback = false;

            $idPlanning = static::insertPlanning($post);
            if (0 < $idPlanning) {
                $rollback = !static::setDependencies($idPlanning, $post, $errors);
            } else {
                $rollback = true;
            }

            if ($rollback) {
                $sql->getPdoObj()->rollback();
                return NIL_INT;
            }
            $sql->getPdoObj()->commit();

            return $idPlanning;
        }
    }

    /**
     * Met à jour un planning
     *
     * @param int   $id
     * @param array $put
     * @param array &$errors
     *
     * @return int
     */
    private static function putPlanning($id, array $put, array &$errors)
    {
        $id = (int) $id;
        if (empty($put['name'])) {
            $errors['Nom'] = _('champ_necessaire');
            return NIL_INT;
        }
        if (static::existPlanningName($put['name'], $id)) {
            $errors['Nom'] = _('nom_existe_deja');
            return NIL_INT;
        }

        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $rollback = false;

        $idPlanning = static::updatePlanning($id, $put);
        if (0 < $idPlanning) {
            $rollback = !static::setDependencies($idPlanning, $put, $errors);
        } else {
            $rollback = true;
        }

        if ($rollback) {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
        $sql->getPdoObj()->commit();

        return $idPlanning;
    }

    /**
     * Tente de définir les dépendances d'un planning
     *
     * @param int $idPlanning
     * @param array $put
     * @param array &$errors Erreurs à remonter
     *
     * @return bool False, en cas d'échec
     */
    private static function setDependencies($idPlanning, array $put, array &$errors)
    {
        $idPlanning = (int) $idPlanning;
        Planning\Creneau::deleteCreneauList($idPlanning);
        if (!empty($put['creneaux'])) {
            $idLastCreneau = Planning\Creneau::postCreneauxList($put['creneaux'], $idPlanning, $errors);
            if (0 >= $idLastCreneau) {
                return false;
            }
        }

        $subalternesAvecPlanning = array_map(function (array $u) {
            return $u['u_login'];
        }, \App\ProtoControllers\Utilisateur::getListByPlanning($idPlanning));
        $subalternesSansSortie = [];
        foreach ($subalternesAvecPlanning as $u) {
            if (!\App\ProtoControllers\Utilisateur::hasSortiesEnCours($u)) {
                $subalternesSansSortie[] = $u;
            }
        }

        if (!empty($subalternesSansSortie)) {
            \App\ProtoControllers\Utilisateur::deleteListAssociationPlanning($idPlanning, $subalternesSansSortie);
        }

        if (empty($put['utilisateurs'])) {
            return true;
        }

        $subalternesSelectionnes = !empty($subalternesSansSortie)
            ? array_intersect($put['utilisateurs'], $subalternesSansSortie)
            : $put['utilisateurs'];

        if (!empty($subalternesSelectionnes)) {
            $hasSubalternesAffectes = \App\ProtoControllers\Utilisateur::putListAssociationPlanning($subalternesSelectionnes, $idPlanning);
            if (!$hasSubalternesAffectes) {
                return false;
            }
        }

        return true;
    }

    /**
     * Supprime un planning
     *
     * @param int   $id
     * @param array &$errors
     * @param string $notice
     *
     * @return int
     */
    private static function deletePlanning($id, array &$errors, &$notice)
    {
        $id = (int) $id;
        // si planning inexistant ou faisant partie des non supprimable
        if (!static::isDeletable($id)) {
            $errors[] = _('planning_non_supprimable');
            return NIL_INT;
        }

        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $res = static::deleteSql($id);
        Planning\Creneau::deleteCreneauList($id);
        if (0 < $res) {
            $notice = _('planning_supprime');
            $sql->getPdoObj()->commit();
            return $res;
        } else {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
    }

    /**
     * Patche un planning
     *
     * @param int   $id
     * @param array $patch
     *
     * @return int
     */
    private static function patchPlanning($id, array $patch)
    {
        return self::patchSql($id, $patch);
    }

    /**
     * Retourne la liste des utilisateurs associés au planning disponibles pour le haut responsable
     *
     * {@inheritDoc}
     */
    public static function getListeUtilisateursAssocies($idPlanning)
    {
        return parent::getListeUtilisateursAssocies($idPlanning);
    }

    /*
     * SQL
     */

    /**
     * Retourne la liste des id de planning
     *
     * @return array
     */
    public static function getListPlanningId()
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT planning_id AS id
                FROM planning
                WHERE status = ' . \App\Models\Planning::STATUS_ACTIVE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne la liste des plannings
     *
     * @param array $listId
     *
     * @return array
     */
    public static function getListPlanning(array $listId)
    {
        if (empty($listId)) {
            return [];
        }

        $listId = array_map('intval', $listId);
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM planning
                WHERE planning_id IN (' . implode(',', $listId) . ')
                ORDER BY planning_id DESC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retourne la liste des id de planning utilisés
     *
     * @param array $listId
     *
     * @return array
     */
    public static function getListPlanningUsed(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $listId = array_map('intval', $listId);
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT planning_id AS id
                FROM conges_users
                WHERE planning_id IN (' . implode(',', $listId) . ')';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Vérifie l'existence d'un planning par son nom
     *
     * @param string $name
     * @param int    $idAuthorized Id autorisé à outrepasser la vérification
     *
     * @return bool
     */
    private static function existPlanningName($name, $idAuthorized = NIL_INT)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT planning_id
                    FROM planning
                    WHERE name = "' . htmlspecialchars($sql->quote($name)) . '"
                      AND status = ' . \App\Models\Planning::STATUS_ACTIVE . '
                      AND planning_id != ' . (int) $idAuthorized . '
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Insère un planning en base
     *
     * @param array $planning
     *
     * @return int
     */
    private static function insertPlanning(array $planning)
    {
        $sql   = \includes\SQL::singleton();
        $req   = 'INSERT INTO planning (planning_id, name, status)
                  VALUES (null, "' . htmlspecialchars($sql->quote($planning['name'])) . '", ' . \App\Models\Planning::STATUS_ACTIVE . ')';
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * Met à jour un planning en base
     *
     * @param int   $id
     * @param array $put
     *
     * @return int
     */
    private static function updatePlanning($id, array $put)
    {
        $sql = \includes\SQL::singleton();
        $req = 'UPDATE planning
                SET name = "' . htmlspecialchars($sql->quote($put['name'])) . '"
                WHERE planning_id = ' . (int) $id;
        $sql->query($req);

        return $id;
    }

    /**
     * Vérifie qu'un planning est supprimable
     *
     * @param int $id
     *
     * @return bool
     */
    private static function isDeletable($id)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT planning_id
                    FROM conges_users
                    WHERE planning_id = ' . (int) $id . '
                )';
        $query = $sql->query($req);

        return 0 >= (int) $query->fetch_array()[0];
    }

    /**
     * Vérifie qu'un planning est visible dans l'application au sens du RH
     *
     * {@inheritDoc}
     */
    public static function isVisible($id)
    {
        return parent::isVisible($id);
    }

    /**
     * Supprime un planning de la base
     *
     * @param int $id
     *
     * @return int
     */
    private static function deleteSql($id)
    {
        $sql = \includes\SQL::singleton();
        $req = 'UPDATE planning
                SET status = ' . \App\Models\Planning::STATUS_DELETED . '
                WHERE planning_id = ' . (int) $id . '
                LIMIT 1';
        $sql->query($req);

        return 0 < $sql->affected_rows ? $id : NIL_INT;
    }

    /**
     * Met à jour le statut d'un planning en base
     *
     * @param int $id
     * @param array $patch
     *
     * @return int
     * @TODO : j'aime pas cette méthode, à supprimer dès que possible
     * @since 1.9
     */
    private static function patchSql($id, array $patch)
    {
        $sql = \includes\SQL::singleton();
        $req = 'UPDATE planning
                SET status = ' . (int) $patch['status'] . '
                WHERE planning_id = ' . $id;
        $sql->query($req);

        return 0 < $sql->affected_rows ? $id : NIL_INT;
    }
}
