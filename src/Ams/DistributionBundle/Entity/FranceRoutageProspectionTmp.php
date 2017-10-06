<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * FranceRoutageProspectionTmp
 *
 * @ORM\Table(name="france_routage_prospection_tmp", indexes={@index(name="idx_adresse_insee", columns={"rnvp_vol4", "rnvp_insee"})
 *                                                      , @index(name="idx_abo_adr_ext", columns={"vol1_ext", "vol2_ext", "vol3_ext", "vol4_ext", "vol5_ext", "cp_ext", "ville_ext"})
 *                                                      , @index(name="idx_livrable", columns={"livrable"})
 *                                                      , @index(name="idx_origine", columns={"origine"})
 *                                                      , @index(name="idx_adr_ok", columns={"adr_ok"})
 *                                                      , @index(name="idx_prob", columns={"type_probl"})
 *                                                      , @index(name="idx_date_distrib", columns={"date_distrib"})
 *                                                      , @index(name="idx_date_ref", columns={"date_ref"})
 *                                                      
 *                                                      }
 *          )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FranceRoutageProspectionTmpRepository")
 */
class FranceRoutageProspectionTmp
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
     * Si 0, ligne ajoutee
     * Si 1, ligne d'origine
     * 
     *
     * @ORM\Column(name="origine", type="integer", nullable=false, options={"default" = 0})
     */
    private $origine;
    
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
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return FranceRoutageProspectionTmp
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->dateDistrib = $dateDistrib;

        return $this;
    }

    /**
     * Get dateDistrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->dateDistrib;
    }

    /**
     * Set dateRef
     *
     * @param \DateTime $dateRef
     * @return FranceRoutageProspectionTmp
     */
    public function setDateRef($dateRef)
    {
        $this->dateRef = $dateRef;

        return $this;
    }

    /**
     * Get dateRef
     *
     * @return \DateTime 
     */
    public function getDateRef()
    {
        return $this->dateRef;
    }

    /**
     * Set numParution
     *
     * @param string $numParution
     * @return FranceRoutageProspectionTmp
     */
    public function setNumParution($numParution)
    {
        $this->numParution = $numParution;

        return $this;
    }

    /**
     * Get numParution
     *
     * @return string 
     */
    public function getNumParution()
    {
        return $this->numParution;
    }

    /**
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return FranceRoutageProspectionTmp
     */
    public function setNumaboExt($numaboExt)
    {
        $this->numaboExt = $numaboExt;

        return $this;
    }

    /**
     * Get numaboExt
     *
     * @return string 
     */
    public function getNumaboExt()
    {
        return $this->numaboExt;
    }

    /**
     * Set vol1Ext
     *
     * @param string $vol1Ext
     * @return FranceRoutageProspectionTmp
     */
    public function setVol1Ext($vol1Ext)
    {
        $this->vol1Ext = $vol1Ext;

        return $this;
    }

    /**
     * Get vol1Ext
     *
     * @return string 
     */
    public function getVol1Ext()
    {
        return $this->vol1Ext;
    }

    /**
     * Set vol2Ext
     *
     * @param string $vol2Ext
     * @return FranceRoutageProspectionTmp
     */
    public function setVol2Ext($vol2Ext)
    {
        $this->vol2Ext = $vol2Ext;

        return $this;
    }

    /**
     * Get vol2Ext
     *
     * @return string 
     */
    public function getVol2Ext()
    {
        return $this->vol2Ext;
    }

    /**
     * Set vol3Ext
     *
     * @param string $vol3Ext
     * @return FranceRoutageProspectionTmp
     */
    public function setVol3Ext($vol3Ext)
    {
        $this->vol3Ext = $vol3Ext;

        return $this;
    }

    /**
     * Get vol3Ext
     *
     * @return string 
     */
    public function getVol3Ext()
    {
        return $this->vol3Ext;
    }

    /**
     * Set vol4Ext
     *
     * @param string $vol4Ext
     * @return FranceRoutageProspectionTmp
     */
    public function setVol4Ext($vol4Ext)
    {
        $this->vol4Ext = $vol4Ext;

        return $this;
    }

    /**
     * Get vol4Ext
     *
     * @return string 
     */
    public function getVol4Ext()
    {
        return $this->vol4Ext;
    }

    /**
     * Set vol5Ext
     *
     * @param string $vol5Ext
     * @return FranceRoutageProspectionTmp
     */
    public function setVol5Ext($vol5Ext)
    {
        $this->vol5Ext = $vol5Ext;

        return $this;
    }

    /**
     * Get vol5Ext
     *
     * @return string 
     */
    public function getVol5Ext()
    {
        return $this->vol5Ext;
    }

    /**
     * Set cpExt
     *
     * @param string $cpExt
     * @return FranceRoutageProspectionTmp
     */
    public function setCpExt($cpExt)
    {
        $this->cpExt = $cpExt;

        return $this;
    }

    /**
     * Get cpExt
     *
     * @return string 
     */
    public function getCpExt()
    {
        return $this->cpExt;
    }

    /**
     * Set villeExt
     *
     * @param string $villeExt
     * @return FranceRoutageProspectionTmp
     */
    public function setVilleExt($villeExt)
    {
        $this->villeExt = $villeExt;

        return $this;
    }

    /**
     * Get villeExt
     *
     * @return string 
     */
    public function getVilleExt()
    {
        return $this->villeExt;
    }

    /**
     * Set qte
     *
     * @param integer $qte
     * @return FranceRoutageProspectionTmp
     */
    public function setQte($qte)
    {
        $this->qte = $qte;

        return $this;
    }

    /**
     * Get qte
     *
     * @return integer 
     */
    public function getQte()
    {
        return $this->qte;
    }

    /**
     * Set divers1
     *
     * @param string $divers1
     * @return FranceRoutageProspectionTmp
     */
    public function setDivers1($divers1)
    {
        $this->divers1 = $divers1;

        return $this;
    }

    /**
     * Get divers1
     *
     * @return string 
     */
    public function getDivers1()
    {
        return $this->divers1;
    }

    /**
     * Set infoComp1
     *
     * @param string $infoComp1
     * @return FranceRoutageProspectionTmp
     */
    public function setInfoComp1($infoComp1)
    {
        $this->infoComp1 = $infoComp1;

        return $this;
    }

    /**
     * Get infoComp1
     *
     * @return string 
     */
    public function getInfoComp1()
    {
        return $this->infoComp1;
    }

    /**
     * Set infoComp2
     *
     * @param string $infoComp2
     * @return FranceRoutageProspectionTmp
     */
    public function setInfoComp2($infoComp2)
    {
        $this->infoComp2 = $infoComp2;

        return $this;
    }

    /**
     * Get infoComp2
     *
     * @return string 
     */
    public function getInfoComp2()
    {
        return $this->infoComp2;
    }

    /**
     * Set divers2
     *
     * @param string $divers2
     * @return FranceRoutageProspectionTmp
     */
    public function setDivers2($divers2)
    {
        $this->divers2 = $divers2;

        return $this;
    }

    /**
     * Get divers2
     *
     * @return string 
     */
    public function getDivers2()
    {
        return $this->divers2;
    }

    /**
     * Set clientType
     *
     * @param integer $clientType
     * @return FranceRoutageProspectionTmp
     */
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;

        return $this;
    }

    /**
     * Get clientType
     *
     * @return integer 
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * Set vol1ARnvp
     *
     * @param string $vol1ARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVol1ARnvp($vol1ARnvp)
    {
        $this->vol1ARnvp = $vol1ARnvp;

        return $this;
    }

    /**
     * Get vol1ARnvp
     *
     * @return string 
     */
    public function getVol1ARnvp()
    {
        return $this->vol1ARnvp;
    }

    /**
     * Set vol2ARnvp
     *
     * @param string $vol2ARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVol2ARnvp($vol2ARnvp)
    {
        $this->vol2ARnvp = $vol2ARnvp;

        return $this;
    }

    /**
     * Get vol2ARnvp
     *
     * @return string 
     */
    public function getVol2ARnvp()
    {
        return $this->vol2ARnvp;
    }

    /**
     * Set vol3ARnvp
     *
     * @param string $vol3ARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVol3ARnvp($vol3ARnvp)
    {
        $this->vol3ARnvp = $vol3ARnvp;

        return $this;
    }

    /**
     * Get vol3ARnvp
     *
     * @return string 
     */
    public function getVol3ARnvp()
    {
        return $this->vol3ARnvp;
    }

    /**
     * Set vol4ARnvp
     *
     * @param string $vol4ARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVol4ARnvp($vol4ARnvp)
    {
        $this->vol4ARnvp = $vol4ARnvp;

        return $this;
    }

    /**
     * Get vol4ARnvp
     *
     * @return string 
     */
    public function getVol4ARnvp()
    {
        return $this->vol4ARnvp;
    }

    /**
     * Set vol5ARnvp
     *
     * @param string $vol5ARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVol5ARnvp($vol5ARnvp)
    {
        $this->vol5ARnvp = $vol5ARnvp;

        return $this;
    }

    /**
     * Get vol5ARnvp
     *
     * @return string 
     */
    public function getVol5ARnvp()
    {
        return $this->vol5ARnvp;
    }

    /**
     * Set cpARnvp
     *
     * @param string $cpARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setCpARnvp($cpARnvp)
    {
        $this->cpARnvp = $cpARnvp;

        return $this;
    }

    /**
     * Get cpARnvp
     *
     * @return string 
     */
    public function getCpARnvp()
    {
        return $this->cpARnvp;
    }

    /**
     * Set villeARnvp
     *
     * @param string $villeARnvp
     * @return FranceRoutageProspectionTmp
     */
    public function setVilleARnvp($villeARnvp)
    {
        $this->villeARnvp = $villeARnvp;

        return $this;
    }

    /**
     * Get villeARnvp
     *
     * @return string 
     */
    public function getVilleARnvp()
    {
        return $this->villeARnvp;
    }

    /**
     * Set rnvpVol1
     *
     * @param string $rnvpVol1
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVol1($rnvpVol1)
    {
        $this->rnvpVol1 = $rnvpVol1;

        return $this;
    }

    /**
     * Get rnvpVol1
     *
     * @return string 
     */
    public function getRnvpVol1()
    {
        return $this->rnvpVol1;
    }

    /**
     * Set rnvpVol2
     *
     * @param string $rnvpVol2
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVol2($rnvpVol2)
    {
        $this->rnvpVol2 = $rnvpVol2;

        return $this;
    }

    /**
     * Get rnvpVol2
     *
     * @return string 
     */
    public function getRnvpVol2()
    {
        return $this->rnvpVol2;
    }

    /**
     * Set rnvpVol3
     *
     * @param string $rnvpVol3
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVol3($rnvpVol3)
    {
        $this->rnvpVol3 = $rnvpVol3;

        return $this;
    }

    /**
     * Get rnvpVol3
     *
     * @return string 
     */
    public function getRnvpVol3()
    {
        return $this->rnvpVol3;
    }

    /**
     * Set rnvpVol4
     *
     * @param string $rnvpVol4
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVol4($rnvpVol4)
    {
        $this->rnvpVol4 = $rnvpVol4;

        return $this;
    }

    /**
     * Get rnvpVol4
     *
     * @return string 
     */
    public function getRnvpVol4()
    {
        return $this->rnvpVol4;
    }

    /**
     * Set rnvpVol5
     *
     * @param string $rnvpVol5
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVol5($rnvpVol5)
    {
        $this->rnvpVol5 = $rnvpVol5;

        return $this;
    }

    /**
     * Get rnvpVol5
     *
     * @return string 
     */
    public function getRnvpVol5()
    {
        return $this->rnvpVol5;
    }

    /**
     * Set rnvpCp
     *
     * @param string $rnvpCp
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpCp($rnvpCp)
    {
        $this->rnvpCp = $rnvpCp;

        return $this;
    }

    /**
     * Get rnvpCp
     *
     * @return string 
     */
    public function getRnvpCp()
    {
        return $this->rnvpCp;
    }

    /**
     * Set rnvpVille
     *
     * @param string $rnvpVille
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpVille($rnvpVille)
    {
        $this->rnvpVille = $rnvpVille;

        return $this;
    }

    /**
     * Get rnvpVille
     *
     * @return string 
     */
    public function getRnvpVille()
    {
        return $this->rnvpVille;
    }

    /**
     * Set rnvpInsee
     *
     * @param string $rnvpInsee
     * @return FranceRoutageProspectionTmp
     */
    public function setRnvpInsee($rnvpInsee)
    {
        $this->rnvpInsee = $rnvpInsee;

        return $this;
    }

    /**
     * Get rnvpInsee
     *
     * @return string 
     */
    public function getRnvpInsee()
    {
        return $this->rnvpInsee;
    }

    /**
     * Set adrOk
     *
     * @param integer $adrOk
     * @return FranceRoutageProspectionTmp
     */
    public function setAdrOk($adrOk)
    {
        $this->adrOk = $adrOk;

        return $this;
    }

    /**
     * Get adrOk
     *
     * @return integer 
     */
    public function getAdrOk()
    {
        return $this->adrOk;
    }

    /**
     * Set typeProbl
     *
     * @param string $typeProbl
     * @return FranceRoutageProspectionTmp
     */
    public function setTypeProbl($typeProbl)
    {
        $this->typeProbl = $typeProbl;

        return $this;
    }

    /**
     * Get typeProbl
     *
     * @return string 
     */
    public function getTypeProbl()
    {
        return $this->typeProbl;
    }

    /**
     * Set chgtAdr
     *
     * @param integer $chgtAdr
     * @return FranceRoutageProspectionTmp
     */
    public function setChgtAdr($chgtAdr)
    {
        $this->chgtAdr = $chgtAdr;

        return $this;
    }

    /**
     * Get chgtAdr
     *
     * @return integer 
     */
    public function getChgtAdr()
    {
        return $this->chgtAdr;
    }

    /**
     * Set livrable
     *
     * @param integer $livrable
     * @return FranceRoutageProspectionTmp
     */
    public function setLivrable($livrable)
    {
        $this->livrable = $livrable;

        return $this;
    }

    /**
     * Get livrable
     *
     * @return integer 
     */
    public function getLivrable()
    {
        return $this->livrable;
    }

    /**
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return FranceRoutageProspectionTmp
     */
    public function setSocCodeExt($socCodeExt)
    {
        $this->socCodeExt = $socCodeExt;

        return $this;
    }

    /**
     * Get socCodeExt
     *
     * @return string 
     */
    public function getSocCodeExt()
    {
        return $this->socCodeExt;
    }

    /**
     * Set prdCodeExt
     *
     * @param string $prdCodeExt
     * @return FranceRoutageProspectionTmp
     */
    public function setPrdCodeExt($prdCodeExt)
    {
        $this->prdCodeExt = $prdCodeExt;

        return $this;
    }

    /**
     * Get prdCodeExt
     *
     * @return string 
     */
    public function getPrdCodeExt()
    {
        return $this->prdCodeExt;
    }

    /**
     * Set sprCodeExt
     *
     * @param string $sprCodeExt
     * @return FranceRoutageProspectionTmp
     */
    public function setSprCodeExt($sprCodeExt)
    {
        $this->sprCodeExt = $sprCodeExt;

        return $this;
    }

    /**
     * Get sprCodeExt
     *
     * @return string 
     */
    public function getSprCodeExt()
    {
        return $this->sprCodeExt;
    }

    /**
     * Set frProspectionListe
     *
     * @param \Ams\DistributionBundle\Entity\FranceRoutageProspectionListe $frProspectionListe
     * @return FranceRoutageProspectionTmp
     */
    public function setFrProspectionListe(\Ams\DistributionBundle\Entity\FranceRoutageProspectionListe $frProspectionListe = null)
    {
        $this->frProspectionListe = $frProspectionListe;

        return $this;
    }

    /**
     * Get frProspectionListe
     *
     * @return \Ams\DistributionBundle\Entity\FranceRoutageProspectionListe 
     */
    public function getFrProspectionListe()
    {
        return $this->frProspectionListe;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return FranceRoutageProspectionTmp
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune = null)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return \Ams\AdresseBundle\Entity\Commune 
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return FranceRoutageProspectionTmp
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot = null)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return \Ams\SilogBundle\Entity\Depot 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return FranceRoutageProspectionTmp
     */
    public function setJour(\Ams\ReferentielBundle\Entity\RefJour $jour = null)
    {
        $this->jour = $jour;

        return $this;
    }

    /**
     * Get jour
     *
     * @return \Ams\ReferentielBundle\Entity\RefJour 
     */
    public function getJour()
    {
        return $this->jour;
    }

    /**
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return FranceRoutageProspectionTmp
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux = null)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set tourneeJour
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneeJour
     * @return FranceRoutageProspectionTmp
     */
    public function setTourneeJour(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneeJour = null)
    {
        $this->tourneeJour = $tourneeJour;

        return $this;
    }

    /**
     * Get tourneeJour
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getTourneeJour()
    {
        return $this->tourneeJour;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return FranceRoutageProspectionTmp
     */
    public function setSociete(\Ams\ProduitBundle\Entity\Societe $societe = null)
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return \Ams\ProduitBundle\Entity\Societe 
     */
    public function getSociete()
    {
        return $this->societe;
    }
}
