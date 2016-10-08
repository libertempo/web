<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur de calendrier, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 *
 * Ne doit contacter que \App\Libraries\Calendrier\BusinessCollection, \App\Libraries\Calendrier\Fournisseur
 * Ne doit être contacté que par la page en procédural
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
     *
     */
    public function get2()
    {
        $return = '';
        $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));

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
        if (!empty($_GET['begin'])) {
            $dateDebut = new \DateTime($_GET['begin']);
        } else {
            $dateDebut = new \DateTime('2016-10-01');
        }
        if (!empty($_GET['end'])) {
            $dateFin = new \DateTime($_GET['end']);
        } else {
        $dateFin = clone $dateDebut;
            $dateFin->modify('+1 month');
        }
        $return .= $this->getFormulaireRecherche($champsRecherche, $idGroupe, $dateDebut, $dateFin);

        $collectionEvenement = new \App\Libraries\Evenement\Collection($_SESSION['userlogin'], $_SESSION['config']['gestion_groupes']);
        $fournisseur = new \App\Libraries\Evenement\Fournisseur($collection);

        $calendar = new \CalendR\Calendar();
        $calendar->getEventManager()->addProvider('provider', $fournisseur);
        $month = $calendar->getMonth($dateDebut);
        $eventCollection = $calendar->getEvents($month);
        d($eventCollection);

        $return .= '<h2>' . $month->format('F Y') . '</h2>';
        $return .= '<table id="calendrier">';

        // Iterate over your month and get weeks
        $pweek = $calendar->getWeek(2016, 1);
        $return .= '<tr><th></th>';
        foreach ($pweek as $day) {
            $return .= '<th>' . $day->getBegin()->format('D') . '</th>';
        }
        $return .= '</tr>';

        // Iterate over your month and get weeks
        foreach ($month as $week) {
            $return .= '<tr><td class="semaineId">' . $week . '</td>';

            // Iterate over your month and get days
            foreach ($week as $day) {
                foreach ($eventCollection->find($day) as $event) {
                    $return .= $event;
                }
                // Check days that are out of your month
                $class = (!$month->includes($day)) ? 'class="horsMois"' : '';
                $return .= '<td ' . $class . '>';
                $return .= '<div class="jourId">' . $day->getBegin()->format('j') . '</div>';
                /* Event test */
                $return .= '<div class="event conges" title="[Congé] Congés payés de Saul Goodman du 12-07-2016 au 18-07-2016">Saul Goodman</div>';
                $return .= '<div class="event absences" title="[Absence] Formation de Capitaine Archibald Haddock du 12-07-2016 au 18-07-2016">Capitaine Archibald Haddock</div>';
                $return .= '<div class="event heure" title="[Heure] Heure de repos de Tarte Tatin du 12-07-2016 à 9h30 au 12-07-2016 à 11h">Tarte Tatin</div>';
                $return .= '<div class="event conges_exceptionnels" title="[Congés exceptionnels] Maladie de Tartampion Champignac du 12-07-2016 au 18-07-2016">Tartampion Champignac</div>';
                $return .= '</td>';
            }
            $return .= '</tr>';
        }

        $return .= '</table>';
        // type de l'événement en title
        // On travaille uniquement sur des dates ISO 8601
        // pour le mois prev / succ, il faudrait passer par \DateTime->modify() pour les urls
        // passage dans l'url de begin mois, de fin de mois et vue. Pour les autres vues on chargera trop mais c'est pas méchant

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

    private function getFormulaireRecherche(array $champsRecherche, &$idGroupe, \DateTime $dateDebut, \DateTime $dateFin)
    {
        //ddd($dateDebut, $dateFin);
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
        //$form = 'vue [mois [par defaut] / semaine / jour ] / groupe à afficher si applicable -- les boutons à part font prev et succ';
        $form = '<form method="post" action="" class="form-inline search" role="form"><div class="form-group">
        <label class="control-label col-md-4" for="statut">Vue&nbsp;:</label>
        <div class="col-md-8"><select class="form-control" name="search[statut]" id="statut">';
        $form .= '<option value="0">Mois</option>';

        foreach (\App\Models\AHeure::getOptionsStatuts() as $key => $value) {
            $selected = (isset($champs['statut']) && $key == $champs['statut'])
                ? 'selected="selected"'
                : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '<option value="' . $key . '" ' . $selected . '>Mois</option>';
        $form .= '</select></div></div>';

        $urlCalendrier = ROOT_PATH . 'calendrier.php';
        $queryBase = [
            'session' => session_id(),
        ];
        $dateDebutPrev = clone $dateDebut;
        $dateDebutPrev->modify('-1 month');
        $queryPrev = [
            'begin' => $dateDebutPrev->format(('Y-m-d')),
            'end'   => $dateDebut->format('Y-m-d'),
            'vue'   => 1,
            // groupe
        ];
        $dateFinNext = clone $dateFin;
        $dateFinNext->modify('+1 month');
        $queryNext = [
            'begin' => $dateFin->format('Y-m-d'),
            'end'   => $dateFinNext->format('Y-m-d'),
            'vue'   => 1,
            // groupe
        ];
        $form .= '<div class="form-group"><div class="input-group">
        <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button></div></div>';
        $form .= '</form>';
        $form .= '<div class="btn-group pull-right"><a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryPrev) . '"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>';
        $form .= '<a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryNext) . '"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></div>';

        return $form;
    }
}
