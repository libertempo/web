<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur de calendrier, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit contacter que \App\Libraries\Calendrier\Facade
 * Ne doit être contacté que par la page en procédural
 */
final class Calendrier
{
    /**
    * @var \DateTimeInterface
     */
    private $jourDemande;

    /**
    * @var \DateTimeInterface
     */
    private $moisDemande;

    /**
     * Retourne la vue du calendrier
     *
     * @return string
     */
    public function getCalendrier()
    {
        // TODO: open bar sur les droits :O
        /* Suis pas fan de la répartition par if, mais ça a l'air de faire le job */
        return $this->isVueJournaliere()
            ? $this->getCalendrierJour()
            : '';
    }

    /**
     * @return bool
     */
    private function isVueJournaliere()
    {
        return $this->jourDemande instanceof \DateTimeInterface;
    }

    /**
     * Retourne la vue au jour du calendrier
     *
     * @return string
     */
    private function getCalendrierJour()
    {
        /* TODO: La lib ne gère pas les immutables, faire une PR */
        $week = $this->calendar->getWeek(new \DateTime($this->dateDebut->format('Y-m-d')));
        $eventCollection = $this->calendar->getEvents($week);

        $return = '<h2>Jour ' . $week->getBegin()->format('W – Y') . '</h2>';
        $return .= '<div id="calendrierJour" class="calendrier">';

        /* Affichage de l'en-tête */
        $pweek = $this->calendar->getWeek(2016, 1);
        $return .= '<div class="jour"><div class="minuteId"></div>';
        foreach ($week as $day) {
            $return .= '<div class="en-tete">' . strftime('%a', $day->getBegin()->getTimestamp()) . '</div>';
        }
        $return .= '</div>';
        $return .= '<div class="jour">';
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
}
