<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * EmpContrat
 *
 * @ORM\Table(name="emp_contrat_type"
 *                , indexes={@ORM\Index(name="idx1_emp_contrat_type", columns={"contrat_id","date_debut","date_fin"})}
 *                ,uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_contrat_type", columns={"contrat_id","date_debut"})}
 *                )
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpContratRepository")
 */
class EmpContratType
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
     * @var \Contrat
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\EmpContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contrat_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $contrat;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin_prevue", type="date", nullable=true)
     */
    private $date_fin_prevue;

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
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="remplace_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $remplace;
    
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
