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

        if($_SESSION['config']['gestion_groupes']) {
            // form avec gestion des groupes
        } else {
            // form sans gestion des groupes
        }


        // get groupe droit
        /*
        * Si gestion des groupes activée :
        *   - Affichage des groupes auxquels le role a droit
        *   - Passer un groupe en paramètre que si explicitement demandé (option du select vide)
        * Sinon :
        *   - Comme existant
        */
        $return .= '<div id="calendar-wrapper"><h1>' . _('calendrier_titre') . '</h1>';

        $return .= '<div id="warning"><code>wrapper_getEvenement</code> must be running.</div>
        <div id="loading">Loading...</div>
        <div id="calendar"></div>';
        $return .= '<script type="text/javascript">
        new calendrierControleur("' . $session . '");
        </script></div>'; // passer le groupe en parametre

        return $return;
    }

    private function getFormulaireRecherche()
    {

    }
}
