<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Ams\ExtensionBundle\Validator\Constraints as AmsAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Produit
 *
 * @ORM\Table(name="produit",
 *              uniqueConstraints={@UniqueConstraint(name="produit_unique",columns={"code","flux_id"})
 *                                  , @UniqueConstraint(name="codes_produit_unique",columns={"soc_code_ext","prd_code_ext","spr_code_ext"})
 *                              }
 *              )
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\ProduitRepository") 
 * @UniqueEntity(
 *      fields={"code"},
 *      errorPath="code",
 *      message="Le code est déjà utilisé."
 * )
 * @UniqueEntity(
 *      fields={"libelle"},
 *      errorPath="libelle",
 *      message="Le libellé est déjà utilisé."
 * )
 */
class Produit {

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
     * @ORM\Column(name="code", type="string", length=20, nullable=false)
     * @AmsAssert\ContainsAlphanumeric
     */
    private $code;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Societe", inversedBy="produits")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id")
     */
    private $societe;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var Ams\ReferentielBundle\Entity\RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     */
    private $flux;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    private $socCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="prd_code_ext", type="string", length=20, nullable=false)
     */
    private $prdCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="prd_code_neopress", type="string", length=100, nullable=true)
     */
    private $prdCodeNeopress;

    /**
     * @var string
     *
     * @ORM\Column(name="spr_code_ext", type="string", length=10, nullable=true)
     */
    private $sprCodeExt;

    /**
     * Pourcentage de la passe - Devrait etre un entier positif
     * @var integer
     * 
     * @Assert\Range(
     *      min = 0,
     *      max = 200,
     *      minMessage = "La valeur de passe minimum est 0",
     *      maxMessage = "La valeur de passe maximum est 200",
     *      invalidMessage = "Cette valeur doit être un entier"
     * )
     * @ORM\Column(name="passe", type="integer", nullable=false)
     */
    private $passe;

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
    // ...

    /**
     * @ORM\ManyToMany(targetEntity="Ams\ProduitBundle\Entity\Produit", mappedBy="enfants",  cascade={"persist"})
     * */
    private $parents;

    /**
     * @ORM\ManyToMany(targetEntity="Ams\ProduitBundle\Entity\Produit", inversedBy="parents",  cascade={"persist"})
     * @ORM\JoinTable(name="dependance_produit",
     *      joinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="enfant_id", referencedColumnName="id")}
     *      )
     * */
    private $enfants;

    /**
     * @var \Fichier
     *
     * @ORM\ManyToOne(targetEntity="Ams\ExtensionBundle\Entity\Fichier")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     */
    private $image;

    /**
     * @var \TypageProduit
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\ProduitType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     */
    private $produitType;

    /**
     * @var integer
     *
     * @ORM\Column(name="lundi", type="integer", nullable=true)
     */
    private $lundi;

    /**
     * @var integer
     *
     * @ORM\Column(name="mardi", type="integer", nullable=true)
     */
    private $mardi;

    /**
     * @var integer
     *
     * @ORM\Column(name="mercredi", type="integer", nullable=true)
     */
    private $mercredi;

    /**
     * @var integer
     *
     * @ORM\Column(name="jeudi", type="integer", nullable=true)
     */
    private $jeudi;

    /**
     * @var integer
     *
     * @ORM\Column(name="vendredi", type="integer", nullable=true)
     */
    private $vendredi;

    /**
     * @var integer
     *
     * @ORM\Column(name="samedi", type="integer", nullable=true)
     */
    private $samedi;

    /**
     * @var integer
     *
     * @ORM\Column(name="dimanche", type="integer", nullable=true)
     */
    private $dimanche;

    /**
     * @var Ams\ReferentielBundle\Entity\RefPeriodicite
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefPeriodicite")
     * @ORM\JoinColumn(name="periodicite_id", referencedColumnName="id", nullable=true)
     */
    private $periodicite;

    public function __construct() {
        $this->dateModif = new \Datetime();
        $this->dateDebut = new \Datetime();
        $this->parents = new ArrayCollection();
        $this->enfants = new ArrayCollection();
        // $this->passe = 2; // Passe = 2%
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Produit
     */
    public function setCode($code) {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Produit
     */
    public function setLibelle($libelle) {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle() {
        return $this->libelle;
    }

    /**
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return Produit
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux) {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFlux() {
        return $this->flux;
    }

    /**
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return Produit
     */
    public function setSocCodeExt($socCodeExt) {
        $this->socCodeExt = $socCodeExt;

        return $this;
    }

    /**
     * Get socCodeExt
     *
     * @return string 
     */
    public function getSocCodeExt() {
        return $this->socCodeExt;
    }

    /**
     * Set prdCodeExt
     *
     * @param string $prdCodeExt
     * @return Produit
     */
    public function setPrdCodeExt($prdCodeExt) {
        $this->prdCodeExt = $prdCodeExt;

        return $this;
    }

    /**
     * Get prdCodeExt
     *
     * @return string 
     */
    public function getPrdCodeExt() {
        return $this->prdCodeExt;
    }

    /**
     * Set sprCodeExt
     *
     * @param string $sprCodeExt
     * @return Produit
     */
    public function setSprCodeExt($sprCodeExt) {
        $this->sprCodeExt = $sprCodeExt;

        return $this;
    }

    /**
     * Get sprCodeExt
     *
     * @return string 
     */
    public function getSprCodeExt() {
        return $this->sprCodeExt;
    }

    /**
     * Set passe
     *
     * @param integer $passe
     * @return Produit
     */
    public function setPasse($passe) {
        $this->passe = $passe;

        return $this;
    }

    /**
     * Get passe
     *
     * @return integer 
     */
    public function getPasse() {
        return $this->passe;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Produit
     */
    public function setDateDebut($dateDebut) {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut() {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Produit
     */
    public function setDateFin($dateFin) {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin() {
        return $this->dateFin;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return Produit
     */
    public function setDateModif($dateModif) {
        $this->dateModif = $dateModif;

        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif() {
        return $this->dateModif;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return Produit
     */
    public function setSociete(\Ams\ProduitBundle\Entity\Societe $societe = null) {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return \Ams\ProduitBundle\Entity\Societe 
     */
    public function getSociete() {
        return $this->societe;
    }

    /**
     * Set produitType
     *
     * @param \Ams\ProduitBundle\Entity\ProduitType $produitType
     * @return Produit
     */
    public function setProduitType(\Ams\ProduitBundle\Entity\ProduitType $produitType = null) {
        $this->produitType = $produitType;

        return $this;
    }

    /**
     * Get produitType
     *
     * @return \Ams\ProduitBundle\Entity\ProduitType 
     */
    public function getProduitType() {
        return $this->produitType;
    }

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return Produit
     */
    public function setUtilisateurModif(\Ams\SilogBundle\Entity\Utilisateur $utilisateurModif = null) {
        $this->utilisateurModif = $utilisateurModif;

        return $this;
    }

    /**
     * Get utilisateurModif
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurModif() {
        return $this->utilisateurModif;
    }

    /**
     * Add parents
     *
     * @param \Ams\ProduitBundle\Entity\Produit $parents
     * @return Produit
     */
    public function addParent(\Ams\ProduitBundle\Entity\Produit $parents) {
        $this->parents[] = $parents;
        $parents->addEnfant($this);

        return $this;
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParents() {
        return $this->parents;
    }

    /**
     * Add enfants
     *
     * @param \Ams\ProduitBundle\Entity\Produit $enfants
     * @return Produit
     */
    public function addEnfant(\Ams\ProduitBundle\Entity\Produit $enfants) {
        $this->enfants[] = $enfants;

        return $this;
    }

    /**
     * Remove enfants
     *
     * @param \Ams\ProduitBundle\Entity\Produit $enfants
     */
    public function removeEnfant(\Ams\ProduitBundle\Entity\Produit $enfants) {
        $this->enfants->removeElement($enfants);
    }

    /**
     * Get enfants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEnfants() {
        return $this->enfants;
    }

    /**
     * Set image
     *
     * @param \Ams\ExtensionBundle\Entity\Fichier $image
     * @return Produit
     */
    public function setImage(\Ams\ExtensionBundle\Entity\Fichier $image = null) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Ams\ExtensionBundle\Entity\Fichier 
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set lundi
     *
     * @param integer $lundi
     * @return Produit
     */
    public function setLundi($lundi) {
        $this->lundi = $lundi;

        return $this;
    }

    /**
     * Get lundi
     *
     * @return integer 
     */
    public function getLundi() {
        return $this->lundi;
    }

    /**
     * Set mardi
     *
     * @param integer $mardi
     * @return Produit
     */
    public function setMardi($mardi) {
        $this->mardi = $mardi;

        return $this;
    }

    /**
     * Get mardi
     *
     * @return integer 
     */
    public function getMardi() {
        return $this->mardi;
    }

    /**
     * Set mercredi
     *
     * @param integer $mercredi
     * @return Produit
     */
    public function setMercredi($mercredi) {
        $this->mercredi = $mercredi;

        return $this;
    }

    /**
     * Get mercredi
     *
     * @return integer 
     */
    public function getMercredi() {
        return $this->mercredi;
    }

    /**
     * Set jeudi
     *
     * @param integer $jeudi
     * @return Produit
     */
    public function setJeudi($jeudi) {
        $this->jeudi = $jeudi;

        return $this;
    }

    /**
     * Get jeudi
     *
     * @return integer 
     */
    public function getJeudi() {
        return $this->jeudi;
    }

    /**
     * Set vendredi
     *
     * @param integer $vendredi
     * @return Produit
     */
    public function setVendredi($vendredi) {
        $this->vendredi = $vendredi;

        return $this;
    }

    /**
     * Get vendredi
     *
     * @return integer 
     */
    public function getVendredi() {
        return $this->vendredi;
    }

    /**
     * Set samedi
     *
     * @param integer $samedi
     * @return Produit
     */
    public function setSamedi($samedi) {
        $this->samedi = $samedi;

        return $this;
    }

    /**
     * Get samedi
     *
     * @return integer 
     */
    public function getSamedi() {
        return $this->samedi;
    }

    /**
     * Set dimanche
     *
     * @param integer $dimanche
     * @return Produit
     */
    public function setDimanche($dimanche) {
        $this->dimanche = $dimanche;

        return $this;
    }

    /**
     * Get dimanche
     *
     * @return integer 
     */
    public function getDimanche() {
        return $this->dimanche;
    }

    /**
     * Remove parents
     *
     * @param \Ams\ProduitBundle\Entity\Produit $parents
     */
    public function removeParent(\Ams\ProduitBundle\Entity\Produit $parents) {
        $this->parents->removeElement($parents);
    }

    /**
     * Set prdCodeNeopress
     *
     * @param string $prdCodeNeopress
     * @return Produit
     */
    public function setPrdCodeNeopress($prdCodeNeopress) {
        $this->prdCodeNeopress = $prdCodeNeopress;

        return $this;
    }

    /**
     * Get prdCodeNeopress
     *
     * @return string 
     */
    public function getPrdCodeNeopress() {
        return $this->prdCodeNeopress;
    }

    /**
     * Set periodicite
     *
     * @param \Ams\ReferentielBundle\Entity\RefPeriodicite $periodicite
     * @return Produit
     */
    public function setPeriodicite(\Ams\ReferentielBundle\Entity\RefPeriodicite $periodicite = null) {
        $this->periodicite = $periodicite;

        return $this;
    }

    /**
     * Get periodicite
     *
     * @return \Ams\ReferentielBundle\Entity\RefPeriodicite 
     */
    public function getPeriodicite() {
        return $this->periodicite;
    }

}
