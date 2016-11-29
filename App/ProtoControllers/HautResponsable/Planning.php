<?php
namespace App\ProtoControllers\HautResponsable;

/**
 * ProtoContrôleur de planning, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
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

            if (!empty($post['creneaux'])) {
                $sql = \includes\SQL::singleton();
                $sql->getPdoObj()->begin_transaction();
                $idPlanning = static::insertPlanning($post);
                \App\ProtoControllers\Responsable\Creneau::deleteCreneauList($idPlanning);
                $idLastCreneau = static::postCreneauxList($post['creneaux'], $idPlanning, $errors);
                if (0 < $idPlanning && 0 < $idLastCreneau) {
                    $sql->getPdoObj()->commit();
                } else {
                    $sql->getPdoObj()->rollback();
                    return NIL_INT;
                }
            } else {
                $idPlanning = static::insertPlanning($post);
                static::deleteCreneauList($idPlanning);
            }

            return $idPlanning;
        }
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
}
