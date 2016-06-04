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
namespace App\ProtoControllers\Heure;

use \App\Models\Heure;

/**
 * ProtoContrôleur d'heures de repos, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Repos
{
    /**
     * Encapsule le comportement du formulaire d'édition d'heures de repos
     *
     * @return string
     * @access public
     * @static
     */
    public static function getForm()
    {
        $return    = '';
        $errorsLst = [];

        if (!empty($_POST)) {
            if (0 >= (int) static::post($_POST, $errorsLst)) {
                $errors = '';
                if (!empty($errorsLst)) {
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $return .= '<div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                }
            } else {
                // TODO: log et redirect
                // ex : log_action($id, 'demande', '', 'Nouvelle demande d\'heure enregistrée : '. $id);
            }
        }

        $return .= '<h1>' . _('user_ajout_heure_repos_titre') . '</h1>';
        // TODO: with modif


        /* Génération du datePicker et de ses options */
        $daysOfWeekDisabled = \utilisateur\Fonctions::getDatePickerDaysOfWeekDisabled();
        $datesFeries        = \utilisateur\Fonctions::getDatePickerJoursFeries();
        $datesFerme         = \utilisateur\Fonctions::getDatePickerFermeture();
        $datesDisabled      = array_merge($datesFeries,$datesFerme);
        $startDate          = \utilisateur\Fonctions::getDatePickerStartDate();

        $datePickerOpts = [
            'daysOfWeekDisabled' => $daysOfWeekDisabled,
            'datesDisabled'      => $datesDisabled,
            'startDate'          => $startDate,
        ];
        $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ', false);</script>';
        $return .= '<form action="" method="post" class="form-group">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);

        $debutId = uniqid();
        $finId   = uniqid();

        $childTable = '<thead><tr><th width="20%">' . _('Jour') . '</th><th>' . _('creneau') . '</th></tr></thead><tbody>';
        $childTable .= '<tr><td><input class="form-control date" type="text" value="'.date("d/m/Y").'" name="new_jour"></td>';
        $childTable .= '<td><div class="form-inline col-xs-3"><input class="form-control" style="width:45%" type="text" id="' . $debutId . '"  value="" name="new_deb_heure">&nbsp;<i class="fa fa-caret-right"></i>&nbsp;<input class="form-control" style="width:45%" type="text" id="' . $finId . '"  value="" name="new_fin_heure"></div></td></tr>';
        $childTable .= '</tbody>';
        $childTable .= '<script type="text/javascript">generateTimePicker("' . $debutId . '");generateTimePicker("' . $finId . '");</script>';

        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<div class="form-group"><input type="submit" class="btn btn-success" value="' . _('form_submit') . '" /></div>';
        $return .='</form>';

        return $return;
    }

    /**
     * Traite la demande/modification/suppression
     *
     * @param array $post
     * @param array &$errorsLst
     *
     * @return int
     */
    private static function post(array $post, array &$errorsLst)
    {
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    return static::delete($post['id_heure'], $_SESSION['userlogin'], $errorsLst);
                    break;
                case 'PUT':
                    return \utilisateur\Fonctions::putDemandeDebitCongesHeure($post, $errorsLst, $_SESSION['userlogin']);
                    break;
            }
        } else {
            if (static::hasErreurs($post, $errorsLst)) {
                $id = static::insert($post, $_SESSION['userlogin']);
                if (0 < $id) {

                    return $id;
                }
            }

            return NIL_INT;
        }
    }

    /**
     * Supprime une demande d'heures
     *
     * @param int $id
     *
     * @return int
     */
    private static function delete($id, $user, array &$errorsLst)
    {
        if (NIL_INT !== \utilisateur\Fonctions::deleteSQLDemandeDebitCongesHeure($id, $user, $errorsLst)) {
            log_action($id, 'annul', '', 'Annulation de la demande d\'heure ' . $id);
            return $id;
        }
        return NIL_INT;
    }

    /**
     * Contrôle l'éligibilité d'une demande d'heures
     *
     * @param array  $post
     * @param array  &$errorsLst
     * @param int    $id
     *
     * @return bool
     */
    private static function hasErreurs(array $post, array &$errorsLst, $id = NIL_INT)
    {
        $localErrors = [];

        /* Syntaxique : champs requis et format */
        if (empty($post['new_jour'])) {
            $localErrors['Jour'] = _('champ_necessaire');
        }
        if (empty($post['new_deb_heure'])) {
            $localErrors['Heure de début'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['new_deb_heure'])) {
            $localErrors['Heure de début'] = _('Format_heure_incorrect');
        }
        if (empty($post['new_fin_heure'])) {
            $localErrors['Heure de fin'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['new_fin_heure'])) {
            $localErrors['Heure de fin'] = _('Format_heure_incorrect');
        }
        if (!empty($localErrors)) {
            $errorsLst = array_merge($errorsLst, $localErrors);

            return empty($localErrors);
        }

        /* Sémantique : sens de prise d'heure */
        if (NIL_INT !== strnatcmp($post['new_deb_heure'], $post['new_fin_heure'])) {
            $localErrors['Heure de début / Heure de fin'] = _('verif_saisie_erreur_heure_fin_avant_debut');
        }
        if (static::isChevauchement($post['new_jour'], $post['new_deb_heure'], $post['new_fin_heure'], $id, $_SESSION['userlogin'])) {
            $localErrors['Cohérence'] = _('Chevauchement_heure_avec_existant');
        }

        $errorsLst = array_merge($errorsLst, $localErrors);

        return empty($localErrors);
    }

    /**
     * Liste des heures
     *
     * @return string
     */
    public static function getListe()
    {
        return '<h1>hello world</h1>';

        // order by debut DESC AND statut ASC
    }

    /*
     * SQL
     */

    /**
     * Vérifie le chevauchement entre les heures demandées et l'existant
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param int    $id
     * @param string $user
     *
     * @return bool
     */
    private static function isChevauchement($jour, $heureDebut, $heureFin, $id, $user)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($jour);
        $timestampDebut = strtotime($jour . ' ' . $heureDebut);
        $timestampFin   = strtotime($jour . ' ' . $heureFin);

        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (SELECT statut
                FROM conges_heure_repos
                WHERE login = "' . $user . '"
                    AND (statut != ' . Heure::STATUT_REFUS . '
                        OR statut != ' . Heure::STATUT_ANNUL . '
                    )
                    AND (debut <= ' . $timestampFin . ' AND fin >= ' . $timestampDebut . ')';
        if (NIL_INT !== $id) {
            $req .= ' AND id_heure !=' . $id;
        }
        $req .= ')';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Ajoute une demande d'heures dans la BDD
     *
     * @param array  $post
     * @param string $user
     *
     * @return int
     */
    private static function insert(array $post, $user)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($post['new_jour']);
        $timestampDebut = strtotime($jour . ' ' . $post['new_deb_heure']);
        $timestampFin   = strtotime($jour . ' ' . $post['new_fin_heure']);
        /* TODO: Toute la partie du check d'erreur et du comptage réel des heures devrait être dans le modèle.
        C'est lui qui devrait remplir le DAO à partir de ses attributs pour l'insertion
        */
        $duree = static::compterHeures($timestampDebut, $timestampFin);
        $sql = \includes\SQL::singleton();
        $req = 'INSERT INTO conges_heure_repos (id_heure, login, debut, fin, duree, statut) VALUES
        (NULL, "' . $user . '", ' . (int) $timestampDebut . ', '. (int) $timestampFin .', '. (int) $duree . ', ' . Heure::STATUT_DEMANDE . ')';
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    private static function compterHeures($debut, $fin)
    {
        return $fin - $debut;
    }
}
