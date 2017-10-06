<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetailExNonDistrib
 *
 * @ORM\Table(name="cptr_detail_ex_non_distrib")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\CptrDetailExNonDistribRepository")
 */
class CptrDetailExNonDistrib
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
     * @var \Ams\ProduitBundle\Entity\Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     */
    private $societe;


    /**
     * @var \Ams\DistributionBundle\Entity\CptrDistribution
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CptrDistribution")
     * @ORM\JoinColumn(name="cptr_distrib_id", referencedColumnName="id", nullable=false)
     */
    private $cptrDistribId;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_ex_abo", type="integer")
     */
    private $nbExAbo;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_ex_diff", type="integer")
     */
    private $nbExDiff;
   
     /**
     * @var integer
     *
     * @ORM\Column(name="nb_abonne_non_livre", type="integer", nullable=true)
     */
    private $nbAbonneNonLivre;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_diff_non_livre", type="integer", nullable=true)
     */
    private $nbDiffNonLivre;
   

       /**
     * @var integer
     *
     * @ORM\Column(name="quantite_initiale", type="integer")
     */
    private $quantiteInitiale;
    
    /**
     * @var \date
     *
     * @ORM\Column(name="date_cpt_rendu", type="date")
     */
    private $dateCptRendu;

    /**
     * @var \Ams\DistributionBundle\Entity\CptrDistribution
     *
     * @ORM\ManyToOne(targetEntity="Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=false)
     */
    private $communeId;


       /**
     * Date export (vers Jade par exemple)
     * @var \DateTime
     *
     * @ORM\Column(name="date_export", type="datetime", nullable=true)
     */
    private $dateExport;

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
     * Set dateCptRendu
     *
     * @param \DateTime $dateCptRendu
     * @return CptrDetailExNonDistrib
     */
    public function setDateCptRendu($dateCptRendu)
    {
        $this->dateCptRendu = $dateCptRendu;

        return $this;
    }

    /**
     * Get dateCptRendu
     *
     * @return \DateTime 
     */
    public function getDateCptRendu()
    {
        return $this->dateCptRendu;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return CptrDetailExNonDistrib
     */
    public function setSociete(\Ams\ProduitBundle\Entity\Societe $societe)
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
     * Set cptrDistribId
     *
     * @param \Ams\DistributionBundle\Entity\CptrDistribution $cptrDistribId
     * @return CptrDetailExNonDistrib
     */
    public function setCptrDistribId(\Ams\DistributionBundle\Entity\CptrDistribution $cptrDistribId)
    {
        $this->cptrDistribId = $cptrDistribId;

        return $this;
    }

    /**
     * Get cptrDistribId
     *
     * @return \Ams\DistributionBundle\Entity\CptrDistribution 
     */
    public function getCptrDistribId()
    {
        return $this->cptrDistribId;
    }

    /**
     * Set quantiteInitiale
     *
     * @param integer $quantiteInitiale
     * @return CptrDetailExNonDistrib
     */
    public function setQuantiteInitiale($quantiteInitiale)
    {
        $this->quantiteInitiale = $quantiteInitiale;

        return $this;
    }

    /**
     * Get quantiteInitiale
     *
     * @return integer 
     */
    public function getQuantiteInitiale()
    {
        return $this->quantiteInitiale;
    }

    /**
     * Set nbExAbo
     *
     * @param integer $nbExAbo
     * @return CptrDetailExNonDistrib
     */
    public function setNbExAbo($nbExAbo)
    {
        $this->nbExAbo = $nbExAbo;

        return $this;
    }

    /**
     * Get nbExAbo
     *
     * @return integer 
     */
    public function getNbExAbo()
    {
        return $this->nbExAbo;
    }

    /**
     * Set nbExDiff
     *
     * @param integer $nbExDiff
     * @return CptrDetailExNonDistrib
     */
    public function setNbExDiff($nbExDiff)
    {
        $this->nbExDiff = $nbExDiff;

        return $this;
    }

    /**
     * Get nbExDiff
     *
     * @return integer 
     */
    public function getNbExDiff()
    {
        return $this->nbExDiff;
    }





    /**
     * Set nbAbonneNonLivre
     *
     * @param integer $nbAbonneNonLivre
     * @return CptrDetailExNonDistrib
     */
    public function setNbAbonneNonLivre($nbAbonneNonLivre)
    {
        $this->nbAbonneNonLivre = $nbAbonneNonLivre;

        return $this;
    }

    /**
     * Get nbAbonneNonLivre
     *
     * @return integer 
     */
    public function getNbAbonneNonLivre()
    {
        return $this->nbAbonneNonLivre;
    }

    /**
     * Set nbDiffNonLivre
     *
     * @param integer $nbDiffNonLivre
     * @return CptrDetailExNonDistrib
     */
    public function setNbDiffNonLivre($nbDiffNonLivre)
    {
        $this->nbDiffNonLivre = $nbDiffNonLivre;

        return $this;
    }

    /**
     * Get nbDiffNonLivre
     *
     * @return integer 
     */
    public function getNbDiffNonLivre()
    {
        return $this->nbDiffNonLivre;
    }

    /**
     * Set communeId
     *
     * @param \Ams\AdresseBundle\Entity\Commune $communeId
     * @return CptrDetailExNonDistrib
     */
    public function setCommuneId(\Ams\AdresseBundle\Entity\Commune $communeId)
    {
        $this->communeId = $communeId;

        return $this;
    }

    /**
     * Get communeId
     *
     * @return \Ams\AdresseBundle\Entity\Commune 
     */
    public function getCommuneId()
    {
        return $this->communeId;
    }

    /**
     * Set dateExport
     *
     * @param \DateTime $dateExport
     * @return CptrDetailExNonDistrib
     */
    public function setDateExport($dateExport)
    {
        $this->dateExport = $dateExport;

        return $this;
    }

    /**
     * Get dateExport
     *
     * @return \DateTime 
     */
    public function getDateExport()
    {
        return $this->dateExport;
    }
}
