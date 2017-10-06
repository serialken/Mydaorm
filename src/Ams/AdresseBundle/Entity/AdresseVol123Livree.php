<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\Mapping\Index AS index;

/**
 * AdresseVol123Livree
 *
 * @ORM\Table(name="adresse_vol123_livree"
 *                                  , indexes={@index(name="idx_adrs", columns={"cadrs", "adresse", "lieudit", "cp", "ville"})
 *                                              }
 *              )
 * 
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\AdresseVol123LivreeRepository")
 */
class AdresseVol123Livree
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
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $jour;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $flux;
    
    /**
     * @var string
     *
     * @ORM\Column(name="mtj_code", type="string", length=100, nullable=true)
     */
    private $mtjCode;
    
    /**
     * Ordre dans tournee
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @var string
     * 
     * @ORM\Column(name="cadrs", type="string", length=100, nullable=true)
     */
    private $cAdrs;

    /**
     * @var string
     * 
     * @ORM\Column(name="adresse", type="string", length=100, nullable=false)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="lieudit", type="string", length=100, nullable=true)
     */
    private $lieuDit;

    /**
     * @var string
     * 
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    private $cp;

    /**
     * @var string
     * 
     * @ORM\Column(name="ville", type="string", length=45, nullable=false)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5, nullable=true)
     */
    private $insee;
    
    /**
     * @var string
     *
     * @ORM\Column(name="liste_soc_code_ext", type="string", length=255, nullable=false)
     */
    private $listeSocCodeExt;
    
        
}
