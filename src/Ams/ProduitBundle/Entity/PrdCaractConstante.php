<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrdCaractConstante
 *
 * @ORM\Table(name="prd_caract_constante"
 *  , uniqueConstraints={@ORM\UniqueConstraint(name="un_prd_caract_constante", columns={"produit_id", "prd_caract_id"})}
 *  , indexes={@ORM\Index(name="fk_prd_caract_constante_prd_caract1_idx", columns={"prd_caract_id"}), @ORM\Index(name="fk_prd_caract_constante_produit_idx", columns={"produit_id"}), @ORM\Index(name="fk_prd_caract_constante_utilisateur1_idx", columns={"utilisateur_id"})})
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\PrdCaractConstanteRepository") 
 */
class PrdCaractConstante
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
     * @var float
     *
     * @ORM\Column(name="valeur_datetime", type="datetime", nullable=true)
     */
    private $valeurDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur_string", type="string", length=256, nullable=true)
     */
    private $valeurString;

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
     * @return PrdCaractConstante
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
     * @return PrdCaractConstante
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
     * @return PrdCaractConstante
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
     * Set date_modif
     *
     * @param \DateTime $dateModif
     * @return PrdCaractConstante
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
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return PrdCaractConstante
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
     * Set prdCaract
     *
     * @param \Ams\ProduitBundle\Entity\PrdCaract $prdCaract
     * @return PrdCaractConstante
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
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return PrdCaractConstante
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
     * Set valeurDateTime
     *
     * @param \DateTime $valeurDateTime
     * @return PrdCaractConstante
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
    
    public function getValueByType($code_type) 
    {
        $value = null;
                
        switch ($code_type) {
            case PrdCaractType::CODE_TYPE_VARCHAR : 
                $value = $this->getValeurString();
                break;
            case PrdCaractType::CODE_TYPE_INT : 
                $value = $this->getValeurInt();
                break;
            case PrdCaractType::CODE_TYPE_FLOAT : 
                $value = $this->getValeurFloat();
                break;
            case PrdCaractType::CODE_TYPE_DATE || PrdCaractType::CODE_TYPE_DATETIME : 
                $value = $this->getValeurDateTime();
                break;
        }
        
        return $value;
    }
    
    public function setValueByType($code_type, $value) 
    {
        switch ($code_type) {
            case PrdCaractType::CODE_TYPE_VARCHAR :
                $this->setValeurString($value);
                break;
            case PrdCaractType::CODE_TYPE_INT : 
                $this->setValeurInt($value);
                break;
            case PrdCaractType::CODE_TYPE_FLOAT : 
                $this->setValeurFloat($value);
                break;
            case PrdCaractType::CODE_TYPE_DATE || PrdCaractType::CODE_TYPE_DATETIME : 
                $this->setValeurDateTime($value);
                break;
        }
        
        return $this;
    }

    /**
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return PrdCaractConstante
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
    
     public function __construct()
    {
        $this->date_creation = new \Datetime();
    }
}
