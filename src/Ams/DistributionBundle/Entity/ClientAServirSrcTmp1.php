<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as index;

/**
 * ClientAServirSrcTmp1
 *
 * @ORM\Table(name="client_a_servir_src_tmp1", indexes={@index(name="idx_cas_tmp_numabo_ext", columns={"numabo_ext"})})
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ClientAServirSrcTmp1Repository")
 */
class ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return ClientAServirSrcTmp1
     */
    public function setDateParution($dateParution)
    {
        $this->dateParution = $dateParution;
    
        return $this;
    }

    /**
     * Get dateParution
     *
     * @return \DateTime 
     */
    public function getDateParution()
    {
        return $this->dateParution;
    }

    /**
     * Set numParution
     *
     * @param string $numParution
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * Set typePortage
     *
     * @param string $typePortage
     * @return ClientAServirSrcTmp1
     */
    public function setTypePortage($typePortage)
    {
        $this->typePortage = $typePortage;
    
        return $this;
    }

    /**
     * Get typePortage
     *
     * @return string 
     */
    public function getTypePortage()
    {
        return $this->typePortage;
    }

    /**
     * Set qte
     *
     * @param integer $qte
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * Set ficRecap
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap
     * @return ClientAServirSrcTmp1
     */
    public function setFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap = null)
    {
        $this->ficRecap = $ficRecap;
    
        return $this;
    }

    /**
     * Get ficRecap
     *
     * @return \Ams\FichierBundle\Entity\FicRecap 
     */
    public function getFicRecap()
    {
        return $this->ficRecap;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return ClientAServirSrcTmp1
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
     * Set clientType
     *
     * @param integer $clientType
     * @return AbonneSoc
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirSrcTmp1
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
     * @return ClientAServirLogist
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
     * @return ClientAServirLogist
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
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return ClientAServirSrcTmp1
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

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return ClientAServirSrcTmp1
     */
    public function setProduit(\Ams\ProduitBundle\Entity\Produit $produit = null)
    {
        $this->produit = $produit;
    
        return $this;
    }

    /**
     * Get produit
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduit()
    {
        return $this->produit;
    }
}
