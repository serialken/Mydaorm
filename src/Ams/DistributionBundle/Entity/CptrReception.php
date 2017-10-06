<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
/**
 * cptrReception
 *
 * @ORM\Table(name="cptr_reception", indexes={@Index(name="idx_date_export", columns={"date_export"})
 *                                              , @Index(name="cr_recept_idx_dat_prd", columns={"date_cpt_rendu","product_id"})
 *                                              , @Index(name="cr_recept_idx_date_cpt_rendu", columns={"date_cpt_rendu"})
 *                                          }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\CptrReceptionRepository")
 */
class CptrReception
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
     * Tournee
     * @var \Ams\ModeleBundle\Entity\GroupeTournee
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\GroupeTournee")
     * @ORM\JoinColumn(name="groupe_tournee_id", referencedColumnName="id", nullable=true)
     */
    private $groupe;
    
    
    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $produit;

     /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tournee;


    /**
     * @var integer
     *
     * @ORM\Column(name="qte_prevue", type="integer")
     */
    private $qtePrevue;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte_recue", type="integer")
     */
    private $qteRecue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_reception", type="datetime", length=10, nullable=true)
     */
    private $heureReception;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaires", type="text", nullable=true)
     */
    private $commentaires;
    
    /**
     * @var \Date
     *
     * @ORM\Column(name="date_cpt_rendu", type="date")
     */
    private $dateCptRendu;

     /**
     * Date export (vers Jade par exemple)
     * @var \DateTime
     *
     * @ORM\Column(name="date_export", type="datetime", nullable=true)
     */
    private $dateExport;

    /**
     * 
     * @var boolean
     *
     * @ORM\Column(name="non_modifiable", type="boolean", nullable=true)
     */
    private $nonModifiable;


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
     * Set qtePrevue
     *
     * @param integer $qtePrevue
     * @return cptrReception
     */
    public function setQtePrevue($qtePrevue)
    {
        $this->qtePrevue = $qtePrevue;

        return $this;
    }

    /**
     * Get qtePrevue
     *
     * @return integer 
     */
    public function getQtePrevue()
    {
        return $this->qtePrevue;
    }

    /**
     * Set qteRecue
     *
     * @param integer $qteRecue
     * @return cptrReception
     */
    public function setQteRecue($qteRecue)
    {
        $this->qteRecue = $qteRecue;

        return $this;
    }

    /**
     * Get qteRecue
     *
     * @return integer 
     */
    public function getQteRecue()
    {
        return $this->qteRecue;
    }

    

    /**
     * Set commentaires
     *
     * @param string $commentaires
     * @return cptrReception
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires
     *
     * @return string 
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }
   
   


 

   

    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return CptrReception
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot)
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
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return CptrReception
     */
    public function setProduit(\Ams\ProduitBundle\Entity\Produit $produit)
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
     * Set groupe
     *
     * @param \Ams\ModeleBundle\Entity\GroupeTournee $groupe
     * @return CptrReception
     */
    public function setGroupe(\Ams\ModeleBundle\Entity\GroupeTournee $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Ams\ModeleBundle\Entity\GroupeTournee 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee
     * @return CptrReception
     */
    public function setTournee(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee = null)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get tournee
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getTournee()
    {
        return $this->tournee;
    }

    /**
     * Set dateCptRendu
     *
     * @param \DateTime $dateCptRendu
     * @return CptrReception
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
     * Set heureReception
     *
     * @param \DateTime $heureReception
     * @return CptrReception
     */
    public function setHeureReception($heureReception)
    {
        $this->heureReception = $heureReception;

        return $this;
    }

    /**
     * Get heureReception
     *
     * @return \DateTime 
     */
    public function getHeureReception()
    {
        return $this->heureReception;
    }

    /**
     * Set dateExport
     *
     * @param \DateTime $dateExport
     * @return CptrReception
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

    /**
     * Set nonModifiable
     *
     * @param boolean $nonModifiable
     * @return CptrReception
     */
    public function setNonModifiable($nonModifiable)
    {
        $this->nonModifiable = $nonModifiable;

        return $this;
    }

    /**
     * Get nonModifiable
     *
     * @return boolean 
     */
    public function getNonModifiable()
    {
        return $this->nonModifiable;
    }
}
