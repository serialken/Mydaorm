<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Etiquette
 *
 * @ORM\Table(name="etiquette",indexes={@ORM\Index(name="etiquette_asoc", columns={"abonne_soc_id"}) })
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\EtiquetteRepository")
 */
class Etiquette
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
     *
     * @ORM\Column(name="abonne_soc_id", type="integer")
     */
    private $abonneSocId;


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
     * Set abonneSocId
     *
     * @param integer $abonneSocId
     * @return Etiquette
     */
    public function setAbonneSocId($abonneSocId)
    {
        $this->abonneSocId = $abonneSocId;

        return $this;
    }

    /**
     * Get abonneSocId
     *
     * @return integer 
     */
    public function getAbonneSocId()
    {
        return $this->abonneSocId;
    }
}
