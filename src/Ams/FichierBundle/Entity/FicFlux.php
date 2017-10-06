<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicEtat
 *
 * @ORM\Table(name="fic_flux")
 * @ORM\Entity
 */
class FicFlux
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", unique=true, type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
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
     * @return FicEtat
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
     * Set id
     *
     * @param integer $id
     * @return FicFlux
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
