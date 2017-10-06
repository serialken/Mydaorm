<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Details des Reperages
 *
 * @ORM\Table(name="reperage_tmp", indexes={@Index(name="idx_abo_ext", columns={"numabo_ext", "soc_code_ext"})
 *                                                      , @Index(name="idx_dat", columns={"date_demar"})
 *                                                      , @Index(name="idx_rep_id_ext", columns={"rep_id_ext"})
 *                                                      }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ReperageTmpRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ReperageTmp
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
     * ID de la demande dans JADE
     * @var string
     *
     * @ORM\Column(name="rep_id_ext", type="string", length=15, nullable=true)
     */
    private $repIdExt;

    /**
     * Date de demarrage
     * @var \DateTime
     *
     * @ORM\Column(name="date_demar", type="date", nullable=false)
     */
    private $dateDemar;

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
     * @var \Ams\ModeleBundle\Entity\ModeleTournee
     * 
   	 * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\ModeleTournee")
     */
    private $tournee;

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
     * @ORM\Column(name="topage", type="string", length=1, nullable=true)
     */
    private $topage;

    /**
     * 
     * @var \Ams\ReferentielBundle\Entity\RefReperageQualif
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefReperageQualif")
     * @ORM\JoinColumn(name="qualif_id", referencedColumnName="id", nullable=true)
     */
    private $qualif;

    /**
     * Commentaire concernant la reponse
     * @var string
     *
     * @ORM\Column(name="cmt_reponse", type="string", length=255, nullable=true)
     */
    private $cmtReponse;

    /**
     * Date de reponse
     * @var \DateTime
     *
     * @ORM\Column(name="date_reponse", type="datetime", nullable=true)
     */
    private $dateReponse;

    /**
     * Utilisateur qui repond au reperage
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_reponse_id", referencedColumnName="id", nullable=true)
     */
    private $utlReponse;

    /**
     * 
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap1_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap1;

    /**
     * Le dernier fichier qui a ecrase l'ancienne info
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_n_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecapN;

    /**
     * Date export (vers Jade par exemple)
     * @var \DateTime
     *
     * @ORM\Column(name="date_export", type="datetime", nullable=true)
     */
    private $dateExport;

    /**
     * N : nouveau client => insertion nouveau reperage
     * 0R (zero reponse) : client connu, reponse pas encore exportee => mise a jour reperage
     * C0 : client connu, Reponse exportee & pas de changement d'adresse => mise a jour date demarrage si necessaire
     * C1 : client connu, Reponse exportee & adresse differente => insertion nouveau reperage
     * @var string
     *
     * @ORM\Column(name="type_reperage", type="string", length=2, nullable=true)
     */
    private $typeReperage;
    
    /**
     * @var integer
     * 0 => produit pas encore mis a jour selon la repartition depot-commune(-societe)
     * 1 => produit deja a jour selon la repartition depot-commune(-societe)
     * @ORM\Column(name="produit_maj", type="smallint", nullable=true,options={"default":0})
     */
    private $produitMaj;

    /**
     * ID reperage a mettre a jour
     * @var \Ams\DistributionBundle\Entity\Reperage
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\Reperage")
     * @ORM\JoinColumn(name="id_reperage", referencedColumnName="id", nullable=true)
     */
    private $idReperage;
    
    
    
    
    
    



    

    

    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
    	$this->id = $id;
    
    	return $this;
    }

    /**
     * Set dateDemar
     *
     * @param \DateTime $dateDemar
     * @return Reperage
     */
    public function setDateDemar($dateDemar)
    {
        $this->dateDemar = $dateDemar;

        return $this;
    }

    /**
     * Get dateDemar
     *
     * @return \DateTime 
     */
    public function getDateDemar()
    {
        return $this->dateDemar;
    }

    /**
     * Set numParution
     *
     * @param string $numParution
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * Set typePortage
     *
     * @param string $typePortage
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * Set topage
     *
     * @param string $topage
     * @return Reperage
     */
    public function setTopage($topage)
    {
        $this->topage = $topage;

        return $this;
    }

    /**
     * Get topage
     *
     * @return string 
     */
    public function getTopage()
    {
        return $this->topage;
    }

    /**
     * Set cmtReponse
     *
     * @param string $cmtReponse
     * @return Reperage
     */
    public function setCmtReponse($cmtReponse)
    {
        $this->cmtReponse = $cmtReponse;

        return $this;
    }

    /**
     * Get cmtReponse
     *
     * @return string 
     */
    public function getCmtReponse()
    {
        return $this->cmtReponse;
    }

    /**
     * Set dateReponse
     *
     * @param \DateTime $dateReponse
     * @return Reperage
     */
    public function setDateReponse($dateReponse)
    {
        $this->dateReponse = $dateReponse;

        return $this;
    }

    /**
     * Get dateReponse
     *
     * @return \DateTime 
     */
    public function getDateReponse()
    {
        return $this->dateReponse;
    }

    /**
     * Set utlReponse
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlReponse
     * @return Reperage
     */
    public function setUtlReponse(\Ams\SilogBundle\Entity\Utilisateur $utlReponse = null)
    {
        $this->utlReponse = $utlReponse;

        return $this;
    }

    /**
     * Get utlReponse
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtlReponse()
    {
        return $this->utlReponse;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return Reperage
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
     * @return Reperage
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
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return Reperage
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
     * Set adresse
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adresse
     * @return Reperage
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
     * @return Reperage
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
     * @return Reperage
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
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return Reperage
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
     * @return Reperage
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

    /**
     * Set qualif
     *
     * @param \Ams\ReferentielBundle\Entity\RefReperageQualif $qualif
     * @return Reperage
     */
    public function setQualif(\Ams\ReferentielBundle\Entity\RefReperageQualif $qualif = null)
    {
        $this->qualif = $qualif;

        return $this;
    }

    /**
     * Get qualif
     *
     * @return \Ams\ReferentielBundle\Entity\RefReperageQualif 
     */
    public function getQualif()
    {
        return $this->qualif;
    }

    /**
     * Set ficRecap1
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap1
     * @return Reperage
     */
    public function setFicRecap1(\Ams\FichierBundle\Entity\FicRecap $ficRecap1 = null)
    {
        $this->ficRecap1 = $ficRecap1;

        return $this;
    }

    /**
     * Get ficRecap1
     *
     * @return \Ams\FichierBundle\Entity\FicRecap 
     */
    public function getFicRecap1()
    {
        return $this->ficRecap1;
    }

    /**
     * Set ficRecapN
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecapN
     * @return Reperage
     */
    public function setFicRecapN(\Ams\FichierBundle\Entity\FicRecap $ficRecapN = null)
    {
        $this->ficRecapN = $ficRecapN;

        return $this;
    }

    /**
     * Get ficRecapN
     *
     * @return \Ams\FichierBundle\Entity\FicRecap 
     */
    public function getFicRecapN()
    {
        return $this->ficRecapN;
    }

    /**
     * Set dateExport
     *
     * @param \DateTime $dateExport
     * @return CrmDetail
     */
    public function setDateExport($dateExport)
    {
        $this->dateExport = $dateExport;

        return $this;
    }

    /**
     * Get dateExport
     *
     * @return \DateTime 
     */
    public function getDateExport()
    {
        return $this->dateExport;
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpdate(){
    	  $this->setDateReponse(new \DateTime);
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTournee $tournee
     * @return Reperage
     */
    public function setTournee(\Ams\ModeleBundle\Entity\ModeleTournee $tournee = null)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get tournee
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTournee 
     */
    public function getTournee()
    {
        return $this->tournee;
    }

    /**
     * Set repIdExt
     *
     * @param string $repIdExt
     * @return Reperage
     */
    public function setRepIdExt($repIdExt)
    {
        $this->repIdExt = $repIdExt;

        return $this;
    }

    /**
     * Get repIdExt
     *
     * @return string 
     */
    public function getRepIdExt()
    {
        return $this->repIdExt;
    }

    /**
     * Set typeReperage
     *
     * @param string $typeReperage
     * @return Reperage
     */
    public function setTypeReperage($typeReperage)
    {
        $this->typeReperage = $typeReperage;

        return $this;
    }

    /**
     * Get typeReperage
     *
     * @return string 
     */
    public function getTypeReperage()
    {
        return $this->typeReperage;
    }
}
