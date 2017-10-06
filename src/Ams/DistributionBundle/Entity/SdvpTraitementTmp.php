<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * SdvpTraitementTmp
 *
 * @ORM\Table(name="sdvp_traitement_tmp", indexes={@index(name="idx_sdvp_tmp_numabo_ext", columns={"numabo_ext", "soc_code_ext"})
 *                                                  , @index(name="idx_sdvp_tmp_depot_dcs", columns={"depot_dcs"})
 *                                                  , @index(name="idx_sdvp_tmp_tournee_dcs", columns={"tournee_dcs"})
 *                                                  }
 *          )
 * @ORM\Entity()
 * 
 */
class SdvpTraitementTmp
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="date", nullable=false)
     */
    private $dateParution;

    /**
     * @var string
     *
     * @ORM\Column(name="num_parution", type="string", length=20, nullable=true)
     */
    private $numParution;

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
     * @var string
     *
     * @ORM\Column(name="type_dis", type="string", length=32, nullable=true)
     */
    private $typeDis;

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
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer", nullable=true)
     */
    private $qte;

    /**
     * @var string
     *
     * @ORM\Column(name="divers1", type="string", length=45, nullable=true)
     */
    private $divers1;

    /**
     * @var string
     *
     * @ORM\Column(name="info_comp1", type="string", length=120, nullable=true)
     */
    private $infoComp1;

    /**
     * @var string
     *
     * @ORM\Column(name="info_comp2", type="string", length=32, nullable=true)
     */
    private $infoComp2;

    /**
     * @var string
     *
     * @ORM\Column(name="divers2", type="string", length=32, nullable=true)
     */
    private $divers2;

    /**
     * @var string
     *
     * @ORM\Column(name="depot_dcs", type="string", length=10, nullable=true)
     */
    private $depotDCS;

    /**
     * @var string
     *
     * @ORM\Column(name="tournee_dcs", type="string", length=10, nullable=true)
     */
    private $tourneeDCS;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre_dcs", type="integer", nullable=true)
     */
    private $ordreDCS;

    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     */
    private $depot;

    /**
     * @var \AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;

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
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5, nullable=true)
     */
    private $insee;
    
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
     * @var \Ams\ModeleBundle\Entity\ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTournee")
     * @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=true)
     */
    private $tournee;

    /**
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
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $jour;
    
    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;


    
    
    
    


    

    

    
}
