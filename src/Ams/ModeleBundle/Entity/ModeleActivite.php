<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModeleActivite
 *
 * @ORM\Table(name="modele_activite"
 *                      ,indexes={@ORM\Index(name="idx1_modele_activite", columns={"depot_id","flux_id"})}
 * 			)
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleActiviteRepository")
 */
class ModeleActivite
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
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $jour;

    /**
     * @var \RefActivite
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefActivite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $activite;

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
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;

    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;

    /**
     * @var string
     *
     * @ORM\Column(name="nbkm_paye", type="decimal", precision=4, scale=0, nullable=true)
     */
    private $nbkmPaye;

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
     * @ORM\Column(name="commentaire", type="string", length=1024, nullable=true)
     */
    private $commentaire;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true)
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
     * @return ModeleActivite
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
     * @return ModeleActivite
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
     * Set heureDebut
     *
     * @param \DateTime $heureDebut
     * @return ModeleActivite
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
     * Get heureFin
     *
     * @return \DateTime 
     */
    public function getHeureFin()
    {
        $str= 'PT' . $this->duree->format('H') . 'H' . $this->duree->format('i') . 'M';
        $interval = new \DateInterval($str);
        return $this->heureDebut->add($interval);
    }

    /**
     * Set duree
     *
     * @param string $duree
     * @return ModeleActivite
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
     * Set nbkmPaye
     *
     * @param string $nbkmPaye
     * @return ModeleActivite
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
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return ModeleActivite
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
     * @return ModeleActivite
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
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return ModeleActivite
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
     * @return ModeleActivite
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
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return ModeleActivite
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
     * Set activite
     *
     * @param \Ams\ReferentielBundle\Entity\RefActivite $activite
     * @return ModeleActivite
     */
    public function setActivite(\Ams\ReferentielBundle\Entity\RefActivite $activite)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite
     *
     * @return \Ams\ReferentielBundle\Entity\RefActivite 
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return ModeleActivite
     */
    public function setEmploye(\Ams\EmployeBundle\Entity\Employe $employe)
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
     * @return ModeleActivite
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
     * @return ModeleActivite
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
}
