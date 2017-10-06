<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * Employe
 *
 * @ORM\Table(name="employe")
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmployeRepository")
 * 
 */
class Employe
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
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="saloid", type="string", length=36, nullable=false)
     */
    private $saloid;
    
    /**
     * Matricule du salarie
     * @var string
     * PEVP : MAT	CHAR(8 BYTE)
     *
     * @ORM\Column(name="matricule", type="string", length=10, unique=true, nullable=false)
     */
    private $matricule;

    /**
     * @var string
     * PEVP : NOM	CHAR(30 BYTE)
     * PEVP : NOMPATRONYMIQUE	CHAR(30 BYTE)
     *
     * @ORM\Column(name="nom", type="string", length=40, nullable=false)
     */
    private $nom;

    /**
     * @var string
     * PEVP : PRENOM	CHAR(20 BYTE)
     *
     * @ORM\Column(name="prenom1", type="string", length=25, nullable=false)
     */
    private $prenom1;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom2", type="string", length=25, nullable=true)
     */
    private $prenom2;    

    /**
     * @var \RefCivilite
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefCivilite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="civilite_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $civilite;

    /**
     * @var \RefNationalite
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefNationalite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nationalite_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $nationalite;

    /**
     * @var string
     * PEVP : NSECU	CHAR(13 BYTE)
     *
     * @ORM\Column(name="secu_numero", type="string", length=13, nullable=true)
     */
    private $secuNumero;

    /**
     * @var string
     * PEVP : CLESECU	CHAR(2 BYTE)
     *
     * @ORM\Column(name="secu_cle", type="string", length=2, nullable=true)
     */
    private $secuCle;

    /**
     * @var \DateTime
     * PEVP : DATENAISSANCE	CHAR(8 BYTE)
     *
     * @ORM\Column(name="naissance_date", type="date", nullable=true)
     */
    private $naissanceDate;

    /**
     * @var string
     * PEVP : LIEUNAISSANCE	CHAR(5 BYTE)
     *
     * @ORM\Column(name="naissance_lieu", type="string", length=5, nullable=true)
     */
    private $naissanceLieu;

    /**
     * @var string
     * PEVP : PAYSNAISSANCE	CHAR(3 BYTE) 
     *
     * @ORM\Column(name="naissance_pays", type="string", length=3, nullable=true)
     */
    private $naissancePays;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sejour_date", type="date", nullable=true)
     */
    private $sejourDate;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set saloid
     *
     * @param string $saloid
     * @return Employe
     */
    public function setSaloid($saloid)
    {
        $this->saloid = $saloid;

        return $this;
    }

    /**
     * Get saloid
     *
     * @return string 
     */
    public function getSaloid()
    {
        return $this->saloid;
    }

    /**
     * Set matricule
     *
     * @param string $matricule
     * @return Employe
     */
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get matricule
     *
     * @return string 
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Employe
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom1
     *
     * @param string $prenom1
     * @return Employe
     */
    public function setPrenom1($prenom1)
    {
        $this->prenom1 = $prenom1;

        return $this;
    }

    /**
     * Get prenom1
     *
     * @return string 
     */
    public function getPrenom1()
    {
        return $this->prenom1;
    }

    /**
     * Set prenom2
     *
     * @param string $prenom2
     * @return Employe
     */
    public function setPrenom2($prenom2)
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    /**
     * Get prenom2
     *
     * @return string 
     */
    public function getPrenom2()
    {
        return $this->prenom2;
    }
    
    
    public function __toString() {
        return $this->matricule.' - '.$this->prenom1.' '.$this->nom;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tournees = new \Doctrine\Common\Collections\ArrayCollection();
    }
}