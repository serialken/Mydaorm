<?php

namespace Ams\AbonneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index AS index;

/**
 * AbonneSoc
 *
 * @ORM\Table(name="abonne_soc",
 *                      uniqueConstraints={@UniqueConstraint(name="numabo_soc_code_ext",columns={"numabo_ext","soc_code_ext"})}
 *                      , indexes={@index(name="client_idx", columns={"numabo_ext","soc_code_ext","client_type"})
 *                                  , @index(name="lv_idx", columns={"numabo_ext","client_type"})
 *                                  }
 *              )
 * @ORM\Entity(repositoryClass="Ams\AbonneBundle\Repository\AbonneSocRepository")
 */
class AbonneSoc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=50, nullable=false)
     */
    protected $numaboExt;

    /**
     * @var \Societe
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    protected $socCodeExt;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    protected $societe;
    
    /**
     * @var integer
     * Si 0, c'est abonne
     * Si 1, c'est Lieu de vente
     * 
     *
     * @ORM\Column(name="client_type", type="integer", nullable=false)
     */
    protected $clientType;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=100, nullable=true)
     */
    protected $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=100, nullable=true)
     */
    protected $vol2;    
    /**
     * @var \Ams\AbonneBundle\Entity\AbonneUnique
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneUnique")
     * @ORM\JoinColumn(name="abonne_unique_id", referencedColumnName="id", nullable=true)
     */
    protected $abonneUnique;

    /**
     * Date premier service
     * @var \DateTime
     *
     * @ORM\Column(name="date_service_1", type="date", nullable=true)
     */
    protected $dateService1;

    /**
     * Date de suspension/arret
     * @var \DateTime
     *
     * @ORM\Column(name="date_stop", type="date", nullable=true)
     */
    protected $dateStop;
    
    /**
     * Bidirectional - (INVERSE SIDE)
     *
     * @ORM\ManyToMany(targetEntity="Ams\DistributionBundle\Entity\InfoPortage", mappedBy="abonnes")
     */
    protected $infosPortages;


    

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
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return AbonneSoc
     */
    public function setNumaboExt($numaboExt)
    {
        $this->numaboExt = $numaboExt;
    
        return $this;
    }

    /**
     * Get numaboExt
     *
     * @return string 
     */
    public function getNumaboExt()
    {
        return $this->numaboExt;
    }

    /**
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return AbonneSoc
     */
    public function setSocCodeExt($socCodeExt)
    {
        $this->socCodeExt = $socCodeExt;
    
        return $this;
    }

    /**
     * Get socCodeExt
     *
     * @return string 
     */
    public function getSocCodeExt()
    {
        return $this->socCodeExt;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return ClientAServirLogist
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
     * Set clientType
     *
     * @param integer $clientType
     * @return AbonneSoc
     */
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;
    
        return $this;
    }

    /**
     * Get clientType
     *
     * @return integer 
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * Set vol1
     *
     * @param string $vol1
     * @return AbonneSoc
     */
    public function setVol1($vol1)
    {
        $this->vol1 = $vol1;
    
        return $this;
    }

    /**
     * Get vol1
     *
     * @return string 
     */
    public function getVol1()
    {
        return $this->vol1;
    }

    /**
     * Set vol2
     *
     * @param string $vol2
     * @return AbonneSoc
     */
    public function setVol2($vol2)
    {
        $this->vol2 = $vol2;
    
        return $this;
    }

    /**
     * Get vol2
     *
     * @return string 
     */
    public function getVol2()
    {
        return $this->vol2;
    }

    /**
     * Set abonneUnique
     *
     * @param \Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique
     * @return AbonneSoc
     */
    public function setAbonneUnique(\Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique)
    {
        $this->abonneUnique = $abonneUnique;
    
        return $this;
    }

    /**
     * Get abonneUnique
     *
     * @return \Ams\AbonneBundle\Entity\AbonneUnique 
     */
    public function getAbonneUnique()
    {
        return $this->abonneUnique;
    }

    /**
     * Set dateService1
     *
     * @param \DateTime $dateService1
     * @return AbonneSoc
     */
    public function setDateService1($dateService1)
    {
        $this->dateService1 = $dateService1;

        return $this;
    }

    /**
     * Get dateService1
     *
     * @return \DateTime 
     */
    public function getDateService1()
    {
        return $this->dateService1;
    }

    /**
     * Set dateStop
     *
     * @param \DateTime $dateStop
     * @return AbonneSoc
     */
    public function setDateStop($dateStop)
    {
        $this->dateStop = $dateStop;

        return $this;
    }

    /**
     * Get dateStop
     *
     * @return \DateTime 
     */
    public function getDateStop()
    {
        return $this->dateStop;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->infosPortages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add infosPortages
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortages
     * @return AbonneSoc
     */
    public function addInfosPortage(\Ams\DistributionBundle\Entity\InfoPortage $infosPortages)
    {
        $this->infosPortages[] = $infosPortages;

        return $this;
    }

    /**
     * Remove infosPortages
     *
     * @param \Ams\DistributionBundle\Entity\InfoPortage $infosPortages
     */
    public function removeInfosPortage(\Ams\DistributionBundle\Entity\InfoPortage $infosPortages)
    {
        $this->infosPortages->removeElement($infosPortages);
    }

    /**
     * Get infosPortages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInfosPortages()
    {
        return $this->infosPortages;
    }
}
