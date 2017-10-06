<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiTournee
 *
 * @ORM\Table(name="pai_tournee"
 *                ,uniqueConstraints={@UniqueConstraint(name="un_pai_tournee",columns={"date_distrib","modele_tournee_jour_id","split_id"})
 *                                   ,@UniqueConstraint(name="un2_pai_tournee",columns={"date_distrib","code"})
 *                                      }
 *                ,indexes={@ORM\Index(name="idx1_pai_tournee", columns={"flux_id","depot_id","date_distrib"})
 *                         ,@ORM\Index(name="idx2_pai_tournee", columns={"date_distrib","employe_id"})
 *                         ,@ORM\Index(name="idx3_pai_tournee", columns={"date_extrait","date_distrib","flux_id","depot_id"})
 *                         ,@ORM\Index(name="idx4_pai_tournee", columns={"tournee_org_id","split_id"})
 *                          }
 *          )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiTourneeRepository")
 */
class PaiTournee
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
     * @var \PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_org_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tourneeOrg;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="split_id", type="integer", nullable=true)
     */
    private $splitId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false, options={"default"=0})
     */
    private $ordre;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

    /**
     * @var \ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modele_tournee_jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tournee;

    /**
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $jour;

    /**
     * @var \RefTypeJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typejour_id", referencedColumnName="id")
     * })
     */
    private $typeJour;

	/**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $depot;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $flux;

    /**
     * @var \GroupeTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\GroupeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $groupe;
    /**
     * @var \GroupeTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiHeure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="heure_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $heure;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=13, nullable=false)
     */
    private $code;
   
    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $employe;

    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_org", type="string", length=2, nullable=false)
     */
    private $valremOrg;

    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valrem;

    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_paie", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valremPaie;

    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_logistique", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valremLogistique;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_majoree", type="decimal", precision=7, scale=5, nullable=false)
     */
    private $valremMajoree;
    
    /**
     * @var string
     *
     * @ORM\Column(name="majoration", type="decimal", precision=5, scale=2, nullable=false, options={"default"=0})
     */
    private $majoration;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_attente", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $attente;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_retard", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $retard;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_calculee", type="time", nullable=true)
     */
    private $heureDebutCalcule;
    
    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_tournee", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeTournee;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_reperage", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeReperage;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_supplement", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeSupplement;
    
      /**
     * @var time
     *
     * @ORM\Column(name="duree_nuit", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $tpsNuit;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="poids", type="integer", nullable=false, options={"default"=0})
     */
    private $poids;

    /**
     * @var \RefTransport
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTransport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transport_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transport;

    /**
     * @var string
     *
     * @ORM\Column(name="nbkm", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbkm;

    /**
     * @var string
     *
     * @ORM\Column(name="nbkm_paye", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbkmPaye;

    /**
     * @var string
     *
     * @ORM\Column(name="nbtitre", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbtitre;

    /**
     * @var string
     *
     * @ORM\Column(name="nbspl", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbspl;

    /**
     * @var string
     *
     * @ORM\Column(name="nbcli", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbcli;

    /**
     * @var string
     *
     * @ORM\Column(name="nbcli_unique", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbcliUnique;

    /**
     * @var string
     *
     * @ORM\Column(name="nbadr", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbadr;

    /**
     * @var string
     *
     * @ORM\Column(name="nbprod", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbprod;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nbrep", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbReperage;
	
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $utilisateur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
        
    

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
     * Set date_distrib
     *
     * @param \DateTime $dateDistrib
     * @return PaiTournee
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->date_distrib = $dateDistrib;

        return $this;
    }

    /**
     * Get date_distrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->date_distrib;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return PaiTournee
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set valrem
     *
     * @param string $valrem
     * @return PaiTournee
     */
    public function setValrem($valrem)
    {
        $this->valrem = $valrem;

        return $this;
    }

    /**
     * Get valrem
     *
     * @return string 
     */
    public function getValrem()
    {
        return $this->valrem;
    }

    /**
     * Set majoration
     *
     * @param string $majoration
     * @return PaiTournee
     */
    public function setMajoration($majoration)
    {
        $this->majoration = $majoration;

        return $this;
    }

    /**
     * Get majoration
     *
     * @return string 
     */
    public function getMajoration()
    {
        return $this->majoration;
    }

    /**
     * Set duree
     *
     * @param \DateTime $duree
     * @return PaiTournee
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
     * Set retard
     *
     * @param \DateTime $retard
     * @return PaiTournee
     */
    public function setRetard($retard)
    {
        $this->retard = $retard;

        return $this;
    }

    /**
     * Get retard
     *
     * @return \DateTime 
     */
    public function getRetard()
    {
        return $this->retard;
    }


    /**
     * Set dureeSupplement
     *
     * @param \DateTime $dureeSupplement
     * @return PaiTournee
     */
    public function setDureeSupplement($dureeSupplement)
    {
        $this->dureeSupplement = $dureeSupplement;

        return $this;
    }

    /**
     * Get dureeSupplement
     *
     * @return \DateTime 
     */
    public function getDureeSupplement()
    {
        return $this->dureeSupplement;
    }

    /**
     * Set nbkm
     *
     * @param string $nbkm
     * @return PaiTournee
     */
    public function setNbkm($nbkm)
    {
        $this->nbkm = $nbkm;

        return $this;
    }

    /**
     * Get nbkm
     *
     * @return string 
     */
    public function getNbkm()
    {
        return $this->nbkm;
    }

    /**
     * Set nbkmPaye
     *
     * @param string $nbkmPaye
     * @return PaiTournee
     */
    public function setNbkmPaye($nbkmPaye)
    {
        $this->nbkmPaye = $nbkmPaye;

        return $this;
    }

    /**
     * Get nbkmPaye
     *
     * @return string 
     */
    public function getNbkmPaye()
    {
        return $this->nbkmPaye;
    }

    /**
     * Set nbadr
     *
     * @param string $nbadr
     * @return PaiTournee
     */
    public function setNbadr($nbadr)
    {
        $this->nbadr = $nbadr;

        return $this;
    }

    /**
     * Get nbadr
     *
     * @return string 
     */
    public function getNbadr()
    {
        return $this->nbadr;
    }

    /**
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return PaiTournee
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get date_creation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set date_modif
     *
     * @param \DateTime $dateModif
     * @return PaiTournee
     */
    public function setDateModif($dateModif)
    {
        $this->date_modif = $dateModif;

        return $this;
    }

    /**
     * Get date_modif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->date_modif;
    }

    /**
     * Set date_extrait
     *
     * @param \DateTime $dateExtrait
     * @return PaiTournee
     */
    public function setDateExtrait($dateExtrait)
    {
        $this->date_extrait = $dateExtrait;

        return $this;
    }

    /**
     * Get date_extrait
     *
     * @return \DateTime 
     */
    public function getDateExtrait()
    {
        return $this->date_extrait;
    }

    /**
     * Set nbReperage
     *
     * @param integer $nbReperage
     * @return PaiTournee
     */
    public function setNbReperage($nbReperage)
    {
        $this->nbReperage = $nbReperage;

        return $this;
    }

    /**
     * Get nbReperage
     *
     * @return integer 
     */
    public function getNbReperage()
    {
        return $this->nbReperage;
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTournee $tournee
     * @return PaiTournee
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
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return PaiTournee
     */
    public function setJour(\Ams\ReferentielBundle\Entity\RefJour $jour)
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
     * Set typeJour
     *
     * @param \Ams\ReferentielBundle\Entity\RefTypeJour $typeJour
     * @return PaiTournee
     */
    public function setTypeJour(\Ams\ReferentielBundle\Entity\RefTypeJour $typeJour = null)
    {
        $this->typeJour = $typeJour;

        return $this;
    }

    /**
     * Get typeJour
     *
     * @return \Ams\ReferentielBundle\Entity\RefTypeJour 
     */
    public function getTypeJour()
    {
        return $this->typeJour;
    }

    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return PaiTournee
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot)
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
     * @return PaiTournee
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux)
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
     * Set groupe
     *
     * @param \Ams\ModeleBundle\Entity\GroupeTournee $groupe
     * @return PaiTournee
     */
    public function setGroupe(\Ams\ModeleBundle\Entity\GroupeTournee $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Ams\ModeleBundle\Entity\GroupeTournee 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set typetournee
     *
     * @param \Ams\ReferentielBundle\Entity\RefTypeTournee $typetournee
     * @return PaiTournee
     */
    public function setTypetournee(\Ams\ReferentielBundle\Entity\RefTypeTournee $typetournee)
    {
        $this->typetournee = $typetournee;

        return $this;
    }

    /**
     * Get typetournee
     *
     * @return \Ams\ReferentielBundle\Entity\RefTypeTournee 
     */
    public function getTypetournee()
    {
        return $this->typetournee;
    }

    /**
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return PaiTournee
     */
    public function setEmploye(\Ams\EmployeBundle\Entity\Employe $employe = null)
    {
        $this->employe = $employe;

        return $this;
    }

    /**
     * Get employe
     *
     * @return \Ams\EmployeBundle\Entity\Employe 
     */
    public function getEmploye()
    {
        return $this->employe;
    }

    /**
     * Set transport
     *
     * @param \Ams\ReferentielBundle\Entity\RefTransport $transport
     * @return PaiTournee
     */
    public function setTransport(\Ams\ReferentielBundle\Entity\RefTransport $transport = null)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Get transport
     *
     * @return \Ams\ReferentielBundle\Entity\RefTransport 
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return PaiTournee
     */
    public function setUtilisateur(\Ams\SilogBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set tpsNuit
     *
     * @param \DateTime $tpsNuit
     * @return PaiTournee
     */
    public function setTpsNuit($tpsNuit)
    {
        $this->tpsNuit = $tpsNuit;

        return $this;
    }

    /**
     * Get tpsNuit
     *
     * @return \DateTime 
     */
    public function getTpsNuit()
    {
        return $this->tpsNuit;
    }
}
