<?php

namespace Ams\EmpBanqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * EmpBanque
 *
 * @ORM\Table(name="emp_banque", uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_banque", columns={"employe_id", "date_debut"})})
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpBanqueRepository")
 * 
 */
class EmpBanque
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
     * @var string
     * PEVP : IBAN	VARCHAR2(34 BYTE)
     *
     * @ORM\Column(name="iban", type="string", length=34, nullable=true)
     */
    private $iban;

    /**
     * @var string
     * PEVP : BIC	VARCHAR2(11 BYTE)
     *
     * @ORM\Column(name="bic", type="string", length=11, nullable=true)
     */
    private $bic;

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
    private $date_modif;}
