<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Ams\ExtensionBundle\Validator\Constraints as AmsAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Depot
 *
 * @ORM\Table(name="depot")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\DepotRepository")
 * @UniqueEntity(fields="code", message="Le code saisi existe deja !")
 */
class Depot
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
     * le code ne doit pas faire plus de 3 caracteres
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, unique=true, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=150, nullable=false)
     */
    private $adresse;

    /**
     * @var \Commune
     *
     * @ORM\ManyToOne(targetEntity="Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=false)
     */
    private $commune; 

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true)
     */
    private $utilisateurId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    private $actif;
    
    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @var \DepotCommune
     *
     * @ORM\OneToMany(targetEntity="Ams\AdresseBundle\Entity\DepotCommune",  mappedBy="depot")
     */
    private $depotCommunes;
    
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
     * @var integer
     * 
     * @ORM\Column(name="geox", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $geoX;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="geoy", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $geoY;
    
    /**
     * @var boolean
     * 1 si défini comme point de départ
     * 0 si pas défini comme point de depart
     * 
     * @ORM\Column(name="pt_depart", type="integer")
     * 
     */
    private $ptDepart;
    
    /**
     * Get geoX
     * 
     * @return integer
     */
    public function getGeoX()
    {
        return $this->geoX;
    }
    
    /**
     * Set geoX
     * 
     * @param integer $val
     * @return Depot
     */
    public function SetGeoX($val)
    {
        $this->geoX = $val;
        return $this;
    }
    
    /**
     * Get geoY
     * 
     * @return integer
     */
    public function getGeoY()
    {
        return $this->geoY;
    }
    
    /**
     * Set geoY
     * 
     * @param integer $val
     * @return Depot
     */
    public function SetGeoY($val)
    {
        $this->geoY = $val;
        return $this;
    }
    
       /**
     * Set ordre
     *
     * @param integer $ordre
     * @return Depot
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
    /**
     * Get ptDepart
     * 
     * @return integer
     */
    public function getPtDepart()
    {
        return $this->ptDepart;
    }
    
    /**
     * Set ptDepart
     * 
     * @param integer $val
     * @return Depot
     */
    public function SetPtDepart($val)
    {
        $this->ptDepart = $val;
        return $this;
    }
    
    /**
     * Set code
     *
     * @param string $code
     * @return Depot
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Depot
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return Depot
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    
        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Depot
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Depot
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
    
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return Depot
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;
    
        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set utilisateurId
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurId
     * @return Depot
     */
    public function setUtilisateurId(\Ams\SilogBundle\Entity\Utilisateur $utilisateurId = null)
    {
        $this->utilisateurId = $utilisateurId;
    
        return $this;
    }

    /**
     * Get utilisateurId
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurId()
    {
        return $this->utilisateurId;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depotCommunes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ptDepart = 0;
    }
    
    /**
     * Add depotCommunes
     *
     * @param \Ams\AdresseBundle\Entity\DepotCommune $depotCommunes
     * @return Depot
     */
    public function addDepotCommune(\Ams\AdresseBundle\Entity\DepotCommune $depotCommunes)
    {
        $this->depotCommunes[] = $depotCommunes;
    
        return $this;
    }

    /**
     * Remove depotCommunes
     *
     * @param \Ams\AdresseBundle\Entity\DepotCommune $depotCommunes
     */
    public function removeDepotCommune(\Ams\AdresseBundle\Entity\DepotCommune $depotCommunes)
    {
        $this->depotCommunes->removeElement($depotCommunes);
    }

    /**
     * Get depotCommunes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepotCommunes()
    {
        return $this->depotCommunes;
    }
    
    

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return Depot
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune)
    {
        $this->commune = $commune;
    
        return $this;
    }

    /**
     * Get commune
     *
     * @return \Ams\AdresseBundle\Entity\Commune 
     */
    public function getCommune()
    {
        return $this->commune;
    }


    
    public function __toString() {
        return $this->code.' - '.$this->libelle;    }
    
}
