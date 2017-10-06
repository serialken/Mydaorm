<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiIntTraitement
 *
 * @ORM\Table(name="pai_int_traitement"
 *            ,indexes={@ORM\Index(name="pai_int_traitement_idx1", columns={"anneemois","flux_id","typetrt"})
 *                      }
 *              )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiIntTraitementRepository")
 */

class PaiIntTraitement
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime", nullable=false)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=true)
     */
    private $dateFin;
	
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
     * @ORM\Column(name="typetrt", type="string", length=128, nullable=false)
     */
    private $typeTrt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=true)
     */
    private $anneeMois;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="statut", type="string", length=1, nullable=false)
     */
    private $statut;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $date_distrib;

	/**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $depot;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $flux;
}
