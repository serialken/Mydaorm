<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Index AS index;

/**
 * AdresseLivree
 *
 * @ORM\Table(name="adresse_livree"
 *                                  , indexes={@index(name="idx_date_distrib", columns={"date_distrib"})
 *                                              , @index(name="idx_adresse_commune", columns={"adresse", "commune_id"})
 *                                              , @index(name="idx_adresse", columns={"adresse"})
 *                                              }
 *              )
 * 
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\AdresseLivreeRepository")
 */
class AdresseLivree
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $dateDistrib;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     */
    private $flux;

    /**
     * @var string
     * 
     * @ORM\Column(name="adresse", type="string", length=100, nullable=false)
     */
    private $adresse;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;
    
    /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tourneeJour;
    
    
}
