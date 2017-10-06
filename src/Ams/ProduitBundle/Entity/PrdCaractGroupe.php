<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrdCaractGroupe
 *
 * @ORM\Table(name="prd_caract_groupe"
 *          , uniqueConstraints={@ORM\UniqueConstraint(name="un_caract_groupe_produit", columns={"produit_id", "prd_caract_id","groupe_id","date_distrib"})}
 * )
 * @ORM\Entity
 */
class PrdCaractGroupe
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
     * @var \PrdJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $produit;

    /**
     * @var \PrdCaract
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\PrdCaract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prd_caract_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $prdCaract;

    /**
     * @var \PrdCaract
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\GroupeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $groupe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

    /**
     * @var integer
     *
     * @ORM\Column(name="valeur_int", type="integer", nullable=true)
     */
    private $valeurInt;

    /**
     * @var float
     *
     * @ORM\Column(name="valeur_float", type="float", precision=10, scale=0, nullable=true)
     */
    private $valeurFloat;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur_string", type="string", length=256, nullable=true)
     */
    private $valeurString;
    
    /**
     * @var string
     *
     * @ORM\Column(name="valeur_datetime", type="datetime", nullable=true)
     */
    private $valeurDateTime;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $utilisateur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;

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
     * Set valeurInt
     *
     * @param integer $valeurInt
     * @return PrdCaractTournee
     */
    public function setValeurInt($valeurInt)
    {
        $this->valeurInt = $valeurInt;

        return $this;
    }

    /**
     * Get valeurInt
     *
     * @return integer 
     */
    public function getValeurInt()
    {
        return $this->valeurInt;
    }

    /**
     * Set valeurFloat
     *
     * @param float $valeurFloat
     * @return PrdCaractTournee
     */
    public function setValeurFloat($valeurFloat)
    {
        $this->valeurFloat = $valeurFloat;

        return $this;
    }

    /**
     * Get valeurFloat
     *
     * @return float 
     */
    public function getValeurFloat()
    {
        return $this->valeurFloat;
    }

    /**
     * Set valeurString
     *
     * @param string $valeurString
     * @return PrdCaractTournee
     */
    public function setValeurString($valeurString)
    {
        $this->valeurString = $valeurString;

        return $this;
    }

    /**
     * Get valeurString
     *
     * @return string 
     */
    public function getValeurString()
    {
        return $this->valeurString;
    }

    /**
     * Set valeurDateTime
     *
     * @param \DateTime $valeurDateTime
     * @return PrdCaractTournee
     */
    public function setValeurDateTime($valeurDateTime)
    {
        $this->valeurDateTime = $valeurDateTime;

        return $this;
    }

    /**
     * Get valeurDateTime
     *
     * @return \DateTime 
     */
    public function getValeurDateTime()
    {
        return $this->valeurDateTime;
    }

    /**
     * Set date_modif
     *
     * @param \DateTime $dateModif
     * @return PrdCaractTournee
     */
    public function setDateModif($dateModif)
    {
        $this->date_modif = $dateModif;

        return $this;
    }

    /**
     * Get date_modif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->date_modif;
    }

    /**
     * Set prdCaract
     *
     * @param \Ams\ProduitBundle\Entity\PrdCaract $prdCaract
     * @return PrdCaractTournee
     */
    public function setPrdCaract(\Ams\ProduitBundle\Entity\PrdCaract $prdCaract)
    {
        $this->prdCaract = $prdCaract;

        return $this;
    }

    /**
     * Get prdCaract
     *
     * @return \Ams\ProduitBundle\Entity\PrdCaract 
     */
    public function getPrdCaract()
    {
        return $this->prdCaract;
    }

    /**
     * Set paiPrdTournee
     *
     * @param \Ams\PaieBundle\Entity\PaiPrdTournee $paiPrdTournee
     * @return PrdCaractTournee
     */
    public function setPaiPrdTournee(\Ams\PaieBundle\Entity\PaiPrdTournee $paiPrdTournee)
    {
        $this->paiPrdTournee = $paiPrdTournee;

        return $this;
    }

    /**
     * Get paiPrdTournee
     *
     * @return \Ams\PaieBundle\Entity\PaiPrdTournee 
     */
    public function getPaiPrdTournee()
    {
        return $this->paiPrdTournee;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return PrdCaractTournee
     */
    public function setUtilisateur(\Ams\SilogBundle\Entity\Utilisateur $utilisateur = null)
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
     * Set date_distrib
     *
     * @param \DateTime $dateDistrib
     * @return PrdCaractGroupe
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->date_distrib = $dateDistrib;

        return $this;
    }

    /**
     * Get date_distrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->date_distrib;
    }

    /**
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return PrdCaractGroupe
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get date_creation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return PrdCaractGroupe
     */
    public function setProduit(\Ams\ProduitBundle\Entity\Produit $produit)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set groupe
     *
     * @param \Ams\ModeleBundle\Entity\GroupeTournee $groupe
     * @return PrdCaractGroupe
     */
    public function setGroupe(\Ams\ModeleBundle\Entity\GroupeTournee $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Ams\ModeleBundle\Entity\GroupeTournee 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
}
