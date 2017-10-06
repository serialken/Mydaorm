<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbonneSoc
 *
 * @ORM\Table(name="produit_type")
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\ProduitTypeRepository")
 */
class ProduitType
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
     * @ORM\Column(name="libelle", type="string", length=250, nullable=false)
     */
    private $libelle;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="hors_presse", type="boolean", nullable=true)
     */
    private $horsPresse = false;
    
    /**
     * @var \Ams\ProduitBundle\Entity\PrdCaract
     *
     * @ORM\OneToMany(targetEntity="\Ams\ProduitBundle\Entity\PrdCaract", mappedBy="produitType")
     */
    protected $caracts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->caracts = new \Doctrine\Common\Collections\ArrayCollection();
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
    
    /**
     * Set id
     * 
     * @param int $id
     * @return InfoPortageAbonneSoc
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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

    /**
     * Add caracts
     *
     * @param \Ams\ProduitBundle\Entity\PrdCaract $caracts
     * @return ProduitType
     */
    public function addCaract(\Ams\ProduitBundle\Entity\PrdCaract $caracts)
    {
        $this->caracts[] = $caracts;

        return $this;
    }

    /**
     * Remove caracts
     *
     * @param \Ams\ProduitBundle\Entity\PrdCaract $caracts
     */
    public function removeCaract(\Ams\ProduitBundle\Entity\PrdCaract $caracts)
    {
        $this->caracts->removeElement($caracts);
    }

    /**
     * Get caracts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCaracts()
    {
        return $this->caracts;
    }
    
    /**
     * Set horsPresse
     *
     * @param boolean $horsPresse
     * @return ProduitType
     */
    public function setHorsPresse($horsPresse)
    {
        $this->horsPresse = $horsPresse;

        return $this;
    }
    
    /**
     * Get horsPresse
     *
     * @return boolean 
     */
    public function getHorsPresse()
    {
        return $this->horsPresse;
    }
    
    
    public function __toString() {
        return $this->getLibelle();
    }
}
