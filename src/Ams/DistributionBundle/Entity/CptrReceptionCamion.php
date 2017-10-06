<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CptrReceptionCamion
 *
 * @ORM\Table(name="cptr_reception_camion")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\repository\CptrReceptionCamionRepository")
 */
class CptrReceptionCamion
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
     * @var integer
     * @ORM\Column(name="id_casl", type="integer")
     */
    private $idCasl;
    
    /**
     * @var \Ams\ProduitBundle\Entity\Produit
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     */
    private $produit;

    /**
     * @var integer
     * @ORM\Column(name="qte_prevue", type="integer")
     */
    private $qtePrevue;

    /**
     * @var integer
     * @ORM\Column(name="qte_recue", type="integer")
     */
    private $qteRecue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_reception", type="datetime")
     */

    private $heureReception;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_cpt_rendu", type="datetime")
     */
    private $dateCptRendu;

    /**
     *@var \Ams\SilogBundle\Entity\Utilisateur
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="commentaires", type="string", length=255)
     */
    private $commentaires;


    

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
     * Set idCasl
     *
     * @param integer $idCasl
     * @return CptrReceptionCamion
     */
    public function setIdCasl($idCasl)
    {
        $this->idCasl = $idCasl;

        return $this;
    }

    /**
     * Get idCasl
     *
     * @return integer 
     */
    public function getIdCasl()
    {
        return $this->idCasl;
    }

    /**
     * Set qtePrevue
     *
     * @param integer $qtePrevue
     * @return CptrReceptionCamion
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
     * @return CptrReceptionCamion
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
     * Set heureReception
     *
     * @param \DateTime $heureReception
     * @return CptrReceptionCamion
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
     * Set dateCptRendu
     *
     * @param \DateTime $dateCptRendu
     * @return CptrReceptionCamion
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
     * Set commentaires
     *
     * @param string $commentaires
     * @return CptrReceptionCamion
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
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return CptrReceptionCamion
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
     * Set user
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $user
     * @return CptrReceptionCamion
     */
    public function setUser(\Ams\SilogBundle\Entity\Utilisateur $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUser()
    {
        return $this->user;
    }
}
