<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Volume d'un paquet de produits
 *
 * @ORM\Table(name="paquet_volume", indexes={@Index(name="idx_pqt_vol_date", columns={"date_distrib"}) })
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\PaquetVolumeRepository")
 */
class PaquetVolume
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $dateDistrib;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=false)
     */
    private $produit;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_exemplaires", type="integer", nullable=false)
     */
    private $nbExemplaires;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $utilisateur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;
    
    public function __construct()
    {
        $this->nbExemplaires    = 0;
        $this->dateModif = new \Datetime();
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
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return PaquetVolume
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->dateDistrib = $dateDistrib;
    
        return $this;
    }

    /**
     * Get dateDistrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->dateDistrib;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return PaquetVolume
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

    /**
     * Set nbExemplaires
     *
     * @param integer $nbExemplaires
     * @return PaquetVolume
     */
    public function setNbExemplaires($nbExemplaires)
    {
        $this->nbExemplaires = $nbExemplaires;
    
        return $this;
    }

    /**
     * Get nbExemplaires
     *
     * @return integer 
     */
    public function getNbExemplaires()
    {
        return $this->nbExemplaires;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return PaquetVolume
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

    /**
     * Set date_modif
     *
     * @param \DateTime $dateModif
     * @return PaquetVolume
     */
    public function setDateModif($dateModif)
    {
        $this->date_modif = $dateModif;

        return $this;
    }

    /**
     * Get date_modif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->date_modif;
    }
    
}
