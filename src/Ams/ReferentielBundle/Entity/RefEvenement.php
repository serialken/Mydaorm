<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jour
 *
 * @ORM\Table(name="ref_evenement")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefEvenementRepository")
 */
class RefEvenement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
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
     * @ORM\Column(name="couleur", type="string", length=10, nullable=false)
     */
    private $couleur;

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
     * @return RefEvenement
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
     * Set libelle
     *
     * @param string $libelle
     * @return RefEvenement
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getCouleur()
    {
        return $this->couleur;
    }
  
}
