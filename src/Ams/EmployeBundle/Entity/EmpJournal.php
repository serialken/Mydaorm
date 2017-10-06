<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeJournal
 *
 * @ORM\Table(name="emp_journal")
* @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpJournalRepository")
 */
class EmpJournal
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
     * @var \ModeleRefErreur
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\EmpRefErreur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="erreur_id", referencedColumnName="id")
     * })
     */
    private $erreur_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="validation_id", type="integer", nullable=false)
     */
    private $validation_id;

    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     */
    private $anneemois;
	
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
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;
}
