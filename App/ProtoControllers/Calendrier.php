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
final class Calendrier
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
     * @var \DateTimeInterface Date de debut de la période courante
     */
    private $dateDebutMaintenant;

    /**
     * @var \DateTimeInterface Date de fin de la période courante
     */
    private $dateFinMaintenant;

    /**
     * @var \DateTimeInterface Date de début de récolte du calendrier
     */
    private $dateDebut;

    /**
     * @var \DateTimeInterface Date de fin de récolte du calendrier
     */
    private $dateFin;

    /**
     * @var \DateTimeInterface Date de début de période précédente
     */
    private $dateDebutPrecedente;

    /**
     * @var \DateTimeInterface Date de fin de période suivante
     */
    private $dateFinSuivante;

    /**
     * @var string Identifiant de session
     */
    private $session;

    /**
     * @var int Vue du calendrier à consulter
     */
    private $vue = self::VUE_MOIS;

    /**
     * @var int Id du groupe dont on veut voir les événements
     */
    private $idGroupe = NIL_INT;

    public function __construct()
    {
        $this->session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));

        if(substr($this->session, 0, 9)!="phpconges") {
            session_start();
            $_SESSION['config']=init_config_tab();
            if(empty($_SESSION['userlogin'])) {
                redirect( ROOT_PATH . 'index.php' );
            }
        } else {
            include_once INCLUDE_PATH . 'session.php';
        }
    }

    /**
     * Retourne la page du calendrier
     *
     * @return string
     */
    public function get()
    {
        if (!empty($_GET) && $this->isSearch($_GET)) {
            $this->vue = (int) $_GET['search']['vue'];
        }
        if (isset($_GET['search']['groupe'])) {
            $this->idGroupe = (int) $_GET['search']['groupe'];
        }
        if (!empty($_GET['begin'])) {
            $this->dateDebut = new \DateTimeImmutable($_GET['begin']);
        } elseif (static::VUE_SEMAINE === $this->vue) {
            $dateDebut = new \DateTime();
            $dateDebut->setISODate(date('Y'), date('W'));
            $dateDebut->setTime(0, 0);
            $this->dateDebut = new \DateTimeImmutable($dateDebut->format('Y-m-d'));
        } else {
            $this->dateDebut = new \DateTimeImmutable(date('Y') . '-' . date('m') . '-01');
        }
        if (!empty($_GET['end'])) {
            $this->dateFin = new \DateTimeImmutable($_GET['end']);
        } elseif (static::VUE_SEMAINE === $this->vue) {
            $this->dateFin = $this->dateDebut->modify('+1 week');
        } else {
            $this->dateFin = $this->dateDebut->modify('+1 month');
        }
        $this->setDatesCourantes();

        /* Div auto fermé par le bottom */
        $return = '<div id="calendar-wrapper"><h1>' . _('calendrier_titre') . '</h1>';
        $return .= $this->getFormulaireRecherche();
        $return .= $this->getCalendrier();

        return $return;
    }

    /**
     * Défini les dates de la période courante
     */
    private function setDatesCourantes()
    {
        if (static::VUE_SEMAINE === $this->vue) {
            $this->dateDebutMaintenant = new \DateTimeImmutable();
            $this->dateDebutMaintenant->setISODate(date('Y'), date('W'));
            $this->dateDebutMaintenant->setTime(0, 0);
            $this->dateFinMaintenant = $this->dateDebutMaintenant->modify('+1 week');
        } else {
            $this->dateDebutMaintenant = new \DateTimeImmutable(date('Y') . '-' . date('m') . '-01');
            $this->dateFinMaintenant = $this->dateDebutMaintenant->modify('+1 month');
        }
    }

    /**
     * Y-a-t-il une recherche dans l'avion ?
     *
     * @param array $get
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
     * @return string
     */
    private function getFormulaireRecherche()
    {
        $form = '<form method="get" action="" class="form-inline search" role="form"><div class="form-group col-md-4 col-sm-5">
        <label class="control-label col-md-3 col-sm-3" for="vue">Vue&nbsp;:</label>
        <div class="col-md-8 col-sm-8"><select class="form-control" name="search[vue]" id="vue">';

        foreach ($this->getOptionsVue() as $valeur => $label) {
            $selected = ($valeur === $this->vue)
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

            foreach (\App\ProtoControllers\Groupe::getOptions() as $id => $groupe) {
                $selected = ($id ===  $this->idGroupe)
                    ? 'selected="selected"'
                    : '';
                $form .= '<option value="' . $id . '" ' . $selected . '>' . $groupe['nom'] . '</option>';
            }
            $form .= '</select></div></div>';
        }

        $form .= '<div class="form-group"><div class="input-group pull-right">
        <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button></div></div>';
        $form .= '<input type="hidden" name="session" value="' . $this->session . '" />';
        $form .= '</form>';

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
     * @return string
     */
    private function getCalendrier()
    {
        $businessCollection = new \App\Libraries\Calendrier\BusinessCollection(
            $this->dateDebut,
            $this->dateFin,
            $_SESSION['userlogin'],
            $this->canSessionVoirEvenementEnTransit($_SESSION),
            $_SESSION['config']['gestion_groupes'],
            $this->idGroupe
        );
        $fournisseur = new \App\Libraries\Calendrier\Fournisseur($businessCollection);
        $calendar = new \CalendR\Calendar();
        $calendar->getEventManager()->addProvider('provider', $fournisseur);
        /* Suis pas fan de la répartition par if, mais ça a l'air de faire le job */
        if (static::VUE_SEMAINE === $this->vue) {
            $this->setPeriodesSemaine();
            $return = $this->getNavigation();
            $return .= $this->getCalendrierSemaine($calendar);
        } else {
            $this->setPeriodesMois();
            $return = $this->getNavigation();
            $return .= $this->getCalendrierMois($calendar);
        }

        return $return;
    }

    private function canSessionVoirEvenementEnTransit(array $donnessUtilisateur)
    {
        return (isset($donnessUtilisateur['is_resp']) && 'Y' === $donnessUtilisateur['is_resp'])
            || (isset($donnessUtilisateur['is_rh']) && 'Y' === $donnessUtilisateur['is_rh'])
            || (isset($donnessUtilisateur['is_admin']) && 'Y' === $donnessUtilisateur['is_admin']);
    }

    /**
     * Défini les périodes utiles pour le calendrier à la semaine
     */
    private function setPeriodesSemaine()
    {
        $this->dateDebutPrecedente = $this->dateDebut->modify('-1 week');
        $this->dateFin = $this->dateDebut->modify('+1 week');
        $this->dateFinSuivante = $this->dateFin->modify('+1 week');
    }

    /**
     * Défini les périodes utiles pour le calendrier au mois
     */
    private function setPeriodesMois()
    {
        $this->dateDebutPrecedente = $this->dateDebut->modify('-1 month');
        $this->dateFinSuivante = $this->dateFin->modify('+1 month');
    }

    /**
     * Retourne la vue à la semaine du calendrier
     *
     * @param \CalendR\Calendar $calendar
     *
     * @return string
     */
    private function getCalendrierSemaine(\CalendR\Calendar $calendar)
    {
        /* TODO: La lib ne gère pas les immutables, faire une PR */
        $week = $calendar->getWeek(new \DateTime($this->dateDebut->format('Y-m-d')));
        $eventCollection = $calendar->getEvents($week);

        $return = '<h2>Semaine ' . $week->getBegin()->format('W – Y') . '</h2>';
        $return .= '<div id="calendrierSemaine" class="calendrier">';

        /* Affichage de l'en-tête */
        $pweek = $calendar->getWeek(2016, 1);
        $return .= '<div class="semaine"><div class="minuteId"></div>';
        foreach ($week as $day) {
            $return .= '<div class="en-tete">' . strftime('%a', $day->getBegin()->getTimestamp()) . '</div>';
        }
        $return .= '</div>';
        $return .= '<div class="semaine">';
        /* Affichage des événements à la journée du calendrier */
        $inflated = [];
        foreach ($week as $day) {
            $return .= '<div class="celluleJour"><div class="evenementJour">';
            $today = ($day->isCurrent()) ? 'today' : '';
            $return .= '<div class="jourId ' . $today . '">' . $day->getBegin()->format('j') . '</div>';
            foreach ($eventCollection->find($day) as $event) {
                /* Suppression des événements qui sont plus fins que la journée */
                if ($event->getBegin() <= $day->getBegin()) {
                    $title = $event->getTitle();
                    $avecTitle = (!empty($title)) ? 'evenement-avec-title': '';
                    $return .= '<div class="' . $avecTitle . ' ' . $event->getClass() . '"
                    title="' . $title . '"><div class="contenu">' . $event->getName() . '</div>';
                    /* Un événement qui se termine est forcément avant la fin de la journée */
                    $return .= ($day->getEnd() < $event->getEnd()) ? '<div class="multijour"></div>' : '';
                    $return .= '</div>';
                }
            }
            $return .= '</div>';

            foreach ($day as $hour) {
                foreach ($hour as $minute) {
                    if (0 === $minute->format('i') % 30) {
                        $demiHeure = clone $minute->getBegin();
                        $demiHeure->modify('+30 minutes');
                        $evenementsPeriode = [];
                        foreach ($eventCollection->find($minute) as $event) {
                            /* Suppression des événements qui sont plus fins que la journée */
                            if ($event->getBegin() > $day->getBegin()
                                && $event->containsPeriod($minute)
                            ) {
                                $title = $event->getTitle();
                                $avecTitle = (!empty($title)) ? 'evenement-avec-title': '';
                                $evenementsPeriode[] = '<div class="' . $avecTitle . ' ' . $event->getClass() . '"
                                title="' . $title . '"><div class="contenu">' . $event->getName() . '</div>';
                                /* Un événement qui se termine est forcément avant la fin de la période */
                                $evenementsPeriode[] = ($demiHeure < $event->getEnd()) ? '<div class="multijour"></div>' : '';
                                $evenementsPeriode[] = '</div>';
                            }
                        }
                        if (!isset($inflated[$minute->format('H\:i')])
                            && !empty($evenementsPeriode)
                        ) {
                            $inflated[$minute->format('H\:i')] = true;
                        }
                        $toInflate = (isset($inflated[$minute->format('H\:i')]) && $inflated[$minute->format('H\:i')])
                            ? 'inflate'
                            : '';
                        $return .= '<div class="celluleMinute ' . $toInflate . '">' . implode('', $evenementsPeriode) . '</div>';
                    }
                }
            }
            $return .= '</div>';
        }
        /* Affichage des heures */
        $return .= '<div class="minuteId"><div class="enteteMinute"></div><div>';
        foreach ($week as $day) {
            foreach ($day as $hour) {
                foreach ($hour as $minute) {
                    if (0 === $minute->format('i') % 30) {
                        $toInflate = (isset($inflated[$minute->format('H\:i')]) && $inflated[$minute->format('H\:i')])
                            ? 'inflate'
                            : '';
                        $return .= '<div class="celluleMinute ' . $toInflate . '">' . $minute->format('H\:i') . '</div>';
                    }
                }
            }
            break;
        }
        $return .= '</div></div>';
        $return .= '</div>';

        return $return;
    }

    /**
     * Retourne la vue au mois du calendrier
     *
     * @param \CalendR\Calendar $calendar
     *
     * @return string
     */
    private function getCalendrierMois(\CalendR\Calendar $calendar)
    {
        /* TODO: La lib ne gère pas les immutables, faire une PR */
        $month = $calendar->getMonth(new \DateTime($this->dateDebut->format('Y-m-d')));
        $eventCollection = $calendar->getEvents($month);

        $return = '<h2>' . strftime('%B %G', $month->getBegin()->getTimestamp()) . '</h2>';
        $return .= '<div id="calendrierMois" class="calendrier">';

        /* Affichage de l'en-tête */
        $pweek = $calendar->getWeek(2016, 1);
        $return .= '<div class="semaine"><div class="semaineId"></div>';
        foreach ($pweek as $day) {
            $return .= '<div class="en-tete">' . strftime('%a', $day->getBegin()->getTimestamp()) . '</div>';
        }
        $return .= '</div>';

        /* Affichage des événements du calendrier */
        foreach ($month as $week) {
            $return .= '<div class="semaine"><div class="semaineId">' . $week . '</div>';
            foreach ($week as $day) {
                /* Vérification que le jour est bien dans le mois */
                $class = (!$month->includes($day)) ? 'horsMois' : '';
                $today = ($day->isCurrent()) ? 'today' : '';
                $return .= '<div class="celluleJour ' . $class . '">';
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

    /**
     * Retourne la navigation
     *
     * @return string
     */
    private function getNavigation()
    {
        $urlCalendrier = ROOT_PATH . 'calendrier.php';
        $queryBase = [
            'session' => $this->session,
            'search[vue]' => $this->vue,
            'search[groupe]' => $this->idGroupe,
        ];
        $queryPrec = [
            'begin' => $this->dateDebutPrecedente->format('Y-m-d'),
            'end'   => $this->dateDebut->format('Y-m-d'),
        ];
        $queryCurrent = [
            'begin' => $this->dateDebutMaintenant->format('Y-m-d'),
            'end'   => $this->dateFinMaintenant->format('Y-m-d'),
        ];
        $querySuc = [
            'begin' => $this->dateFin->format('Y-m-d'),
            'end'   => $this->dateFinSuivante->format('Y-m-d'),
        ];

        $return = '<div class="btn-group pull-right"><a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryPrec) . '"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>';
        $return .= '<a class="btn btn-default" title="' . _('retour_periode_courante') . '" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $queryCurrent) . '"><i class="fa fa-home" aria-hidden="true"></i></a>';
        $return .= '<a class="btn btn-default" href="' . $urlCalendrier . '?' . http_build_query($queryBase + $querySuc) . '"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></div>';

        return $return;
    }
}
