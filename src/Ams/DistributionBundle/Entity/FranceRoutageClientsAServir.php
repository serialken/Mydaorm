<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * FranceRoutageClientsAServir
 *
 * @ORM\Table(name="france_routage_c_a_s", indexes={@index(name="idx_dat_soc", columns={"date_distrib", "soc_code_ext"})
 *                                                      
 *                                                      }
 *          )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageClientsAServirRepository")
 * 
 */
class FranceRoutageClientsAServir
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
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=50, nullable=false)
     */
    private $numaboExt;

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
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;

    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     */
    private $depot;

    /**
     * @var string
     *
     * @ORM\Column(name="type_portage", type="string", length=1, nullable=true)
     */
    private $typePortage;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer", nullable=true)
     */
    private $qte;

    /**
     * @var string
     *
     * @ORM\Column(name="divers1", type="string", length=255, nullable=true)
     */
    private $divers1;

    /**
     * @var string
     *
     * @ORM\Column(name="info_comp1", type="string", length=255, nullable=true)
     */
    private $infoComp1;

    /**
     * @var string
     *
     * @ORM\Column(name="info_comp2", type="string", length=255, nullable=true)
     */
    private $infoComp2;

    /**
     * @var string
     *
     * @ORM\Column(name="divers2", type="string", length=255, nullable=true)
     */
    private $divers2;

    /**
     * @var \AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;
    
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
     * @var integer
     * Si 0 ou NULL, KO
     * Si 1, c'est OK == rnvp ok
     * 
     *
     * @ORM\Column(name="adr_ok", type="integer", nullable=true)
     */
    private $adrOk;

    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp", inversedBy="adresses_livraison")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
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
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;
    
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
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $jour;

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


    
    
    
    


    

    
}
