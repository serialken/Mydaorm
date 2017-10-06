<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NavElementDroit
 *
 * @ORM\Table(name="nav_element_droit")
 * @ORM\Entity
 */
class NavElementDroit
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
     * @ORM\Column(name="edr_droit_elt", type="string", length=10, unique=true, nullable=false)
     */
    private $edrDroitElt;

    /**
     * @var string
     *
     * @ORM\Column(name="edr_droit_elt_libelle", type="string", length=45, nullable=false)
     */
    private $edrDroitEltLibelle;



    /**
     * Get srcId
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get edrDroitElt
     *
     * @return string 
     */
    public function getEdrDroitElt()
    {
        return $this->edrDroitElt;
    }

    /**
     * Set EdrDroitElt
     *
     * @param string $edrDroitElt
     * @return NavElementDroit
     */
    public function setEdrDroitElt($edrDroitElt)
    {
        $this->edrDroitElt = $edrDroitElt;
    
        return $this;
    }

    /**
     * Set edrDroitEltLibelle
     *
     * @param string $edrDroitEltLibelle
     * @return NavElementDroit
     */
    public function setEdrDroitEltLibelle($edrDroitEltLibelle)
    {
        $this->edrDroitEltLibelle = $edrDroitEltLibelle;
    
        return $this;
    }

    /**
     * Get edrDroitEltLibelle
     *
     * @return string 
     */
    public function getEdrDroitEltLibelle()
    {
        return $this->edrDroitEltLibelle;
    }
}
