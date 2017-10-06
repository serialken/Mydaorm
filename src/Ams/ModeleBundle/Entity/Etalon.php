<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Etalon
 *
 * @ORM\Table(name="etalon"
 *                      ,indexes={@ORM\Index(name="idx1_modele_valrem", columns={"depot_id","flux_id"})}
 * 			)
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\EtalonRepository")
 */
class Etalon
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \RefTypeEtalon
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeEtalon")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $type;

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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;
    
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_requete", type="datetime", nullable=true)
     */
    private $date_requete;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_application", type="date", nullable=false)
     */
    private $date_application;
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demandeur_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $demandeur;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_demande", type="datetime", nullable=true)
     */
    private $date_demande;
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="valideur_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $valideur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_validation", type="datetime", nullable=true)
     */
    private $date_validation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_refus", type="datetime", nullable=true)
     */
    private $date_refus;
    
    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=1024, nullable=true)
     */
    private $commentaire;
}
