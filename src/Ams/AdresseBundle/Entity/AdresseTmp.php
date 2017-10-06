<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index AS index;

/**
 * Adresse
 *
 * @ORM\Table(name="adresse_tmp", indexes={
 *                                      @index(name="adresse_ext_idx", columns={"vol3", "vol4", "vol5", "cp", "ville"})
 *                                      }
 *              )
 *           
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\AdresseTmpRepository")
 */
class AdresseTmp
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=100, nullable=false)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=100, nullable=false)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=100, nullable=false)
     */
    private $vol5;

    /**
     * @var string
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=45, nullable=false)
     */
    private $ville;

    /**
     * @var \AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="rnvp_id", referencedColumnName="id", nullable=true)
     */
    private $rnvp;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;

    /**
     * @var \Ams\AdresseBundle\Entity\AdresseRnvpEtat
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvpEtat")
     * @ORM\JoinColumn(name="adresse_rnvp_etat_id", referencedColumnName="id", nullable=true)
     */
    private $rnvpEtat;
    
    
    
    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;



    public function __construct()
    {
        
    }




    

    

    
    

    /**
     * Set id
     *
     * @param integer $id
     * @return AdresseTmp
     */
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
     * Set vol3
     *
     * @param string $vol3
     * @return AdresseTmp
     */
    public function setVol3($vol3)
    {
        $this->vol3 = $vol3;
    
        return $this;
    }

    /**
     * Get vol3
     *
     * @return string 
     */
    public function getVol3()
    {
        return $this->vol3;
    }

    /**
     * Set vol4
     *
     * @param string $vol4
     * @return AdresseTmp
     */
    public function setVol4($vol4)
    {
        $this->vol4 = $vol4;
    
        return $this;
    }

    /**
     * Get vol4
     *
     * @return string 
     */
    public function getVol4()
    {
        return $this->vol4;
    }

    /**
     * Set vol5
     *
     * @param string $vol5
     * @return AdresseTmp
     */
    public function setVol5($vol5)
    {
        $this->vol5 = $vol5;
    
        return $this;
    }

    /**
     * Get vol5
     *
     * @return string 
     */
    public function getVol5()
    {
        return $this->vol5;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return AdresseTmp
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
     * @return AdresseTmp
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
     * Set rnvp
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $rnvp
     * @return AdresseTmp
     */
    public function setRnvp(\Ams\AdresseBundle\Entity\AdresseRnvp $rnvp = null)
    {
        $this->rnvp = $rnvp;
    
        return $this;
    }

    /**
     * Get rnvp
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getRnvp()
    {
        return $this->rnvp;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return Adresse
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
     * Set rnvpEtat
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvpEtat $rnvpEtat
     * @return Adresse
     */
    public function setRnvpEtat(\Ams\AdresseBundle\Entity\AdresseRnvpEtat $rnvpEtat = null)
    {
        $this->rnvpEtat = $rnvpEtat;
    
        return $this;
    }

    /**
     * Get rnvpEtat
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvpEtat 
     */
    public function getRnvpEtat()
    {
        return $this->rnvpEtat;
    }

    /**
     * Set pointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return AdresseTmp
     */
    public function setPointLivraison(\Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison = null)
    {
        $this->pointLivraison = $pointLivraison;
    
        return $this;
    }

    /**
     * Get pointLivraison
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }
}