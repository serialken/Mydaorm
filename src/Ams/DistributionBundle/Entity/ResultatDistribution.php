<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResultatDistribution
 *
 * @ORM\Table(name="resultat_distribution")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ResultatDistributionRepository")
 */
class ResultatDistribution
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
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     */
    private $depot;

    /**
     * @var string
     *
     * @ORM\Column(name="nb_paquet", type="string", length=9)
     */
    private $nbPaquet;

    /**
     * @var string
     *
     * @ORM\Column(name="nb_appoint", type="string", length=9)
     */
    private $nbAppoint;

    /**
     * @var integer
     *
     * @ORM\Column(name="conditionnement", type="integer")
     */
    private $conditionnement;

    /**
     * @var \Ams\ProduitBundle\Entity\Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=false)
     */
    private $produit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distribution", type="date")
     */
    private $dateDistribution;

    /**
     * @var boolean
     *
     * @ORM\Column(name="passe", type="boolean")
     */
    private $passe;

    /**
     * @var string
     *
     * @ORM\Column(name="passe_value", type="string", length=7)
     */
    private $passeValue;


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
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return QtesQuotidiennes
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
     * Set nbPaquet
     *
     * @param string $nbPaquet
     * @return ResultatDistribution
     */
    public function setNbPaquet($nbPaquet)
    {
        $this->nbPaquet = $nbPaquet;

        return $this;
    }

    /**
     * Get nbPaquet
     *
     * @return string 
     */
    public function getNbPaquet()
    {
        return $this->nbPaquet;
    }

    /**
     * Set nbAppoint
     *
     * @param string $nbAppoint
     * @return ResultatDistribution
     */
    public function setNbAppoint($nbAppoint)
    {
        $this->nbAppoint = $nbAppoint;

        return $this;
    }

    /**
     * Get nbAppoint
     *
     * @return string 
     */
    public function getNbAppoint()
    {
        return $this->nbAppoint;
    }

    /**
     * Set conditionnement
     *
     * @param integer $conditionnement
     * @return ResultatDistribution
     */
    public function setConditionnement($conditionnement)
    {
        $this->conditionnement = $conditionnement;

        return $this;
    }

    /**
     * Get conditionnement
     *
     * @return integer 
     */
    public function getConditionnement()
    {
        return $this->conditionnement;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return QtesQuotidiennes
     */
    public function setProduit($produit)
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
     * Set dateDistribution
     *
     * @param \DateTime $dateDistribution
     * @return ResultatDistribution
     */
    public function setDateDistribution($dateDistribution)
    {
        $this->dateDistribution = $dateDistribution;

        return $this;
    }

    /**
     * Get dateDistribution
     *
     * @return \DateTime 
     */
    public function getDateDistribution()
    {
        return $this->dateDistribution;
    }

    /**
     * Set passe
     *
     * @param boolean $passe
     * @return ResultatDistribution
     */
    public function setPasse($passe)
    {
        $this->passe = $passe;

        return $this;
    }

    /**
     * Get passe
     *
     * @return boolean 
     */
    public function getPasse()
    {
        return $this->passe;
    }

    /**
     * Set passeValue
     *
     * @param string $passeValue
     * @return ResultatDistribution
     */
    public function setPasseValue($passeValue)
    {
        $this->passeValue = $passeValue;

        return $this;
    }

    /**
     * Get passeValue
     *
     * @return string 
     */
    public function getPasseValue()
    {
        return $this->passeValue;
    }
}
