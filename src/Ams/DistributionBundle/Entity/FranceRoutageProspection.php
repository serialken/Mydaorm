<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * FranceRoutageProspection
 *
 * @ORM\Table(name="france_routage_prospection", indexes={@index(name="idx_adresse_insee", columns={"rnvp_vol4", "rnvp_insee"})
 *                                                      , @index(name="idx_abo_adr_ext", columns={"vol1_ext", "vol2_ext", "vol3_ext", "vol4_ext", "vol5_ext", "cp_ext", "ville_ext"})
 *                                                      , @index(name="idx_livrable", columns={"livrable"})
 *                                                      , @index(name="idx_adr_ok", columns={"adr_ok"})
 *                                                      , @index(name="idx_prob", columns={"type_probl"})
 *                                                      , @index(name="idx_date_distrib", columns={"date_distrib"})
 *                                                      , @index(name="idx_date_ref", columns={"date_ref"})
 *                                                      
 *                                                      }
 *          )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageProspectionRepository")
 */
class FranceRoutageProspection
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
     * @var \FranceRoutageProspectionListe
     *
     * @ORM\ManyToOne(targetEntity="Ams\DistributionBundle\Entity\FranceRoutageProspectionListe")
     * @ORM\JoinColumn(name="fr_prospection_liste_id", referencedColumnName="id", nullable=true)
     */
    private $frProspectionListe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $dateDistrib;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ref", type="date", nullable=true)
     */
    private $dateRef;

    /**
     * @var string
     *
     * @ORM\Column(name="num_parution", type="string", length=20, nullable=true)
     */
    private $numParution;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=100, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1_ext", type="string", length=150, nullable=true)
     */
    private $vol1Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2_ext", type="string", length=150, nullable=true)
     */
    private $vol2Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3_ext", type="string", length=150, nullable=true)
     */
    private $vol3Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4_ext", type="string", length=150, nullable=true)
     */
    private $vol4Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5_ext", type="string", length=150, nullable=true)
     */
    private $vol5Ext;

    /**
     * @var string
     *
     * @ORM\Column(name="cp_ext", type="string", length=100, nullable=true)
     */
    private $cpExt;

    /**
     * @var string
     *
     * @ORM\Column(name="ville_ext", type="string", length=150, nullable=true)
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
     * @ORM\Column(name="vol1_a_rnvp", type="string", length=150, nullable=true)
     */
    private $vol1ARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2_a_rnvp", type="string", length=150, nullable=true)
     */
    private $vol2ARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3_a_rnvp", type="string", length=150, nullable=true)
     */
    private $vol3ARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4_a_rnvp", type="string", length=150, nullable=true)
     */
    private $vol4ARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5_a_rnvp", type="string", length=150, nullable=true)
     */
    private $vol5ARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="cp_a_rnvp", type="string", length=100, nullable=true)
     */
    private $cpARnvp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville_a_rnvp", type="string", length=150, nullable=true)
     */
    private $villeARnvp;
    
    

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
     * @var string
     * Type de probleme concernant la ligne
     * 'HORS_IDF' : Hors Ile de France
     * 'SOC_INCONNU' : Societe inconnue (non parametree)
     * 'ERR_RNVP_*' : Erreur RNVP
     *
     * @ORM\Column(name="type_probl", type="string", length=255, nullable=true)
     */
    private $typeProbl;
    
    /**
     * @var integer
     * Si 0 ou NULL, pas de changement d'adresse
     * Si 1, Changement d'adresse
     *
     * @ORM\Column(name="chgt_adr", type="integer", nullable=true)
     */
    private $chgtAdr;

    /**
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     */
    private $jour;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;
    
    /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tourneeJour;
    
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
