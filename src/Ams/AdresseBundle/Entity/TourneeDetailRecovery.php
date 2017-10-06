<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TourneeDetailRecovery
 *
 * @ORM\Table(name="tournee_detail_recovery")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\TourneeDetailRecoveryRepository")
 */
class TourneeDetailRecovery
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
     *
     * @ORM\Column(name="abonne_soc_id", type="integer",nullable=true)
     */
    private $abonneSocId;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=50,nullable=true)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="modele_tournee_jour_code", type="string", length=50,nullable=true)
     */
    private $modeleTourneeJourCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="flux_id", type="integer",nullable=true)
     */
    private $fluxId;

    /**
     * @var integer
     *
     * @ORM\Column(name="jour_id", type="integer",nullable=true)
     */
    private $jourId;

    /**
     * @var integer
     *
     * @ORM\Column(name="insee", type="integer",nullable=true)
     */
    private $insee;

    /**
     * @var integer
     *
     * @ORM\Column(name="point_livraison_id", type="integer",nullable=true)
     */
    private $pointLivraisonId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer",nullable=true)
     */
    private $ordre;

    /**
     * @var string
     *
     * @ORM\Column(name="soc", type="string", length=10,nullable=true)
     */
    private $soc;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=200,nullable=true)
     */
    private $titre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modification", type="datetime",nullable=true)
     */
    private $dateModification;

    /**
     * @var string
     *
     * @ORM\Column(name="source_modification", type="string", length=255,nullable=true)
     */
    private $sourceModification;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_crud", type="datetime",nullable=true)
     */
    private $dateCrud;

    /**
     * @var string
     *
     * @ORM\Column(name="source_crud", type="string", length=255,nullable=true)
     */
    private $sourceCrud;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text",nullable=true)
     */
    private $data;


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
     * Set AbonneSocId
     *
     * @param integer $abonneSocId
     * @return TourneeDetailRecovery
     */
    public function setAbonneSocId($abonneSocId)
    {
        $this->abonneSocId = $abonneSocId;

        return $this;
    }

    /**
     * Get AbonneSocId
     *
     * @return integer 
     */
    public function getAbonneSocId()
    {
        return $this->abonneSocId;
    }

    /**
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return TourneeDetailRecovery
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
     * Set modeleTourneeJourCode
     *
     * @param string $modeleTourneeJourCode
     * @return TourneeDetailRecovery
     */
    public function setModeleTourneeJourCode($modeleTourneeJourCode)
    {
        $this->modeleTourneeJourCode = $modeleTourneeJourCode;

        return $this;
    }

    /**
     * Get modeleTourneeJourCode
     *
     * @return string 
     */
    public function getModeleTourneeJourCode()
    {
        return $this->modeleTourneeJourCode;
    }

    /**
     * Set fluxId
     *
     * @param integer $fluxId
     * @return TourneeDetailRecovery
     */
    public function setFluxId($fluxId)
    {
        $this->fluxId = $fluxId;

        return $this;
    }

    /**
     * Get fluxId
     *
     * @return integer 
     */
    public function getFluxId()
    {
        return $this->fluxId;
    }

    /**
     * Set jourId
     *
     * @param integer $jourId
     * @return TourneeDetailRecovery
     */
    public function setJourId($jourId)
    {
        $this->jourId = $jourId;

        return $this;
    }

    /**
     * Get jourId
     *
     * @return integer 
     */
    public function getJourId()
    {
        return $this->jourId;
    }

    /**
     * Set insee
     *
     * @param integer $insee
     * @return TourneeDetailRecovery
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;

        return $this;
    }

    /**
     * Get insee
     *
     * @return integer 
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set pointLivraisonId
     *
     * @param integer $pointLivraisonId
     * @return TourneeDetailRecovery
     */
    public function setPointLivraisonId($pointLivraisonId)
    {
        $this->pointLivraisonId = $pointLivraisonId;

        return $this;
    }

    /**
     * Get pointLivraisonId
     *
     * @return integer 
     */
    public function getPointLivraisonId()
    {
        return $this->pointLivraisonId;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return TourneeDetailRecovery
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set soc
     *
     * @param string $soc
     * @return TourneeDetailRecovery
     */
    public function setSoc($soc)
    {
        $this->soc = $soc;

        return $this;
    }

    /**
     * Get soc
     *
     * @return string 
     */
    public function getSoc()
    {
        return $this->soc;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return TourneeDetailRecovery
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set dateModification
     *
     * @param \DateTime $dateModification
     * @return TourneeDetailRecovery
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime 
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Set sourceMoficat
     *
     * @param string $sourceModification
     * @return TourneeDetailRecovery
     */
    public function setSourceModification($sourceModification)
    {
        $this->sourceModification = $sourceModification;

        return $this;
    }

    /**
     * Get sourceMoficat
     *
     * @return string 
     */
    public function getSourceModification()
    {
        return $this->sourceModification;
    }

    /**
     * Set dateDelete
     *
     * @param \DateTime $dateCrud
     * @return TourneeDetailRecovery
     */
    public function setDateCrud($dateCrud)
    {
        $this->dateCrud = $dateCrud;

        return $this;
    }

    /**
     * Get dateCrud
     *
     * @return \DateTime 
     */
    public function getDateCrud()
    {
        return $this->dateCrud;
    }

    /**
     * Set sourceDelete
     *
     * @param string $sourceCrud
     * @return TourneeDetailRecovery
     */
    public function setSourceCrud($sourceCrud)
    {
        $this->sourceCrud = $sourceCrud;

        return $this;
    }

    /**
     * Get sourceCrud
     *
     * @return string 
     */
    public function getSourceCrud()
    {
        return $this->sourceCrud;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return TourneeDetailRecovery
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }
}
