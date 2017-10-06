<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;
use Ams\ExtensionBundle\Validator\Constraints as AmsAssert;

/**
 * ParutionSpeciale
 *
 * @ORM\Table(name="parution_speciale")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ParutionSpecialeRepository")
 */
class ParutionSpeciale
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="date")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="date")
     * @AmsAssert\DatePosterieure
     */
    private $dateParution;

    
    /**
     * @var \RefEvenement
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefEvenement")
     * @ORM\JoinColumn(name="evenement_id", referencedColumnName="id", nullable=true)
     */
    private $evenement;
    
    
    

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $produit;
    
    
    


    
    
      /**
     * @var string
     *
     * @ORM\Column(name="zone_distribution", type="string", length=255)
     */
    private $zoneDistribution;
    
  
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
     * })
     */
    private $utilisateur;
    
    
    
    public function __construct(){
        
        $this->dateCreation = new \Datetime();
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
     * Set libelle
     *
     * @param string $libelle
     * @return ParutionSpeciale
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return ParutionSpeciale
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return ParutionSpeciale
     */
    public function setDateParution($dateParution)
    {
        $this->dateParution = $dateParution;

        return $this;
    }

    /**
     * Get dateParution
     *
     * @return \DateTime 
     */
    public function getDateParution()
    {
        return $this->dateParution;
    }
    
    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return ParutionSpeciale
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
     * Set zoneDistribution
     *
     * @param string $zoneDistribution
     * @return ParutionSpeciale
     */
    public function setZoneDistribution($zoneDistribution)
    {
        $this->zoneDistribution = $zoneDistribution;

        return $this;
    }

    /**
     * Get zoneDistribution
     *
     * @return string 
     */
    public function getZoneDistribution()
    {
        return $this->zoneDistribution;
    }

    /**
     * Set evenement
     *
     * @param \Ams\ReferentielBundle\Entity\RefEvenement $evenement
     * @return ParutionSpeciale
     */
    public function setEvenement(\Ams\ReferentielBundle\Entity\RefEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement
     *
     * @return \Ams\ReferentielBundle\Entity\RefEvenement 
     */
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return ParutionSpeciale
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
