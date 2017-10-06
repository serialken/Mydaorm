<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CptrDistribution
 *
 * @ORM\Table(name="cptr_distribution")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\CptrDistributionRepository")
 */
class CptrDistribution
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
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     */
    private $depot;

    /**
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=false)
     */
    private $tournee;

    
    /**
     * @var \Ams\DistributionBundle\Entity\CptrTypeIncident
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CptrTypeIncident")
     * @ORM\JoinColumn(name="type_incident_id", referencedColumnName="id", nullable=true)
     */
    private $typeIncident;


    /**
     * @var integer
     *
     * @ORM\Column(name="nb_ex_abo", type="integer", nullable=true)
     */
    private $nbExAbo;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_ex_diff", type="integer", nullable=true)
     */
    private $nbExDiff;

      /**
     * @var integer
     *
     * @ORM\Column(name="nb_abonne_non_livre", type="integer", nullable=true)
     */
    private $nbAbonneNonLivre;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_diff_non_livre", type="integer", nullable=true)
     */
    private $nbDiffNonLivre;

    /**
     * @var time
     *
     * @ORM\Column(name="heure_fin_tournee", type="time", nullable=true, options={"default":NULL}))
     */
    private $heureFinDeTournee;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cmt_incident_ab", type="text", nullable=true)
     */
    private $cmtIncidentAb;

    /**
     * @var string
     *
     * @ORM\Column(name="cmt_incident_diff", type="text", nullable=true)
     */
    private $cmtIncidentDiff;
    
    /**
    * @ORM\ManyToMany(targetEntity="Ams\AdresseBundle\Entity\Commune", cascade={"persist"})
    */
    private $ville;
    
    /**
     * @var \Date
     *
     * @ORM\Column(name="date_cpt_rendu", type="date")
     */
    private $dateCptRendu;

    /**
     * @var \Ams\DistributionBundle\Entity\CptrTypeAnomalie
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CptrTypeAnomalie")
     * @ORM\JoinColumn(name="type_anomalie_id", referencedColumnName="id", nullable=true)
     */
    private $typeAnomalie;

       /**
     * Date export (vers Jade par exemple)
     * @var \DateTime
     *
     * @ORM\Column(name="date_export", type="datetime", nullable=true)
     */
    private $dateExport;

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
     * @return CptrDistribution
     */
    public function setDepotId($depot)
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
     * Set Tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee
     * @return CptrDistribution
     */
    public function setTournee($tournee)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get Tournee
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getTournee()
    {
        return $this->tournee;
    }

    

    /**
     * Set nbExAbo
     *
     * @param integer $nbExAbo
     * @return CptrDistribution
     */
    public function setNbExAbo($nbExAbo)
    {
        $this->nbExAbo = $nbExAbo;

        return $this;
    }

    /**
     * Get nbExAbo
     *
     * @return integer 
     */
    public function getNbExAbo()
    {
        return $this->nbExAbo;
    }

    /**
     * Set nbExDiff
     *
     * @param integer $nbExDiff
     * @return CptrDistribution
     */
    public function setNbExDiff($nbExDiff)
    {
        $this->nbExDiff = $nbExDiff;

        return $this;
    }

    /**
     * Get nbExDiff
     *
     * @return integer 
     */
    public function getNbExDiff()
    {
        return $this->nbExDiff;
    }

   
   
    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return CptrDistribution
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot)
    {
        $this->depot = $depot;

        return $this;
    }

    

    /**
     * Set typeIncident
     *
     * @param \Ams\DistributionBundle\Entity\CptrTypeIncident $typeIncident
     * @return CptrDistribution
     */
    public function setTypeIncident(\Ams\DistributionBundle\Entity\CptrTypeIncident $typeIncident)
    {
        $this->typeIncident = $typeIncident;

        return $this;
    }

    /**
     * Get typeIncident
     *
     * @return \Ams\DistributionBundle\Entity\CptrTypeIncident 
     */
    public function getTypeIncident()
    {
        return $this->typeIncident;
    }



    /**
     * Set dateCptRendu
     *
     * @param \DateTime $dateCptRendu
     * @return CptrDistribution
     */
    public function setDateCptRendu($dateCptRendu)
    {
        $this->dateCptRendu = $dateCptRendu;

        return $this;
    }

    /**
     * Get dateCptRendu
     *
     * @return \DateTime 
     */
    public function getDateCptRendu()
    {
        return $this->dateCptRendu;
    }

    /**
     * Set heureFinDeTournee
     *
     * @param \DateTime $heureFinDeTournee
     * @return CptrDistribution
     */
    public function setHeureFinDeTournee($heureFinDeTournee)
    {
        $this->heureFinDeTournee = $heureFinDeTournee;

        return $this;
    }

    /**
     * Get heureFinDeTournee
     *
     * @return \DateTime 
     */
    public function getHeureFinDeTournee()
    {
        return $this->heureFinDeTournee;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ville = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add ville
     *
     * @param \Ams\AdresseBundle\Entity\Commune $ville
     * @return CptrDistribution
     */
    public function addVille(\Ams\AdresseBundle\Entity\Commune $ville)
    {
        $this->ville[] = $ville;

        return $this;
    }

    /**
     * Remove ville
     *
     * @param \Ams\AdresseBundle\Entity\Commune $ville
     */
    public function removeVille(\Ams\AdresseBundle\Entity\Commune $ville)
    {
        $this->ville->removeElement($ville);
    }

    /**
     * Get ville
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set nbAbonneNonLivre
     *
     * @param integer $nbAbonneNonLivre
     * @return CptrDistribution
     */
    public function setNbAbonneNonLivre($nbAbonneNonLivre)
    {
        $this->nbAbonneNonLivre = $nbAbonneNonLivre;

        return $this;
    }

    /**
     * Get nbAbonneNonLivre
     *
     * @return integer 
     */
    public function getNbAbonneNonLivre()
    {
        return $this->nbAbonneNonLivre;
    }

    /**
     * Set nbDiffNonLivre
     *
     * @param integer $nbDiffNonLivre
     * @return CptrDistribution
     */
    public function setNbDiffNonLivre($nbDiffNonLivre)
    {
        $this->nbDiffNonLivre = $nbDiffNonLivre;

        return $this;
    }

    /**
     * Get nbDiffNonLivre
     *
     * @return integer 
     */
    public function getNbDiffNonLivre()
    {
        return $this->nbDiffNonLivre;
    }

    /**
     * Set typeAnomalie
     *
     * @param \Ams\DistributionBundle\Entity\CptrTypeAnomalie $typeAnomalie
     * @return CptrDistribution
     */
    public function setTypeAnomalie(\Ams\DistributionBundle\Entity\CptrTypeAnomalie $typeAnomalie = null)
    {
        $this->typeAnomalie = $typeAnomalie;

        return $this;
    }

    /**
     * Get typeAnomalie
     *
     * @return \Ams\DistributionBundle\Entity\CptrTypeAnomalie 
     */
    public function getTypeAnomalie()
    {
        return $this->typeAnomalie;
    }

    /**
     * Set cmtIncidentAb
     *
     * @param string $cmtIncidentAb
     * @return CptrDistribution
     */
    public function setCmtIncidentAb($cmtIncidentAb)
    {
        $this->cmtIncidentAb = $cmtIncidentAb;

        return $this;
    }

    /**
     * Get cmtIncidentAb
     *
     * @return string 
     */
    public function getCmtIncidentAb()
    {
        return $this->cmtIncidentAb;
    }

    /**
     * Set cmtIncidentDiff
     *
     * @param string $cmtIncidentDiff
     * @return CptrDistribution
     */
    public function setCmtIncidentDiff($cmtIncidentDiff)
    {
        $this->cmtIncidentDiff = $cmtIncidentDiff;

        return $this;
    }

    /**
     * Get cmtIncidentDiff
     *
     * @return string 
     */
    public function getCmtIncidentDiff()
    {
        return $this->cmtIncidentDiff;
    }

    /**
     * Set dateExport
     *
     * @param \DateTime $dateExport
     * @return CptrDistribution
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
}
