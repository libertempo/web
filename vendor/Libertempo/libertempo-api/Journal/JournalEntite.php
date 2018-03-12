<?php
namespace LibertAPI\Journal;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \LibertAPI\Tests\Units\Journal\JournalEntite
 */
final class JournalEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    /**
     * @return int
     */
    public function getNumeroPeriode()
    {
        return (int) $this->getFreshData('numeroPeriode');
    }

    /**
     * @return string
     */
    public function getUtilisateurActeur()
    {
        return $this->getFreshData('utilisateurActeur');
    }

    /**
     * @return string
     */
    public function getUtilisateurObjet()
    {
        return $this->getFreshData('utilisateurObjet');
    }

    /**
     * @return string
     */
    public function getEtat()
    {
        return $this->getFreshData('etat');
    }

    /**
     * @return string
     */
    public function getCommentaire()
    {
        return $this->getFreshData('commentaire');
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->getFreshData('date');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        throw new \RuntimeException('Action is forbidden');
    }
}
