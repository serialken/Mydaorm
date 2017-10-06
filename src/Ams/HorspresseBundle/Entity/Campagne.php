<?php

namespace Ams\HorspresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Ams\AmsProduitBundle\Entity\Societe;

/**
 * Campagne
 *
 * @ORM\Table(name="campagne_hp")
 * @ORM\Entity(repositoryClass="Ams\HorspresseBundle\Entity\CampagneRepository")
 */
class Campagne
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="debordement", type="boolean")
     */
    private $debordement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime")
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime")
     */
    private $dateFin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_crea", type="datetime")
     */
    private $dateCrea;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    /**
     * @var string
     *
     * @ORM\Column(name="statut", type="string", length=255)
     */
    private $statut;

    /**
     *
     * @var @ORM\OneToMany(
     *      targetEntity="Ams\HorspresseBundle\Entity\ConfigurationCampagne",
     *      mappedBy="campagne"
     * )
     */
    private $configurations;

    /**
     *
     * @ORM\OneToOne(
     *  targetEntity="Ams\ProduitBundle\Entity\Societe"
     * )
     */
    private $societe;

    /**
     *
     * @ORM\OneToOne(
     *  targetEntity="Ams\ProduitBundle\Entity\Produit"
     * )
     */
    private $produit;
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
     * Set libelle
     *
     * @param string $libelle
     * @return Campagne
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
     * Set type
     *
     * @param string $type
     * @return Campagne
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set debordement
     *
     * @param boolean $debordement
     * @return Campagne
     */
    public function setDebordement($debordement)
    {
        $this->debordement = $debordement;

        return $this;
    }

    /**
     * Get debordement
     *
     * @return boolean 
     */
    public function getDebordement()
    {
        return $this->debordement;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Campagne
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
     * Set statut
     *
     * @param string $statut
     * @return Campagne
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return string 
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Campagne
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
     * Set dateCrea
     *
     * @param \DateTime $dateCrea
     * @return Campagne
     */
    public function setDateCrea($dateCrea)
    {
        $this->dateCrea = $dateCrea;

        return $this;
    }

    /**
     * Get dateCrea
     *
     * @return \DateTime 
     */
    public function getDateCrea()
    {
        return $this->dateCrea;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return Campagne
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
     * Constructor
     */
    public function __construct()
    {
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add configurations
     *
     * @param \Ams\HorspresseBundle\Entity\ConfigurationCampagne $configurations
     * @return Campagne
     */
    public function addConfiguration(\Ams\HorspresseBundle\Entity\ConfigurationCampagne $configurations)
    {
        $this->configurations[] = $configurations;

        return $this;
    }

    /**
     * Remove configurations
     *
     * @param \Ams\HorspresseBundle\Entity\ConfigurationCampagne $configurations
     */
    public function removeConfiguration(\Ams\HorspresseBundle\Entity\ConfigurationCampagne $configurations)
    {
        $this->configurations->removeElement($configurations);
    }

    /**
     * Get configurations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return Campagne
     */
    public function setSociete(\Ams\ProduitBundle\Entity\Societe $societe = null)
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return \Ams\ProduitBundle\Entity\Societe 
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return Campagne
     */
    public function setProduit(\Ams\ProduitBundle\Entity\Produit $produit = null)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduit()
    {
        return $this->produit;
    }
}
