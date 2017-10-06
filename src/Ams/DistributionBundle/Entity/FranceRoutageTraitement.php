<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FranceRoutageTraitement
 *
 * @ORM\Table("france_routage_traitement")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageTraitementRepository")
 */
class FranceRoutageTraitement
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
     * @var string
     *
     * @ORM\Column(name="repertoire_ftp_source", type="string", length=255)
     */
    private $repertoireFtpSource;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fichier", type="string", length=255)
     */
    private $nomFichier;

    /**
     * @var string
     *
     * @ORM\Column(name="code_societe", type="string", length=4)
     */
    private $codeSociete;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="datetime")
     */
    private $dateParution;

    /**
     * @var string
     *
     * @ORM\Column(name="repertoire_ftp_destination", type="string", length=255)
     */
    private $repertoireFtpDestination;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_generation_france_routage", type="datetime", nullable=true)
     */
    private $dateGenerationFranceRoutage;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fichier_genere", type="string", length=255,nullable=true)
     */
    private $nomFichierGenere;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut_traitement", type="datetime",nullable=true)
     */
    private $dateDebutTraitement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin_traitement", type="datetime",nullable=true)
     */
    private $dateFinTraitement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_annulation", type="datetime",nullable=true)
     */
    private $dateAnnulation;

    /**
    * @var boolean
    *
    * @ORM\Column(name="succes", type="boolean", nullable=true)
    */
    private $succes;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
    * @var boolean
    *
    * @ORM\Column(name="is_read", type="boolean", nullable=true)
    */
    private $read;

    /**
     * @var integer
     * @ORM\Column(name="fichier_portes_exclus", type="smallint", nullable=true)
     */
    private $fichierPortesExclus;
    
    /**
     * @var string
     * @ORM\Column(name="france_routage_script_code", type="string", length=7, nullable=true)
     */
    private $franceRoutageScriptCode;

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
     * Set repertoireFtpSource
     *
     * @param string $repertoireFtpSource
     * @return FranceRoutageTraitement
     */
    public function setRepertoireFtpSource($repertoireFtpSource)
    {
        $this->repertoireFtpSource = $repertoireFtpSource;

        return $this;
    }

    /**
     * Get repertoireFtpSource
     *
     * @return string 
     */
    public function getRepertoireFtpSource()
    {
        return $this->repertoireFtpSource;
    }

    /**
     * Set nomFichier
     *
     * @param string $nomFichier
     * @return FranceRoutageTraitement
     */
    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    /**
     * Get nomFichier
     *
     * @return string 
     */
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set codeSociete
     *
     * @param string $codeSociete
     * @return FranceRoutageTraitement
     */
    public function setCodeSociete($codeSociete)
    {
        $this->codeSociete = $codeSociete;

        return $this;
    }

    /**
     * Get codeSociete
     *
     * @return string 
     */
    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    /**
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return FranceRoutageTraitement
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
     * Set repertoireFtpDestination
     *
     * @param string $repertoireFtpDestination
     * @return FranceRoutageTraitement
     */
    public function setRepertoireFtpDestination($repertoireFtpDestination)
    {
        $this->repertoireFtpDestination = $repertoireFtpDestination;

        return $this;
    }

    /**
     * Get repertoireFtpDestination
     *
     * @return string 
     */
    public function getRepertoireFtpDestination()
    {
        return $this->repertoireFtpDestination;
    }

    /**
     * Set dateGenerationFranceRoutage
     *
     * @param \DateTime $dateGenerationFranceRoutage
     * @return FranceRoutageTraitement
     */
    public function setDateGenerationFranceRoutage($dateGenerationFranceRoutage)
    {
        $this->dateGenerationFranceRoutage = $dateGenerationFranceRoutage;

        return $this;
    }

    /**
     * Get dateGenerationFranceRoutage
     *
     * @return \DateTime 
     */
    public function getDateGenerationFranceRoutage()
    {
        return $this->dateGenerationFranceRoutage;
    }

    /**
     * Set nomFichierGenere
     *
     * @param string $nomFichierGenere
     * @return FranceRoutageTraitement
     */
    public function setNomFichierGenere($nomFichierGenere)
    {
        $this->nomFichierGenere = $nomFichierGenere;

        return $this;
    }

    /**
     * Get nomFichierGenere
     *
     * @return string 
     */
    public function getNomFichierGenere()
    {
        return $this->nomFichierGenere;
    }

    /**
     * Set dateDebutTraitement
     *
     * @param \DateTime $dateDebutTraitement
     * @return FranceRoutageTraitement
     */
    public function setDateDebutTraitement($dateDebutTraitement)
    {
        $this->dateDebutTraitement = $dateDebutTraitement;

        return $this;
    }

    /**
     * Get dateDebutTraitement
     *
     * @return \DateTime 
     */
    public function getDateDebutTraitement()
    {
        return $this->dateDebutTraitement;
    }

    /**
     * Set dateFinTraitement
     *
     * @param \DateTime $dateFinTraitement
     * @return FranceRoutageTraitement
     */
    public function setDateFinTraitement($dateFinTraitement)
    {
        $this->dateFinTraitement = $dateFinTraitement;

        return $this;
    }

    /**
     * Get dateFinTraitement
     *
     * @return \DateTime 
     */
    public function getDateFinTraitement()
    {
        return $this->dateFinTraitement;
    }

    /**
     * Set dateAnnulation
     *
     * @param \DateTime $dateAnnulation
     * @return FranceRoutageTraitement
     */
    public function setDateAnnulation($dateAnnulation)
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    /**
     * Get dateAnnulation
     *
     * @return \DateTime 
     */
    public function getDateAnnulation()
    {
        return $this->dateAnnulation;
    }

    /**
     * Set succes
     *
     * @param boolean $succes
     * @return FranceRoutageTraitement
     */
    public function setSucces($succes)
    {
        $this->succes = $succes;

        return $this;
    }

    /**
     * Get succes
     *
     * @return boolean 
     */
    public function getSucces()
    {
        return $this->succes;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return FranceRoutageTraitement
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return FranceRoutageTraitement
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return boolean 
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set fichierPortesExclus
     *
     * @param integer $fichierPortesExclus
     * @return FranceRoutageTraitement
     */
    public function setFichierPortesExclus($fichierPortesExclus)
    {
        $this->fichierPortesExclus = $fichierPortesExclus;

        return $this;
    }

    /**
     * Get fichierPortesExclus
     *
     * @return integer 
     */
    public function getFichierPortesExclus()
    {
        return $this->fichierPortesExclus;
    }
    
    public function getFranceRoutageScriptCode() {
        return $this->franceRoutageScriptCode;
    }

    public function setFranceRoutageScriptCode($franceRoutageScriptCode) {
        $this->franceRoutageScriptCode = $franceRoutageScriptCode;
    }


}
