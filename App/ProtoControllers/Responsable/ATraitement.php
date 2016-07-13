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
     * Traite les demandes d'heures
     *
     * @param array  $put
     * @param string $user
     *
     * @return int
     */
    abstract protected function put(array $put, $resp, &$notice, array &$errorLst);

    /**
     * Retourne une liste d'id des demandes pour le responsable
     *
     * @param array $resp
     *
     * @return array
     */
    abstract protected function getDemandesRespId($resp);

    /**
     * Retourne une liste d'heures
     *
     * @param array $listId
     *
     * @return array
     * 
     * utiliser celui dans Heure/Additionnelle?
     */
    abstract protected function getListeSQL(array $listId);

    /**
     * Traite la demande/modification/suppression
     *
     * @param array  $post
     * @param string $notice
     *
     * @return int
     */
    protected function post(array $post, &$notice, array &$errorLst)
    {
        if (!empty($post['_METHOD']) && $post['_METHOD'] == "PUT") {
            $demandeTraitable = array_intersect($post['demande'], $this->getDemandesRespId($_SESSION['userlogin']));
            return $this->put($post, $_SESSION['userlogin'], $notice, $errorLst);
        } else {
            return NIL_INT;
        }
    }

    /**
     * Retourne le nom d'un utilisateur
     *
     * @param string $login
     *
     * @return string
     */
    public function getNom($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_nom FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $nom = $query->fetch_array()[0];

        return $nom;
    }

    /**
     * Retourne le prenom d'un utilisateur
     *
     * @param string $login
     *
     * @return string
     */
    public function getPrenom($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_prenom FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $nom = $query->fetch_array()[0];

        return $nom;
    }
    
    /**
     * Retourne le solde d'heure d'un utilisateur
     *
     * @param string $login
     *
     * @return int
     */
    public function getSoldeHeure($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_heure_solde FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $solde = $query->fetch_array()[0];

        return $solde;
    }

    /**
     * Retourne l'id des groupes d'un responsable donné
     *
     * @param string $resp
     *
     * @return array
     */
    public function getGroupeRespId($resp)
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
     * Retourne l'id des groupes d'un grand responsable donné
     *
     * @param string $gresp
     *
     * @return array
     */
    public function getGroupeGrandRespId($gresp)
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
     * Retourne le login des membres d'un ou plusieurs groupes
     *
     * @param array $groupes
     *
     * @return array
     */
    public function getUsersGroupe(array $groupes)
    {
         $sql = \includes\SQL::singleton();
         $req = 'SELECT gu_login FROM `conges_groupe_users` WHERE gu_gid IN (' . implode(',', $groupes) . ')';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $users[] = $data['gu_login'];
         }

         return $users;
    }
        
    /**
     * Traitement
     */
    protected function demandeOk($demande)
    {
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        
        $idSolde = $this->updateSolde($demande);
        $idStatut = $this->updateStatutOk($demande);
        if (0 < $idSolde && 0 < $idStatut) {
            $sql->getPdoObj()->commit();
        } else {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
        return $demande;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getDemandesResp($resp)
    {
        $demandesId = $this->getDemandesRespId($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getListeSQL($demandesId);

        return $demandes;
    }
    
    protected function getDemandesTab(array $demandes)
    {
        $i=true;
        $Table='';
        
        foreach ( $demandes as $demande ) {
            $jour   = date('d/m/Y', $demande['debut']);
            $debut  = date('H\:i', $demande['debut']);
            $fin    = date('H\:i', $demande['fin']);
            $duree  = \App\Helpers\Formatter::Timestamp2Duree($demande['duree']);
            $id = $demande['id_heure'];
            $nom = $this->getNom($demande['login']);
            $prenom = $this->getPrenom($demande['login']);
            $solde = \App\Helpers\Formatter::Timestamp2Duree($this->getSoldeHeure($demande['login']));
            $Table .= '<tr class="'.($i?'i':'p').'">';
            $Table .= '<td><b>'.$nom.'</b><br>'.$prenom.'</td><td>'.$jour.'</td><td>'.$debut.'</td><td>'.$fin.'</td><td>'.$duree.'</td><td>'.$solde.'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td>' . $demande['comment'] . '</td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="STATUT_OK"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="STATUT_REFUS"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" max="100"></td></tr>';

            $i = !$i;
            }
            
        return $Table;
    }

    /**
     * {@inheritDoc}
     */
    public function getDemandesGrandResp($resp)
    {
        $demandesId = $this->getDemandesGrandRespId($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getListeSQL($demandesId);

        return $demandes;
    }

    /**
     * {@inheritDoc}
     */
    public function isRespDeUser($resp, $user) {
        return $this->isRespDirect($resp, $user) || $this->isRespGroupe($resp, $this->getGroupesId($user));
    }
    
    /**
     * {@inheritDoc}
     */
    public function isGrandRespDeUser($resp, array $groupesId) {
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function isDemandeTraitable($statutDb, $statut)
    {
        return $statutDb != $statut;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * Retourne le nombre de demande d'heure
     * 
     * @param $resp
     * 
     * @return int
     */
    public function getNbDemande($resp)
    {
        $demandesResp= $this->getDemandesRespId($resp);
        $demandesGResp=  $this->getDemandesGrandRespId($resp);
        
        return count($demandesResp) + count($demandesGResp);
    }
}