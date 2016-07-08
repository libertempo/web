<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/
namespace App\ProtoControllers\Responsable;

/**
 * ProtoContrôleur de planning, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Planning
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
                    return \App\ProtoControllers\Responsable\Planning::deletePlanning($post['planning_id'], $errors, $notice);
                    break;
                case 'PUT':
                    if (!empty($post['planning_id']) && 0 < (int) $post['planning_id']) {
                        return \App\ProtoControllers\Responsable\Planning::putPlanning($post['planning_id'], $post, $errors);
                    }
                    break;
                case 'PATCH':
                    if (!empty($post['planning_id']) && 0 < (int) $post['planning_id']) {
                        return \App\ProtoControllers\Responsable\Planning::patchPlanning($post['planning_id'], $post);
                    }
                    break;
            }
        } else {
            if (empty($post['name'])) {
                $errors['Nom'] = _('champ_necessaire');
                return NIL_INT;
            }
            if (\App\ProtoControllers\Responsable\Planning::existPlanningName($post['name'])) {
                $errors['Nom'] = _('nom_existe_deja');
                return NIL_INT;
            }

            if (!empty($post['creneaux'])) {
                $sql = \includes\SQL::singleton();
                $sql->getPdoObj()->begin_transaction();
                $idPlanning = \App\ProtoControllers\Responsable\Planning::insertPlanning($post);
                \App\ProtoControllers\Responsable\Creneau::deleteCreneauList($idPlanning);
                $idLastCreneau = \App\ProtoControllers\Responsable\Creneau::postCreneauxList($post['creneaux'], $idPlanning, $errors);
                if (0 < $idPlanning && 0 < $idLastCreneau) {
                    $sql->getPdoObj()->commit();
                } else {
                    $sql->getPdoObj()->rollback();
                    return NIL_INT;
                }
            } else {
                $idPlanning = \App\ProtoControllers\Responsable\Planning::insertPlanning($post);
                \App\ProtoControllers\Responsable\Creneau::deleteCreneauList($idPlanning);
            }

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
        $utilisateurs = \App\ProtoControllers\Utilisateur::getListByPlanning($id);
        /* TODO: Peut mieux faire */
        foreach ($utilisateurs as $utilisateur) {
            $login = $utilisateur['u_login'];
            if (\App\ProtoControllers\Utilisateur::hasCongesEnCours($login)
                || \App\ProtoControllers\Utilisateur::hasHeureReposEnCours($login)
                || \App\ProtoControllers\Utilisateur::hasHeureAdditionnelleEnCours($login)
            ) {
                $errors['Planning'] = _('demande_en_cours_sur_planning');
                return NIL_INT;
            }
        }
        if (empty($put['name'])) {
            $errors['Nom'] = _('champ_necessaire');
            return NIL_INT;
        }
        if (\App\ProtoControllers\Responsable\Planning::existPlanningName($put['name'], $id)) {
            $errors['Nom'] = _('nom_existe_deja');
            return NIL_INT;
        }

        if (!empty($put['creneaux'])) {
            $sql = \includes\SQL::singleton();
            $sql->getPdoObj()->begin_transaction();
            $idPlanning = \App\ProtoControllers\Responsable\Planning::updatePlanning($id, $put);
            \App\ProtoControllers\Responsable\Creneau::deleteCreneauList($idPlanning);
            if (0 < $idPlanning) {
                $idLastCreneau = \App\ProtoControllers\Responsable\Creneau::postCreneauxList($put['creneaux'], $idPlanning, $errors);
                if (0 < $idLastCreneau) {
                    $sql->getPdoObj()->commit();
                } else {
                    $sql->getPdoObj()->rollback();
                    return NIL_INT;
                }
            } else {
                $sql->getPdoObj()->rollback();
            }
        } else {
            $idPlanning = \App\ProtoControllers\Responsable\Planning::updatePlanning($id, $put);
        }

        return $idPlanning;
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
        // si planning inexistant ou faisant partie des non supprimable
        if (!\App\ProtoControllers\Responsable\Planning::isDeletable($id)) {
            $errors[] = _('planning_non_supprimable');
            return NIL_INT;
        }

        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $res = \App\ProtoControllers\Responsable\Planning::deleteSql($id);
        \App\ProtoControllers\Responsable\Creneau::deleteCreneauList($id);
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
        return \App\ProtoControllers\Responsable\Planning::patchSql($id, $patch);
    }

    /*
     * SQL
     */

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
     * Vérifie qu'un planning est visible dans l'application
     *
     * @param int $id
     *
     * @return bool
     */
    public static function isVisible($id)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT planning_id
                    FROM planning
                    WHERE planning_id = ' . (int) $id . '
                      AND status = ' . \App\Models\Planning::STATUS_ACTIVE . '
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
                WHERE planning_id = ' . $id;
        $sql->query($req);

        return $id;
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
}
