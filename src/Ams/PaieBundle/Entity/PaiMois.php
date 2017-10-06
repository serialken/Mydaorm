<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiMois
 *
 * @ORM\Table(name="pai_mois"
 *              )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiMoisRepository")
 */
class PaiMois
{
    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     * @ORM\Id
     */
    private $anneemois;

    /**
     * @var \RefFlux
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $flux;
    
    /**
     * @var string
     *
     * @ORM\Column(name="annee", type="string", length=4, nullable=false)
     */
    private $annee;

    /**
     * @var string
     *
     * @ORM\Column(name="mois", type="string", length=2, nullable=false)
     */
    private $mois;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $date_debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=false)
     */
    private $date_fin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_blocage", type="datetime", nullable=true)
     */
    private $date_blocage;
    
    /**
     * @var string
     *
     * @ORM\Column(name="anneemois_reclamation", type="string", length=6, nullable=false)
     */
    private $anneemois_reclamation;

    /**
     * @var string
     *
     * @ORM\Column(name="date_debut_string", type="string", length=8, nullable=false)
     */
    private $date_debut_string;
    
    /**
     * @var string
     *
     * @ORM\Column(name="date_fin_string", type="string", length=8, nullable=false)
     */
    private $date_fin_string;
}
