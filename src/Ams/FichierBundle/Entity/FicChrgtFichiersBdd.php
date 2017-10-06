<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicChrgtFichiersBdd
 *
 * @ORM\Table(name="fic_chrgt_fichiers_bdd")
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicChrgtFichiersBddRepository")
 */
class FicChrgtFichiersBdd
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
     * @var string
     *
     * @ORM\Column(name="fic_code", type="string", length=45, unique=true, nullable=false)
     */
    private $code;

    /**
     * @var \FicFtp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicFtp")
     * @ORM\JoinColumn(name="fic_ftp", referencedColumnName="id", nullable=false)
     */
    private $ficFtp;

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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicSource")
     * @ORM\JoinColumn(name="fic_source", referencedColumnName="id", nullable=false)
     */
    private $ficSource;

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
     * Sous repertoire de traitement en local
     * @var string
     *
     * @ORM\Column(name="ss_rep_traitement", type="string", length=200, nullable=false)
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
     * Set code
     *
     * @param string $code
     * @return FicChrgtFichiersBdd
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
     * Set regexFic
     *
     * @param string $regexFic
     * @return FicChrgtFichiersBdd
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
     * @return FicChrgtFichiersBdd
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
     * @return FicChrgtFichiersBdd
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
     * @return FicChrgtFichiersBdd
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
     * Set trimVal
     *
     * @param string $trimVal
     * @return FicChrgtFichiersBdd
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
     * @return FicChrgtFichiersBdd
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
     * Set ficFtp
     *
     * @param \Ams\FichierBundle\Entity\FicFtp $ficFtp
     * @return FicChrgtFichiersBdd
     */
    public function setFicFtp(\Ams\FichierBundle\Entity\FicFtp $ficFtp)
    {
        $this->ficFtp = $ficFtp;
    
        return $this;
    }

    /**
     * Get ficFlux
     *
     * @return \Ams\FichierBundle\Entity\FicFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set ficFlux
     *
     * @param \Ams\FichierBundle\Entity\FicFlux $flux
     * @return FicChrgtFichiersBdd
     */
    public function setFlux(\Ams\FichierBundle\Entity\FicFlux $flux)
    {
        $this->flux = $flux;
    
        return $this;
    }

    /**
     * Get ficFtp
     *
     * @return \Ams\FichierBundle\Entity\FicFtp 
     */
    public function getFicFtp()
    {
        return $this->ficFtp;
    }

    /**
     * Set ficSource
     *
     * @param \Ams\FichierBundle\Entity\FicSource $ficSource
     * @return FicChrgtFichiersBdd
     */
    public function setFicSource(\Ams\FichierBundle\Entity\FicSource $ficSource)
    {
        $this->ficSource = $ficSource;
    
        return $this;
    }

    /**
     * Get ficSource
     *
     * @return \Ams\FichierBundle\Entity\FicSource 
     */
    public function getFicSource()
    {
        return $this->ficSource;
    }

    /**
     * Set ssRepTraitement
     *
     * @param string $ssRepTraitement
     * @return FicChrgtFichiersBdd
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
}
