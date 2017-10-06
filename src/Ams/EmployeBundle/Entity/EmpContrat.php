<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * EmpContrat
 *
 * @ORM\Table(name="emp_contrat"
 *                , indexes={@ORM\Index(name="idx1_emp_contrat", columns={"employe_id","date_debut","date_fin"})}
 *                ,uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_contrat", columns={"rcoid"})}
 *                )
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpContratRepository")
 */
class EmpContrat
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
     * @ORM\Column(name="rcoid", type="string", length=36, unique=true, nullable=false)
     */
    private $rcoid;

    /**
     * @var string
     *
     * @ORM\Column(name="rc", type="string", length=6, nullable=false)
     */
    private $rc;

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
