<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * FranceRoutageProspectionAdrNormRefTmp
 *
 * @ORM\Table(name="france_routage_prospection_adr_norm_ref_tmp", indexes={@index(name="idx_adresse_insee", columns={"adresse", "insee"})
 *                                                                          , @index(name="idx_date_ref", columns={"date_ref"})
 *                                                      }
 *          )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageProspectionAdrNormRefTmpRepository")
 */
class FranceRoutageProspectionAdrNormRefTmp
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
     * @ORM\Column(name="adresse", type="string", length=100, nullable=false)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5, nullable=true)
     */
    private $insee;
    
    /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tourneeJour;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ref", type="date", nullable=true)
     */
    private $dateRef;
    
    

}
