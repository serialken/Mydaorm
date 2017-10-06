<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrdRefSaisie
 *
 * @ORM\Table(name="prd_ref_saisie")
 * @ORM\Entity
 */
class PrdRefSaisie
{
    const CODE_CONST = "cst";
    const CODE_JOUR = "jour";
    const CODE_GROUPE = "grpe";
    const CODE_TOURNEE = "trn";
    
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
     * * @ORM\Column(name="code", type="string", length=10, nullable=false)
     */
    private $code;
    
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

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
     * @return PrdRefSaisie
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
     * Set code
     *
     * @param string $code
     * @return PrdRefSaisie
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
            
    public function __toString() {
        return $this->getLibelle();
    }
}
