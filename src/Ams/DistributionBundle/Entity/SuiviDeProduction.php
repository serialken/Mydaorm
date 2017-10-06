<?php
/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 12/05/2017
 * Time: 17:33
 */

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SuiviDeProduction
 * @package Ams\DistributionBundle\Entity
 *
 * @ORM\Table(name="suivi_de_production")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\SuiviDeProductionRepository")
 */
class SuiviDeProduction
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_edi", type="datetime", nullable=false)
     */
    private $dateEdi;
    /**
     * @var string
     *
     * @ORM\Column(name="libelle_edi", type="string", length=45, nullable=false)
     */
    private $libelleEdi;
    /**
     * @var string
     *
     * @ORM\Column(name="code_route", type="string", length=10, nullable=false)
     */
    private $codeRoute;
    /**
     * @var string
     *
     * @ORM\Column(name="libelle_route", type="string", length=45, nullable=true)
     */
    private $libelleRoute;

    /**
     * @var integer
     *
     * @ORM\Column(name="pqt_prev", type="integer", nullable=false)
     */
    private $pqtPrev;

    /**
     * @var integer
     *
     * @ORM\Column(name="pqt_eject", type="integer", nullable=false)
     */
    private $pqtEject;

    /**
     * @var integer
     *
     * @ORM\Column(name="ex_prev", type="integer", nullable=false)
     */
    private $exPrev;

    /**
     * @var integer
     *
     * @ORM\Column(name="ex_eject", type="integer", nullable=false)
     */
    private $exEject;

    /**
     * @var \Ams\FichierBundle\Entity\FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_up", type="datetime", nullable=true)
     */
    private $dateMod;


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
     * Set dateEdi
     *
     * @param \DateTime $dateEdi
     * @return SuiviDeProduction
     */
    public function setDateEdi($dateEdi)
    {
        $this->dateEdi = $dateEdi;

        return $this;
    }

    /**
     * Get dateEdi
     *
     * @return \DateTime 
     */
    public function getDateEdi()
    {
        return $this->dateEdi;
    }

    /**
     * Set libelleEdi
     *
     * @param string $libelleEdi
     * @return SuiviDeProduction
     */
    public function setLibelleEdi($libelleEdi)
    {
        $this->libelleEdi = $libelleEdi;

        return $this;
    }

    /**
     * Get libelleEdi
     *
     * @return string 
     */
    public function getLibelleEdi()
    {
        return $this->libelleEdi;
    }

    /**
     * Set codeRoute
     *
     * @param string $codeRoute
     * @return SuiviDeProduction
     */
    public function setCodeRoute($codeRoute)
    {
        $this->codeRoute = $codeRoute;

        return $this;
    }

    /**
     * Get codeRoute
     *
     * @return string 
     */
    public function getCodeRoute()
    {
        return $this->codeRoute;
    }

    /**
     * Set libelleRoute
     *
     * @param string $libelleRoute
     * @return SuiviDeProduction
     */
    public function setLibelleRoute($libelleRoute)
    {
        $this->libelleRoute = $libelleRoute;

        return $this;
    }

    /**
     * Get libelleRoute
     *
     * @return string 
     */
    public function getLibelleRoute()
    {
        return $this->libelleRoute;
    }

    /**
     * Set pqtPrev
     *
     * @param integer $pqtPrev
     * @return SuiviDeProduction
     */
    public function setPqtPrev($pqtPrev)
    {
        $this->pqtPrev = $pqtPrev;

        return $this;
    }

    /**
     * Get pqtPrev
     *
     * @return integer 
     */
    public function getPqtPrev()
    {
        return $this->pqtPrev;
    }

    /**
     * Set pqtEject
     *
     * @param integer $pqtEject
     * @return SuiviDeProduction
     */
    public function setPqtEject($pqtEject)
    {
        $this->pqtEject = $pqtEject;

        return $this;
    }

    /**
     * Get pqtEject
     *
     * @return integer 
     */
    public function getPqtEject()
    {
        return $this->pqtEject;
    }

    /**
     * Set exPrev
     *
     * @param integer $exPrev
     * @return SuiviDeProduction
     */
    public function setExPrev($exPrev)
    {
        $this->exPrev = $exPrev;

        return $this;
    }

    /**
     * Get exPrev
     *
     * @return integer 
     */
    public function getExPrev()
    {
        return $this->exPrev;
    }

    /**
     * Set exEject
     *
     * @param integer $exEject
     * @return SuiviDeProduction
     */
    public function setExEject($exEject)
    {
        $this->exEject = $exEject;

        return $this;
    }

    /**
     * Get exEject
     *
     * @return integer 
     */
    public function getExEject()
    {
        return $this->exEject;
    }


    /**
     * Set dateMod
     *
     * @param \DateTime $dateMod
     * @return SuiviDeProduction
     */
    public function setDateMod($dateMod)
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    /**
     * Get dateMod
     *
     * @return \DateTime 
     */
    public function getDateMod()
    {
        return $this->dateMod;
    }

    /**
     * Set ficRecap
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap
     * @return SuiviDeProduction
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
}
