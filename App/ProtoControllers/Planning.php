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
namespace App\ProtoControllers;

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
     *
     * @return int
     */
    public static function postPlanning(array $post, array &$errors)
    {
        /* On fait la distinction entre les verbes post / put (début REST) */
        if (!empty($post['_METHOD'])) {
            if ('DELETE' === $post['_METHOD']) {
                return \App\ProtoControllers\Planning::deletePlanning($post['planning_id'], $errors);

            } elseif ('PUT' === $post['_METHOD'] && !empty($post['planning_id']) && 0 < (int) $post['planning_id']) {
                return \App\ProtoControllers\Planning::putPlanning($post['planning_id'], $post, $errors);
            }
        } else {
            if (empty($post['planning_name'])) {
                $errors['Nom'] = _('champ doit etre rempli');
            }

            if (!empty($post['creneaux'])) {
                $sql = \includes\SQL::singleton();
                $sql->getPdoObj()->begin_transaction();
                $idPlanning = \App\ProtoControllers\Planning::insertPlanning($post);
                \App\ProtoControllers\Creneau::deleteCreneauList($idPlanning);
                $idLastCreneau = \App\ProtoControllers\Creneau::postCreneauxList($post['creneaux'], $idPlanning, $errors);
                if (0 < $idPlanning && 0 < $idLastCreneau) {
                    $sql->getPdoObj()->commit();
                } else {
                    $sql->getPdoObj()->rollback();
                    return NIL_INT;
                }
            } else {
                $idPlanning = \App\ProtoControllers\Planning::insertPlanning($post);
                \App\ProtoControllers\Creneau::deleteCreneauList($idPlanning);
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
        if (empty($post['planning_name'])) {
            $errors['Nom'] = _('champ doit etre rempli');
        }
        if (!empty($put['creneaux'])) {
            $sql = \includes\SQL::singleton();
            $sql->getPdoObj()->begin_transaction();
            $idPlanning = \App\ProtoControllers\Planning::updatePlanning($id, $put);
            \App\ProtoControllers\Creneau::deleteCreneauList($idPlanning);
            if (0 < $idPlanning) {
                $idLastCreneau = \App\ProtoControllers\Creneau::postCreneauxList($put['creneaux'], $idPlanning, $errors);
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
            $idPlanning = \App\ProtoControllers\Planning::updatePlanning($id, $put);
        }

        return $idPlanning;
    }

    /**
     * Supprime un planning
     *
     * @param int   $id
     * @param array &$errors
     *
     * @return int
     */
    private static function deletePlanning($id, array &$errors)
    {
        // si planning inexistant ou faisant partie des non supprimable
        if (!\App\ProtoControllers\Planning::isDeletable($id)) {
            $errors[] = _('planning_non_supprimable');
            return NIL_INT;
        }

        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $res = \App\ProtoControllers\Planning::deleteSql($id);
        \App\ProtoControllers\Creneau::deleteCreneauList($id);
        if (0 < $res) {
            $sql->getPdoObj()->commit();
            return $res;
        } else {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
    }

    /*
     * SQL
     *
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
     * Insère un planning en base
     *
     * @param array $planning
     *
     * @return int
     */
    private static function insertPlanning(array $planning)
    {
        $sql   = \includes\SQL::singleton();
        $req   = 'INSERT INTO conges_planning (planning_id, planning_name)
                  VALUES ("", "' . htmlspecialchars($sql->quote($planning['planning_name'])) . '")';
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
        $req = 'UPDATE conges_planning
                SET planning_name = "' . htmlspecialchars($sql->quote($put['planning_name'])) . '"
                WHERE planning_id = ' . $id;
        $sql->query($req);

        return 0 < $sql->affected_rows ? $id : NIL_INT;
    }

    /**
     * Supprime un planning de la base
     *
     * @param int   $id
     *
     * @return int
     */
    private static function deleteSql($id)
    {
        $sql = \includes\SQL::singleton();
        $req = 'DELETE FROM conges_planning
                WHERE planning_id = ' . $id . '
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
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_planning
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
                FROM conges_planning';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }
}
