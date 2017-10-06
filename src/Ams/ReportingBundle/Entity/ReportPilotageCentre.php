<?php

namespace Ams\ReportingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * report_pilotage_centre
 *
 * @ORM\Table(name="report_pilotage_centre")
 * @ORM\Entity(repositoryClass="Ams\ReportingBundle\Repository\ReportPilotageCentreRepository")
 */
class ReportPilotageCentre
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
     * @var integer
     *
     * @ORM\Column(name="depot", type="string" , length=3)
     */
    private $depot;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_client_Abo", type="integer")
     */
    private $nbClientAbo;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_Ex_Abo", type="integer")
     */
    private $nbExAbo;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_Diff", type="integer")
     */
    private $nbDiff;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_clients_DIV", type="integer")
     */
    private $nbClientsDIV;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_Ex_DIV", type="integer")
     */
    private $nbExDIV;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_ex_en_supplements", type="integer")
     */
    private $nbExEnSupplements;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_adresses", type="integer")
     */
    private $nbAdresses;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_Heures", type="time")
     */
    private $nbHeures;

    /**
     * @var float
     *
     * @ORM\Column(name="Etalon", type="float" , length=6)
     */
    private $etalon;

    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_reclam_brut", type="integer")
     */
    private $nombreReclamBrut;

    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_reclam_net", type="integer")
     */
    private $nombreReclamNet;

    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_reclam_Div_Brut", type="integer")
     */
    private $nombreReclamDivBrut;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_reclam_Div_Net", type="integer")
     */
    private $nombreReclamDivNet;

    /**
     * @var integer
     *
     * @ORM\Column(name="Nb_km", type="integer")
     */
    private $nbKm;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="code_tournee", type="string", length=50)
     */
    private $codeTournee;
    
    /**
     * @var date
     *
     * @ORM\Column(name="date_distrib", type="date")
     */
    private $dateDistrib;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="flux", type="smallint", options={"default":0})
     */
    private $flux;
    
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
     * @param integer $depot
     * @return report_pilotage_centre
     */
    public function setDepot($depot)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return integer 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set nbClientAbo
     *
     * @param integer $nbClientAbo
     * @return report_pilotage_centre
     */
    public function setNbClientAbo($nbClientAbo)
    {
        $this->nbClientAbo = $nbClientAbo;

        return $this;
    }

    /**
     * Get nbClientAbo
     *
     * @return integer 
     */
    public function getNbClientAbo()
    {
        return $this->nbClientAbo;
    }

    /**
     * Set nbExAbo
     *
     * @param integer $nbExAbo
     * @return report_pilotage_centre
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
     * Set nbDiff
     *
     * @param integer $nbDiff
     * @return report_pilotage_centre
     */
    public function setNbDiff($nbDiff)
    {
        $this->nbDiff = $nbDiff;

        return $this;
    }

    /**
     * Get nbDiff
     *
     * @return integer 
     */
    public function getNbDiff()
    {
        return $this->nbDiff;
    }

    /**
     * Set nbClientsDIV
     *
     * @param integer $nbClientsDIV
     * @return report_pilotage_centre
     */
    public function setNbClientsDIV($nbClientsDIV)
    {
        $this->nbClientsDIV = $nbClientsDIV;

        return $this;
    }

    /**
     * Get nbClientsDIV
     *
     * @return integer 
     */
    public function getNbClientsDIV()
    {
        return $this->nbClientsDIV;
    }

    /**
     * Set nbExDIV
     *
     * @param integer $nbExDIV
     * @return report_pilotage_centre
     */
    public function setNbExDIV($nbExDIV)
    {
        $this->nbExDIV = $nbExDIV;

        return $this;
    }

    /**
     * Get nbExDIV
     *
     * @return integer 
     */
    public function getNbExDIV()
    {
        return $this->nbExDIV;
    }

    /**
     * Set nbExEnSupplements
     *
     * @param integer $nbExEnSupplements
     * @return report_pilotage_centre
     */
    public function setNbExEnSupplements($nbExEnSupplements)
    {
        $this->nbExEnSupplements = $nbExEnSupplements;

        return $this;
    }

    /**
     * Get nbExEnSupplements
     *
     * @return integer 
     */
    public function getNbExEnSupplements()
    {
        return $this->nbExEnSupplements;
    }

    /**
     * Set nbAdresses
     *
     * @param integer $nbAdresses
     * @return report_pilotage_centre
     */
    public function setNbAdresses($nbAdresses)
    {
        $this->nbAdresses = $nbAdresses;

        return $this;
    }

    /**
     * Get nbAdresses
     *
     * @return integer 
     */
    public function getNbAdresses()
    {
        return $this->nbAdresses;
    }

    /**
     * Set nbHeures
     *
     * @param integer $nbHeures
     * @return report_pilotage_centre
     */
    public function setNbHeures($nbHeures)
    {
        $this->nbHeures = $nbHeures;

        return $this;
    }

    /**
     * Get nbHeures
     *
     * @return integer 
     */
    public function getNbHeures()
    {
        return $this->nbHeures;
    }

    /**
     * Set etalon
     *
     * @param float $etalon
     * @return report_pilotage_centre
     */
    public function setEtalon($etalon)
    {
        $this->etalon = $etalon;

        return $this;
    }

    /**
     * Get etalon
     *
     * @return float 
     */
    public function getEtalon()
    {
        return $this->etalon;
    }

    /**
     * Set nombreReclamBrut
     *
     * @param integer $nombreReclamBrut
     * @return report_pilotage_centre
     */
    public function setNombreReclamBrut($nombreReclamBrut)
    {
        $this->nombreReclamBrut = $nombreReclamBrut;

        return $this;
    }

    /**
     * Get nombreReclamBrut
     *
     * @return integer 
     */
    public function getNombreReclamBrut()
    {
        return $this->nombreReclamBrut;
    }

    /**
     * Set nombreReclamNet
     *
     * @param integer $nombreReclamNet
     * @return report_pilotage_centre
     */
    public function setNombreReclamNet($nombreReclamNet)
    {
        $this->nombreReclamNet = $nombreReclamNet;

        return $this;
    }

    /**
     * Get nombreReclamNet
     *
     * @return integer 
     */
    public function getNombreReclamNet()
    {
        return $this->nombreReclamNet;
    }

    /**
     * Set nombreReclamDivBrut
     *
     * @param integer $nombreReclamDivBrut
     * @return report_pilotage_centre
     */
    public function setNombreReclamDivBrut($nombreReclamDivBrut)
    {
        $this->nombreReclamDivBrut = $nombreReclamDivBrut;

        return $this;
    }

    /**
     * Get nombreReclamDivBrut
     *
     * @return integer 
     */
    public function getNombreReclamDivBrut()
    {
        return $this->nombreReclamDivBrut;
    }
    
    /**
     * Set nombreReclamDivNet
     *
     * @param integer $nombreReclamDivNet
     * @return report_pilotage_centre
     */
    public function setNombreReclamDivNet($nombreReclamDivNet)
    {
        $this->nombreReclamDivNet = $nombreReclamDivNet;

        return $this;
    }

    /**
     * Get nombreReclamDivNet
     *
     * @return integer 
     */
    public function getNombreReclamDivNet()
    {
        return $this->nombreReclamDivNet;
    }

    /**
     * Set nbKm
     *
     * @param integer $nbKm
     * @return report_pilotage_centre
     */
    public function setNbKm($nbKm)
    {
        $this->nbKm = $nbKm;

        return $this;
    }

    /**
     * Get nbKm
     *
     * @return integer 
     */
    public function getNbKm()
    {
        return $this->nbKm;
    }
    
    /**
     * Set codeTournee
     *
     * @param string $codeTournee
     * @return report_pilotage_centre
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
     * Set dateDistrib
     *
     * @param date $dateDistrib
     * @return report_pilotage_centre
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->dateDistrib = $dateDistrib;

        return $this;
    }

    /**
     * Get dateDistrib
     *
     * @return date 
     */
    public function getDateDistrib()
    {
        return $this->dateDistrib;
    }

    /**
     * Set flux
     *
     * @param integer $flux
     * @return ReportPilotageCentre
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
}
