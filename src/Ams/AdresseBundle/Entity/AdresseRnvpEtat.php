<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RnvpEtat
 *
 * @ORM\Table(name="adresse_rnvp_etat")
 * @ORM\Entity
 */
class AdresseRnvpEtat
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
     * OK - KO - A VERIFIER
     * @var string
     *
     * @ORM\Column(name="qualite", type="string", length=15, nullable=false)
     */
    private $qualite;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=30, unique=true, nullable=false)
     */
    private $code;

    

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
     * Set qualite
     *
     * @param string $qualite
     * @return AdresseRnvpEtat
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
    
        return $this;
    }

    /**
     * Get qualite
     *
     * @return string 
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return AdresseRnvpEtat
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
     * @return AdresseRnvpEtat
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
}