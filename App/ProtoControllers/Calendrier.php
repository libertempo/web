<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur de calendrier, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Calendrier
{
    /**
     *
     */
    public function get()
    {
        $return = '';
        $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

        if(substr($session, 0, 9)!="phpconges") {
            session_start();
            // on initialise le tableau des variables de config
            $_SESSION['config']=init_config_tab();
            if(empty($_SESSION['userlogin'])) {
                redirect( ROOT_PATH . 'index.php' );
            }
        } else {
            include_once INCLUDE_PATH . 'session.php';
        }

        $return .= '<div id="calendar-wrapper"><h1>' . _('calendrier_titre') . '</h1>';
        $idGroupe = '';
        // --------------
        if (!empty($_POST) && $this->isSearch($_POST)) {
            $champsRecherche = $_POST['search'];
            //$champsSql       = $this->transformChampsRecherche($_POST);
        } else {
            $champsRecherche = [];
            //$champsSql       = [];
        }
        // ------------------
        $return .= $this->getFormulaireRecherche($champsRecherche, $idGroupe);

        $return .= '<div id="warning"><code>wrapper_getEvenement</code> must be running.</div>
        <div id="loading">Loading...</div>
        <div id="calendar"></div>';
        $return .= '<script type="text/javascript">
        new calendrierControleur("' . $session . '", "' . $idGroupe . '");
        </script></div>';

        return $return;
    }

    /**
     * Y-a-t-il une recherche dans l'avion ?
     *
     * @param array $post
     *
     * @return bool
     */
    private function isSearch(array $post)
    {
        return !empty($post['search']);
    }

    private function getFormulaireRecherche(array $champsRecherche, &$idGroupe)
    {
        // get groupe droit
        /*
        * Si gestion des groupes activée :
        *   - Affichage des groupes auxquels le role a droit
        *   - Passer un groupe en paramètre que si explicitement demandé (option du select vide)
        * Sinon :
        *   - Comme existant
        */
        if($_SESSION['config']['gestion_groupes']) {
            //$idGroupe = 70;
            // form avec gestion des groupes
        } else {
            // form sans gestion des groupes
        }
        $form = 'grouper à afficher';
        $form .= '<form method="post" action="" class="form-inline search" role="form"><div class="form-group">
        <label class="control-label col-md-4" for="statut">Statut&nbsp;:</label>
        <div class="col-md-8"><select class="form-control" name="search[statut]" id="statut">';
        foreach (\App\Models\AHeure::getOptionsStatuts() as $key => $value) {
            $selected = (isset($champs['statut']) && $key == $champs['statut'])
                ? 'selected="selected"'
                : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '</select></div></div><div class="form-group"><label class="control-label col-md-4" for="annee">Année&nbsp;:</label>
        <div class="col-md-8"><select class="form-control" name="search[annee]" id="sel1">';
        // groupe si éligible
        foreach (\utilisateur\Fonctions::getOptionsAnnees() as $key => $value) {
            $selected = (isset($champs['annee']) && $key == $champs['annee'])
                ? 'selected="selected"'
                : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '</select></div></div><div class="form-group"><div class="input-group">
        <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>
        &nbsp;<a href="' . ROOT_PATH . 'calendrier.php?session='. session_id() . '" type="reset" class="btn btn-default">Reset</a></div></div></form>';

        return $form;
    }
}
