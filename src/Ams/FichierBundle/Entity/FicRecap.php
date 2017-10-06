<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks As HasLifecycleCallbacks;

/**
 * FicRecap
 *
 * @ORM\Table(name="fic_recap")
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicRecapRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FicRecap
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=45, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=50, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=true)
     */
    private $socCodeExt;

    /**
     * @var \Ams\ProduitBundle\Entity\Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $dateDistrib;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="date", nullable=true)
     */
    private $dateParution;

    /**
     * @var integer
     *
     * @ORM\Column(name="checksum", type="string", length=32, nullable=true)
     */
    private $checksum;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_lignes", type="integer", nullable=false)
     */
    private $nbLignes;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_exemplaires", type="integer", nullable=false)
     */
    private $nbExemplaires;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_traitement", type="datetime", nullable=false)
     */
    private $dateTraitement;

    /**
     * @var \FicSource
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicSource")
     * @ORM\JoinColumn(name="fic_source_id", referencedColumnName="id", nullable=true)
     */
    private $ficSource;

    /**
     * @var \FicEtat
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicEtat")
     * @ORM\JoinColumn(name="fic_etat_id", referencedColumnName="id")
     */
    private $ficEtat;

    /**
     * @var \Ams\FichierBundle\Entity\FicFlux
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     */
    private $flux;

    /**
     * @var string
     *
     * @ORM\Column(name="eta_msg", type="string", length=255, nullable=true)
     */
    private $etaMsg;
    
    /**
     * @var \ProduitRecapDepots
     * 
     * @ORM\OneToMany(targetEntity="\Ams\DistributionBundle\Entity\ProduitRecapDepot", mappedBy="ficRecap")
     */
    private $produitRecapDepots;

    /**
     *
     * @var type integer
     * @ORM\Column(name="origine", type="integer")
     */
    private $origine;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=true)
     */
    private $dateUpdate;

    public function __construct()
    {
        $this->dateTraitement = new \Datetime();
        $this->origine = 0;
    }
    
    public function __clone(){
        if($this->id){
            $this->id = null;
            $this->produitRecapDepots = new \Doctrine\Common\Collections\ArrayCollection();
            $this->origine = 1;
        }
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
     * Set code
     *
     * @param string $code
     * @return FicRecap
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return FicRecap
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return FicRecap
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
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return FicRecap
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
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return FicRecap
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
     * Set checksum
     *
     * @param string $checksum
     * @return FicRecap
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;
    
        return $this;
    }

    /**
     * Get checksum
     *
     * @return string 
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Set nbLignes
     *
     * @param integer $nbLignes
     * @return FicRecap
     */
    public function setNbLignes($nbLignes)
    {
        $this->nbLignes = $nbLignes;
    
        return $this;
    }

    /**
     * Get nbLignes
     *
     * @return integer 
     */
    public function getNbLignes()
    {
        return $this->nbLignes;
    }

    /**
     * Set nbExemplaires
     *
     * @param integer $nbExemplaires
     * @return FicRecap
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
     * Set dateTraitement
     *
     * @param \DateTime $dateTraitement
     * @return FicRecap
     */
    public function setDateTraitement($dateTraitement)
    {
        $this->dateTraitement = $dateTraitement;
    
        return $this;
    }

    /**
     * Get dateTraitement
     *
     * @return \DateTime 
     */
    public function getDateTraitement()
    {
        return $this->dateTraitement;
    }

    /**
     * Get ficFlux
     *
     * @return \Ams\FichierBundle\Entity\FicFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set ficFlux
     *
     * @param \Ams\FichierBundle\Entity\FicFlux $flux
     * @return FicChrgtFichiersBdd
     */
    public function setFlux(\Ams\FichierBundle\Entity\FicFlux $flux)
    {
        $this->flux = $flux;
    
        return $this;
    }

    /**
     * Set etaMsg
     *
     * @param string $etaMsg
     * @return FicRecap
     */
    public function setEtaMsg($etaMsg)
    {
        $this->etaMsg = $etaMsg;
    
        return $this;
    }

    /**
     * Get etaMsg
     *
     * @return string 
     */
    public function getEtaMsg()
    {
        return $this->etaMsg;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return FicRecap
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
     * Set ficSource
     *
     * @param \Ams\FichierBundle\Entity\FicSource $ficSource
     * @return FicRecap
     */
    public function setFicSource(\Ams\FichierBundle\Entity\FicSource $ficSource = null)
    {
        $this->ficSource = $ficSource;
    
        return $this;
    }

    /**
     * Get ficSource
     *
     * @return \Ams\FichierBundle\Entity\FicSource 
     */
    public function getFicSource()
    {
        return $this->ficSource;
    }

    /**
     * Set ficEtat
     *
     * @param \Ams\FichierBundle\Entity\FicEtat $ficEtat
     * @return FicRecap
     */
    public function setFicEtat(\Ams\FichierBundle\Entity\FicEtat $ficEtat = null)
    {
        $this->ficEtat = $ficEtat;
    
        return $this;
    }

    /**
     * Get ficEtat
     *
     * @return \Ams\FichierBundle\Entity\FicEtat 
     */
    public function getFicEtat()
    {
        return $this->ficEtat;
    }

    /**
     * Set origine
     *
     * @param integer $origine
     * @return FicRecap
     */
    public function setOrigine($origine = null)
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return integer
     */
    public function getOrigine()
    {
        return $this->origine;
    }


    /**
     * Add produitRecapDepots
     *
     * @param \Ams\DistributionBundle\Entity\ProduitRecapDepot $produitRecapDepots
     * @return FicRecap
     */
    public function addProduitRecapDepot(\Ams\DistributionBundle\Entity\ProduitRecapDepot $produitRecapDepots)
    {
        $this->produitRecapDepots[] = $produitRecapDepots;

        return $this;
    }

    /**
     * Remove produitRecapDepots
     *
     * @param \Ams\DistributionBundle\Entity\ProduitRecapDepot $produitRecapDepots
     */
    public function removeProduitRecapDepot(\Ams\DistributionBundle\Entity\ProduitRecapDepot $produitRecapDepots)
    {
        $this->produitRecapDepots->removeElement($produitRecapDepots);
    }

    /**
     * Get produitRecapDepots
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProduitRecapDepots()
    {
        return $this->produitRecapDepots;
    }
    
    /**
     * Return array of data for create Calendar Event
     * @return array
     */
    public function getEventCalendarData()
    {
        return array(
            "id" => $this->getId(),
            "start" => $this->getDateDistrib()->getTimestamp()*1000, //Format pour interpretation direct en JS
            "title" => $this->getNbExemplaires(),
            "modale" => $this->getSocCodeExt(),
            "image" => $this->getSociete()->getImage() ? $this->getSociete()->getImage()->getWebPath() : '',
            "color" => $this->getFicEtat()->getCouleur(),
            "type" => "event",
            "attrTitle" => $this->getSociete()->getLibelle()
        );
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return FicRecap
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime 
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }
}
