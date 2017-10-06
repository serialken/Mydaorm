<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmpPopDepot
 *
 * @ORM\Table(name="emp_pop_depot", indexes={@ORM\Index(name="idx1_emp_pop_depot", columns={"rcoid"})
 *                                   })
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpPopDepotRepository")
 */
class EmpPopDepot
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
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $employe;

    /**
     * @var \EmpContrat
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\EmpContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contrat_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $contrat;

    /**
     * @var \EmpContratType
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\EmpContratType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contrattype_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $contratType;

    /**
     * @var \EmpCycle
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\EmpCycle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cycle_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $empCycle;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $date_debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=false)
     */
    private $date_fin;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="rcoid", type="string", length=36, nullable=false)
     */
    private $rcoid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dRC", type="date", nullable=false)
     */
    private $dRC;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fRC", type="date", nullable=false)
     */
    private $fRC;

    /**
     * @var string
     *
     * @ORM\Column(name="rc", type="string", length=6, nullable=false)
     */
    private $rc;

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
     * @var \RefSociete
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmpSociete")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $societe;

    /**
     * @var \RefPopulation
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefPopulation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="population_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $population;

    /**
     * @var \RefEmploi
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmploi")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="emploi_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $emploi;

    /**
     * @var \RefTypeTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typetournee_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeTournee;

    /**
     * @var \RefTypeUrssaf
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeUrssaf")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeurssaf_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeUrssaf;

    /**
     * @var \RefTypeContrat
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typecontrat_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeContrat;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_garanties", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $nbHeuresGaranties;

    /**
     * @var boolean
     *
     * @ORM\Column(name="km_paye", type="boolean", nullable=false, options={"default"=1})
     */
    private $kmPaye;

    /**
     * @var string
     *
     * @ORM\Column(name="cycle", type="string", length=7, nullable=false)
     */
    private $cycle;

    /**
     * @var boolean
     *
     * @ORM\Column(name="lundi", type="boolean", nullable=false)
     */
    private $lundi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mardi", type="boolean", nullable=false)
     */
    private $mardi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mercredi", type="boolean", nullable=false)
     */
    private $mercredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="jeudi", type="boolean", nullable=false)
     */
    private $jeudi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vendredi", type="boolean", nullable=false)
     */
    private $vendredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="samedi", type="boolean", nullable=false)
     */
    private $samedi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dimanche", type="boolean", nullable=false)
     */
    private $dimanche;
    
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
}
