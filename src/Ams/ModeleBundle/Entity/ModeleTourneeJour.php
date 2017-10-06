<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 * ModeleTourneeJour
 * @ORM\Table(name="modele_tournee_jour"
 * 	     ,uniqueConstraints={@UniqueConstraint(name="un_modele_tournee_jour",columns={"tournee_id","jour_id","date_debut"})}
 *           , indexes={@Index(name="modele_tournee_jour_idx1", columns={"code"})
 *                     ,@Index(name="modele_tournee_jour_idx2", columns={"tournee_id","employe_id"})
 *                     ,@Index(name="modele_tournee_jour_idx3", columns={"id","jour_id","date_debut","date_fin"})
 *                     ,@Index(name="modele_tournee_jour_idx4", columns={"employe_id","jour_id","date_debut","date_fin"})
 *                     ,@Index(name="modele_tournee_jour_idx5", columns={"employe_id","date_debut","date_fin"})
 *                     ,@Index(name="modele_tournee_jour_idx6", columns={"remplacant_id","date_debut","date_fin"})
 *                     }
 * 	     )                 
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleTourneeJourRepository")
 */
class ModeleTourneeJour
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
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $date_debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=false)
     */
    private $date_fin;

    /**
     * @var \ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="ModeleTournee", inversedBy="tourneesJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=false)
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=11, nullable=false)
     */
    private $code;

    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id")
     * })
     */
    private $employe;
    
    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="remplacant_id", referencedColumnName="id")
     * })
     */
    private $remplacant;
    
    /**
     * @var \RefTransport
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTransport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transport_id", referencedColumnName="id")
     * })
     */
    private $transport;

    /**
     * @var string
     *
     * @ORM\Column(name="tauxhoraire", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $tauxhoraire;

    /**
     * @var string
     *
     * @ORM\Column(name="valrem", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valrem;
    
    /**
     * @var string
     *
     * @ORM\Column(name="valrem_moyen", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valremMoyen;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalonCalcule;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon_moyen", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalonMoyen;
    
    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;

    /**
     * @var string
     *
     * @ORM\Column(name="nbcli", type="decimal", precision=6, scale=0, nullable=true)
     */
    private $nbcli;

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
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=true)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;

    /**
     * @var boolean
     * 1 si ce modèle a le dépot comme point de départ
     * 0 si le point de depart est le premier abonné à livrer
     * 
     * @ORM\Column(name="depart_depot", type="boolean", nullable = true, options={"default" : true})
     * 
     */
    private $departDepot;

    /**
     * @var boolean
     * 1 si cette tournée se termine par un retour au dépot
     * 0 si le dernier point de la tournée est le dernier abonné à livrer
     * 
     * @ORM\Column(name="retour_depot", type="boolean", nullable = true, options={"default" : true})
     * 
     */
    private $retourDepot;

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
     * Set date_debut
     *
     * @param \DateTime $dateDebut
     * @return ModeleTourneeJour
     */
    public function setDateDebut($dateDebut)
    {
        $this->date_debut = $dateDebut;

        return $this;
    }

    /**
     * Get date_debut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->date_debut;
    }

    /**
     * Set date_fin
     *
     * @param \DateTime $dateFin
     * @return ModeleTourneeJour
     */
    public function setDateFin($dateFin)
    {
        $this->date_fin = $dateFin;

        return $this;
    }

    /**
     * Get date_fin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->date_fin;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return ModeleTourneeJour
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
     * Set dureeGeo
     *
     * @param string $dureeGeo
     * @return ModeleTourneeJour
     */
    public function setDureeGeo($dureeGeo)
    {
        $this->dureeGeo = $dureeGeo;

        return $this;
    }

    /**
     * Get dureeGeo
     *
     * @return string 
     */
    public function getDureeGeo()
    {
        return $this->dureeGeo;
    }

    /**
     * Set qteGeo
     *
     * @param string $qteGeo
     * @return ModeleTourneeJour
     */
    public function setQteGeo($qteGeo)
    {
        $this->qteGeo = $qteGeo;

        return $this;
    }

    /**
     * Get qteGeo
     *
     * @return string 
     */
    public function getQteGeo()
    {
        return $this->qteGeo;
    }

    /**
     * Set nbcliGeo
     *
     * @param string $nbcliGeo
     * @return ModeleTourneeJour
     */
    public function setNbcliGeo($nbcliGeo)
    {
        $this->nbcliGeo = $nbcliGeo;

        return $this;
    }

    /**
     * Get nbcliGeo
     *
     * @return string 
     */
    public function getNbcliGeo()
    {
        return $this->nbcliGeo;
    }

    /**
     * Set nbkmGeo
     *
     * @param string $nbkmGeo
     * @return ModeleTourneeJour
     */
    public function setNbkmGeo($nbkmGeo)
    {
        $this->nbkmGeo = $nbkmGeo;

        return $this;
    }

    /**
     * Get nbkmGeo
     *
     * @return string 
     */
    public function getNbkmGeo()
    {
        return $this->nbkmGeo;
    }

    /**
     * Set nbadrGeo
     *
     * @param string $nbadrGeo
     * @return ModeleTourneeJour
     */
    public function setNbadrGeo($nbadrGeo)
    {
        $this->nbadrGeo = $nbadrGeo;

        return $this;
    }

    /**
     * Get nbadrGeo
     *
     * @return string 
     */
    public function getNbadrGeo()
    {
        return $this->nbadrGeo;
    }

    /**
     * Set valrem
     *
     * @param string $valrem
     * @return ModeleTourneeJour
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
     * Set valremCalculee
     *
     * @param string $valremCalculee
     * @return ModeleTourneeJour
     */
    public function setValremCalculee($valremCalculee)
    {
        $this->valremCalculee = $valremCalculee;

        return $this;
    }

    /**
     * Get valremCalculee
     *
     * @return string 
     */
    public function getValremCalculee()
    {
        return $this->valremCalculee;
    }

    /**
     * Set duree
     *
     * @param string $duree
     * @return ModeleTourneeJour
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return string 
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Get heureDebut
     *
     * @return \DateTime 
     */
    public function getHeureDebut()
    {
        return $this->tournee->getGroupe()->getHeureDebut();
    }

    /**
     * Get heureFin
     *
     * @return \DateTime 
     */
    public function getHeureFin()
    {
         $str= 'PT' . $this->duree->format('H') . 'H' . $this->duree->format('i') . 'M';
        $interval = new \DateInterval($str);
        return $hd->add($interval);
     }

    /**
     * Set nbkm
     *
     * @param string $nbkm
     * @return ModeleTourneeJour
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
     * @return ModeleTourneeJour
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
     * Set valide
     *
     * @param boolean $valide
     * @return ModeleTourneeJour
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return boolean 
     */
    public function getValide()
    {
        return $this->valide;
    }

    /**
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return ModeleTourneeJour
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
     * @return ModeleTourneeJour
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
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTournee $tournee
     * @return ModeleTourneeJour
     */
    public function setTournee(\Ams\ModeleBundle\Entity\ModeleTournee $tournee)
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
     * @return ModeleTourneeJour
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
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return ModeleTourneeJour
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
     * @return ModeleTourneeJour
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
     * @return ModeleTourneeJour
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
     * Get departDepot
     * Retourne TRUE si le point de départ de la tournée est son dépôt
     * @return boolean
     */
    public function getDepartDepot()
    {
        return $this->departDepot;
    }
    
    /**
     * Set departDepot
     * 
     * @param boolean $val
     * @return Depot
     */
    public function SetDepartDepot($val)
    {
        $this->departDepot = $val;
        return $this;
    }
    
    /**
     * Get retourDepot
     * Retourne TRUE si le dernier point de la tournée est son dépôt
     * @return boolean
     */
    public function getRetourDepot()
    {
        return $this->retourDepot;
    }
    
    /**
     * Set retourDepot
     * 
     * @param boolean $val
     * @return Depot
     */
    public function SetRetourDepot($val)
    {
        $this->retourDepot = $val;
        return $this;
    }
            
}
