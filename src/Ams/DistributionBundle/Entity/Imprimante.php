<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Imprimante
 *
 * @ORM\Table(name="imprimante")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ImprimanteRepository")
 */
class Imprimante
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     */
    private $depotId;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_imprimante", type="string", length=255)
     */
    private $libelleImprimante;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_imprimante", type="string", length=255)
     */
    private $ipImprimante;


    /**
     * @var boolean
     *
     * @ORM\Column(name="etat", type="boolean")
     */
    private $etat;
    
    

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
     * Set libelleImprimante
     *
     * @param string $libelleImprimante
     * @return Imprimante
     */
    public function setLibelleImprimante($libelleImprimante)
    {
        $this->libelleImprimante = $libelleImprimante;

        return $this;
    }

    /**
     * Get libelleImprimante
     *
     * @return string 
     */
    public function getLibelleImprimante()
    {
        return $this->libelleImprimante;
    }

    /**
     * Set ipImprimante
     *
     * @param string $ipImprimante
     * @return Imprimante
     */
    public function setIpImprimante($ipImprimante)
    {
        $this->ipImprimante = $ipImprimante;

        return $this;
    }

    /**
     * Get ipImprimante
     *
     * @return string 
     */
    public function getIpImprimante()
    {
        return $this->ipImprimante;
    }

    /**
     * Set etat
     *
     * @param boolean $etat
     * @return Imprimante
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return boolean 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set depotId
     *
     * @param \Ams\SilogBundle\Entity\Depot $depotId
     * @return Imprimante
     */
    public function setDepotId(\Ams\SilogBundle\Entity\Depot $depotId = null)
    {
        $this->depotId = $depotId;

        return $this;
    }

    /**
     * Get depotId
     *
     * @return \Ams\SilogBundle\Entity\Depot 
     */
    public function getDepotId()
    {
        return $this->depotId;
    }
}
