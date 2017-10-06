<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TourneeStats
 *
 * @ORM\Table(name="tournee_stats")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\TourneeStatsRepository")
 */
class TourneeStats
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="tournee_jour_code", type="string", length=255)
     */
    private $tourneeJourCode;

    /**
     * @var \DateTime
     * @ORM\Id
     * @ORM\Column(name="date_distrib", type="datetime")
     */
    private $dateDistrib;

    /**
     * @var string
     *
     * @ORM\Column(name="stats", type="text")
     */
    private $stats;

    /**
     * Set tourneeJourCode
     *
     * @param string $tourneeJourCode
     * @return TourneeStats
     */
    public function setTourneeJourCode($tourneeJourCode)
    {
        $this->tourneeJourCode = $tourneeJourCode;

        return $this;
    }

    /**
     * Get tourneeJourCode
     *
     * @return string 
     */
    public function getTourneeJourCode()
    {
        return $this->tourneeJourCode;
    }

    /**
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return TourneeStats
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->dateDistrib = $dateDistrib;

        return $this;
    }

    /**
     * Get dateDistrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->dateDistrib;
    }

    /**
     * Set stats
     *
     * @param string $stats
     * @return TourneeStats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * Get stats
     *
     * @return string 
     */
    public function getStats()
    {
        return $this->stats;
    }
}
