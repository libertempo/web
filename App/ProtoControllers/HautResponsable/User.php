<?php
namespace App\ProtoControllers\HautResponsable;

/**
 * ProtoContrôleur de gestion des utilisateurs, en attendant la migration vers le MVC REST
 *
 * @since  1.11
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@gmail.com>
 */
class User
{


    /**
     * Encapsule le comportement du module d'ajout / modification d'un utilisateur
     *
     * @param int $id
     *
     * @return string
     */
    public static function getFormUserModule($id = NIL_INT)
    {
        $return    = '';
        $message   = '';
        $errorsLst = [];
        $notice    = '';
        $valueName = '';
        if (!empty($_POST)) {
            if (0 < (int) \App\ProtoControllers\HautResponsable\User::postUser($_POST, $errorsLst, $notice)) {
                log_action(0, '', '', 'Édition de l\'utilisateur ' . $_POST['name']);
                redirect(ROOT_PATH . 'hr/hr_index.php?onglet=page_principale', false);
            } else {
                if (!empty($errorsLst)) {
                    $errors = '';
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
                }
                $valueName = $_POST['name'];
            }
        }

        if (NIL_INT !== $id) {
            $return .= '<h1>' . _('Modification utilisateur : ') . '</h1>';
        } else {
            $return .= '<h1>' . _('Nouvel Utilisateur') . '</h1>';
        }
        $return .= $message;

        $return .= '<form action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);

        $childTable = '<thead>';
        $childTable .= '<tr>';
        if ($_SESSION['config']['export_users_from_ldap'] ) {
            $childTable .= '<th>' . _('Nom et Prenom') . '</th>';
        } else {
            $childTable .= '<th>' . _('Identifiant') . '</th>';
            $childTable .= '<th>' . _('Nom') . '</th>';
            $childTable .= '<th>' . _('Prénom') . '</th>';
        }
        $childTable .= '<th>' . _('Quotité') . '</th>';
        if ($_SESSION['config']['gestion_heures'] ) {
            $childTable .= '<th>' . _('solde d\'heure') . '</th>';
        }
        $childTable .= '<th>' . _('Responsable?') . '</th>';
        $childTable .= '<th>' . _('Administrateur?') . '</th>';
        $childTable .= '<th>' . _('Haut responsable?') . '</th>';
        if ( !$_SESSION['config']['export_users_from_ldap'] ) {
            $childTable .= '<th>' . _('Courriel') . '</th>';
        }
        if ($_SESSION['config']['how_to_connect_user'] == "dbconges") {
            $childTable .= '<th>' . _('mot de passe') . '</th>';
            $childTable .= '<th>' . _('ressaisir le mot de passe') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';
        $soldeHeureId = uniqid();
        
    }
    /**
     * Poste un nouveau utilisateur
     *
     * @param array $post
     * @param array &$errors
     * @param string $notice
     *
     * @return int
     */
    public static function postUser(array $post, array &$errors, &$notice)
    {
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    return static::deleteUser($post['user_id'], $errors, $notice);
                    break;
                case 'PUT':
                    if (!empty($post['user_id']) && 0 < (int) $post['user_id']) {
                        return static::putUser($post['user_id'], $post, $errors);
                    }
                    break;
                case 'POST':
                    if (!empty($post['user_id']) && 0 < (int) $post['user_id']) {
                        return static::postUser($post['user_id'], $post);
                    }
                    break;
            }
        } else {

        }
    }
}