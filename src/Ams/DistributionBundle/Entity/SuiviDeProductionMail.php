<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SuiviDeProductionMail
 * @package Ams\DistributionBundle\Entity
 *
 * @ORM\Table(name="suivi_de_production_mail")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\SuiviDeProductionMailRepository")
 */
class SuiviDeProductionMail
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
     * @ORM\Column(name="nom", type="string", length=45, nullable=false)
     */
    private $nom;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_edition", type="date", nullable=false)
     */
    private $dateEdition;
    
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="envoyer", type="boolean")
     */
    private $envoyer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_envoi", type="datetime", nullable=true)
     */
    private $dateEnvoi;

   /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=100, nullable=false)
     */
    private $etat;

    

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
     * Set nom
     *
     * @param string $nom
     * @return SuiviDeProductionMail
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set dateEdition
     *
     * @param \DateTime $dateEdition
     * @return SuiviDeProductionMail
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition
     *
     * @return \DateTime 
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set envoyer
     *
     * @param boolean $envoyer
     * @return SuiviDeProductionMail
     */
    public function setEnvoyer($envoyer)
    {
        $this->envoyer = $envoyer;

        return $this;
    }

    /**
     * Get envoyer
     *
     * @return boolean 
     */
    public function getEnvoyer()
    {
        return $this->envoyer;
    }

    /**
     * Set dateEnvoi
     *
     * @param \DateTime $dateEnvoi
     * @return SuiviDeProductionMail
     */
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * Get dateEnvoi
     *
     * @return \DateTime 
     */
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return SuiviDeProductionMail
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string 
     */
    public function getEtat()
    {
        return $this->etat;
    }
}
