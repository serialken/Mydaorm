<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * FranceRoutageAdresse
 *
 * @ORM\Table(name="france_routage_adresse", indexes={@index(name="idx_abonne_id_ext", columns={"numabo_ext", "soc_code_ext"})
 *                                                      , @index(name="idx_abo_adr_ext", columns={"vol1_ext", "vol2_ext", "vol3_ext", "vol4_ext", "vol5_ext", "cp_ext", "ville_ext"})
 *                                                      
 *                                                      }
 *          )
 * @ORM\Entity()
 */
class FranceRoutageAdresse
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
     * @ORM\Column(name="numabo_ext", type="string", length=50, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    private $socCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1_ext", type="string", length=100, nullable=true)
     */
    private $vol1Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2_ext", type="string", length=100, nullable=true)
     */
    private $vol2Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3_ext", type="string", length=100, nullable=true)
     */
    private $vol3Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4_ext", type="string", length=100, nullable=true)
     */
    private $vol4Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5_ext", type="string", length=100, nullable=true)
     */
    private $vol5Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="cp_ext", type="string", length=5, nullable=true)
     */
    private $cpExt;

    /**
     * @var string
     *
     * @ORM\Column(name="ville_ext", type="string", length=45, nullable=true)
     */
    private $villeExt;
    
    /**
     * @var integer
     * Si 0, c'est abonne
     * Si 1, c'est Lieu de vente
     * 
     *
     * @ORM\Column(name="client_type", type="integer", nullable=true)
     */
    private $clientType;
    
    

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_vol1", type="string", length=100, nullable=true)
     */
    private $rnvpVol1;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_vol2", type="string", length=100, nullable=true)
     */
    private $rnvpVol2;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_vol3", type="string", length=100, nullable=true)
     */
    private $rnvpVol3;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_vol4", type="string", length=100, nullable=true)
     */
    private $rnvpVol4;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_vol5", type="string", length=100, nullable=true)
     */
    private $rnvpVol5;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_cp", type="string", length=5, nullable=true)
     */
    private $rnvpCp;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_ville", type="string", length=45, nullable=true)
     */
    private $rnvpVille;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_insee", type="string", length=5, nullable=true)
     */
    private $rnvpInsee;
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * @var \Ams\AbonneBundle\Entity\AbonneUnique
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneUnique")
     * @ORM\JoinColumn(name="abonne_unique_id", referencedColumnName="id", nullable=true)
     */
    private $abonneUnique;
    
    /**
     * @var string
     * @ORM\Column(name="modele_tournee_jour_code", type="string")
     */
    private $modeleTourneeJourCode;
    
    /**
     * @var integer
     * Si 0 ou NULL, Non livrable
     * Si 1, c'est Livrable
     * 
     *
     * @ORM\Column(name="livrable", type="integer", nullable=true)
     */
    private $livrable;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;

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


    
    
    
    


    

    
}
