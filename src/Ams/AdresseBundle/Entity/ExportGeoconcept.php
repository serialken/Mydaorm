<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExportGeoconcept
 *
 * @ORM\Table(name="export_geoconcept")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\ExportGeoconceptRepository")
 */
class ExportGeoconcept
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="date")
     */
    private $dateParution;

    /**
     * @var string
     *
     * @ORM\Column(name="num_parution", type="string", length=20)
     */
    private $numParution;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer")
     */
    private $qte;

    /**
     * @var string
     *
     * @ORM\Column(name="volet1", type="string", length=50)
     */
    private $volet1;

    /**
     * @var string
     *
     * @ORM\Column(name="volet2", type="string", length=50)
     */
    private $volet2;
    
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=38, nullable=false)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=38, nullable=false)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=38, nullable=false)
     */
    private $vol5;

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
     * @ORM\Column(name="type_service", type="string", length=1)
     */
    private $typeService;

    /**
     * @var string
     *
     * @ORM\Column(name="type_client", type="string", length=1)
     */
    private $typeClient;

    /**
     * @var integer
     *
     * @ORM\Column(name="point_livraison_ordre", type="integer")
     */
    private $pointLivraisonOrdre;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre_dans_arret", type="integer")
     */
    private $ordreDansArret;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=10)
     */
    private $numaboExt;

    /**
     * ID de l'abonné dans la table abonne_soc
     * @var string
     *
     * @ORM\Column(name="abonne_soc_id", type="integer")
     */
    private $numaboId;

    /**
     * @var string
     *
     * @ORM\Column(name="pl_cards", type="string", length=100)
     */
    private $plCards;

    /**
     * @var string
     *
     * @ORM\Column(name="pl_adresse", type="string", length=255)
     */
    private $plAdresse;

    /**
     * @var string
     *
     * @ORM\Column(name="pl_lieudit", type="string", length=100)
     */
    private $plLieudit;

    /**
     * @var string
     *
     * @ORM\Column(name="pl_cp", type="string", length=5)
     */
    private $plCp;

    /**
     * @var string
     *
     * @ORM\Column(name="pl_ville", type="string", length=100)
     */
    private $plVille;

    /**
     * @var integer
     *
     * @ORM\Column(name="pl_geox", type="float")
     */
    private $plGeox;

    /**
     * @var integer
     *
     * @ORM\Column(name="pl_geoy", type="float")
     */
    private $plGeoy;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_cards", type="string", length=100)
     */
    private $rnvpCards;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_adresse", type="string", length=255)
     */
    private $rnvpAdresse;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_lieudit", type="string", length=100)
     */
    private $rnvpLieudit;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_cp", type="string", length=5)
     */
    private $rnvpCp;

    /**
     * @var string
     *
     * @ORM\Column(name="rnvp_ville", type="string", length=100)
     */
    private $rnvpVille;

    /**
     * @var integer
     *
     * @ORM\Column(name="rnvp_geox", type="float")
     */
    private $rnvpGeox;

    /**
     * @var integer
     *
     * @ORM\Column(name="rnvp_geoy", type="float")
     */
    private $rnvpGeoy;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="fusion_soc_id", type="integer", nullable=true)
     */
    private $fusionSocId;    
    
    /**
     * La valeur de cet attribut est un produit
     * @var \Ams\ProduitBundle\Entity\Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $oProduit;

    /**
     * @var string
     *
     * @ORM\Column(name="produit", type="string", length=100)
     */
    private $produit;

    /**
     * @var string
     *
     * @ORM\Column(name="societe", type="string", length=50)
     */
    private $societe;

    /**
     * @var string
     *
     * @ORM\Column(name="depot", type="string", length=25)
     */
    private $depot;

     /**
     * @var string
     *
     * @ORM\Column(name="code_tournee", type="string", length=20)
     */
    private $codeTournee;
    
    /**
     * @var time
     *
     * @ORM\Column(name="duree_livraison", type="time", length=20)
     */
    private $dureeLivraison;
    
    /**
     * La valeur de cet attribut est une requête d'export
     * @var \Ams\AdresseBundle\Entity\RequeteExport
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\RequeteExport", inversedBy="lignesExport")
     * @ORM\JoinColumn(name="requete_export_id", referencedColumnName="id", nullable=true)
     */
    private $requeteExport;
    
      /**
     * @var string
     *
     * @ORM\Column(name="requete", type="string", length=100)
     */
    private $requete;
    
    /**
     * La valeur de cet attribut est une adresse RNVP définie en tant que point de livraison
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;
    
    /**
     * @var \Ams\ReferentielBundle\Entity\RefFlux
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;

    /**
     * @var \Ams\ReferentielBundle\Entity\RefJour
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     */
    private $jour;
    
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
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * Set qte
     *
     * @param integer $qte
     * @return ExportGeoconcept
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
     * Set volet1
     *
     * @param string $volet1
     * @return ExportGeoconcept
     */
    public function setVolet1($volet1)
    {
        $this->volet1 = $volet1;

        return $this;
    }

    /**
     * Get volet1
     *
     * @return string 
     */
    public function getVolet1()
    {
        return $this->volet1;
    }

    /**
     * Set volet2
     *
     * @param string $volet2
     * @return ExportGeoconcept
     */
    public function setVolet2($volet2)
    {
        $this->volet2 = $volet2;

        return $this;
    }

    /**
     * Get volet2
     *
     * @return string 
     */
    public function getVolet2()
    {
        return $this->volet2;
    }

    /**
     * Set typeService
     *
     * @param string $typeService
     * @return ExportGeoconcept
     */
    public function setTypeService($typeService)
    {
        $this->typeService = $typeService;

        return $this;
    }

    /**
     * Get typeService
     *
     * @return string 
     */
    public function getTypeService()
    {
        return $this->typeService;
    }

    /**
     * Set typeClient
     *
     * @param string $typeClient
     * @return ExportGeoconcept
     */
    public function setTypeClient($typeClient)
    {
        $this->typeClient = $typeClient;

        return $this;
    }

    /**
     * Get typeClient
     *
     * @return string 
     */
    public function getTypeClient()
    {
        return $this->typeClient;
    }

    /**
     * Set pointLivraisonOrdre
     *
     * @param integer $pointLivraisonOrdre
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return ExportGeoconcept
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
     * Set numaboId
     *
     * @param int $numaboId
     * @return ExportGeoconcept
     */
    public function setNumaboId($numaboId)
    {
        $this->numaboId = $numaboId;

        return $this;
    }

    /**
     * Get numaboId
     *
     * @return int 
     */
    public function getNumaboId()
    {
        return $this->numaboId;
    }

    /**
     * Set plCards
     *
     * @param string $plCards
     * @return ExportGeoconcept
     */
    public function setPlCards($plCards)
    {
        $this->plCards = $plCards;

        return $this;
    }

    /**
     * Get plCards
     *
     * @return string 
     */
    public function getPlCards()
    {
        return $this->plCards;
    }

    /**
     * Set plAdresse
     *
     * @param string $plAdresse
     * @return ExportGeoconcept
     */
    public function setPlAdresse($plAdresse)
    {
        $this->plAdresse = $plAdresse;

        return $this;
    }

    /**
     * Get plAdresse
     *
     * @return string 
     */
    public function getPlAdresse()
    {
        return $this->plAdresse;
    }

    /**
     * Set plLieudit
     *
     * @param string $plLieudit
     * @return ExportGeoconcept
     */
    public function setPlLieudit($plLieudit)
    {
        $this->plLieudit = $plLieudit;

        return $this;
    }

    /**
     * Get plLieudit
     *
     * @return string 
     */
    public function getPlLieudit()
    {
        return $this->plLieudit;
    }

    /**
     * Set plCp
     *
     * @param string $plCp
     * @return ExportGeoconcept
     */
    public function setPlCp($plCp)
    {
        $this->plCp = $plCp;

        return $this;
    }

    /**
     * Get plCp
     *
     * @return string 
     */
    public function getPlCp()
    {
        return $this->plCp;
    }

    /**
     * Set plVille
     *
     * @param string $plVille
     * @return ExportGeoconcept
     */
    public function setPlVille($plVille)
    {
        $this->plVille = $plVille;

        return $this;
    }

    /**
     * Get plVille
     *
     * @return string 
     */
    public function getPlVille()
    {
        return $this->plVille;
    }

    /**
     * Set plGeox
     *
     * @param integer $plGeox
     * @return ExportGeoconcept
     */
    public function setPlGeox($plGeox)
    {
        $this->plGeox = $plGeox;

        return $this;
    }

    /**
     * Get plGeox
     *
     * @return integer 
     */
    public function getPlGeox()
    {
        return $this->plGeox;
    }

    /**
     * Set plGeoy
     *
     * @param integer $plGeoy
     * @return ExportGeoconcept
     */
    public function setPlGeoy($plGeoy)
    {
        $this->plGeoy = $plGeoy;

        return $this;
    }

    /**
     * Get plGeoy
     *
     * @return integer 
     */
    public function getPlGeoy()
    {
        return $this->plGeoy;
    }

    /**
     * Set rnvpCards
     *
     * @param string $rnvpCards
     * @return ExportGeoconcept
     */
    public function setRnvpCards($rnvpCards)
    {
        $this->rnvpCards = $rnvpCards;

        return $this;
    }

    /**
     * Get rnvpCards
     *
     * @return string 
     */
    public function getRnvpCards()
    {
        return $this->rnvpCards;
    }

    /**
     * Set rnvpAdresse
     *
     * @param string $rnvpAdresse
     * @return ExportGeoconcept
     */
    public function setRnvpAdresse($rnvpAdresse)
    {
        $this->rnvpAdresse = $rnvpAdresse;

        return $this;
    }

    /**
     * Get rnvpAdresse
     *
     * @return string 
     */
    public function getRnvpAdresse()
    {
        return $this->rnvpAdresse;
    }

    /**
     * Set rnvpLieudit
     *
     * @param string $rnvpLieudit
     * @return ExportGeoconcept
     */
    public function setRnvpLieudit($rnvpLieudit)
    {
        $this->rnvpLieudit = $rnvpLieudit;

        return $this;
    }

    /**
     * Get rnvpLieudit
     *
     * @return string 
     */
    public function getRnvpLieudit()
    {
        return $this->rnvpLieudit;
    }

    /**
     * Set rnvpCp
     *
     * @param string $rnvpCp
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * Set rnvpGeox
     *
     * @param integer $rnvpGeox
     * @return ExportGeoconcept
     */
    public function setRnvpGeox($rnvpGeox)
    {
        $this->rnvpGeox = $rnvpGeox;

        return $this;
    }

    /**
     * Get rnvpGeox
     *
     * @return integer 
     */
    public function getRnvpGeox()
    {
        return $this->rnvpGeox;
    }

    /**
     * Set rnvpGeoy
     *
     * @param integer $rnvpGeoy
     * @return ExportGeoconcept
     */
    public function setRnvpGeoy($rnvpGeoy)
    {
        $this->rnvpGeoy = $rnvpGeoy;

        return $this;
    }

    /**
     * Get rnvpGeoy
     *
     * @return integer 
     */
    public function getRnvpGeoy()
    {
        return $this->rnvpGeoy;
    }

    /**
     * Set produit
     *
     * @param string $produit
     * @return ExportGeoconcept
     */
    public function setProduit($produit)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return string 
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set societe
     *
     * @param string $societe
     * @return ExportGeoconcept
     */
    public function setSociete($societe)
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return string 
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set depot
     *
     * @param string $depot
     * @return ExportGeoconcept
     */
    public function setDepot($depot)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return string 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set codeTournee
     *
     * @param string $codeTournee
     * @return ExportGeoconcept
     */
    public function setCodeTournee($codeTournee)
    {
        $this->codeTournee = $codeTournee;

        return $this;
    }

    /**
     * Get codeTournee
     *
     * @return string 
     */
    public function getCodeTournee()
    {
        return $this->codeTournee;
    }

   

    /**
     * Set requete
     *
     * @param string $requete
     * @return ExportGeoconcept
     */
    public function setRequete($requete)
    {
        $this->requete = $requete;

        return $this;
    }

    /**
     * Get requete
     *
     * @return string 
     */
    public function getRequete()
    {
        return $this->requete;
    }

    /**
     * Set vol3
     *
     * @param string $vol3
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * @return ExportGeoconcept
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
     * Set dureeLivraison
     *
     * @param \DateTime $dureeLivraison
     * @return ExportGeoconcept
     */
    public function setDureeLivraison($dureeLivraison)
    {
        $this->dureeLivraison = $dureeLivraison;

        return $this;
    }

    /**
     * Get dureeLivraison
     *
     * @return \DateTime 
     */
    public function getDureeLivraison()
    {
        return $this->dureeLivraison;
    }
    
    /**
     * Set requeteExport
     *
     * @param \Ams\AdresseBundle\Entity\RequeteExport $requeteExport
     * @return ExportGeoconcept
     */
    public function setRequeteExport(\Ams\AdresseBundle\Entity\RequeteExport $requeteExport)
    {
        $this->requeteExport = $requeteExport;

        return $this;
    }

    /**
     * Get requeteExport
     *
     * @return \Ams\AdresseBundle\Entity\RequeteExport 
     */
    public function getRequeteExport()
    {
        return $this->requeteExport;
    }
    
    /**
     * Set PointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return ExportGeoconcept
     */
    public function setPointLivraison(\Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison)
    {
        $this->pointLivraison = $pointLivraison;

        return $this;
    }

    /**
     * Get PointLivraison
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }

    /**
     * Set oProduit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $oProduit
     * @return ExportGeoconcept
     */
    public function setOProduit(\Ams\ProduitBundle\Entity\Produit $oProduit = null)
    {
        $this->oProduit = $oProduit;

        return $this;
    }

    /**
     * Get oProduit
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getOProduit()
    {
        return $this->oProduit;
    }

    /**
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return ExportGeoconcept
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
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return ExportGeoconcept
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
     * Set fusionSocId
     *
     * @param integer $fusionSocId
     * @return ExportGeoconcept
     */
    public function setFusionSocId($fusionSocId)
    {
        $this->fusionSocId = $fusionSocId;

        return $this;
    }

    /**
     * Get fusionSocId
     *
     * @return integer 
     */
    public function getFusionSocId()
    {
        return $this->fusionSocId;
    }
}
