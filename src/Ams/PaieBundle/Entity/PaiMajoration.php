<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiMajoration
 *
 * @ORM\Table(name="pai_majoration"
 *                ,indexes={@ORM\Index(name="idx1_pai_majoration", columns={"date_extrait","typetournee_id","date_distrib","flux_id","depot_id","employe_id"})
 *                          }
 *                ,uniqueConstraints={@UniqueConstraint(name="un_pai_majoration",columns={"date_distrib","employe_id"})}
 *          )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiMajorationRepository")
 */
class PaiMajoration
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
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

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
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $employe;
   
    /**
     * @var \RefTypeTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typetournee_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typetournee;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_poly", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $majorationPoly;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_nuit", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $majorationNuit;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_df", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $majorationDF;

    /**
     * @var string
     *
     * @ORM\Column(name="duree_nuit_modele", type="time", nullable=false)
     */
    private $dureeNuitModele;

    /**
     * @var string
     *
     * @ORM\Column(name="taux_service_abonne", type="decimal", precision=5, scale=4, nullable=false)
     */
    private $tauxServiceAbonne;

    /**
     * @var string
     *
     * @ORM\Column(name="taux_service_diffuseur", type="decimal", precision=5, scale=4, nullable=false)
     */
    private $tauxServiceDiffuseur;

    /**
     * @var string
     *
     * @ORM\Column(name="nb_incident", type="decimal", precision=5, scale=4, nullable=false)
     */
    private $nbIncident;
 
    /**
     * @var string
     *
     * @ORM\Column(name="remuneration", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $remuneration;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;   
}
