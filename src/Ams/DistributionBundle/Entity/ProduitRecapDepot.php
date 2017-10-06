<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicRecap
 *
 * @ORM\Table(name="produit_recap_depot")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ProduitRecapDepotRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ProduitRecapDepot
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
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $dateDistrib;

    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     */
    private $depot;

    /**
     * @var \Ams\FichierBundle\Entity\FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap", inversedBy="produitRecapDepots")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id")
     */
    private $ficRecap;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $produit;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_exemplaires", type="integer", nullable=false)
     */
    private $nbExemplaires;
    
    public function __construct()
    {
        
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
     * @return ProduitRecapDepot
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
     * Set nbExemplaires
     *
     * @param integer $nbExemplaires
     * @return ProduitRecapDepot
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
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return ProduitRecapDepot
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot = null)
    {
        $this->depot = $depot;
    
        return $this;
    }

    /**
     * Get depot
     *
     * @return \Ams\SilogBundle\Entity\Depot 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set FicRecap
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap
     * @return ProduitRecapDepot
     */
    public function setFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap = null)
    {
        $this->ficRecap = $ficRecap;
    
        return $this;
    }

    /**
     * Get FicRecap
     *
     * @return \Ams\FichierBundle\Entity\FicRecap 
     */
    public function getFicRecap()
    {
        return $this->ficRecap;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return ProduitRecapDepot
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
