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
     * Retourne la page du calendrier
     *
     * @return string
     */
    public function get()
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

        /* Div auto fermé par le bottom */
        $return .= '<div id="calendar-wrapper"><h1>' . _('calendrier_titre') . '</h1>';
        $idGroupe = NIL_INT;
        $vue = NIL_INT;
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
            $dateDebut = new \DateTime(date('Y') . '-' . date('m') . '-01');
        }
        if (!empty($_GET['end'])) {
            $dateFin = new \DateTime($_GET['end']);
        } else {
            $dateFin = clone $dateDebut;
            $dateFin->modify('+1 month');
        }
        $return .= $this->getFormulaireRecherche($vue, $idGroupe, $dateDebut, $dateFin);
        $return .= $this->getCalendrier($vue, $idGroupe, $dateDebut, $dateFin);

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

    private function getFormulaireRecherche($vue, &$idGroupe, \DateTime $dateDebut, \DateTime $dateFin)
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

    /**
     * Retourne la vue du calendrier
     *
     * @param int $vue Sélection des différents affichage du calendrier
     * @param int $idGroupe Groupe dont on veut voir les événements
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     *
     * @return string
     */
    private function getCalendrier($vue, $idGroupe, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $businessCollection = new \App\Libraries\Calendrier\BusinessCollection(
            $dateDebut,
            $dateFin,
            $_SESSION['userlogin'],
            $_SESSION['config']['gestion_groupes'],
            $idGroupe
        );
        $fournisseur = new \App\Libraries\Calendrier\Fournisseur($businessCollection);
        $calendar = new \CalendR\Calendar();
        $calendar->getEventManager()->addProvider('provider', $fournisseur);
        $month = $calendar->getMonth($dateDebut);
        $eventCollection = $calendar->getEvents($month);
        //d($eventCollection);

        $return = '<h2>' . strftime('%B %G', $month->getBegin()->getTimestamp()) . '</h2>';
        $return .= '<div id="calendrier">';

        /* Affichage de l'en-tête */
        $pweek = $calendar->getWeek(2016, 1);
        $return .= '<div class="ligne"><div class="semaineId"></div>';
        foreach ($pweek as $day) {
            $return .= '<div class="en-tete">' . strftime('%a', $day->getBegin()->getTimestamp()) . '</div>';
        }
        $return .= '</div>';

        /* Affichage du corps du calendrier */
        foreach ($month as $week) {
            $return .= '<div class="ligne"><div class="semaineId">' . $week . '</div>';
            foreach ($week as $day) {
                /* Vérification que le jour est bien dans le mois */
                $class = (!$month->includes($day)) ? 'horsMois' : '';
                $today = ($day->isCurrent()) ? 'today' : '';
                $return .= '<div class="cellule ' . $class . '">';
                $return .= '<div class="jourId ' . $today . '">' . $day->getBegin()->format('j') . '</div>';
                $hasTitle = false;
                //d($day->getBegin()->format('d/m/Y'), $eventCollection->find($day));
                foreach ($eventCollection->find($day->getBegin()) as $event) {
                    // si on veut espérer aligner les évenements multi jour, on peut utiliser le uid
                    // on peut aussi faire en sorte de représenter l'événement differemment s'il est multijour, genre avec une pointe
                    $title = $event->getTitle();
                    $avecTitle = (!empty($title)) ? 'evenement-avec-title': '';
                    $return .= '<div class="' . $avecTitle . ' ' . $event->getClass() . '"
                    title="' . $title . '"><div class="contenu">' . $event->getName() . '</div>';
                    //d($event->getBegin(), $day->getBegin(), $event->getEnd());
                    $return .= ($day->getBegin() < $event->getEnd()) ? '<div class="multijour"></div>' : '';
                    $return .= '</div>';
                }
                /* Event test */
                //$return .= '<div class="event conges" title="[Congé] Congés payés de Saul Goodman du 12-07-2016 au 18-07-2016">Saul Goodman</div>';
                //$return .= '<div class="event absences" title="[Absence] Formation de Capitaine Archibald Haddock du 12-07-2016 au 18-07-2016">Capitaine Archibald Haddock</div>';
                //$return .= '<div class="event heure" title="[Heure] Heure de repos de Tarte Tatin du 12-07-2016 à 9h30 au 12-07-2016 à 11h">Tarte Tatin</div>';
                //$return .= '<div class="event conges_exceptionnels" title="[Congés exceptionnels] Maladie de Tartampion Champignac du 12-07-2016 au 18-07-2016">Tartampion Champignac</div>';
                $return .= '</div>';
            }
            $return .= '</div>';
        }
        $return .= '</div>';

        return $return;
    }
}
