<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * TourneeDetail
 *
 * @ORM\Table(name="tournee_detail", indexes={@index(name="idx_modele_tournee_jour_code", columns={"modele_tournee_jour_code"})
 *                                            , @index(name="idx_abonne_soc", columns={"num_abonne_id"})
 *                                            , @index(name="idx_abo_jour", columns={"num_abonne_id", "jour_id"})
 *                                            , @index(name="idx_reperage", columns={"reperage"})
 *                                            , @index(name="idx_exclure", columns={"exclure_ressource"})
 *                                            , @index(name="idx_insee", columns={"insee"})
 *                                          }
 *          )
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\TourneeDetailRepository")
 */
class TourneeDetail
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
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;

    /**
     * Permet de sauvegarder l'ordre défini lors d'une optimisation de tournée
     *  @var integer
     *
     * @ORM\Column(name="ordre_optimisation", type="integer", nullable=true)
     */
    private $ordreOptim;

    /**
     * Indique le moment du dernier changement sur cet enregistrement
     * @var \DateTime
     *
     * @ORM\Column(name="date_modification", type="datetime", nullable=true)
     */
    private $dateModification;
    
    /**
     * @var string
     *
     * @ORM\Column(name="source_modification", type="string", length=50, nullable=true)
     */
    private $sourceModification;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre_stop", type="integer", nullable=true)
     */
    private $ordreStop;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float")
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float")
     */
    private $longitude;
    
    /**
     * @var int
     *
     * @ORM\Column(name="num_abonne_id", type="integer", nullable=true)
     */
    private $numAbonneId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="num_abonne_soc", type="string", length=50, nullable=true)
     */
    private $numAbonneSoc;
    
    /**
     * @var string
     * @ORM\Column(name="modele_tournee_jour_code", type="string")
     */
    private $modeleTourneeJourCode;

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
     * @var \DateTime
     *
     * @ORM\Column(name="duree_conduite", type="time", nullable=true)
     */
    private $dureeConduite;

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
     * @ORM\Column(name="etat", type="string", length=50)
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
     * @var float
     *
     * @ORM\Column(name="trajet_total_tournee", type="float", nullable=true)
     */
    private $trajetTotalTournee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="debut_plage_horaire", type="time", nullable=true)
     */
    private $debutPlageHoraire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin_plage_horaire", type="time", nullable=true)
     */
    private $finPlageHoraire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="duree_viste_fixe", type="time")
     */
    private $dureeVisteFixe;

    /**
     * @var string
     *
     * @ORM\Column(name="exclure_ressource", type="string", length=255)
     */
    private $exclureRessource;

    /**
     * @var string
     *
     * @ORM\Column(name="assigner_ressource", type="string", length=255)
     */
    private $assignerRessource;

    /**
     * @var boolean
     *
     * @ORM\Column(name="a_traiter", type="boolean")
     */
    private $aTraiter;
    
    /**
     * @ORM\Column(name="nb_stop", type="integer", nullable=true)
    */
    private $nbStop;
    
 		/**
     * @var string
     * @ORM\Column(name="temps_conduite", type="string", length=25, nullable=true)
     */
    private $sDriveTime;
    
    
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
     * @var string
     *
     * @ORM\Column(name="soc", type="string", length=10, nullable=true)
     */
    private $soc;
    
    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=200, nullable=true)
     */
    private $titre;
    
    /**
     * @var integer
     * @ORM\Column(name="insee", type="integer", nullable=true)
     */
    private $insee;
    
    /**
     * @var integer
     * @ORM\Column(name="flux_id", type="smallint", nullable=true)
     */
    private $flux;
    
    
    /**
     * @var boolean
     * @ORM\Column(name="reperage", type="smallint", nullable=true, options={"default":0})
     */
    private $reperage;
    
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
     * Set pointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return ClientAServirLogist
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
     * Set ordre
     *
     * @param integer $ordre
     * @return TourneeDetail
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return TourneeDetail
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return TourneeDetail
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set numAbonneSoc
     *
     * @param string $numAbonneSoc
     * @return TourneeDetail
     */
    public function setNumAbonneSoc($numAbonneSoc)
    {
        $this->numAbonneSoc = $numAbonneSoc;

        return $this;
    }

    /**
     * Get numAbonneSoc
     *
     * @return string 
     */
    public function getNumAbonneSoc()
    {
        return $this->numAbonneSoc;
    }

    /**
     * Set modeleTourneeJourCode
     *
     * @param string $modeleTourneeJourCode
     * @return TourneeDetail
     */
    public function setModeleTourneeJourCode($modeleTourneeJourCode)
    {
        $this->modeleTourneeJourCode = $modeleTourneeJourCode;

        return $this;
    }

    /**
     * Get modeleTourneeJourCode
     *
     * @return string 
     */
    public function getModeleTourneeJourCode()
    {
        return $this->modeleTourneeJourCode;
    }

    /**
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return ModeleTourneeJour
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
     * Set dureeConduite
     *
     * @param \DateTime $dureeConduite
     * @return TourneeDetail
     */
    public function setDureeConduite($dureeConduite)
    {
        $this->dureeConduite = $dureeConduite;

        return $this;
    }

    /**
     * Get dureeConduite
     *
     * @return \DateTime 
     */
    public function getDureeConduite()
    {
        return $this->dureeConduite;
    }

    /**
     * Set heureDebut
     *
     * @param \DateTime $heureDebut
     * @return TourneeDetail
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
     * @return TourneeDetail
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
     * @return TourneeDetail
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
     * @return TourneeDetail
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
     * @return TourneeDetail
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
     * Set trajetTotalTournee
     *
     * @param float $trajetTotalTournee
     * @return TourneeDetail
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
     * Set debutPlageHoraire
     *
     * @param \DateTime $debutPlageHoraire
     * @return TourneeDetail
     */
    public function setDebutPlageHoraire($debutPlageHoraire)
    {
        $this->debutPlageHoraire = $debutPlageHoraire;

        return $this;
    }

    /**
     * Get debutPlageHoraire
     *
     * @return \DateTime 
     */
    public function getDebutPlageHoraire()
    {
        return $this->debutPlageHoraire;
    }

    /**
     * Set finPlageHoraire
     *
     * @param \DateTime $finPlageHoraire
     * @return TourneeDetail
     */
    public function setFinPlageHoraire($finPlageHoraire)
    {
        $this->finPlageHoraire = $finPlageHoraire;

        return $this;
    }

    /**
     * Get finPlageHoraire
     *
     * @return \DateTime 
     */
    public function getFinPlageHoraire()
    {
        return $this->finPlageHoraire;
    }

    /**
     * Set dureeVisteFixe
     *
     * @param \DateTime $dureeVisteFixe
     * @return TourneeDetail
     */
    public function setDureeVisteFixe($dureeVisteFixe)
    {
        $this->dureeVisteFixe = $dureeVisteFixe;

        return $this;
    }

    /**
     * Get dureeVisteFixe
     *
     * @return \DateTime 
     */
    public function getDureeVisteFixe()
    {
        return $this->dureeVisteFixe;
    }

    /**
     * Set exclureRessource
     *
     * @param string $exclureRessource
     * @return TourneeDetail
     */
    public function setExclureRessource($exclureRessource)
    {
        $this->exclureRessource = $exclureRessource;

        return $this;
    }

    /**
     * Get exclureRessource
     *
     * @return string 
     */
    public function getExclureRessource()
    {
        return $this->exclureRessource;
    }

    /**
     * Set assignerRessource
     *
     * @param string $assignerRessource
     * @return TourneeDetail
     */
    public function setAssignerRessource($assignerRessource)
    {
        $this->assignerRessource = $assignerRessource;

        return $this;
    }

    /**
     * Get assignerRessource
     *
     * @return string 
     */
    public function getAssignerRessource()
    {
        return $this->assignerRessource;
    }

    /**
     * Set aTraiter
     *
     * @param boolean $aTraiter
     * @return TourneeDetail
     */
    public function setATraiter($aTraiter)
    {
        $this->aTraiter = $aTraiter;

        return $this;
    }

    /**
     * Get aTraiter
     *
     * @return boolean 
     */
    public function getATraiter()
    {
        return $this->aTraiter;
    }

    /**
     * Set numAbonneId
     *
     * @param \int $numAbonneId
     * @return TourneeDetail
     */
    public function setNumAbonneId($numAbonneId)
    {
        $this->numAbonneId = $numAbonneId;

        return $this;
    }

    /**
     * Get numAbonneId
     *
     * @return \int 
     */
    public function getNumAbonneId()
    {
        return $this->numAbonneId;
    }

    /**
     * Set nbStop
     *
     * @param integer $nbStop
     * @return TourneeDetail
     */
    public function setNbStop($nbStop)
    {
        $this->nbStop = $nbStop;

        return $this;
    }

    /**
     * Get nbStop
     *
     * @return integer 
     */
    public function getNbStop()
    {
        return $this->nbStop;
    }

    /**
     * Set sDriveTime
     *
     * @param string $sDriveTime
     * @return TourneeDetail
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
     * Set sTourneeTime
     *
     * @param string $sTourneeTime
     * @return TourneeDetail
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
     * @return TourneeDetail
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
     * Set ordreStop
     *
     * @param integer $ordreStop
     * @return TourneeDetail
     */
    public function setOrdreStop($ordreStop)
    {
        $this->ordreStop = $ordreStop;

        return $this;
    }

    /**
     * Get ordreStop
     *
     * @return integer 
     */
    public function getOrdreStop()
    {
        return $this->ordreStop;
    }

    /**
     * Set soc
     *
     * @param string $soc
     * @return TourneeDetail
     */
    public function setSoc($soc)
    {
        $this->soc = $soc;

        return $this;
    }

    /**
     * Get soc
     *
     * @return string 
     */
    public function getSoc()
    {
        return $this->soc;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return TourneeDetail
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set insee
     *
     * @param integer $insee
     * @return TourneeDetail
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;

        return $this;
    }

    /**
     * Get insee
     *
     * @return integer 
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set flux
     *
     * @param integer $flux
     * @return TourneeDetail
     */
    public function setFlux($flux)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return integer 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set ordreOptim
     *
     * @param integer $ordreOptim
     * @return TourneeDetail
     */
    public function setOrdreOptim($ordreOptim)
    {
        $this->ordreOptim = $ordreOptim;

        return $this;
    }

    /**
     * Get ordreOptim
     *
     * @return integer 
     */
    public function getOrdreOptim()
    {
        return $this->ordreOptim;
    }

    /**
     * Set dateModification
     *
     * @param \DateTime $dateModification
     * @return TourneeDetail
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime 
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Set sourceModification
     *
     * @param string $sourceModification
     * @return TourneeDetail
     */
    public function setSourceModification($sourceModification)
    {
        $this->sourceModification = $sourceModification;

        return $this;
    }

    /**
     * Get sourceModification
     *
     * @return string 
     */
    public function getSourceModification()
    {
        return $this->sourceModification;
    }

    /**
     * Set reperage
     *
     * @param boolean $reperage
     * @return TourneeDetail
     */
    public function setReperage($reperage)
    {
        $this->reperage = $reperage;

        return $this;
    }

    /**
     * Get reperage
     *
     * @return boolean 
     */
    public function getReperage()
    {
        return $this->reperage;
    }
}
