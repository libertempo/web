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
 * ProtoContrôleur de créneau, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Creneau
{
    /**
     * Poste une liste de créneaux de planning
     *
     * @param array $post
     * @param int   $idPlanning
     * @param array &$errors
     *
     * @return int
     */
    public static function postCreneauxList(array $post, $idPlanning, array &$errors)
    {
        foreach ($post as $typeSemaine => $jours) {
            foreach ($jours as $jourId => $periodes) {
                if (!\App\ProtoControllers\Creneau::verifieCoherenceCreneaux($periodes, $errors)) {
                    return NIL_INT;
                }
            }
        }

        return \App\ProtoControllers\Creneau::insertCreneauList($post, $idPlanning);
    }

    /**
     * S'assure que les données passées aient un sens
     *
     * @param array $periodes
     * @param array &$errors
     *
     * @return bool
     */
    private static function verifieCoherenceCreneaux(array $periodes, array &$errors)
    {
        $localError = [];
        $precedentsCreneauxJours = [];
        $pattern = '/^(([01]?[0-9])|(2[0-3])):[0-5][0-9]$/';
        foreach ($periodes as $typeCreneau => $creneaux) {
            foreach ($creneaux as $creneauxJour) {
                $debut = $creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_DEBUT];
                $fin   = $creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_FIN];
                if (-1 !== strnatcmp($debut, $fin)) {
                    $localError['Créneaux de travail'][] = _('date_fin_superieure_date_debut');
                }
                if (!preg_match($pattern, $debut) || !preg_match($pattern, $fin))  {
                    $localError['Créneaux de travail'][] = _('format_heure_incorrect');
                }
                if (!empty($precedentsCreneauxJours) && 1 !== strnatcmp($debut, $precedentsCreneauxJours[\App\Models\Planning\Creneau::TYPE_HEURE_FIN])) {
                    $localError['Créneaux de travail'][] = _('creneaux_consecutifs');
                }
                $precedentsCreneauxJours = $creneauxJour;
            }
        }
        $errors = array_merge($errors, $localError);

        return empty($localError);
    }

    /**
     * Organise les Creneaux selon leurs critères de groupement à partir des infos de l'utilisateur
     *
     * @param array $list
     *
     * @return array
     */
    private static function groupCreneauxFromUser(array $list)
    {
        $grouped = [];
        foreach ($list as $jourId => $periodes) {
            foreach ($periodes as $typeCreneau => $creneaux) {
                foreach ($creneaux as $creneauxJour) {
                    $grouped[$jourId][$typeCreneau][] = [
                        \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT => $creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_DEBUT],
                        \App\Models\Planning\Creneau::TYPE_HEURE_FIN => $creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_FIN]
                    ];
                }
            }
        }

        return $grouped;
    }

    /**
     * Organise les Creneaux selon leurs critères de groupement à partir des infos de la BDD
     *
     * @param array $creneaux
     *
     * @return array
     */
    private static function groupCreneauxFromDb(array $creneaux)
    {
        $grouped = [];
        foreach ($creneaux as $creneau) {
            $jourId      = $creneau['jour_id'];
            $typePeriode = $creneau['type_periode'];
            $debut       = date('H\:i', $creneau['debut']);
            $fin         = date('H\:i', $creneau['fin']);

            $grouped[$jourId][$typePeriode][] = [
                \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT => $debut,
                \App\Models\Planning\Creneau::TYPE_HEURE_FIN   => $fin,
            ];
        }

        return $grouped;
    }


    /*
     * SQL
     *
     */


    /**
     * Supprime tous les créneaux d'un planning donné
     *
     * @param int $idPlanning
     *
     * @return bool
     */
    public static function deleteCreneauList($idPlanning)
    {
        $sql = \includes\SQL::singleton();
        $req = 'DELETE FROM conges_planning_creneau WHERE planning_id = ' . (int) $idPlanning;
        $sql->query($req);

        return (bool) $sql->affected_rows;
    }

    /**
     * Insère une liste de créneaux de travail en base de donnée
     *
     * @param array $list
     * @param int   $idPlanning
     *
     * @return int
     */
    private static function insertCreneauList(array $list, $idPlanning)
    {
        $sql = \includes\SQL::singleton();
        $toInsert = [];
        foreach ($list as $typeSemaine => $jours) {
            foreach ($jours as $jourId => $periodes) {
                foreach ($periodes as $typeCreneau => $creneaux) {
                    foreach ($creneaux as $creneauxJour) {
                        $timeDebut = \App\Helpers\Formatter::hour2Time($creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_DEBUT]);
                        $timeFin   = \App\Helpers\Formatter::hour2Time($creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_FIN]);
                        $toInsert[] = '(null, ' . (int) $idPlanning . ', ' . (int) $jourId . ', ' . (int) $typeSemaine . ', ' . (int) $typeCreneau . ', "' . (int) $timeDebut . '", "' . (int) $timeFin . '")';
                    }
                }
            }
        }
        // insertion multiple en fonction de la liste
        $req = 'INSERT INTO conges_planning_creneau (creneau_id, planning_id, jour_id, type_semaine, type_periode, debut, fin) VALUES ' . implode(', ', $toInsert);
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * Retourne les créneaux de travail groupés
     *
     * @param array $post        Totalité des données postées par l'utilisateur
     * @param int   $idPlanning
     * @param int   $typeSemaine
     *
     * @return array
     */
    public static function getCreneauxGroupes(array $post, $idPlanning, $typeSemaine)
    {
        if (!empty($post['creneaux'][$typeSemaine])) {
            return \App\ProtoControllers\Creneau::groupCreneauxFromUser($post['creneaux'][$typeSemaine]);
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_planning_creneau
                WHERE planning_id = ' . (int) $idPlanning . '
                  AND type_semaine = ' . (int) $typeSemaine;
        $res = $sql->query($req);
        if (!$res->num_rows) {
            return [];
        }

        return \App\ProtoControllers\Creneau::groupCreneauxFromDb($res->fetch_all(\MYSQLI_ASSOC));
    }
}
