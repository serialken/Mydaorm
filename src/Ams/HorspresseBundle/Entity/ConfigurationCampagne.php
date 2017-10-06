<?php

namespace Ams\HorspresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigurationCampagne
 *
 * @ORM\Table("config_campagne_hp")
 * @ORM\Entity(repositoryClass="Ams\HorspresseBundle\Entity\ConfigurationCampagneRepository")
 */
class ConfigurationCampagne
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
     *
     * @ORM\OneToOne(
     *      targetEntity="Ams\HorspresseBundle\Entity\Fichier",
     * )
     */
    private $fichier;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fichierNominatif", type="boolean")
     */
    private $fichierNominatif;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fichierQte", type="boolean")
     */
    private $fichierQte;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fichierNbBal", type="boolean")
     */
    private $fichierNbBal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="chargeTournees", type="boolean")
     */
    private $chargeTournees;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tourneesDediees", type="boolean")
     */
    private $tourneesDediees;

    /**
     * @var integer
     *
     * @ORM\Column(name="chargeTourneesMax", type="integer")
     */
    private $chargeTourneesMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="chargeTourneesDedieesMax", type="integer", nullable=true)
     */
    private $chargeTourneesDedieesMax;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="periodeHistorique", type="integer")
     */
    private $periodeHistorique;

    /**
     * @var array
     *
     * @ORM\Column(name="structureFichier", type="json_array", nullable=true)
     */
    private $structureFichier;

    /**
     * @var integer
     *
     * @ORM\Column(name="tempsSup", type="integer")
     */
    private $tempsSup;

    /**
     * @var array
     *
     * @ORM\Column(name="flux", type="array")
     */
    private $flux;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;
    
    /**
     *
     * @ORM\ManyToOne(
     *      targetEntity="Ams\HorspresseBundle\Entity\Campagne",
     *      inversedBy="configurations"
     * )
     */
    private $campagne;


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
     * Set fichierNominatif
     *
     * @param boolean $fichierNominatif
     * @return ConfigurationCampagne
     */
    public function setFichierNominatif($fichierNominatif)
    {
        $this->fichierNominatif = $fichierNominatif;

        return $this;
    }

    /**
     * Get fichierNominatif
     *
     * @return boolean 
     */
    public function getFichierNominatif()
    {
        return $this->fichierNominatif;
    }

    /**
     * Set fichierQte
     *
     * @param boolean $fichierQte
     * @return ConfigurationCampagne
     */
    public function setFichierQte($fichierQte)
    {
        $this->fichierQte = $fichierQte;

        return $this;
    }

    /**
     * Get fichierQte
     *
     * @return boolean 
     */
    public function getFichierQte()
    {
        return $this->fichierQte;
    }

    /**
     * Set fichierNbBal
     *
     * @param boolean $fichierNbBal
     * @return ConfigurationCampagne
     */
    public function setFichierNbBal($fichierNbBal)
    {
        $this->fichierNbBal = $fichierNbBal;

        return $this;
    }

    /**
     * Get fichierNbBal
     *
     * @return boolean 
     */
    public function getFichierNbBal()
    {
        return $this->fichierNbBal;
    }

    /**
     * Set chargeTournees
     *
     * @param boolean $chargeTournees
     * @return ConfigurationCampagne
     */
    public function setChargeTournees($chargeTournees)
    {
        $this->chargeTournees = $chargeTournees;

        return $this;
    }

    /**
     * Get chargeTournees
     *
     * @return boolean 
     */
    public function getChargeTournees()
    {
        return $this->chargeTournees;
    }

    /**
     * Set tourneesDediees
     *
     * @param boolean $tourneesDediees
     * @return ConfigurationCampagne
     */
    public function setTourneesDediees($tourneesDediees)
    {
        $this->tourneesDediees = $tourneesDediees;

        return $this;
    }

    /**
     * Get tourneesDediees
     *
     * @return boolean 
     */
    public function getTourneesDediees()
    {
        return $this->tourneesDediees;
    }

    /**
     * Set chargeTourneesMax
     *
     * @param integer $chargeTourneesMax
     * @return ConfigurationCampagne
     */
    public function setChargeTourneesMax($chargeTourneesMax)
    {
        $this->chargeTourneesMax = $chargeTourneesMax;

        return $this;
    }

    /**
     * Get chargeTourneesMax
     *
     * @return integer 
     */
    public function getChargeTourneesMax()
    {
        return $this->chargeTourneesMax;
    }

    /**
     * Set chargeTourneesDedieesMax
     *
     * @param integer $chargeTourneesDedieesMax
     * @return ConfigurationCampagne
     */
    public function setChargeTourneesDedieesMax($chargeTourneesDedieesMax)
    {
        $this->chargeTourneesDedieesMax = $chargeTourneesDedieesMax;

        return $this;
    }

    /**
     * Get chargeTourneesDedieesMax
     *
     * @return integer 
     */
    public function getChargeTourneesDedieesMax()
    {
        return $this->chargeTourneesDedieesMax;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return ConfigurationCampagne
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set periodeHistorique
     *
     * @param integer $periodeHistorique
     * @return ConfigurationCampagne
     */
    public function setPeriodeHistorique($periodeHistorique)
    {
        $this->periodeHistorique = $periodeHistorique;

        return $this;
    }

    /**
     * Get periodeHistorique
     *
     * @return integer 
     */
    public function getPeriodeHistorique()
    {
        return $this->periodeHistorique;
    }

    /**
     * Set structureFichier
     *
     * @param array $structureFichier
     * @return ConfigurationCampagne
     */
    public function setStructureFichier($structureFichier)
    {
        $this->structureFichier = $structureFichier;

        return $this;
    }

    /**
     * Get structureFichier
     *
     * @return array 
     */
    public function getStructureFichier()
    {
        return $this->structureFichier;
    }

    /**
     * Set tempsSup
     *
     * @param integer $tempsSup
     * @return ConfigurationCampagne
     */
    public function setTempsSup($tempsSup)
    {
        $this->tempsSup = $tempsSup;

        return $this;
    }

    /**
     * Get tempsSup
     *
     * @return integer 
     */
    public function getTempsSup()
    {
        return $this->tempsSup;
    }

    /**
     * Set flux
     *
     * @param array $flux
     * @return ConfigurationCampagne
     */
    public function setFlux($flux)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return array 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return ConfigurationCampagne
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set campagne
     *
     * @param \Ams\HorspresseBundle\Entity\Campagne $campagne
     * @return ConfigurationCampagne
     */
    public function setCampagne(\Ams\HorspresseBundle\Entity\Campagne $campagne = null)
    {
        $this->campagne = $campagne;

        return $this;
    }

    /**
     * Get campagne
     *
     * @return \Ams\HorspresseBundle\Entity\Campagne 
     */
    public function getCampagne()
    {
        return $this->campagne;
    }

    /**
     * Set fichier
     *
     * @param \Ams\HorspresseBundle\Entity\Fichier $fichier
     * @return ConfigurationCampagne
     */
    public function setFichier(\Ams\HorspresseBundle\Entity\Fichier $fichier = null)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return \Ams\HorspresseBundle\Entity\Fichier 
     */
    public function getFichier()
    {
        return $this->fichier;
    }
}
