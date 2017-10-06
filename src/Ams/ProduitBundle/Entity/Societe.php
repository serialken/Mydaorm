<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Societe
 *
 * @ORM\Table(name="societe",
 *              uniqueConstraints={@UniqueConstraint(name="societe_unique",columns={"code"})}
 *          )
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\SocieteRepository")
 * @UniqueEntity(
 *      fields={"code", "libelle"},
 *      errorPath="code",
 *      message="Les code et/ou libellé sont déjà utilisés."
 * )
 */
class Societe
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, unique=true, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_defaut_id", referencedColumnName="id", nullable=true)
     */
    private $produitDefaut;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id_defaut", referencedColumnName="id", nullable=true)
     */
    private $fluxDefaut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_modif", referencedColumnName="id", nullable=true)
     */
    private $utilisateurModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=false)
     */
    private $dateModif;
    
    
    /**
     * @var \Produit
     *
     * @ORM\OneToMany(targetEntity="Ams\ProduitBundle\Entity\Produit",  mappedBy="societe")
     */
    private $produits;
    
        
    /**
     * @var \Fichier
     *
     * @ORM\ManyToOne(targetEntity="Ams\ExtensionBundle\Entity\Fichier")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     */
    private $image;  
    
    /**
     * @var Boolean
     * @ORM\Column(name="active", type="boolean", nullable=false)
    */
    private $active = true;

    /**
     * @var string
     * Si une societe est concerne par plusieurs specificites, on les separe par "|".
     * La liste des specificites : 
     *     - "-FRANCE_ROUTAGE-" --> France routage
     *     - "-FRANCE_ROUTAGE_PROSPECT-" --> Prospection France routage
     *
     * @ORM\Column(name="specificite", type="string", length=255, nullable=true)
     */
    private $specificite;
    
    /**
     * @var etiquette
     * @ORM\Column(name="etiquette", type="boolean", nullable=true)
     */
    private $etiquette;
    
    public function __construct()
    {
        $this->dateModif = new \Datetime();
        $this->dateDebut = new \Datetime();
        $this->produits = new ArrayCollection();
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
     * @return Societe
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
     * Set libelle
     *
     * @param string $libelle
     * @return Societe
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
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Societe
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
     * @return Societe
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
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return Societe
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;
    
        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set produitDefaut
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produitDefaut
     * @return Societe
     */
    public function setProduitDefaut(\Ams\ProduitBundle\Entity\Produit $produitDefaut = null)
    {
        $this->produitDefaut = $produitDefaut;
    
        return $this;
    }

    /**
     * Get produitDefaut
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduitDefaut()
    {
        return $this->produitDefaut;
    }

    /**
     * Set fluxDefaut
     *
     * @param Ams\ReferentielBundle\Entity\RefFlux $fluxDefaut
     * @return Societe
     */
    public function setFluxDefaut(\Ams\ReferentielBundle\Entity\RefFlux $fluxDefaut)
    {
        $this->fluxDefaut = $fluxDefaut;
    
        return $this;
    }

    /**
     * Get fluxDefaut
     *
     * @return Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFluxDefaut()
    {
        return $this->fluxDefaut;
    }

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return Societe
     */
    public function setUtilisateurModif(\Ams\SilogBundle\Entity\Utilisateur $utilisateurModif = null)
    {
        $this->utilisateurModif = $utilisateurModif;
    
        return $this;
    }

    /**
     * Get utilisateurModif
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurModif()
    {
        return $this->utilisateurModif;
    }

    /**
     * Add produits
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produits
     * @return Societe
     */
    public function addProduit(\Ams\ProduitBundle\Entity\Produit $produits)
    {
        $this->produits[] = $produits;
    
        return $this;
    }

    /**
     * Remove produits
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produits
     */
    public function removeProduit(\Ams\ProduitBundle\Entity\Produit $produits)
    {
        $this->produits->removeElement($produits);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProduits()
    {
        return $this->produits;
    }


    /**
     * Set image
     *
     * @param \Ams\ExtensionBundle\Entity\Fichier $image
     * @return Societe
     */
    public function setImage(\Ams\ExtensionBundle\Entity\Fichier $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Ams\ExtensionBundle\Entity\Fichier 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Societe
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set specificite
     *
     * @param string $specificite
     * @return Societe
     */
    public function setSpecificite($specificite)
    {
        $this->specificite = $specificite;
    
        return $this;
    }

    /**
     * Get specificite
     *
     * @return string 
     */
    public function getSpecificite()
    {
        return $this->specificite;
    }
        
    /**
     * Set etiquette
     *
     * @param boolean $etiquette
     * @return Societe
     */
    public function setEtiquette($etiquette)
    {
        $this->etiquette = $etiquette;

        return $this;
    }

    /**
     * Get etiquette
     *
     * @return boolean 
     */
    public function getEtiquette()
    {
        return $this->etiquette;
    }
}
