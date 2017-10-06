<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bordereau
 *
 * @ORM\Table(name="bordereau")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\BordereauRepository")
 */
class Bordereau
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="point_livraison", type="integer")
     */
    private $pointLivraison;

    /**
     * @var boolean
     *
     * @ORM\Column(name="state", type="boolean")
     */
    private $state;


    

    
    /**
     * Set pointLivraison
     *
     * @param integer $pointLivraison
     * @return Bordereau
     */
    public function setPointLivraison($pointLivraison)
    {
        $this->pointLivraison = $pointLivraison;

        return $this;
    }

    /**
     * Get pointLivraison
     *
     * @return integer 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }

    /**
     * Set state
     *
     * @param boolean $state
     * @return Bordereau
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return boolean 
     */
    public function getState()
    {
        return $this->state;
    } 
}
