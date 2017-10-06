<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * InfoPortage
 *
 * @ORM\Table(name="info_portage")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\InfoPortageRepository")
 */
class InfoPortage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255)
     */
    protected $valeur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date")
     */
    protected $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date")
     */
    protected $dateFin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime")
     */
    protected $dateModif;
    
    /**
     *
     * @var type integer
     * @ORM\Column(name="origine", type="integer")
     */
    protected $origine;
    
    
    /**
     *
     * @var type boolean
     * @ORM\Column(name="active", type="boolean", options={"default":true})
     */
    protected $active;

    
    /**
     * @var \TypeInfoPortage
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\TypeInfoPortage", cascade={"detach", "merge"})
     * @ORM\JoinColumn(name="type_info_id", referencedColumnName="id")
     */
    protected $typeInfoPortage;
        
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc",  cascade={"persist","detach", "merge"},  inversedBy="infosPortages")
     * @ORM\JoinTable(name="infos_portages_abonnes",
     *   joinColumns={
     *     @ORM\JoinColumn(name="info_portage_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="abonne_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $abonnes;
        
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp",  cascade={"persist","detach", "merge"},  inversedBy="infosPortagesAdresse")
     * @ORM\JoinTable(name="infos_portages_adresses",
     *   joinColumns={
     *     @ORM\JoinColumn(name="info_portage_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="adresse_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $adresses;
        
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp",  cascade={"persist","detach", "merge"},  inversedBy="infosPortagesLivraison")
     * @ORM\JoinTable(name="infos_portages_livraisons",
     *   joinColumns={
     *     @ORM\JoinColumn(name="info_portage_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="livraison_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $livraisons;

    
    /**
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private  $utilisateur;
    
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
     * Set valeur
     *
     * @param string $valeur
     * @return InfoPortage
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return InfoPortage
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
     * @return InfoPortage
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
     * @return InfoPortage
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
     * Set origine
     *
     * @param integer $origine
     * @return InfoPortage
     */
    public function setOrigine($origine)
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return integer 
     */
    public function getOrigine()
    {
        return $this->origine;
    }
    
    
    
    /**
     * Set active
     *
     * @param integer $origine
     * @return InfoPortage
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    

    /**
     * Set typeInfoPortage
     *
     * @param \Ams\DistributionBundle\Entity\TypeInfoPortage $typeInfoPortage
     * @return InfoPortage
     */
    public function setTypeInfoPortage(\Ams\DistributionBundle\Entity\TypeInfoPortage $typeInfoPortage = null)
    {
        $this->typeInfoPortage = $typeInfoPortage;

        return $this;
    }

    /**
     * Get typeInfoPortage
     *
     * @return \Ams\DistributionBundle\Entity\TypeInfoPortage 
     */
    public function getTypeInfoPortage()
    {
        return $this->typeInfoPortage;
    }

    /**
     * Add abonnes
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonnes
     * @return InfoPortage
     */
    public function addAbonne(\Ams\AbonneBundle\Entity\AbonneSoc $abonnes)
    {
        $this->abonnes[] = $abonnes;

        return $this;
    }

    /**
     * Remove abonnes
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonnes
     */
    public function removeAbonne(\Ams\AbonneBundle\Entity\AbonneSoc $abonnes)
    {
        $this->abonnes->removeElement($abonnes);
    }

    /**
     * Get abonnes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAbonnes()
    {
        return $this->abonnes;
    }

    /**
     * Add adresses
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRNVP $adresses
     * @return InfoPortage
     */
    public function addAdresse(\Ams\AdresseBundle\Entity\AdresseRNVP $adresses)
    {
        $this->adresses[] = $adresses;

        return $this;
    }

    /**
     * Remove adresses
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRNVP $adresses
     */
    public function removeAdresse(\Ams\AdresseBundle\Entity\AdresseRNVP $adresses)
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
     * Add livraisons
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRNVP $livraisons
     * @return InfoPortage
     */
    public function addLivraison(\Ams\AdresseBundle\Entity\AdresseRNVP $livraisons)
    {
        $this->livraisons[] = $livraisons;

        return $this;
    }

    /**
     * Remove livraisons
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRNVP $livraisons
     */
    public function removeLivraison(\Ams\AdresseBundle\Entity\AdresseRNVP $livraisons)
    {
        $this->livraisons->removeElement($livraisons);
    }

    /**
     * Get livraisons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLivraisons()
    {
        return $this->livraisons;
    }
    
    public function __clone() {
        if ($this->id) {
            $this->id = null;            
            $this->origine = 1;
            $this->abonnes = new ArrayCollection();
            $this->adresses = new ArrayCollection();
            $this->livraisons = new ArrayCollection();
            $this->dateModif = new \DateTime();
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->abonnes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adresses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->livraisons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dateModif = new \DateTime();
    }


    /**
     * Add adresses
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $adresses
     * @return InfoPortage
     */
  /*  public function addAdress(\Ams\AdresseBundle\Entity\AdresseRnvp $adresses)
    {
        $this->adresses[] = $adresses;

        return $this;
    }*/

    /**
     * Remove adresses
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $adresses
     */
    /*public function removeAdress(\Ams\AdresseBundle\Entity\AdresseRnvp $adresses)
    {
        $this->adresses->removeElement($adresses);
    }*/

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return InfoPortage
     */
    public function setUtilisateur(\Ams\SilogBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
