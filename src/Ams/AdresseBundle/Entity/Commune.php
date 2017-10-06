<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Insee
 *
 * @ORM\Table(name="commune",
 *              uniqueConstraints={@UniqueConstraint(name="insee",columns={"insee"})}
 *              )
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\CommuneRepository")
 * @UniqueEntity(
 *      fields={"insee"},
 *      errorPath="insee",
 *      message="Le code INSEE est déjà utilisé."
 * )
 */
class Commune
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5)
     */
    protected $insee;

    /**
     * @var string
     *
     * @ORM\Column(name="cp", type="string", length=5)
     */
    protected $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    protected $libelle;
    
    /**
     * @var \DepotCommune
     *
     * @ORM\OneToMany(targetEntity="Ams\AdresseBundle\Entity\DepotCommune",  mappedBy="commune")
     */
    protected $depotCommunes;

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
     * Set insee
     *
     * @param string $insee
     * @return Insee
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;
    
        return $this;
    }

    /**
     * Get insee
     *
     * @return string 
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return Commune
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    
        return $this;
    }

    /**
     * Get cp
     *
     * @return string 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Insee
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
     * Constructor
     */
    public function __construct()
    {
        $this->depotCommunes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add depotCommunes
     *
     * @param \Ams\AdresseBundle\Entity\DepotCommune $depotCommunes
     * @return Commune
     */
    public function addDepotCommune(\Ams\AdresseBundle\Entity\DepotCommune $depotCommunes)
    {
        $this->depotCommunes[] = $depotCommunes;
    
        return $this;
    }

    /**
     * Remove depotCommunes
     *
     * @param \Ams\AdresseBundle\Entity\DepotCommune $depotCommunes
     */
    public function removeDepotCommune(\Ams\AdresseBundle\Entity\DepotCommune $depotCommunes)
    {
        $this->depotCommunes->removeElement($depotCommunes);
    }

    /**
     * Get depotCommunes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepotCommunes()
    {
        return $this->depotCommunes;
    }
    
    public function getLibelleWithCp()
    {
        return $this->getCp() . " - " . $this->getLibelle();
    }
}