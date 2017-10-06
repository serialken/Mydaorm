<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicExport
 *
 * @ORM\Table(name="fic_export")
 * @ORM\Entity
 */
class FicExport
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
     * @var string
     *
     * @ORM\Column(name="regex_fic", type="string", length=255, nullable=false)
     */
    private $regexFic;

    /**
     * @var string
     *
     * @ORM\Column(name="format_fic", type="string", nullable=false)
     */
    private $formatFic;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_lignes_ignorees", type="integer", nullable=false)
     */
    private $nbLignesIgnorees;

    /**
     * @var string
     *
     * @ORM\Column(name="separateur", type="string", length=1, nullable=true)
     */
    private $separateur;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_sauvegarde", type="string", length=255)
     */
    private $repSauvegarde;

    /**
     * @var \Ams\FichierBundle\Entity\FicFlux
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     */
    private $flux;
  

    /**
     * @var string
     *
     * @ORM\Column(name="fic_code", type="string", nullable=false)
     */
    private $ficCode;


     /**
     * @var string
     *
     * @ORM\Column(name="trim_val", type="string", nullable=false)
     */
    private $trimVal;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_col", type="integer", nullable=false)
     */
    private $nbCol;

      /**
     * @var string
     *
     * @ORM\Column(name="ss_rep_traitement", type="string", nullable=false)
     */
    private $ssRepTraitement;


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
     * Set regexFic
     *
     * @param string $regexFic
     * @return FicExport
     */
    public function setRegexFic($regexFic)
    {
        $this->regexFic = $regexFic;

        return $this;
    }

    /**
     * Get regexFic
     *
     * @return string 
     */
    public function getRegexFic()
    {
        return $this->regexFic;
    }

    /**
     * Set formatFic
     *
     * @param string $formatFic
     * @return FicExport
     */
    public function setFormatFic($formatFic)
    {
        $this->formatFic = $formatFic;

        return $this;
    }

    /**
     * Get formatFic
     *
     * @return string 
     */
    public function getFormatFic()
    {
        return $this->formatFic;
    }

    /**
     * Set nbLignesIgnorees
     *
     * @param integer $nbLignesIgnorees
     * @return FicExport
     */
    public function setNbLignesIgnorees($nbLignesIgnorees)
    {
        $this->nbLignesIgnorees = $nbLignesIgnorees;

        return $this;
    }

    /**
     * Get nbLignesIgnorees
     *
     * @return integer 
     */
    public function getNbLignesIgnorees()
    {
        return $this->nbLignesIgnorees;
    }

    /**
     * Set separateur
     *
     * @param string $separateur
     * @return FicExport
     */
    public function setSeparateur($separateur)
    {
        $this->separateur = $separateur;

        return $this;
    }

    /**
     * Get separateur
     *
     * @return string 
     */
    public function getSeparateur()
    {
        return $this->separateur;
    }

    /**
     * Set repSauvegarde
     *
     * @param string $repSauvegarde
     * @return FicExport
     */
    public function setRepSauvegarde($repSauvegarde)
    {
        $this->repSauvegarde = $repSauvegarde;

        return $this;
    }

    /**
     * Get repSauvegarde
     *
     * @return string 
     */
    public function getRepSauvegarde()
    {
        return $this->repSauvegarde;
    }

    /**
     * Set trimVal
     *
     * @param string $trimVal
     * @return FicExport
     */
    public function setTrimVal($trimVal)
    {
        $this->trimVal = $trimVal;

        return $this;
    }

    /**
     * Get trimVal
     *
     * @return string 
     */
    public function getTrimVal()
    {
        return $this->trimVal;
    }

    /**
     * Set nbCol
     *
     * @param integer $nbCol
     * @return FicExport
     */
    public function setNbCol($nbCol)
    {
        $this->nbCol = $nbCol;

        return $this;
    }

    /**
     * Get nbCol
     *
     * @return integer 
     */
    public function getNbCol()
    {
        return $this->nbCol;
    }

    /**
     * Set ssRepTraitement
     *
     * @param string $ssRepTraitement
     * @return FicExport
     */
    public function setSsRepTraitement($ssRepTraitement)
    {
        $this->ssRepTraitement = $ssRepTraitement;

        return $this;
    }

    /**
     * Get ssRepTraitement
     *
     * @return string 
     */
    public function getSsRepTraitement()
    {
        return $this->ssRepTraitement;
    }

    /**
     * Set flux
     *
     * @param \Ams\FichierBundle\Entity\FicFlux $flux
     * @return FicExport
     */
    public function setFlux(\Ams\FichierBundle\Entity\FicFlux $flux)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\FichierBundle\Entity\FicFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set ficCode
     *
     * @param string $ficCode
     * @return FicExport
     */
    public function setFicCode($ficCode)
    {
        $this->ficCode = $ficCode;

        return $this;
    }

    /**
     * Get ficCode
     *
     * @return string 
     */
    public function getFicCode()
    {
        return $this->ficCode;
    }
}
