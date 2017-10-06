<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RequeteExport
 *
 * @ORM\Table(name="requete_export")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\RequeteExportRepository")
 * @UniqueEntity(fields="libelle", message="Attention ce libellé est déjà utilisé.")
 */
class RequeteExport
{
    const STATUT_ENCOURS = 'E';
    const STATUT_APPLIQUEE = 'A';
    const STATUT_NONAPPLIQUEE = 'N';
    const STATUT_PASENCORE = 'P';
    const STATUT_ANCIENNE_VERSION = 'O';
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
     * @Assert\NotBlank()
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false, unique=true)
     */
    private $libelle;

    
    /**
     * @var string
     * @ORM\Column(name="commentaire", type="text",nullable=false)
     */
    private $commentaire;
    
   /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="requete", type="text",nullable=false)
     */
    private $requete;
    
   /**
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=false)
     */
    private  $utilisateur;

     /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    
    private $dateCreation;
    
      /**
     * @var integer
     * @ORM\Column(name="nb_resultat", type="integer",nullable=false)
     */
    private $nbResultat;
    
      /**
     * @var integer
     * @ORM\Column(name="nb_imports", type="integer",nullable=true)
     */
    private $nbImports;
    
    /**
     * @ORM\OneToMany(targetEntity="Ams\AdresseBundle\Entity\ExportGeoconcept", mappedBy="requeteExport")
     */
    private $lignesExport;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_verification", type="datetime", nullable=true)
     */
    private $dateVerification;
    
    /**
     * @var Boolean
     * @ORM\Column(name="valid", type="boolean", nullable=false)
    */
    private $isValid = false;
    
    /**
     * @var string
     * @ORM\Column(name="commentaire_verif", type="string", length=255, nullable=true)
     */
    private $commentVerif;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_application", type="datetime", nullable=true)
     */
    private $dateApplication;
    
    /**
     * @var string
     * @ORM\Column(name="jour_type", type="string", length=255, nullable=true)
     */
    private $jourType;
    
    /**
     * @var string
     * @ORM\Column(name="liste_tournees", type="text", nullable=true)
     */
    private $listeTournees;
    
    /**
     * @var string
     * @ORM\Column(name="statut", type="string", length=50, nullable=true)
     */
    private $statut;
    


    public function __construct() {
        $this->dateCreation = new \DateTime();
    }

    
    /**
     * @var string
     * @ORM\Column(name="optim_info", type="text", nullable=true)
     */
    private $optimInfo;    

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
     * Set libelle
     *
     * @param string $libelle
     * @return RequeteExport
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return RequeteExport
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set requete
     *
     * @param string $requete
     * @return RequeteExport
     */
    public function setRequete($requete)
    {
        $this->requete = $requete;

        return $this;
    }

    /**
     * Get requete
     *
     * @return string 
     */
    public function getRequete()
    {
        return $this->requete;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return RequeteExport
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set nbResultat
     *
     * @param integer $nbResultat
     * @return RequeteExport
     */
    public function setNbResultat($nbResultat)
    {
        $this->nbResultat = $nbResultat;

        return $this;
    }

    /**
     * Get nbResultat
     *
     * @return integer 
     */
    public function getNbResultat()
    {
        return $this->nbResultat;
    }

    /**
     * Set dateVerification
     *
     * @param \DateTime $dateVerification
     * @return RequeteExport
     */
    public function setDateVerification($dateVerification)
    {
        $this->dateVerification = $dateVerification;

        return $this;
    }

    /**
     * Get dateVerification
     *
     * @return \DateTime 
     */
    public function getDateVerification()
    {
        return $this->dateVerification;
    }

    /**
     * Set isValid
     *
     * @param boolean $isValid
     * @return RequeteExport
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return boolean 
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Set commentVerif
     *
     * @param string $commentVerif
     * @return RequeteExport
     */
    public function setCommentVerif($commentVerif)
    {
        $this->commentVerif = $commentVerif;

        return $this;
    }

    /**
     * Get commentVerif
     *
     * @return string 
     */
    public function getCommentVerif()
    {
        return $this->commentVerif;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return RequeteExport
     */
    public function setUtilisateur(\Ams\SilogBundle\Entity\Utilisateur $utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Add lignesExport
     *
     * @param \Ams\AdresseBundle\Entity\ExportGeoconcept $lignesExport
     * @return RequeteExport
     */
    public function addLignesExport(\Ams\AdresseBundle\Entity\ExportGeoconcept $lignesExport)
    {
        $this->lignesExport[] = $lignesExport;

        return $this;
    }

    /**
     * Remove lignesExport
     *
     * @param \Ams\AdresseBundle\Entity\ExportGeoconcept $lignesExport
     */
    public function removeLignesExport(\Ams\AdresseBundle\Entity\ExportGeoconcept $lignesExport)
    {
        $this->lignesExport->removeElement($lignesExport);
    }

    /**
     * Get lignesExport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLignesExport()
    {
        return $this->lignesExport;
    }

    /**
     * Set dateApplication
     *
     * @param \DateTime $dateApplication
     * @return RequeteExport
     */
    public function setDateApplication($dateApplication)
    {
        $this->dateApplication = $dateApplication;

        return $this;
    }

    /**
     * Get dateApplication
     *
     * @return \DateTime 
     */
    public function getDateApplication()
    {
        return $this->dateApplication;
    }

    /**
     * Set listeTournees
     *
     * @param string $listeTournees
     * @return RequeteExport
     */
    public function setListeTournees($listeTournees)
    {
        $this->listeTournees = $listeTournees;

        return $this;
    }

    /**
     * Get listeTournees
     *
     * @return string 
     */
    public function getListeTournees()
    {
        return $this->listeTournees;
    }

    /**
     * Set nbImports
     *
     * @param integer $nbImports
     * @return RequeteExport
     */
    public function setNbImports($nbImports)
    {
        $this->nbImports = $nbImports;

        return $this;
    }

    /**
     * Get nbImports
     *
     * @return integer 
     */
    public function getNbImports()
    {
        return $this->nbImports;
    }

    /**
     * Set statut
     *
     * @param string $statut
     * @return RequeteExport
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return string 
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set jourType
     *
     * @param string $jourType
     * @return RequeteExport
     */
    public function setJourType($jourType)
    {
        $this->jourType = $jourType;

        return $this;
    }

    /**
     * Get jourType
     *
     * @return string 
     */
    public function getJourType()
    {
        return $this->jourType;
    }

    /**
     * Set optimInfo
     *
     * @param string $optimInfo
     * @return RequeteExport
     */
    public function setOptimInfo($optimInfo)
    {
        $this->optimInfo = $optimInfo;

        return $this;
    }

    /**
     * Get optimInfo
     *
     * @return string 
     */
    public function getOptimInfo()
    {
        return $this->optimInfo;
    }
}
