<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PrdCaract
 *
 * @ORM\Table(name="prd_caract"
 *  * 	,uniqueConstraints={@UniqueConstraint(name="un_prd_caract",columns={"produit_type_id","code","saisie_id"})}
        , indexes={@ORM\Index(name="fk_prd_caract_produit_type1_idx", columns={"produit_type_id"}), @ORM\Index(name="fk_prd_caract_prd_caract_type1_idx", columns={"caract_type_id"}), @ORM\Index(name="fk_prd_caract_prd_ref_saisie2_idx", columns={"saisie_id"})}
 *          )
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\PrdCaractRepository") 
 * 
 */
class PrdCaract
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
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9_][a-zA-Z0-9_\-:]*$/D",
     *     message="Le code ne doit contenir que lettres, chiffres, nombre, _ , - ou : "
     * )
     */
    private $code;
    
    /**
     * @var type boolean
     * 
     * @ORM\Column(name="actif", type="boolean", nullable=false)
     */
    private $actif = 1;
    
    /**
     * @var \ProduitType
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\ProduitType", inversedBy="caracts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_type_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $produitType;

    /**
     * @var \PrdCaractType
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\PrdCaractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caract_type_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $caractType;

    /**
     * @var \PrdRefSaisie
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\PrdRefSaisie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="saisie_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $saisie;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=true)
     */
    private $dateFin;

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
     * @return PrdCaract
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
     * Set produitType
     *
     * @param \Ams\ProduitBundle\Entity\ProduitType $produitType
     * @return PrdCaract
     */
    public function setProduitType(\Ams\ProduitBundle\Entity\ProduitType $produitType)
    {
        $this->produitType = $produitType;

        return $this;
    }

    /**
     * Get produitType
     *
     * @return \Ams\ProduitBundle\Entity\ProduitType 
     */
    public function getProduitType()
    {
        return $this->produitType;
    }

    /**
     * Set caractType
     *
     * @param \Ams\ProduitBundle\Entity\PrdCaractType $caractType
     * @return PrdCaract
     */
    public function setCaractType(\Ams\ProduitBundle\Entity\PrdCaractType $caractType)
    {
        $this->caractType = $caractType;

        return $this;
    }

    /**
     * Get caractType
     *
     * @return \Ams\ProduitBundle\Entity\PrdCaractType 
     */
    public function getCaractType()
    {
        return $this->caractType;
    }

    /**
     * Set saisie
     *
     * @param \Ams\ProduitBundle\Entity\PrdRefSaisie $saisie
     * @return PrdCaract
     */
    public function setSaisie(\Ams\ProduitBundle\Entity\PrdRefSaisie $saisie)
    {
        $this->saisie = $saisie;

        return $this;
    }

    /**
     * Get saisie
     *
     * @return \Ams\ProduitBundle\Entity\PrdRefSaisie 
     */
    public function getSaisie()
    {
        return $this->saisie;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return PrdCaract
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return PrdCaract
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
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return PrdCaract
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
}
