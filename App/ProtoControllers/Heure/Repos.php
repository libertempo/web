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
class Repos extends \App\ProtoControllers\Heure
{
    /**
     * {@inheritDoc}
     */
    public function getForm($id = NIL_INT)
    {
        $return     = '';
        $errorsLst  = [];
        $valueJour  = date('d/m/Y');
        $valueDebut = '';
        $valueFin   = '';

        if (!empty($_POST)) {
            if (0 >= (int) $this->post($_POST, $errorsLst)) {
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
                $valueJour  = $_POST['jour'];
                $valueDebut = $_POST['debut_heure'];
                $valueFin   = $_POST['fin_heure'];
            } else {
                log_action(0, 'demande', '', 'Nouvelle demande d\'heure de repos enregistrée');
                redirect(ROOT_PATH . 'utilisateur/user_index.php?session='. session_id() . '&onglet=liste_heure_repos', false);
            }
        }

        if (NIL_INT !== $id) {
            $return .= '<h1>' . _('user_modif_heure_repos_titre') . '</h1>';
        } else {
            $return .= '<h1>' . _('user_ajout_heure_repos_titre') . '</h1>';
        }

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

        $childTable = '';

        if (NIL_INT !== $id) {
            $sql   = 'SELECT * FROM conges_heure_repos WHERE id_heure = ' . $id;
            $query = \includes\SQL::query($sql);
            $data = $query->fetch_array();
            $valueJour  = date('d/m/Y', $data['debut']);
            $valueDebut = date('H\:i', $data['debut']);
            $valueFin   = date('H\:i', $data['fin']);

            $childTable .= '<input type="hidden" name="id_heure" value="' . $id . '" /><input type="hidden" name="_METHOD" value="PUT" />';
        }

        $debutId = uniqid();
        $finId   = uniqid();

        $childTable .= '<thead><tr><th width="20%">' . _('Jour') . '</th><th>' . _('creneau') . '</th></tr></thead><tbody>';
        $childTable .= '<tr><td><input class="form-control date" type="text" value="' . $valueJour . '" name="jour"></td>';
        $childTable .= '<td><div class="form-inline col-xs-3"><input class="form-control" style="width:45%" type="text" id="' . $debutId . '"  value="' . $valueDebut . '" name="debut_heure">&nbsp;<i class="fa fa-caret-right"></i>&nbsp;<input class="form-control" style="width:45%" type="text" id="' . $finId . '"  value="' . $valueFin . '" name="fin_heure"></div></td></tr>';
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
     * {@inheritDoc}
     */
    protected function put(array $put, array &$errorsLst, $user)
    {
        if (!$this->hasErreurs($put, $errorsLst, $put['id_heure'])) {
            $id = $this->update($put, $user, $put['id_heure']);
            log_action($put['id_heure'], 'modif', '', 'Modification demande d\'heure de repos ' . $put['id_heure']);

            return $id;
        }

        return NIL_INT;
    }

    /**
     * Supprime une demande d'heures
     *
     * @param int $id
     *
     * @return int
     */
    private function delete($id, $user, array &$errorsLst)
    {
        if (NIL_INT !== \utilisateur\Fonctions::deleteSQLDemandeDebitCongesHeure($id, $user, $errorsLst)) {
            log_action($id, 'annul', '', 'Annulation de la demande d\'heure ' . $id);
            return $id;
        }
        return NIL_INT;
    }

    /**
     * {@inheritDoc}
     */
    public function getListe()
    {
        $return = '<h1>' . _('user_liste_heure_repos') . '</h1>';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead><tr><th>jour</th><th>debut</th><th>fin</th><th>durée</th><th>statut</th><th></th></tr></thead><tbody>';
        $session = session_id();
        $listId = $this->getListeId();
        if (empty($listId)) {
            $childTable .= '<tr><td colspan=6><center>' . _('aucun_resultat') . '</center></td></tr>';
        } else {
            $listeRepos = $this->getListeSQL($listId);
            foreach ($listeRepos as $repos) {
                $jour   = date('d/m/Y', $repos['debut']);
                $debut  = date('H\:i', $repos['debut']);
                $fin    = date('H\:i', $repos['fin']);
                $duree  = date('H\:i', $repos['duree']);
                $statut = Heure::statusText($repos['statut']);
                $childTable .= '<tr><td>' . $jour . '</td><td>' . $debut . '</td><td>' . $fin . '</td><td>' . $duree . '</td><td>' . $statut . '</td><td><a  title="' . _('form_modif') . '" href="user_index.php?onglet=modif_heure_repos&id=' . $repos['id_heure'] .
                                '&session=' . $session . '"><i class="fa fa-pencil"></i></a></td></tr>';
            }
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        return $return;
    }

    /**
     * @return array
     */
    private function getListeId()
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM conges_heure_repos';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * @return array
     */
    private function getListeSQL(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_heure_repos
                WHERE id_heure IN (' . implode(',', $listId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }

    /*
     * SQL
     */

    /**
     * {@inheritDoc}
     */
    protected function isChevauchement($jour, $heureDebut, $heureFin, $id, $user)
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
     * {@inheritDoc}
     */
    protected function insert(array $post, $user)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($post['jour']);
        $timestampDebut = strtotime($jour . ' ' . $post['debut_heure']);
        $timestampFin   = strtotime($jour . ' ' . $post['fin_heure']);
        /* TODO: Toute la partie du check d'erreur et du comptage réel des heures devrait être dans le modèle.
        C'est lui qui devrait remplir le DAO à partir de ses attributs pour l'insertion
        */
        $duree = $this->countDuree($timestampDebut, $timestampFin);
        $sql = \includes\SQL::singleton();
        $req = 'INSERT INTO conges_heure_repos (id_heure, login, debut, fin, duree, statut) VALUES
        (NULL, "' . $user . '", ' . (int) $timestampDebut . ', '. (int) $timestampFin .', '. (int) $duree . ', ' . Heure::STATUT_DEMANDE . ')';
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * {@inheritDoc}
     */
    protected function update(array $put, $user, $id)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($put['jour']);
        $timestampDebut = strtotime($jour . ' ' . $put['debut_heure']);
        $timestampFin   = strtotime($jour . ' ' . $put['fin_heure']);
        $duree = \utilisateur\Fonctions::compter_heures($timestampDebut, $timestampFin);
        $sql   = \includes\SQL::singleton();
        $toInsert = [];
        $req   = 'UPDATE conges_heure_repos
                SET debut = ' . $timestampDebut . ',
                    fin = ' . $timestampFin . ',
                    duree = ' . $duree . '
                WHERE id_heure = '. (int) $id . '
                AND login = "' . $user . '"';
        $query = $sql->query($req);

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    protected function countDuree($debut, $fin)
    {
        return $fin - $debut;
    }
}
