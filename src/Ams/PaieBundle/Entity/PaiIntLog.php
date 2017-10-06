<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiIntLog
 *
 * @ORM\Table(name="pai_int_log")
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiIntLogRepository")
 */

class PaiIntLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
    /**
     * @var \PaiIntTraitement
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiIntTraitement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idtrt", referencedColumnName="id", nullable=false)
     * })
     */
    private $traitement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_log", type="datetime", nullable=false)
     */
    private $dateLog;

    /**
     * @var \String
     *
     * @ORM\Column(name="module", type="string", length=64, nullable=false)
     */
    private $module;

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level;

    /**
     * @var \String
     *
     * @ORM\Column(name="msg", type="string", length=1024, nullable=false)
     */
    private $msg;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    private $count;

}
