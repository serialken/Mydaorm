<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parametre
 *
 * @ORM\Table(name="parametre")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\ParametreRepository")
 */
class Parametre
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
     * @ORM\Column(name="attr", type="string", length=25, unique=true, nullable=false)
     */
    private $attr;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255, nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=150, nullable=false)
     */
    private $description;




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
     * Set attr
     *
     * @param string $attr
     * @return Parametre
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    
        return $this;
    }

    /**
     * Get attr
     *
     * @return string 
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     * @return Parametre
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;
    
        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Parametre
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
}
