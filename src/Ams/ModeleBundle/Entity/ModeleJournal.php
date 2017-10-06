<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModeleJournal
 *
 * @ORM\Table(name="modele_journal")
* @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleJournalRepository")
 */
class ModeleJournal
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
     * @var \ModeleRefErreur
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\ModeleRefErreur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="erreur_id", referencedColumnName="id")
     * })
     */
    private $erreur_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="validation_id", type="integer", nullable=false)
     */
    private $validation_id;

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
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $jour;

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
     * @var \ModeleActivite
     *
     * @ORM\ManyToOne(targetEntity="ModeleActivite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $activite;

    /**
     * @var \ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="ModeleTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tournee;

    /**
     * @var \ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="ModeleTourneeJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tourneeJour;

    /**
     * @var \ModeleTourneeGeo
     *
     * @ORM\ManyToOne(targetEntity="Remplacement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="remplacement_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $remplacement;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;
    
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
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return ModeleJournal
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
     * @return ModeleJournal
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
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return ModeleJournal
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
     * Set activite
     *
     * @param \Ams\ModeleBundle\Entity\ModeleActivite $activite
     * @return ModeleJournal
     */
    public function setActivite(\Ams\ModeleBundle\Entity\ModeleActivite $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite
     *
     * @return \Ams\ModeleBundle\Entity\ModeleActivite 
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee
     * @return ModeleJournal
     */
    public function setTournee(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee = null)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get tournee
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getTournee()
    {
        return $this->tournee;
    }

    /**
     * Set validation_id
     *
     * @param integer $validationId
     * @return ModeleJournal
     */
    public function setValidationId($validationId)
    {
        $this->validation_id = $validationId;

        return $this;
    }

    /**
     * Get validation_id
     *
     * @return integer 
     */
    public function getValidationId()
    {
        return $this->validation_id;
    }

    /**
     * Set jour
     *
     * @param \Ams\ReferentielBundle\Entity\RefJour $jour
     * @return ModeleJournal
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
}
