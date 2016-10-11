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
     * @var int
     */
    const VUE_MOIS = 1;

    /**
     * @var int
     */
    const VUE_SEMAINE = 2;

    /**
     * Retourne la page du calendrier
     *
     * @return string
     */
    public function get()
    {
        /* Div auto fermé par le bottom */
        $return = '<div id="calendar-wrapper"><h1>' . _('calendrier_titre') . '</h1>';
        $idGroupe = NIL_INT;
        $vue = static::VUE_MOIS;
        if (!empty($_GET) && $this->isSearch($_GET)) {
            $vue = (int) $_GET['search']['vue'];
            $idGroupe = (int) $_GET['search']['groupe'];
        }
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
    private function isSearch(array $get)
    {
        return !empty($get['search']);
    }

    /**
     * Retourne le formulaire de recherche
     *
     * @param int $vue Vue du calendrier demandée
     * @param int $idGroupe Groupe à consulter
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     *
     * @return string
     */
    private function getFormulaireRecherche($vue, &$idGroupe, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));

        if(substr($session, 0, 9)!="phpconges") {
            session_start();
            $_SESSION['config']=init_config_tab();
            if(empty($_SESSION['userlogin'])) {
                redirect( ROOT_PATH . 'index.php' );
            }
        } else {
            include_once INCLUDE_PATH . 'session.php';
        }

        $form = '<form method="get" action="" class="form-inline search" role="form"><div class="form-group col-md-4 col-sm-4">
        <label class="control-label col-md-3 col-sm-3" for="vue">Vue&nbsp;:</label>
        <div class="col-md-8 col-sm-8"><select class="form-control" name="search[vue]" id="vue">';

        foreach ($this->getOptionsVue() as $valeur => $label) {
            $selected = ($valeur === $vue)
                ? 'selected="selected"'
                : '';
            $form .= '<option value="' . $valeur . '" ' . $selected . '>' . _($label) . '</option>';
        }
        $form .= '</select></div></div>';
        if($_SESSION['config']['gestion_groupes']) {
            $form .= '<div class="form-group col-md-4 col-sm-5">
            <label class="control-label col-md-3 col-sm-3" for="groupe">Groupe&nbsp;:</label>
            <div class="col-md-8 col-sm-8"><select class="form-control" name="search[groupe]" id="groupe">';
            $form .= '<option value="' . NIL_INT . '">Tous</option>';

            foreach (\App\ProtoControllers\Groupe::getOptions() as $valeur => $label) {
                $selected = ($valeur ===  $idGroupe)
                    ? 'selected="selected"'
                    : '';
                $form .= '<option value="' . $valeur . '" ' . $selected . '>' . _($label) . '</option>';
            }
            $form .= '</select></div></div>';
        }

        $urlCalendrier = ROOT_PATH . 'calendrier.php';
        $queryBase = [
            'session' => $session,
            'search[vue]' => $vue,
            'search[groupe]' => $idGroupe,
        ];
        $dateDebutPrev = clone $dateDebut;
        $dateDebutPrev->modify('-1 month');
        $queryPrev = [
            'begin' => $dateDebutPrev->format(('Y-m-d')),
            'end'   => $dateDebut->format('Y-m-d'),
        ];
        $dateFinNext = clone $dateFin;
        $dateFinNext->modify('+1 month');
        $queryNext = [
            'begin' => $dateFin->format('Y-m-d'),
            'end'   => $dateFinNext->format('Y-m-d'),
        ];
        $form .= '<div class="form-group"><div class="input-group pull-right">
        <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button></div></div>';
        $form .= '<input type="hidden" name="session" value="' . $session . '" />';
        $form .= '</form>';
        $form .= '<div class="btn-group pull-right"><a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryPrev) . '"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>';
        $form .= '<a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryNext) . '"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></div>';

        return $form;
    }

    /**
     * Retourne les options de select des vues
     *
     * @return array
     */
    private function getOptionsVue()
    {
        return [
            static::VUE_MOIS => 'vue_mois',
            static::VUE_SEMAINE => 'vue_semaine',
        ];
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
                /* Tous les événements qui sont contenus dans des jours */
                foreach ($eventCollection->find($day) as $event) {
                    $title = $event->getTitle();
                    $avecTitle = (!empty($title)) ? 'evenement-avec-title': '';
                    $return .= '<div class="' . $avecTitle . ' ' . $event->getClass() . '"
                    title="' . $title . '"><div class="contenu">' . $event->getName() . '</div>';
                    /* Un événement qui se termine est forcément avant la fin de la journée */
                    $return .= ($day->getEnd() < $event->getEnd()) ? '<div class="multijour"></div>' : '';
                    $return .= '</div>';
                }
                $return .= '</div>';
            }
            $return .= '</div>';
        }
        $return .= '</div>';

        return $return;
    }
}
