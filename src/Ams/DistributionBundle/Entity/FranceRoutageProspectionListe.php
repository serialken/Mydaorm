<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FranceRoutageProspectionListe
 *
 * @ORM\Table("france_routage_prospection_liste")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageProspectionListeRepository")
 */
class FranceRoutageProspectionListe
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
     * @ORM\Column(name="ftp_repertoire", type="string", length=255)
     */
    private $ftpRepertoire;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fichier", type="string", length=255)
     */
    private $nomFichier;

    /**
     * @var string
     * Fichier transforme
     *
     * @ORM\Column(name="fic_transforme", type="string", length=100)
     */
    private $ficTransforme;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     */
    private $societe;

    /**
     * @var \DateTime
     * Date de reference
     *
     * @ORM\Column(name="date_ref", type="date", nullable=false)
     */
    private $dateRef;

    /**
     * @var \RefFlux
     * Si NULL, on classe dans les tournees NUIT par defaut. Si adresse inconnue la NUIT, on verifie si l'adresse est connu le JOUR
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;

    /**
     * @var string
     * Texte definissant le test de prospection
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_commande", type="datetime",nullable=false)
     */
    private $dateCommande;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_commande_id", referencedColumnName="id", nullable=true)
     */
    private $utilisateurCommande;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_annulation", type="datetime",nullable=true)
     */
    private $dateAnnulation;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_annulation_id", referencedColumnName="id", nullable=true)
     */
    private $utilisateurAnnulation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut_traitement", type="datetime",nullable=true)
     */
    private $dateDebutTraitement;
    

    /**
     * @var \Ams\DistributionBundle\Entity\FranceRoutageProspectionEtat
     *
     * @ORM\ManyToOne(targetEntity="Ams\DistributionBundle\Entity\FranceRoutageProspectionEtat")
     * @ORM\JoinColumn(name="etat_id", referencedColumnName="id", nullable=false)
     */
    private $etat;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin_traitement", type="datetime",nullable=true)
     */
    private $dateFinTraitement;

    /**
     * @var string
     *
     * @ORM\Column(name="erreur_traitement", type="text", nullable=true)
     */
    private $erreurTraitement;
    
    
    
    


    

    

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
     * Set ftpRepertoire
     *
     * @param string $ftpRepertoire
     * @return FranceRoutageProspectionListe
     */
    public function setFtpRepertoire($ftpRepertoire)
    {
        $this->ftpRepertoire = $ftpRepertoire;

        return $this;
    }

    /**
     * Get ftpRepertoire
     *
     * @return string 
     */
    public function getFtpRepertoire()
    {
        return $this->ftpRepertoire;
    }

    /**
     * Set nomFichier
     *
     * @param string $nomFichier
     * @return FranceRoutageProspectionListe
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
     * Set ficTransforme
     *
     * @param string $ficTransforme
     * @return FranceRoutageProspectionListe
     */
    public function setFicTransforme($ficTransforme)
    {
        $this->ficTransforme = $ficTransforme;

        return $this;
    }

    /**
     * Get ficTransforme
     *
     * @return string 
     */
    public function getFicTransforme()
    {
        return $this->ficTransforme;
    }

    /**
     * Set dateRef
     *
     * @param \DateTime $dateRef
     * @return FranceRoutageProspectionListe
     */
    public function setDateRef($dateRef)
    {
        $this->dateRef = $dateRef;

        return $this;
    }

    /**
     * Get dateRef
     *
     * @return \DateTime 
     */
    public function getDateRef()
    {
        return $this->dateRef;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return FranceRoutageProspectionListe
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
     * Set dateCommande
     *
     * @param \DateTime $dateCommande
     * @return FranceRoutageProspectionListe
     */
    public function setDateCommande($dateCommande)
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * Get dateCommande
     *
     * @return \DateTime 
     */
    public function getDateCommande()
    {
        return $this->dateCommande;
    }

    /**
     * Set dateAnnulation
     *
     * @param \DateTime $dateAnnulation
     * @return FranceRoutageProspectionListe
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
     * Set dateDebutTraitement
     *
     * @param \DateTime $dateDebutTraitement
     * @return FranceRoutageProspectionListe
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
     * @return FranceRoutageProspectionListe
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
     * Set erreurTraitement
     *
     * @param string $erreurTraitement
     * @return FranceRoutageProspectionListe
     */
    public function setErreurTraitement($erreurTraitement)
    {
        $this->erreurTraitement = $erreurTraitement;

        return $this;
    }

    /**
     * Get erreurTraitement
     *
     * @return string 
     */
    public function getErreurTraitement()
    {
        return $this->erreurTraitement;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return FranceRoutageProspectionListe
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
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return FranceRoutageProspectionListe
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux = null)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set utilisateurCommande
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurCommande
     * @return FranceRoutageProspectionListe
     */
    public function setUtilisateurCommande(\Ams\SilogBundle\Entity\Utilisateur $utilisateurCommande = null)
    {
        $this->utilisateurCommande = $utilisateurCommande;

        return $this;
    }

    /**
     * Get utilisateurCommande
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurCommande()
    {
        return $this->utilisateurCommande;
    }

    /**
     * Set utilisateurAnnulation
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurAnnulation
     * @return FranceRoutageProspectionListe
     */
    public function setUtilisateurAnnulation(\Ams\SilogBundle\Entity\Utilisateur $utilisateurAnnulation = null)
    {
        $this->utilisateurAnnulation = $utilisateurAnnulation;

        return $this;
    }

    /**
     * Get utilisateurAnnulation
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurAnnulation()
    {
        return $this->utilisateurAnnulation;
    }

    /**
     * Set etat
     *
     * @param \Ams\DistributionBundle\Entity\FranceRoutageProspectionEtat $etat
     * @return FranceRoutageProspectionListe
     */
    public function setEtat(\Ams\DistributionBundle\Entity\FranceRoutageProspectionEtat $etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return \Ams\DistributionBundle\Entity\FranceRoutageProspectionEtat 
     */
    public function getEtat()
    {
        return $this->etat;
    }
}
