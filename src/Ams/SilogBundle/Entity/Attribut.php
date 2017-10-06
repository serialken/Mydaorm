<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbonneSoc
 *
 * @ORM\Table(name="attribut")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\AttributRepository")
 */
class Attribut
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
     * @ORM\Column(name="code", type="string", length=30, unique=true, nullable=false)
     */
    private $code;
      
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=250, unique=true, nullable=false)
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
     * @return InfoPortageAbonneSoc
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
}
