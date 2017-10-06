<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * ClientAServirSrcTmp2
 *
 * @ORM\Table(name="client_a_servir_src_tmp2", indexes={
 *                                                      @index(name="idx_cas_tmp2_numabo_ext", columns={"numabo_ext"})
 *                                                      , @index(name="adresse_idx", columns={"vol3", "vol4", "vol5", "cp", "ville"})
 *                                                      , @index(name="rnvp_ancien_idx", columns={"vol4_anc", "insee_anc"})
 *                                                      , @index(name="chgt_adr_idx", columns={"chgt_adr"})
 *                                                      }
 *          )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ClientAServirSrcTmp2Repository")
 */
class ClientAServirSrcTmp2
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
     * @ORM\Column(name="numabo_ext", type="string", length=50, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=100, nullable=true)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=100, nullable=true)
     */
    private $vol2;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=100, nullable=true)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=100, nullable=true)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=100, nullable=true)
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
     * @ORM\Column(name="vol4_anc", type="string", length=38, nullable=true)
     */
    private $vol4Anc;

    /**
     * @var string
     *
     * @ORM\Column(name="insee_anc", type="string", length=5, nullable=true)
     */
    private $inseeAnc;
    
    /**
     * Si 0 : pas de changement d adresse
     * Si 1 : il y a changement d adresse... mais le volet 4 et le INSEE n ont pas change
     * Si 2 : il y a changement d adresse (changement des volet 4 et INSEE ou RNVP non connu auparavant)
     * @var integer
     *
     * @ORM\Column(name="chgt_adr", type="integer", nullable=true)
     */
    private $chgtAdr;

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
     * @var \Ams\AbonneBundle\Entity\AbonneUnique
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneUnique")
     * @ORM\JoinColumn(name="abonne_unique_id", referencedColumnName="id", nullable=true)
     */
    private $abonneUnique;

    /**
     * @var \Adresse
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Adresse")
     * @ORM\JoinColumn(name="adresse_id", referencedColumnName="id", nullable=true)
     */
    private $adresse;

    /**
     * @var \AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="rnvp_id", referencedColumnName="id", nullable=true)
     */
    private $rnvp;
    
    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;
    
    /**
     * Ordre du point de livraison
     * @var integer
     *
     * @ORM\Column(name="point_livraison_ordre", type="integer", nullable=true)
     */
    private $pointLivraisonOrdre;
    
    /**
     * Ordre de livraison d'un abonne par (au sein d'un) point de livraison
     * @var integer
     *
     * @ORM\Column(name="ordre_dans_arret", type="integer", nullable=true)
     */
    private $ordreDansArret;

    /**
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="modele_tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $modeleTourneeJour;

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
     * @var integer
     *
     * @ORM\Column(name="info_traitee", type="integer", nullable=true)
     */
    private $infoTraitee;

    
    
    
    



    

    

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
     * @return ClientAServirSrcTmp2
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
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return ClientAServirSrcTmp2
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
     * Set vol1
     *
     * @param string $vol1
     * @return ClientAServirSrcTmp2
     */
    public function setVol1($vol1)
    {
        $this->vol1 = $vol1;
    
        return $this;
    }

    /**
     * Get vol1
     *
     * @return string 
     */
    public function getVol1()
    {
        return $this->vol1;
    }

    /**
     * Set vol2
     *
     * @param string $vol2
     * @return ClientAServirSrcTmp2
     */
    public function setVol2($vol2)
    {
        $this->vol2 = $vol2;
    
        return $this;
    }

    /**
     * Get vol2
     *
     * @return string 
     */
    public function getVol2()
    {
        return $this->vol2;
    }

    /**
     * Set vol3
     *
     * @param string $vol3
     * @return ClientAServirSrcTmp2
     */
    public function setVol3($vol3)
    {
        $this->vol3 = $vol3;
    
        return $this;
    }

    /**
     * Get vol3
     *
     * @return string 
     */
    public function getVol3()
    {
        return $this->vol3;
    }

    /**
     * Set vol4
     *
     * @param string $vol4
     * @return ClientAServirSrcTmp2
     */
    public function setVol4($vol4)
    {
        $this->vol4 = $vol4;
    
        return $this;
    }

    /**
     * Get vol4
     *
     * @return string 
     */
    public function getVol4()
    {
        return $this->vol4;
    }

    /**
     * Set vol5
     *
     * @param string $vol5
     * @return ClientAServirSrcTmp2
     */
    public function setVol5($vol5)
    {
        $this->vol5 = $vol5;
    
        return $this;
    }

    /**
     * Get vol5
     *
     * @return string 
     */
    public function getVol5()
    {
        return $this->vol5;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return ClientAServirSrcTmp2
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    
        return $this;
    }

    /**
     * Get cp
     *
     * @return string 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return ClientAServirSrcTmp2
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    
        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set vol4Anc
     *
     * @param string $vol4Anc
     * @return ClientAServirSrcTmp2
     */
    public function setVol4Anc($vol4Anc)
    {
        $this->vol4Anc = $vol4Anc;
    
        return $this;
    }

    /**
     * Get vol4Anc
     *
     * @return string 
     */
    public function getVol4Anc()
    {
        return $this->vol4Anc;
    }

    /**
     * Set inseeAnc
     *
     * @param string $inseeAnc
     * @return ClientAServirSrcTmp2
     */
    public function setInseeAnc($inseeAnc)
    {
        $this->inseeAnc = $inseeAnc;
    
        return $this;
    }

    /**
     * Get inseeAnc
     *
     * @return string 
     */
    public function getInseeAnc()
    {
        return $this->inseeAnc;
    }

    /**
     * Set chgtAdr
     *
     * @param integer $chgtAdr
     * @return ClientAServirSrcTmp2
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
     * Set divers1
     *
     * @param string $divers1
     * @return ClientAServirSrcTmp2
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
     * @return ClientAServirSrcTmp2
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
     * @return ClientAServirSrcTmp2
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
     * @return ClientAServirSrcTmp2
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
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return ClientAServirSrcTmp2
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
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return ClientAServirSrcTmp2
     */
    public function setAbonneSoc(\Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc = null)
    {
        $this->abonneSoc = $abonneSoc;
    
        return $this;
    }

    /**
     * Get abonneSoc
     *
     * @return \Ams\AbonneBundle\Entity\AbonneSoc 
     */
    public function getAbonneSoc()
    {
        return $this->abonneSoc;
    }

    /**
     * Set abonneUnique
     *
     * @param \Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique
     * @return AbonneSoc
     */
    public function setAbonneUnique(\Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique)
    {
        $this->abonneUnique = $abonneUnique;
    
        return $this;
    }

    /**
     * Get abonneUnique
     *
     * @return \Ams\AbonneBundle\Entity\AbonneUnique 
     */
    public function getAbonneUnique()
    {
        return $this->abonneUnique;
    }

    /**
     * Set adresse
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adresse
     * @return ClientAServirSrcTmp2
     */
    public function setAdresse(\Ams\AdresseBundle\Entity\Adresse $adresse = null)
    {
        $this->adresse = $adresse;
    
        return $this;
    }

    /**
     * Get adresse
     *
     * @return \Ams\AdresseBundle\Entity\Adresse 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set rnvp
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $rnvp
     * @return ClientAServirSrcTmp2
     */
    public function setRnvp(\Ams\AdresseBundle\Entity\AdresseRnvp $rnvp = null)
    {
        $this->rnvp = $rnvp;
    
        return $this;
    }

    /**
     * Get rnvp
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getRnvp()
    {
        return $this->rnvp;
    }

    /**
     * Set pointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return Adresse
     */
    public function setPointLivraison(\Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison = null)
    {
        $this->pointLivraison = $pointLivraison;
    
        return $this;
    }

    /**
     * Get pointLivraison
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }

    /**
     * Set pointLivraisonOrdre
     *
     * @param integer $pointLivraisonOrdre
     * @return ClientAServirSrcTmp2
     */
    public function setPointLivraisonOrdre($pointLivraisonOrdre)
    {
        $this->pointLivraisonOrdre = $pointLivraisonOrdre;

        return $this;
    }

    /**
     * Get pointLivraisonOrdre
     *
     * @return integer 
     */
    public function getPointLivraisonOrdre()
    {
        return $this->pointLivraisonOrdre;
    }

    /**
     * Set ordreDansArret
     *
     * @param integer $ordreDansArret
     * @return ClientAServirSrcTmp2
     */
    public function setOrdreDansArret($ordreDansArret)
    {
        $this->ordreDansArret = $ordreDansArret;

        return $this;
    }

    /**
     * Get ordreDansArret
     *
     * @return integer 
     */
    public function getOrdreDansArret()
    {
        return $this->ordreDansArret;
    }

    /**
     * Set modeleTourneeJour
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $modeleTourneeJour
     * @return ModeleTourneeJour
     */
    public function setModeleTourneeJour(\Ams\ModeleBundle\Entity\ModeleTourneeJour $modeleTourneeJour = null)
    {
        $this->modeleTourneeJour = $modeleTourneeJour;
    
        return $this;
    }

    /**
     * Get modeleTourneeJour
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getModeleTourneeJour()
    {
        return $this->modeleTourneeJour;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return ClientAServirSrcTmp2
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
     * @return ClientAServirSrcTmp2
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
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return ClientAServirSrcTmp2
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
     * Set infoTraitee
     *
     * @param integer $infoTraitee
     * @return ClientAServirSrcTmp2
     */
    public function setInfoTraitee($infoTraitee)
    {
        $this->infoTraitee = $infoTraitee;
    
        return $this;
    }

    /**
     * Get infoTraitee
     *
     * @return integer 
     */
    public function getInfoTraitee()
    {
        return $this->infoTraitee;
    }
}
