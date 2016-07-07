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
namespace App\ProtoControllers\Responsable\Traitement;

use App\Models\AHeure;

/**
 * ProtoContrôleur de validation d'heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Repos extends \App\ProtoControllers\Responsable\ATraitement
{
    /**
     * {@inheritDoc}
     */
    protected function put(array $put, $resp, &$notice, array &$errorLst)
    {
        foreach ($put['demande'] as $id_heure => $statut){
            $infoDemande = $this->getListeSQL(explode(" ", $id_heure));
            if($this->isDemandeTraitable($infoDemande[0]['statut'], $statut)) {
                if( ($this->isRespDeUser($resp, $infoDemande[0]['login']) || $this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['login']))) && $statut == 'STATUT_REFUS') {
                    $id = $this->updateStatut($id_heure, \App\Models\AHeure::STATUT_REFUS);
                    log_action(0, '', '', 'Refus de la demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[0]['login']);
                } elseif( (($this->isRespDeUser($resp, $infoDemande[0]['login']) && !$this->isDoubleValGroupe($infoDemande[0]['login'])) || ($this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['login'])) && $this->isDoubleValGroupe($infoDemande[0]['login']))) && $statut == 'STATUT_OK' ) {
                        $id = $this->demandeOk($id_heure, \App\Models\AHeure::STATUT_OK);
                        log_action(0, '', '', 'Validation de la demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[0]['login']);
                } elseif($this->isRespDeUser($resp, $infoDemande[0]['login']) && $this->isDoubleValGroupe($infoDemande[0]['login']) && $statut == 'STATUT_OK' ) {
                        $id = $this->updateStatut($id_heure, \App\Models\AHeure::STATUT_VALIDE);
                        log_action(0, '', '', 'Demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[0]['login'] . ' transmise au grand responsable');
                } elseif($statut != "NULL") {
                    $errorLst[] = _('traitement_non_autorise').': '.$infoDemande[0]['login'];
                }
            } else {
                $errorLst[] = _('demande_deja_traite');
            }
        }
        $notice = _('traitement_effectue');
        return NIL_INT;
    }

    /**
     * Mise a jour du statut de la demande d'heure
     * 
     * @param int $demande
     * @param int $statut
     * 
     * @return int $id 
     */
    protected function updateStatut($demande, $statut)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_repos
                SET statut = ' . $statut . '
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
    }

    /**
     * Mise a jour du solde du demandeur
     * 
     * @param int $demande
     * 
     * @return int $demande
     */
    protected function updateSolde($demande)
    {
        $user = $this->getListeSQL(explode(" ",$demande));
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_users
                SET u_heure_solde = u_heure_solde-' .$user[0]['duree'] . '
                WHERE u_login = \''. $user[0]['login'] .'\'';
        $query = $sql->query($req);

        return $demande;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        $return     = '';
        $notice = '';
        $errorsLst  = [];
        $i = true;

        if (!empty($_POST)) {
            if (0 >= (int) $this->post($_POST, $notice, $errorsLst)) {
                $errors = '';
                if (!empty($errorsLst)) {
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $return .= '<div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                } elseif(!empty($notice)) {
                    $return .= '<div class="alert alert-info">' .  $notice . '.</div>';

                }
            } else {

                //redirect(ROOT_PATH . 'responsable/resp_index.php?session='. session_id() . '&onglet=traitement_demandes', false);
            }
        }

        $return .= '<h1>' . _('traitement_heure_repos_titre') . '</h1>';
        $return .= '<form action="" method="post" class="form-group">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        $childTable = '<thead><tr><th width="20%">' . _('nom') . '</th><th>' . _('solde') . '</th><th>' . _('jour') . '</th><th>' . _('debut') . '</th><th>' . _('fin') . '</th><th>' . _('duree') . '</th><th>' . _('accept') . '</th><th>' . _('refus') . '</th><th>' . _('attente') . '</th></tr></thead><tbody>';

        $demandesResp = $this->getDemandesResp($_SESSION['userlogin']);
        $demandesGrandResp = $this->getDemandesGrandResp($_SESSION['userlogin']);
        if (empty($demandesResp) && empty($demandesGrandResp) ) {
            $childTable .= '<tr><td colspan="6"><center>' . _('aucun_resultat') . '</center></td></tr>';
        } else {
            if(!empty($demandesResp)) {
                $childTable .= $this->getDemandesTab($demandesResp);
            }
            if (!empty($demandesGrandResp)) {
                $childTable .= $this->getDemandesTab($demandesGrandResp);

            }
        }

        $childTable .= '</tbody>';

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
    protected function getDemandesRespId($resp)
    {
        $groupId = []; 
        $groupId = $this->getGroupeRespId($resp);
        if (empty($groupId)) {
            return [];
        }

        $usersResp = [];
        $usersResp = $this->getUsersGroupe($groupId);
        if (empty($usersResp)) {
            return [];
        }

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE login IN (\'' . implode(',', $usersResp) . '\')
                AND statut = '.AHeure::STATUT_DEMANDE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }
        return $ids;
    }

     /**
      * {@inheritDoc}
      */
    protected function getDemandesGrandRespId($gResp)
    {
        $groupId = $this->getGroupeGrandRespId($gResp);
        if (empty($groupId)) {
            return [];
        }
        
        $usersResp = $this->getUsersGroupe($groupId);
        if (empty($usersResp)) {
            return [];
        }
        
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE login IN (\'' . implode(',', $usersResp) . '\')
                AND statut = '.AHeure::STATUT_VALIDE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }
        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    protected function getListeSQL(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos
                WHERE id_heure IN (' . implode(',', $listId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
