<?php

namespace Ams\EmpAdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * EmpAdresse
 *
 * @ORM\Table(name="emp_adresse", uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_adresse", columns={"employe_id", "date_debut"})})
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpAdresseRepository")
 * 
 */
class EmpAdresse
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
     * PEVP : NUMEROVOIE	VARCHAR2(10 BYTE)
     *
     * @ORM\Column(name="numerovoie", type="string", length=10, nullable=true)
     */
    private $numerovoie;

    /**
     * @var string
     * PEVP : NATUREVOIE	CHAR(4 BYTE)
     *
     * @ORM\Column(name="naturevoie", type="string", length=4, nullable=true)
     */
    private $naturevoie;
    
    /**
     * @var string
     * PEVP : CPLTADR1	VARCHAR2(32 BYTE)
     *
     * @ORM\Column(name="cpltadr1", type="string", length=32, nullable=true)
     */
    private $cpltadr1;
    
    /**
     * @var string
     * PEVP : CPLTADR2	VARCHAR2(32 BYTE)
     *
     * @ORM\Column(name="cpltadr2", type="string", length=32, nullable=true)
     */
    private $cpltadr2;
    
    /**
     * @var string
     * PEVP : NOMVOIE1	VARCHAR2(22 BYTE)
     *
     * @ORM\Column(name="nomvoie1", type="string", length=22, nullable=true)
     */
    private $nomvoie1;
    
    /**
     * @var string
     * PEVP : NOMVOIE2	VARCHAR2(22 BYTE)
     *
     * @ORM\Column(name="nomvoie2", type="string", length=22, nullable=true)
     */
    private $nomvoie2;
    
    /**
     * @var string
     * PEVP : NOMVOIE3	VARCHAR2(22 BYTE)
     *
     * @ORM\Column(name="nomvoie3", type="string", length=22, nullable=true)
     */
    private $nomvoie3;

    /**
     * @var string
     * PEVP : CODEPOSTAL	CHAR(5 BYTE)
     *
     * @ORM\Column(name="codepostal", type="string", length=5, nullable=true)
     */
    private $codepostal;
    
    /**
     * @var string
     * PEVP : CODEINSEE	CHAR(10 BYTE)
     *
     * @ORM\Column(name="codeinsee", type="string", length=10, nullable=true)
     */
    private $codeinsee;
   
    /**
     * @var string
     * PEVP : VILLE	VARCHAR2(26 BYTE)
     *
     * @ORM\Column(name="ville", type="string", length=26, nullable=true)
     */
    private $ville;

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
