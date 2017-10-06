<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * FicFormatEnregistrement
 *
 * @ORM\Table(name="fic_format_enregistrement",
 * 			uniqueConstraints={@UniqueConstraint(name="fic_code_attribut",columns={"FIC_CODE","ATTRIBUT"})}
 * 			)
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicFormatEnregistrementRepository")
 */
class FicFormatEnregistrement
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
     * @ORM\Column(name="fic_code", type="string", length=45, nullable=false)
     */
    private $ficCode;

    /**
     * @var string
     *
     * @ORM\Column(name="attribut", type="string", length=45, nullable=false)
     */
    private $attribut;

    /**
     * @var integer
     *
     * @ORM\Column(name="col_debut", type="integer", nullable=true)
     */
    private $colDebut;

    /**
     * @var integer
     *
     * @ORM\Column(name="col_long", type="integer", nullable=true)
     */
    private $colLong;

    /**
     * @var integer
     *
     * @ORM\Column(name="col_val", type="integer", nullable=true)
     */
    private $colVal;

    /**
     * @var string
     *
     * @ORM\Column(name="col_val_rplct", type="string", length=255, nullable=true)
     */
    private $colValRplct;

    /**
     * @var string
     *
     * @ORM\Column(name="col_desc", type="string", length=255, nullable=true)
     */
    private $colDesc;



    /**
     * Set ficCode
     *
     * @param string $ficCode
     * @return FicFormatEnregistrement
     */
    public function setFicCode($ficCode)
    {
        $this->ficCode = $ficCode;
    
        return $this;
    }

    /**
     * Get ficCode
     *
     * @return string 
     */
    public function getFicCode()
    {
        return $this->ficCode;
    }

    /**
     * Set attribut
     *
     * @param string $attribut
     * @return FicFormatEnregistrement
     */
    public function setAttribut($attribut)
    {
        $this->attribut = $attribut;
    
        return $this;
    }

    /**
     * Get attribut
     *
     * @return string 
     */
    public function getAttribut()
    {
        return $this->attribut;
    }

    /**
     * Set colDebut
     *
     * @param integer $colDebut
     * @return FicFormatEnregistrement
     */
    public function setColDebut($colDebut)
    {
        $this->colDebut = $colDebut;
    
        return $this;
    }

    /**
     * Get colDebut
     *
     * @return integer 
     */
    public function getColDebut()
    {
        return $this->colDebut;
    }

    /**
     * Set colLong
     *
     * @param integer $colLong
     * @return FicFormatEnregistrement
     */
    public function setColLong($colLong)
    {
        $this->colLong = $colLong;
    
        return $this;
    }

    /**
     * Get colLong
     *
     * @return integer 
     */
    public function getColLong()
    {
        return $this->colLong;
    }

    /**
     * Set colVal
     *
     * @param integer $colVal
     * @return FicFormatEnregistrement
     */
    public function setColVal($colVal)
    {
        $this->colVal = $colVal;
    
        return $this;
    }

    /**
     * Get colVal
     *
     * @return integer 
     */
    public function getColVal()
    {
        return $this->colVal;
    }

    /**
     * Set colValRplct
     *
     * @param string $colValRplct
     * @return FicFormatEnregistrement
     */
    public function setColValRplct($colValRplct)
    {
        $this->colValRplct = $colValRplct;
    
        return $this;
    }

    /**
     * Get colValRplct
     *
     * @return string 
     */
    public function getColValRplct()
    {
        return $this->colValRplct;
    }

    /**
     * Set colDesc
     *
     * @param string $colDesc
     * @return FicFormatEnregistrement
     */
    public function setColDesc($colDesc)
    {
        $this->colDesc = $colDesc;
    
        return $this;
    }

    /**
     * Get colDesc
     *
     * @return string 
     */
    public function getColDesc()
    {
        return $this->colDesc;
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
}
