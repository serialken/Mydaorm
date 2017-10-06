<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientAServirSrcTmp
 *
 * @ORM\Table(name="info_portage_tmp")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\InfoPortageTmpRepository")
 */
class InfoPortageTmp
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
     * @var integer
     * Id de la table "info_portage"
     *
     * @ORM\Column(name="info_portage_id", type="integer", nullable=false)
     */
    private $infoPortageId;

    /**
     * @var \AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;

    
    /**
     * @var \TypeInfoPortage
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\TypeInfoPortage")
     * @ORM\JoinColumn(name="type_info_id", referencedColumnName="id")
     */
    private $typeInfoPortage;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255)
     */
    private $valeur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date")
     */
    protected $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date")
     */
    protected $dateFin;
    
    
    


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
     * Set valeur
     *
     * @param string $valeur
     * @return InfoPortageTmp
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return InfoPortageTmp
     */
    public function setAbonneSoc(\Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc = null)
    {
        $this->abonneSoc = $abonneSoc;

        return $this;
    }

    /**
     * Get abonneSoc
     *
     * @return \Ams\AbonneBundle\Entity\AbonneSoc 
     */
    public function getAbonneSoc()
    {
        return $this->abonneSoc;
    }

    /**
     * Set typeInfoPortage
     *
     * @param \Ams\DistributionBundle\Entity\TypeInfoPortage $typeInfoPortage
     * @return InfoPortageTmp
     */
    public function setTypeInfoPortage(\Ams\DistributionBundle\Entity\TypeInfoPortage $typeInfoPortage = null)
    {
        $this->typeInfoPortage = $typeInfoPortage;

        return $this;
    }

    /**
     * Get typeInfoPortage
     *
     * @return \Ams\DistributionBundle\Entity\TypeInfoPortage 
     */
    public function getTypeInfoPortage()
    {
        return $this->typeInfoPortage;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return InfoPortage
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
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return InfoPortage
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
     * Set infoPortageId
     *
     * @param integer $infoPortageId
     * @return InfoPortageTmp
     */
    public function setInfoPortageId($infoPortageId)
    {
        $this->infoPortageId = $infoPortageId;

        return $this;
    }

    /**
     * Get infoPortageId
     *
     * @return integer 
     */
    public function getInfoPortageId()
    {
        return $this->infoPortageId;
    }
}
