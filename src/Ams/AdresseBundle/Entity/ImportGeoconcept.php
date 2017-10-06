<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExportGeoconcept
 *
 * @ORM\Table(name="import_geoconcept")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\ImportGeoconceptRepository")
 */
class ImportGeoconcept
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
     * ID de l'abonné dans la table abonne_soc
     * @var string
     *
     * @ORM\Column(name="abonne_soc_id", type="integer")
     */
    private $numaboId;
    
    /**
     * La valeur de cet attribut est une adresse RNVP définie en tant que point de livraison
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;

   /**
     * @var integer
     *
     * @ORM\Column(name="point_livraison_ordre", type="integer",nullable=true)
     */
    private $pointLivraisonOrdre;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre_dans_arret", type="integer",nullable=true)
     */
    private $ordreDansArret;

     /**
     * @var string
     *
     * @ORM\Column(name="code_tournee", type="string", length=20,nullable=true)
     */
    private $codeTournee;
    
    /**
     * @var time
     *
     * @ORM\Column(name="duree_livraison", type="time", length=20,nullable=true)
     */
    private $dureeLivraison;
    
    /**
     * La valeur de cet attribut est une requête d'export
     * @var \Ams\AdresseBundle\Entity\RequeteExport
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\RequeteExport")
     * @ORM\JoinColumn(name="requete_export_id", referencedColumnName="id", nullable=true)
     */
    private $requeteExport;
    
      /**
     * @var string
     *
     * @ORM\Column(name="requete", type="string", length=100,nullable=true)
     */
    private $requete;
    
    /**
     * @var string
     *
     * @ORM\Column(name="depot", type="string", length=25,nullable=true)
     */
    private $depot;
    
    /**
     * @var datetime
     * La date à laquelle l'import a été fait
     * @ORM\Column(name="date_import", type="datetime", nullable=true)
     */
    private $dateImport;
    
    /**
     * @var datetime
     * La date à laquelle le retour d'optimisation a été faite
     * @ORM\Column(name="date_optim", type="datetime", nullable=true)
     */
    private $dateOptim;
    
    /**
     * @var datetime
     * La date à partir de laquelle appliquer l'optimisaton
     * @ORM\Column(name="date_application_optim", type="date", nullable=true)
     */
    private $dateApplicationOptim;
    
    /**
     * @var string
     * @ORM\Column(name="temps_conduite", type="string", length=25, nullable=true)
     */
    private $sDriveTime;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;
    
    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=50,nullable=true)
     */
    private $etat;
    
    /**
     * @var float
     *
     * @ORM\Column(name="distance_trajet", type="float", nullable=true)
     */
    private $distanceTrajet;
    
    /**
     * @var float
     *
     * @ORM\Column(name="trajet_cumule", type="float", nullable=true)
     */
    private $trajetCumule;
    
    /**
     * @var string
     * @ORM\Column(name="temps_tournee", type="string", length=25, nullable=true)
     */
    private $sTourneeTime;
    
    /**
     * @var string
     * @ORM\Column(name="temps_visite", type="string", length=25, nullable=true)
     */
    private $sVisitTime;
    
    /**
     * @var float
     *
     * @ORM\Column(name="trajet_total_tournee", type="float", nullable=true)
     */
    private $trajetTotalTournee;
    
    /**
     * La valeur de cet attribut est un produit
     * @var \Ams\ProduitBundle\Entity\Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $oProduit;
    
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
     * Booléen indiquant quand la ligne a été appliquée à la distribution
     * @var \DateTime
     * @ORM\Column(name="date_appliq", type="datetime", nullable=true)
     */
    private $dateAppliq;

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
     * Set dateImport
     *
     * @param \DateTime $dateImport
     * @return ImportGeoconcept
     */
    public function setDateImport($dateImport)
    {
        $this->dateImport = $dateImport;

        return $this;
    }

    /**
     * Get dateImport
     *
     * @return \DateTime 
     */
    public function getDateImport()
    {
        return $this->dateImport;
    }

    /**
     * Set dateOptim
     *
     * @param \DateTime $dateOptim
     * @return ImportGeoconcept
     */
    public function setDateOptim($dateOptim)
    {
        $this->dateOptim = $dateOptim;

        return $this;
    }

    /**
     * Get dateOptim
     *
     * @return \DateTime 
     */
    public function getDateOptim()
    {
        return $this->dateOptim;
    }

    /**
     * Set dateApplicationOptim
     *
     * @param \DateTime $dateApplicationOptim
     * @return ImportGeoconcept
     */
    public function setDateApplicationOptim($dateApplicationOptim)
    {
        $this->dateApplicationOptim = $dateApplicationOptim;

        return $this;
    }

    /**
     * Get dateApplicationOptim
     *
     * @return \DateTime 
     */
    public function getDateApplicationOptim()
    {
        return $this->dateApplicationOptim;
    }

    /**
     * Set sDriveTime
     *
     * @param string $sDriveTime
     * @return ImportGeoconcept
     */
    public function setSDriveTime($sDriveTime)
    {
        $this->sDriveTime = $sDriveTime;

        return $this;
    }

    /**
     * Get sDriveTime
     *
     * @return string 
     */
    public function getSDriveTime()
    {
        return $this->sDriveTime;
    }

    /**
     * Set heureDebut
     *
     * @param \DateTime $heureDebut
     * @return ImportGeoconcept
     */
    public function setHeureDebut($heureDebut)
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    /**
     * Get heureDebut
     *
     * @return \DateTime 
     */
    public function getHeureDebut()
    {
        return $this->heureDebut;
    }

    /**
     * Set duree
     *
     * @param \DateTime $duree
     * @return ImportGeoconcept
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return \DateTime 
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return ImportGeoconcept
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set distanceTrajet
     *
     * @param float $distanceTrajet
     * @return ImportGeoconcept
     */
    public function setDistanceTrajet($distanceTrajet)
    {
        $this->distanceTrajet = $distanceTrajet;

        return $this;
    }

    /**
     * Get distanceTrajet
     *
     * @return float 
     */
    public function getDistanceTrajet()
    {
        return $this->distanceTrajet;
    }

    /**
     * Set trajetCumule
     *
     * @param float $trajetCumule
     * @return ImportGeoconcept
     */
    public function setTrajetCumule($trajetCumule)
    {
        $this->trajetCumule = $trajetCumule;

        return $this;
    }

    /**
     * Get trajetCumule
     *
     * @return float 
     */
    public function getTrajetCumule()
    {
        return $this->trajetCumule;
    }

    /**
     * Set sTourneeTime
     *
     * @param string $sTourneeTime
     * @return ImportGeoconcept
     */
    public function setSTourneeTime($sTourneeTime)
    {
        $this->sTourneeTime = $sTourneeTime;

        return $this;
    }

    /**
     * Get sTourneeTime
     *
     * @return string 
     */
    public function getSTourneeTime()
    {
        return $this->sTourneeTime;
    }

    /**
     * Set sVisitTime
     *
     * @param string $sVisitTime
     * @return ImportGeoconcept
     */
    public function setSVisitTime($sVisitTime)
    {
        $this->sVisitTime = $sVisitTime;

        return $this;
    }

    /**
     * Get sVisitTime
     *
     * @return string 
     */
    public function getSVisitTime()
    {
        return $this->sVisitTime;
    }

    /**
     * Set trajetTotalTournee
     *
     * @param float $trajetTotalTournee
     * @return ImportGeoconcept
     */
    public function setTrajetTotalTournee($trajetTotalTournee)
    {
        $this->trajetTotalTournee = $trajetTotalTournee;

        return $this;
    }

    /**
     * Get trajetTotalTournee
     *
     * @return float 
     */
    public function getTrajetTotalTournee()
    {
        return $this->trajetTotalTournee;
    }

    /**
     * Set oProduit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $oProduit
     * @return ImportGeoconcept
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
     * @return ImportGeoconcept
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
     * @return ImportGeoconcept
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
     * Set dateAppliq
     *
     * @param \DateTime $dateAppliq
     * @return ImportGeoconcept
     */
    public function setDateAppliq($dateAppliq)
    {
        $this->dateAppliq = $dateAppliq;

        return $this;
    }

    /**
     * Get dateAppliq
     *
     * @return \DateTime 
     */
    public function getDateAppliq()
    {
        return $this->dateAppliq;
    }
}
