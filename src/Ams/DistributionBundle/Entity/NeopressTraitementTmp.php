<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Table temporaire des traitements divers concernant Neopress
 *
 * @ORM\Table(name="neopress_traitement_tmp", indexes={@Index(name="idx_abo_ext", columns={"numabo_ext", "soc_code_ext"})
 *                                                      , @Index(name="idx_neo_tournee", columns={"neo_tournee"})
 *                                                     }
 *          )
 * @ORM\Entity
 */
class NeopressTraitementTmp
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
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=20, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=38, nullable=true)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=38, nullable=true)
     */
    private $vol2;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=38, nullable=true)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=38, nullable=true)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=38, nullable=true)
     */
    private $vol5;

    /**
     * @var string
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=true)
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=45, nullable=true)
     */
    private $ville;

    /**
     * longitude
     * @var integer
     *
     * @ORM\Column(name="geox", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $geox;

    /**
     * latitude
     * @var integer
     *
     * @ORM\Column(name="geoy", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $geoy;

    /**
     * @var \AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $produit;

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
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    private $socCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="prd_code_ext", type="string", length=20, nullable=false)
     */
    private $prdCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="spr_code_ext", type="string", length=10, nullable=false)
     */
    private $sprCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="neo_produit", type="string", length=150, nullable=false)
     */
    private $neoProduit;

    /**
     * @var string
     *
     * @ORM\Column(name="neo_tournee", type="string", length=20, nullable=false)
     */
    private $neoTournee;

    /**
     * @var \Ams\ModeleBundle\Entity\ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tournee;

    /**
     * @var string
     *
     * @ORM\Column(name="tournee_code", type="string", length=20, nullable=true)
     */
    private $tourneeCode;

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
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tourneeJour;

    /**
     * @var string
     *
     * @ORM\Column(name="tournee_jour_code", type="string", length=20, nullable=true)
     */
    private $tourneeJourCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="tournee_ordre", type="integer", nullable=true)
     */
    private $tourneeOrdre;
    
    

    /**
     * clients_a_servir : Clients a servir
     * reperage : Reperage
     *
     * @ORM\Column(name="type_flux", type="string", length=30, nullable=true)
     */
    private $typeFlux;
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
