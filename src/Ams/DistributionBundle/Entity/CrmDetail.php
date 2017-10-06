<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * CRM - Detail des demandes ou des remontees d'info
 *
 * @ORM\Table(name="crm_detail", indexes={@Index(name="idx_date_export", columns={"date_export"})
 *                                              , @Index(name="idx_numaboext_soc", columns={"numabo_ext","soc_code_ext"})
 *                                              , @Index(name="idx_crm_ext", columns={"soc_code_ext","crm_id_editeur"})
 *                                                      }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\CrmDetailRepository")
 */
class CrmDetail
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
     * @ORM\Column(name="crm_id_ext", type="string", length=15, nullable=true)
     */
    private $crmIdExt;

    /**
     * ID de la demande chez l'editeur
     * @var string
     *
     * @ORM\Column(name="crm_id_editeur", type="string", length=38, nullable=true)
     */
    private $crmIdEditeur;

    /**
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creat", type="datetime", nullable=false)
     */
    private $dateCreat;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=20, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=100, nullable=false)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=100, nullable=false)
     */
    private $vol2;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=100, nullable=false)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=100, nullable=false)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=100, nullable=false)
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
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    private $socCodeExt;

    /**
     * Code demande
     * @var string
     *
     * @ORM\Column(name="code_demande", type="string", length=10, nullable=false)
     */
    private $codeDemande;
    
    /**
     * @var \Ams\DistributionBundle\Entity\CrmDemande
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmDemande")
     * @ORM\JoinColumn(name="crm_demande_id", referencedColumnName="id", nullable=true)
     **/
    private $crmDemande;

    /**
     * Commentaire de demande ou de remontee d'info
     * @var string
     *
     * @ORM\Column(name="cmt_demande", type="string", length=255, nullable=true)
     */
    private $cmtDemande;

    /**
     * Utilisateur qui a fait la saisie
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_saisie_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private $utlSaisie;

    /**
     * Date d'integration ou de saisie
     * @var \DateTime
     *
     * @ORM\Column(name="date_saisie_modif", type="datetime", nullable=true)
     */
    private $dateSaisieModif;
    
    /**
     * Origine (Import:0 | Application:1)
     * @var integer
     *
     * @ORM\Column(name="origine", type="integer", nullable=true)
     */
    private $origine;
    
    /**
     * Date validation - Seules les Reclam|Rem infos validees qui seront envoyees a l'editeur
     * @var \DateTime
     *
     * @ORM\Column(name="date_valid", type="datetime", nullable=true)
     */
    private $dateValidation;

    /**
     * Utilisateur qui repond a la demande
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_valid_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private $utlValidation;

    /**
     * Date de reponse
     * @var \DateTime
     *
     * @ORM\Column(name="date_reponse", type="datetime", nullable=true)
     */
    private $dateReponse;
    
    /**
     * @var \Ams\DistributionBundle\Entity\CrmReponse
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmReponse")
     * @ORM\JoinColumn(name="crm_reponse_id", referencedColumnName="id", nullable=true)
     **/
    private $crmReponse;

    /**
     * Commentaire concernant la reponse
     * @var string
     *
     * @ORM\Column(name="cmt_reponse", type="string", length=255, nullable=true)
     */
    private $cmtReponse;

    /**
     * Utilisateur qui repond a la demande
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_reponse_id", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private $utlReponse;
    
    /**
     * Annulation (...:0 | Annule:1)
     * @var integer
     *
     * @ORM\Column(name="annule", type="integer", nullable=true)
     */
    private $annule;

    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     */
    private $depot;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;
    
    /**
     * @var \Ams\AbonneBundle\Entity\AbonneSoc
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
     * @ORM\Column(name="client_type", type="integer", nullable=false)
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
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="rnvp_id", referencedColumnName="id", nullable=true)
     */
    private $rnvp;
    
    /**
     * paiTournee
     * @var \Ams\PaieBundle\Entity\PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="\Ams\PaieBundle\Entity\PaiTournee")
     * @ORM\JoinColumn(name="pai_tournee_id", referencedColumnName="id", nullable=true)
     */
    private $tournee;

    /**
     * modeleTourneeJour
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="modele_tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tourneeJour;

    /**
     * Date export (vers Jade par exemple)
     * @var \DateTime
     *
     * @ORM\Column(name="date_export", type="datetime", nullable=true)
     */
    private $dateExport;
        
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_demande_arbitrage", type="datetime", nullable=true)
     */
    private $dateDemandeArbitrage;
    
    /**
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_demande_arbitrage", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private $utlDemandeArbitrage;
    
    /**
     * @var \Ams\DistributionBundle\Entity\ImputationService
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\DemandeArbitrage")
     * @ORM\JoinColumn(name="motif", referencedColumnName="id", nullable=true)
     */
    private $motif;
    
    /**
    *Imputaion porteur
     * @var boolean
     * @ORM\Column(name="ipp", type="boolean", nullable=true, options={"default" = 1})
     */
    private $ipp;
    
    /**
     * @var \Ams\DistributionBundle\Entity\ImputationService
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\ImputationService")
     * @ORM\JoinColumn(name="imputation", referencedColumnName="id", nullable=true)
     */
    private $imputation;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="date_reponse_arbitrage", type="datetime", nullable=true)
     */
    private $dateReponseArbitrage;
    
    /**
     * @var \Ams\SilogBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_reponse_arbitrage", referencedColumnName="id", nullable=true,onDelete="CASCADE")
     */
    private $utlReponseArbitrage;
    
    /**
     * @var string
     * @ORM\Column(name="cmt_reponse_arbitrage", type="string", length=255, nullable=true)
     */
    private $cmtReponseArbitrage;
    
     /**
     *date imputation paie
     * @var \DateTime
     *
     * @ORM\Column(name="date_imputation_paie", type="date", nullable=true)
     */
    private $dateImputationPaie;

      /**
      *imputation paie
     * @var boolean
     * @ORM\Column(name="imputation_paie", type="boolean", nullable=true, options={"default" = 1})
     */
    private $imputationPaie;

    /**
     * Commentaire imputation paie
     * @var string
     *
     * @ORM\Column(name="cmt_imputation_paie", type="string", length=255, nullable=true)
     */
    private $cmtImputationPaie;


    
    
    /**
     *date_extrait qui sera renseignée lorsque la réclamation sera prise en compte paie
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="date", nullable=true)
     */
    private $dateExtrait;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Par defaut, on considere que si on passe par l'instanciation de classe, l'"origine" est "1" c-a-d "saisie manuelle"
        $this->origine  = 1;
       
        $this->annule   = 0;
    }
    


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
     * Set crmIdExt
     *
     * @param string $crmIdExt
     * @return CrmDetail
     */
    public function setCrmIdExt($crmIdExt)
    {
        $this->crmIdExt = $crmIdExt;

        return $this;
    }

    /**
     * Get crmIdExt
     *
     * @return string 
     */
    public function getCrmIdExt()
    {
        return $this->crmIdExt;
    }

    /**
     * Set crmIdEditeur
     *
     * @param string $crmIdEditeur
     * @return CrmDetail
     */
    public function setCrmIdEditeur($crmIdEditeur)
    {
        $this->crmIdEditeur = $crmIdEditeur;

        return $this;
    }

    /**
     * Get crmIdEditeur
     *
     * @return string 
     */
    public function getCrmIdEditeur()
    {
        return $this->crmIdEditeur;
    }
    
    
    

    /**
     * Set ficRecap
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap
     * @return ClientAServirLogist
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
     * Set dateCreat
     *
     * @param \DateTime $dateCreat
     * @return CrmDetail
     */
    public function setDateCreat($dateCreat)
    {
        $this->dateCreat = $dateCreat;

        return $this;
    }

    /**
     * Get dateCreat
     *
     * @return \DateTime 
     */
    public function getDateCreat()
    {
        return $this->dateCreat;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return CrmDetail
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return CrmDetail
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * Set codeDemande
     *
     * @param string $codeDemande
     * @return CrmDetail
     */
    public function setCodeDemande($codeDemande)
    {
        $this->codeDemande = $codeDemande;

        return $this;
    }

    /**
     * Get codeDemande
     *
     * @return string 
     */
    public function getCodeDemande()
    {
        return $this->codeDemande;
    }

    /**
     * Set cmtDemande
     *
     * @param string $cmtDemande
     * @return CrmDetail
     */
    public function setCmtDemande($cmtDemande)
    {
        $this->cmtDemande = $cmtDemande;

        return $this;
    }

    /**
     * Get cmtDemande
     *
     * @return string 
     */
    public function getCmtDemande()
    {
        return $this->cmtDemande;
    }

    /**
     * Set dateSaisieModif
     *
     * @param \DateTime $dateSaisieModif
     * @return CrmDetail
     */
    public function setDateSaisieModif($dateSaisieModif)
    {
        $this->dateSaisieModif = $dateSaisieModif;

        return $this;
    }

    /**
     * Get dateSaisieModif
     *
     * @return \DateTime 
     */
    public function getDateSaisieModif()
    {
        return $this->dateSaisieModif;
    }

    /**
     * Set origine
     *
     * @param integer $origine
     * @return CrmDetail
     */
    public function setOrigine($origine)
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return integer 
     */
    public function getOrigine()
    {
        return $this->origine;
    }

    /**
     * Set dateValidation
     *
     * @param \DateTime $dateValidation
     * @return CrmDetail
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation
     *
     * @return \DateTime 
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set dateReponse
     *
     * @param \DateTime $dateReponse
     * @return CrmDetail
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
     * Set cmtReponse
     *
     * @param string $cmtReponse
     * @return CrmDetail
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
     * Set annule
     *
     * @param integer $annule
     * @return CrmDetail
     */
    public function setAnnule($annule)
    {
        $this->annule = $annule;

        return $this;
    }

    /**
     * Get annule
     *
     * @return integer 
     */
    public function getAnnule()
    {
        return $this->annule;
    }

    /**
     * Set clientType
     *
     * @param integer $clientType
     * @return CrmDetail
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
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return CrmDetail
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
     * Set crmDemande
     *
     * @param \Ams\DistributionBundle\Entity\CrmDemande $crmDemande
     * @return CrmDetail
     */
    public function setCrmDemande(\Ams\DistributionBundle\Entity\CrmDemande $crmDemande = null)
    {
        $this->crmDemande = $crmDemande;

        return $this;
    }

    /**
     * Get crmDemande
     *
     * @return \Ams\DistributionBundle\Entity\CrmDemande 
     */
    public function getCrmDemande()
    {
        return $this->crmDemande;
    }

    /**
     * Set utlSaisie
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlSaisie
     * @return CrmDetail
     */
    public function setUtlSaisie(\Ams\SilogBundle\Entity\Utilisateur $utlSaisie = null)
    {
        $this->utlSaisie = $utlSaisie;

        return $this;
    }

    /**
     * Get utlSaisie
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtlSaisie()
    {
        return $this->utlSaisie;
    }

    /**
     * Set utlValidation
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlValidation
     * @return CrmDetail
     */
    public function setUtlValidation(\Ams\SilogBundle\Entity\Utilisateur $utlValidation = null)
    {
        $this->utlValidation = $utlValidation;

        return $this;
    }

    /**
     * Get utlValidation
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtlValidation()
    {
        return $this->utlValidation;
    }

    /*
     * Set crmReponse
     *
     * @param \Ams\DistributionBundle\Entity\CrmReponse $crmReponse
     * @return CrmDetail
     */
    public function setCrmReponse(\Ams\DistributionBundle\Entity\CrmReponse $crmReponse = null)
    {
        $this->crmReponse = $crmReponse;

        return $this;
    }

    /**
     * Get crmReponse
     *
     * @return \Ams\DistributionBundle\Entity\CrmReponse 
     */
    public function getCrmReponse()
    {
        return $this->crmReponse;
    }

    /**
     * Set utlReponse
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlReponse
     * @return CrmDetail
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
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return CrmDetail
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
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return CrmDetail
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
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return CrmDetail
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
     * @return CrmDetail
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
     * @return CrmDetail
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
     * Set tournee
     *
     * @param \Ams\PaieBundle\Entity\PaiTournee $tournee
     * @return CrmDetail
     */
    public function setTournee(\Ams\PaieBundle\Entity\PaiTournee $tournee = null)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get PaiTournee
     *
     * @return \Ams\PaieBundle\Entity\PaiTournee 
     */
    public function getTournee()
    {
        return $this->tournee;
    }

    /**
     * Set dateDemandeArbitrage
     *
     * @param \DateTime $dateDemandeArbitrage
     * @return CrmDetail
     */
    public function setDateDemandeArbitrage($dateDemandeArbitrage)
    {
        $this->dateDemandeArbitrage = $dateDemandeArbitrage;

        return $this;
    }

    /**
     * Get dateDemandeArbitrage
     *
     * @return \DateTime 
     */
    public function getDateDemandeArbitrage()
    {
        return $this->dateDemandeArbitrage;
    }

    /**
     * Set ipp
     *
     * @param boolean $ipp
     * @return CrmDetail
     */
    public function setIpp($ipp)
    {
        $this->ipp = $ipp;

        return $this;
    }

    /**
     * Get ipp
     *
     * @return boolean 
     */
    public function getIpp()
    {
        return $this->ipp;
    }

    /**
     * Set dateReponseArbitrage
     *
     * @param \DateTime $dateReponseArbitrage
     * @return CrmDetail
     */
    public function setDateReponseArbitrage($dateReponseArbitrage)
    {
        $this->dateReponseArbitrage = $dateReponseArbitrage;

        return $this;
    }

    /**
     * Get dateReponseArbitrage
     *
     * @return \DateTime 
     */
    public function getDateReponseArbitrage()
    {
        return $this->dateReponseArbitrage;
    }

    /**
     * Set cmtReponseArbitrage
     *
     * @param string $cmtReponseArbitrage
     * @return CrmDetail
     */
    public function setCmtReponseArbitrage($cmtReponseArbitrage)
    {
        $this->cmtReponseArbitrage = $cmtReponseArbitrage;

        return $this;
    }

    /**
     * Get cmtReponseArbitrage
     *
     * @return string 
     */
    public function getCmtReponseArbitrage()
    {
        return $this->cmtReponseArbitrage;
    }

    /**
     * Set utlDemandeArbitrage
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlDemandeArbitrage
     * @return CrmDetail
     */
    public function setUtlDemandeArbitrage(\Ams\SilogBundle\Entity\Utilisateur $utlDemandeArbitrage = null)
    {
        $this->utlDemandeArbitrage = $utlDemandeArbitrage;

        return $this;
    }

    /**
     * Get utlDemandeArbitrage
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtlDemandeArbitrage()
    {
        return $this->utlDemandeArbitrage;
    }

    /**
     * Set motif
     *
     * @param \Ams\DistributionBundle\Entity\DemandeArbitrage $motif
     * @return CrmDetail
     */
    public function setMotif(\Ams\DistributionBundle\Entity\DemandeArbitrage $motif = null)
    {
        $this->motif = $motif;

        return $this;
    }

    /**
     * Get motif
     *
     * @return \Ams\DistributionBundle\Entity\DemandeArbitrage 
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set imputation
     *
     * @param \Ams\DistributionBundle\Entity\ImputationService $imputation
     * @return CrmDetail
     */
    public function setImputation(\Ams\DistributionBundle\Entity\ImputationService $imputation = null)
    {
        $this->imputation = $imputation;

        return $this;
    }

    /**
     * Get imputation
     *
     * @return \Ams\DistributionBundle\Entity\ImputationService 
     */
    public function getImputation()
    {
        return $this->imputation;
    }

    /**
     * Set utlReponseArbitrage
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utlReponseArbitrage
     * @return CrmDetail
     */
    public function setUtlReponseArbitrage(\Ams\SilogBundle\Entity\Utilisateur $utlReponseArbitrage = null)
    {
        $this->utlReponseArbitrage = $utlReponseArbitrage;

        return $this;
    }

    /**
     * Get utlReponseArbitrage
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtlReponseArbitrage()
    {
        return $this->utlReponseArbitrage;
    }

    /**
     * Set dateImputationPaie
     *
     * @param \DateTime $dateImputationPaie
     * @return CrmDetail
     */
    public function setDateImputationPaie($dateImputationPaie)
    {
        $this->dateImputationPaie = $dateImputationPaie;

        return $this;
    }

    /**
     * Get dateImputationPaie
     *
     * @return \DateTime 
     */
    public function getDateImputationPaie()
    {
        return $this->dateImputationPaie;
    }

    /**
     * Set imputationPaie
     *
     * @param boolean $imputationPaie
     * @return CrmDetail
     */
    public function setImputationPaie($imputationPaie)
    {
        $this->imputationPaie = $imputationPaie;

        return $this;
    }

    /**
     * Get imputationPaie
     *
     * @return boolean 
     */
    public function getImputationPaie()
    {
        return $this->imputationPaie;
    }

    /**
     * Set cmtImputationPaie
     *
     * @param string $cmtImputationPaie
     * @return CrmDetail
     */
    public function setCmtImputationPaie($cmtImputationPaie)
    {
        $this->cmtImputationPaie = $cmtImputationPaie;

        return $this;
    }

    /**
     * Get cmtImputationPaie
     *
     * @return string 
     */
    public function getCmtImputationPaie()
    {
        return $this->cmtImputationPaie;
    }
    
      /**
     * Set dateExtrait
     *
     * @param \DateTime $dateExtrait
     * @return CrmDetail
     */
    public function setDateExtrait($dateExtrait)
    {
        $this->dateExtrait = $dateExtrait;

        return $this;
    }

    /**
     * Get dateExtrait
     *
     * @return \DateTime 
     */
    public function getDateExtrait()
    {
        return $this->dateExtrait;
    }

   

    /**
     * Set tourneeJour
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneeJour
     * @return CrmDetail
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
}
