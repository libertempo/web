<?php
namespace App\ProtoControllers\Responsable;

/**
 * ProtoContrôleur abstrait d'heures, en attendant la migration vers le MVC REST
 *
 * TODO: On pourrait davantage faire de chose dans la classe abstraite, mais on est empêché par les log. Ça devrait être un sujet d'étude pour l'avenir
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
abstract class ATraitement
{
    /**
     * Encapsule le comportement du formulaire de traitement des demandes d'heures
     *
     * @return string
     * @access public
     */
    abstract public function getForm();

    /**
     * Traite les demandes
     *
     * @param array  $put
     * @param string $resp
     * @param string $notice
     * @param array $errors
     *
     * @return int
     */
    abstract public function put(array $put, $resp, &$notice, array &$errors);


    /**
     * Vérifie et traite une demande en tant que responsable
     * 
     * @param array $infoDemande
     * @param int $statut
     * @param array $put
     * @param array $errors
     * 
     * @return int
     */
    abstract protected function putResponsable(array $infoDemande, $statut, array $put, array &$errors);

    /**
     * Vérifie et traite une demande en tant que grand responsable
     * 
     * @param array $infoDemande
     * @param int $statut
     * @param array $put
     * @param array $errors
     * 
     * @return int
     */
    abstract protected function putGrandResponsable(array $infoDemande, $statut, array $put, array &$errors);

    /**
     * Retourne une liste d'id des demandes pour le responsable
     *
     * @param array $resp
     *
     * @return array $ids
     */
    abstract protected function getIdDemandesResponsable($resp);

    /**
     * Retourne la liste détaillée des demandes
     *
     * @param array $listId
     *
     * @return array $infoDemande
     * 
     */
    abstract protected function getInfoDemandes(array $listId);

    /**
     * Traite les demandes
     *
     * @param array  $post
     * @param string $notice
     * @param array $errorLst 
     *
     * @return int
     */
    protected function post(array $post, &$notice, array &$errorLst)
    {
        if (!empty($post['_METHOD']) && $post['_METHOD'] == "PUT") {
            return $this->put($post, $_SESSION['userlogin'], $notice, $errorLst);
        } else {
            return NIL_INT;
        }
    }

    /**
     * Retourne les informations d'un utilisateur
     *
     * @param string $login
     * 
     * @return string $donnees
     */
    public static function getDonneesUtilisateur($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users 
                WHERE u_login = \''.  \includes\SQL::quote($login).'\'';
        $query = $sql->query($req);
        $donnees = $query->fetch_array();

        return $donnees;
    }

    /**
     * Retourne l'id des groupes d'un responsable
     *
     * @param string $resp
     * 
     * @return array $ids
     */
    public function getIdGroupeResp($resp)
    {
        $ids = [];

        $sql = \includes\SQL::singleton();
        $req = 'SELECT gr_gid AS id FROM `conges_groupe_resp` WHERE gr_login =\''.$resp.'\'';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne l'id des groupes d'un grand responsable
     *
     * @param string $gresp
     * 
     * @return array $ids
     */
    public function getIdGroupeGrandResponsable($gresp)
    {
        $ids=[];
         $sql = \includes\SQL::singleton();
         $req = 'SELECT ggr_gid AS id FROM `conges_groupe_grd_resp` WHERE ggr_login =\''.$gresp.'\'';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $ids[] = (int) $data['id'];
         }

         return $ids;
    }

    /**
     * Retourne le login des membres d'une liste de groupes
     *
     * @param array $groupesId
     * 
     * @return array $users
     */
    public function getUsersGroupe(array $groupesId)
    {
         $sql = \includes\SQL::singleton();
         $req = 'SELECT gu_login FROM `conges_groupe_users` WHERE gu_gid IN (' . implode(',', $groupesId) . ')';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $users[] = $data['gu_login'];
         }

         return $users;
    }
        
    /**
     * Traitement d'une validation avec modification du solde
     * 
     * @param int $demandeId
     * 
     * @return int
     */
    protected function putValidationFinale($demandeId)
    {
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        
        $updateSolde = $this->updateSolde($demandeId);
        $updateStatut = $this->updateStatutValidationFinale($demandeId);
        if (0 < $updateSolde && 0 < $updateStatut) {
            $sql->getPdoObj()->commit();
        } else {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
        return $sql->affected_rows;
    }
    
    /**
     * Retourne les demandes en cours d'un responsable
     * 
     * @param string $resp login du responsable
     * 
     * @return array $demandes
     */
    public function getDemandesResponsable($resp)
    {
        $demandesId = $this->getIdDemandesResponsable($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getInfoDemandes($demandesId);

        return $demandes;
    }
    
    /**
     * Retourne un tableau html des demandes à traiter
     * 
     * @param array $demandes
     * 
     * @return string
     */
    protected function getFormDemandes(array $demandes)
    {
        $i=true;
        $Table='';
        
        foreach ( $demandes as $demande ) {
           $jour   = date('d/m/Y', $demande['debut']);
            $debut  = date('H\:i', $demande['debut']);
            $fin    = date('H\:i', $demande['fin']);
            $duree  = \App\Helpers\Formatter::Timestamp2Duree($demande['duree']);
            $id = $demande['id_heure'];
            $infoUtilisateur = $this->getDonneesUtilisateur($demande['login']);
            $solde = \App\Helpers\Formatter::Timestamp2Duree($infoUtilisateur['u_heure_solde']);
            $Table .= '<tr class="'.($i?'i':'p').'">';
            $Table .= '<td><b>'.$infoUtilisateur['u_nom'].'</b><br>'.$infoUtilisateur['u_prenom'].'</td><td>'.$jour.'</td><td>'.$debut.'</td><td>'.$fin.'</td><td>'.$duree.'</td><td>'.$solde.'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td>' . $demande['comment'] . '</td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="1"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="2"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" max="100"></td></tr>';

            $i = !$i;
            }
            
        return $Table;
    }

    /**
     * Retourne le détail des demandes à traiter en tant que grand responsable
     * 
     * @param string $resp
     * 
     * @return array $demandes
     */
    public function getDemandesGrandResponsable($resp)
    {
        $demandesId = $this->getIdDemandesGrandResponsable($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getInfoDemandes($demandesId);

        return $demandes;
    }

    /**
     * Vérifie si un utilisateur est bien le responsable d'un employé
     * 
     * @param string $resp
     * @param string $user
     * 
     * @return bool
     */
    public function isRespDeUtilisateur($resp, $user) {
        return $this->isRespDirect($resp, $user) || $this->isRespGroupe($resp, $this->getGroupesId($user));
    }
    
    /**
     * Vérifie si un utilisateur est bien le grand responsable d'un employé
     * 
     * @param string $resp
     * @param array $groupesId
     * 
     * @return bool
     */
    public function isGrandRespDeUtilisateur($resp, array $groupesId) {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT ggr_gid
                    FROM conges_groupe_grd_resp
                    WHERE ggr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND ggr_login = "'.\includes\SQL::quote($resp).'"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Verifie si un utilisateur est responsable d'une liste de groupe
     * 
     * @param string $resp
     * @param array $groupesId
     * 
     * @return bool
     */
    public function isRespGroupe($resp, array $groupesId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT gr_gid
                    FROM conges_groupe_resp
                    WHERE gr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND gr_login = "'.\includes\SQL::quote($resp).'"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }
    
    /**
     * Verifie si la demande n'a pas déja été traité
     * 
     * @param string $statutDb
     * @param string $statut
     * 
     * @return bool
     */
    public function isDemandeTraitable($statut)
    {
        return ($statut != \App\Models\AHeure::STATUT_ANNUL || $statut != \App\Models\AHeure::STATUT_OK || $statut != \App\Models\AHeure::STATUT_REFUS);
    }

    /**
     * Verifie si un utilisateur est grand responsable d'une liste de groupe
     *
     * @param string $gResp
     * @param int $groupesId
     * 
     * @return bool
     */
    public function isGrandRespGroupe($gResp, $groupesId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT ggr_gid
                    FROM conges_groupe_grd_resp
                    WHERE ggr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND ggr_login = "'.\includes\SQL::quote($gResp).'"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     *  Verifie si un utilisateur est responsable d'un employé
     * 
     * @param string $resp
     * @param string $user
     * 
     * @return bool
     */
    public function isRespDirect($resp, $user)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT u_resp_login 
                    FROM conges_users 
                    WHERE u_login ="'.\includes\SQL::quote($user).'"
                        AND u_resp_login ="'.\includes\SQL::quote($resp).'"
           )';
    $query = $sql->query($req);

    return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * retourne les identifiants de groupe auquel un utilisateur appartient
     * 
     * @param string $user
     * 
     * @return array $ids
     */
    public function getGroupesId($user)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gu_gid AS id
                    FROM conges_groupe_users 
                    WHERE gu_login ="'.\includes\SQL::quote($user).'"';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Vérifie si le groupe d'un employé est en double validation
     * 
     * @param string $user
     * 
     * @return bool
     */
    protected function isDoubleValGroupe($user)
    {
        $groupes = [];
        $groupes = $this->getGroupesId($user);
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT g_double_valid
                    FROM conges_groupe
                    WHERE g_gid ='. $groupes[0] . '
                    AND g_double_valid = "Y"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }
    
    /**
     * Retourne le nombre de demande en cours d'un responsable
     * 
     * @param $resp
     * 
     * @return int
     */
    public function getNbDemandesATraiter($resp)
    {
        $demandesResp= $this->getIdDemandesResponsable($resp);
        $demandesGResp=  $this->getIdDemandesGrandResponsable($resp);
        
        return count($demandesResp) + count($demandesGResp);
    }
}
