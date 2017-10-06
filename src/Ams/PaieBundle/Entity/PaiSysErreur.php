<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiIntLog
 *
 * @ORM\Table(name="sys_erreur")
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiSysErreurRepository")
 */

class PaiSysErreur
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
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $depot;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $flux;
    
    /**
     * @var \String
     *
     * @ORM\Column(name="session", type="text", nullable=true)
     */
    private $session;

    /**
     * @var \String
     *
     * @ORM\Column(name="request", type="text", nullable=true)
     */
    private $request;

    /**
     * @var \String
     *
     * @ORM\Column(name="post", type="text", nullable=true)
     */
    private $post;

    /**
     * @var \String
     *
     * @ORM\Column(name="msg", type="text", nullable=true)
     */
    private $msg;

    /**
     * @var \String
     *
     * @ORM\Column(name="msgException", type="text", nullable=true)
     */
    private $msgException;
	
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $utilisateur;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;
    
}
