<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index AS index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AdresseRnvp
 *
 * @ORM\Table(name="adresse_rnvp",
 *                      uniqueConstraints={@UniqueConstraint(name="abosoc_info_unique",columns={"cadrs","adresse","lieudit","cp","ville"})}
 *                      , indexes={@index(name="adresse_insee_idx", columns={"adresse", "insee"})
 *                                  ,@index(name="adresse_geo_etat_idx", columns={"geo_etat"})
 *                                  }
 *              )
 * @UniqueEntity(fields={"cAdrs","adresse","lieuDit","cp","ville"}, message="Attention cette adresse normalisée existe déjà!")
 * 
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\AdresseRnvpRepository")
 */
class AdresseRnvp
{
    CONST GEOCONCEPT = 1;
    CONST GOOGLE = 2;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="cadrs", type="string", length=100, nullable=true)
     */
    protected $cAdrs;

    /**
     * @var string
     * 
     * @ORM\Column(name="adresse", type="string", length=100, nullable=false)
     */
    protected $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="lieudit", type="string", length=100, nullable=true)
     */
    protected $lieuDit;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    protected $cp;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="ville", type="string", length=45, nullable=false)
     */
    protected $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5, nullable=true)
     */
    protected $insee;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    protected $commune;

    /**
     * @var integer
     *
     * @ORM\Column(name="geox", type="decimal", precision=10, scale=7, nullable=true)
     */
    protected $geox;

    /**
     * @var integer
     *
     * @ORM\Column(name="geoy", type="decimal", precision=10, scale=7, nullable=true)
     */
    protected $geoy;

	
    /**
     * @var integer
     *
     * @ORM\Column(name="geo_score", type="integer", nullable=true)
     */
    protected $geoScore;

    /**
     * @var integer
     *
     * @ORM\Column(name="geo_type", type="integer", nullable=true)
     */
    protected $geoType;
	
	/**
     * @var boolean
     * 1 si point de livraison saisie
     * 0 dans les autres cas 
     * @ORM\Column(name="type_rnvp", type="integer" )
     */
    protected $typeRnvp;
    

    /**
     * @var integer
     * 0 si KO
     * 1 si OK Automatique
     * 2 si OK Manuel
     * NULL si non encore geocode
     *
     * @ORM\Column(name="geo_etat", type="integer", nullable=true)
     */
    protected $geoEtat;
    

    /**
     * @var integer
     * Possibilite de considerer cette adresse comme un point de livraison
     * 1 si oui
     * 0 autrement
     *
     * @ORM\Column(name="stop_livraison_possible", columnDefinition="ENUM('0', '1')", type="integer")
     */
    protected $stopLivraisonPossible;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_id_modif", referencedColumnName="id", nullable=true)
     */
    protected $utilisateurModif;

	
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    protected $dateModif;

    
    /**
     * Bidirectional - (INVERSE SIDE)
     *
     * @ORM\ManyToMany(targetEntity="Ams\DistributionBundle\Entity\InfoPortage", mappedBy="adresses")
     */
    protected $infosPortagesAdresse;
    
    /**
     * Bidirectional - (INVERSE SIDE)
     *
     * @ORM\ManyToMany(targetEntity="Ams\DistributionBundle\Entity\InfoPortage", mappedBy="livraisons")
     */
    protected $infosPortagesLivraison;
        
    /**
     * @var \Ams\AdresseBundle\Entity\Adresse
     *
     * @ORM\OneToMany(targetEntity="\Ams\AdresseBundle\Entity\Adresse", mappedBy="rnvp")
     */
    protected $adresses;
    /**
     * @var \Ams\AdresseBundle\Entity\Adresse
     *
     * @ORM\OneToMany(targetEntity="\Ams\AdresseBundle\Entity\Adresse", mappedBy="pointLivraison")
     */
    protected $adresses_livraison;
    
    /**
     * @var integer
     * 1 => GEOCONCEPT
     * 2 => GOOGLE
     * @ORM\Column(name="ws_source", type="smallint", nullable=true,options={"default":1})
     */
    protected $wsSource;
    
    
    
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }
    
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
     * Set cAdrs
     *
     * @param string $cAdrs
     * @return AdresseRnvp
     */
    public function setCAdrs($cAdrs)
    {
        $this->cAdrs = $cAdrs;
    
        return $this;
    }

    /**
     * Get cAdrs
     *
     * @return string 
     */
    public function getCAdrs()
    {
        return $this->cAdrs;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return AdresseRnvp
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
     * Set lieuDit
     *
     * @param string $lieuDit
     * @return AdresseRnvp
     */
    public function setLieuDit($lieuDit)
    {
        $this->lieuDit = $lieuDit;
    
        return $this;
    }

    /**
     * Get lieuDit
     *
     * @return string 
     */
    public function getLieuDit()
    {
        return $this->lieuDit;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return AdresseRnvp
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    
        return $this;
    }

    /**
     * Get cp
     *
     * @return string 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return AdresseRnvp
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    
        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set insee
     *
     * @param string $insee
     * @return AdresseRnvp
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;
    
        return $this;
    }

    /**
     * Get insee
     *
     * @return string 
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set geox
     *
     * @param integer $geox
     * @return AdresseRnvp
     */
    public function setGeox($geox)
    {
        $this->geox = $geox;
    
        return $this;
    }

    /**
     * Get geox
     *
     * @return integer 
     */
    public function getGeox()
    {
        return $this->geox;
    }

    /**
     * Set geoy
     *
     * @param integer $geoy
     * @return AdresseRnvp
     */
    public function setGeoy($geoy)
    {
        $this->geoy = $geoy;
    
        return $this;
    }

    /**
     * Get geoy
     *
     * @return integer 
     */
    public function getGeoy()
    {
        return $this->geoy;
    }

    /**
     * Set geoScore
     *
     * @param integer $geoScore
     * @return AdresseRnvp
     */
    public function setGeoScore($geoScore)
    {
        $this->geoScore = $geoScore;
    
        return $this;
    }

    /**
     * Get geoScore
     *
     * @return integer 
     */
    public function getGeoScore()
    {
        return $this->geoScore;
    }

    /**
     * Set geoType
     *
     * @param integer $geoType
     * @return AdresseRnvp
     */
    public function setGeoType($geoType)
    {
        $this->geoType = $geoType;
    
        return $this;
    }

    /**
     * Get geoType
     *
     * @return integer 
     */
    public function getGeoType()
    {
        return $this->geoType;
    }

    /**
     * Set geoEtat
     *
     * @param integer $geoEtat
     * @return AdresseRnvp
     */
    public function setGeoEtat($geoEtat)
    {
        $this->geoEtat = $geoEtat;
    
        return $this;
    }

    /**
     * Get geoEtat
     *
     * @return integer 
     */
    public function getGeoEtat()
    {
        return $this->geoEtat;
    }

    /**
     * Set stopLivraisonPossible
     *
     * @param integer $stopLivraisonPossible
     * @return AdresseRnvp
     */
    public function setStopLivraisonPossible($stopLivraisonPossible)
    {
        $this->stopLivraisonPossible = $stopLivraisonPossible;
    
        return $this;
    }

    /**
     * Get stopLivraisonPossible
     *
     * @return integer 
     */
    public function getStopLivraisonPossible()
    {
        return $this->stopLivraisonPossible;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return AdresseRnvp
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
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return AdresseRnvp
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune = null)
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

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return AdresseRnvp
     */
    public function setUtilisateurModif(\Ams\SilogBundle\Entity\Utilisateur $utilisateurModif = null)
    {
        $this->utilisateurModif = $utilisateurModif;
    
        return $this;
    }

    /**
     * Get utilisateurModif
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurModif()
    {
        return $this->utilisateurModif;
    }

	/**
     * Set typeRnvp
     *
     * @param integer $typeRnvp
     * @return AdresseRnvp
     */
    public function setTypeRnvp($typeRnvp)
    {
        $this->typeRnvp = $typeRnvp;
    
        return $this;
    }

    /**
     * Get typeRnvp
     *
     * @return integer 
     */
    public function getTypeRnvp()
    {
        return $this->typeRnvp;
    }
 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->infosPortagesAdresse = new \Doctrine\Common\Collections\ArrayCollection();
        $this->infosPortagesLivraison = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add infosPortagesAdresse
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortagesAdresse
     * @return AdresseRnvp
     */
    public function addInfosPortagesAdresse(\Ams\DistributionBundle\Entity\InfoPortage $infosPortagesAdresse)
    {
        $this->infosPortagesAdresse[] = $infosPortagesAdresse;

        return $this;
    }

    /**
     * Remove infosPortagesAdresse
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortagesAdresse
     */
    public function removeInfosPortagesAdresse(\Ams\DistributionBundle\Entity\InfoPortage $infosPortagesAdresse)
    {
        $this->infosPortagesAdresse->removeElement($infosPortagesAdresse);
    }

    /**
     * Get infosPortagesAdresse
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInfosPortagesAdresse()
    {
        return $this->infosPortagesAdresse;
    }

    /**
     * Add infosPortagesLivraison
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortagesLivraison
     * @return AdresseRnvp
     */
    public function addInfosPortagesLivraison(\Ams\DistributionBundle\Entity\InfoPortage $infosPortagesLivraison)
    {
        $this->infosPortagesLivraison[] = $infosPortagesLivraison;

        return $this;
    }

    /**
     * Remove infosPortagesLivraison
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortagesLivraison
     */
    public function removeInfosPortagesLivraison(\Ams\DistributionBundle\Entity\InfoPortage $infosPortagesLivraison)
    {
        $this->infosPortagesLivraison->removeElement($infosPortagesLivraison);
    }

    /**
     * Get infosPortagesLivraison
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInfosPortagesLivraison()
    {
        return $this->infosPortagesLivraison;
    }


    
    public function __toString() {
        return $this->adresse.' - '.$this->ville.', '.$this->cp;
    }

    /**
     * Set wsSource
     *
     * @param integer $wsSource
     * @return AdresseRnvp
     */
    public function setWsSource($wsSource)
    {
        $this->wsSource = $wsSource;

        return $this;
    }

    /**
     * Get wsSource
     *
     * @return integer 
     */
    public function getWsSource()
    {
        return $this->wsSource;
    }

    /**
     * Add adresses
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adresses
     * @return AdresseRnvp
     */
    public function addAdress(\Ams\AdresseBundle\Entity\Adresse $adresses)
    {
        $this->adresses[] = $adresses;

        return $this;
    }

    /**
     * Remove adresses
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adresses
     */
    public function removeAdress(\Ams\AdresseBundle\Entity\Adresse $adresses)
    {
        $this->adresses->removeElement($adresses);
    }

    /**
     * Get adresses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdresses()
    {
        return $this->adresses;
    }

    /**
     * Add adresses_livraison
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adressesLivraison
     * @return AdresseRnvp
     */
    public function addAdressesLivraison(\Ams\AdresseBundle\Entity\Adresse $adressesLivraison)
    {
        $this->adresses_livraison[] = $adressesLivraison;

        return $this;
    }

    /**
     * Remove adresses_livraison
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adressesLivraison
     */
    public function removeAdressesLivraison(\Ams\AdresseBundle\Entity\Adresse $adressesLivraison)
    {
        $this->adresses_livraison->removeElement($adressesLivraison);
    }

    /**
     * Get adresses_livraison
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdressesLivraison()
    {
        return $this->adresses_livraison;
    }
}
