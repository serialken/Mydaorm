<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 * ModeleTourneeTransfert
 * @ORM\Table(name="modele_tournee_transfert"
 * 	     ,uniqueConstraints={@UniqueConstraint(name="un_modele_tournee_transferable_une_fois",columns={"tournee_id_init"})
 *                              , @UniqueConstraint(name="un_modele_tournee_future_unique",columns={"tournee_id_future"})
 *                              }
 *           , indexes={@Index(name="idx_date_application", columns={"date_application"})
 *                     }
 * 	     )                 
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleTourneeTransfertRepository")
 */
class ModeleTourneeTransfert
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
     * Tournee a transferer
     * @var \ModeleTournee
     *
     * @ORM\OneToOne(targetEntity="ModeleTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id_init", referencedColumnName="id", nullable=false)
     * })
     */
    private $tourneeInit;

    /**
     * Tournee future
     * @var \ModeleTournee
     *
     * @ORM\OneToOne(targetEntity="ModeleTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id_future", referencedColumnName="id", nullable=false)
     * })
     */
    private $tourneeFuture;

    /**
     * Date d'application
     * @var \DateTime
     *
     * @ORM\Column(name="date_application", type="date", nullable=false)
     */
    private $dateApplication;
    
    /**
     * Detail des traitements de transfert 
     * @var string
     * @ORM\Column(name="detail", type="text", nullable=true)
     */
    private $detail;

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
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

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
     * Set dateApplication
     *
     * @param \DateTime $dateApplication
     * @return ModeleTourneeTransfert
     */
    public function setDateApplication($dateApplication)
    {
        $this->dateApplication = $dateApplication;

        return $this;
    }

    /**
     * Get dateApplication
     *
     * @return \DateTime 
     */
    public function getDateApplication()
    {
        return $this->dateApplication;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return ModeleTourneeTransfert
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;

        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set tourneeInit
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTournee $tourneeInit
     * @return ModeleTourneeTransfert
     */
    public function setTourneeInit(\Ams\ModeleBundle\Entity\ModeleTournee $tourneeInit)
    {
        $this->tourneeInit = $tourneeInit;

        return $this;
    }

    /**
     * Get tourneeInit
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTournee 
     */
    public function getTourneeInit()
    {
        return $this->tourneeInit;
    }

    /**
     * Set tourneeFuture
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTournee $tourneeFuture
     * @return ModeleTourneeTransfert
     */
    public function setTourneeFuture(\Ams\ModeleBundle\Entity\ModeleTournee $tourneeFuture)
    {
        $this->tourneeFuture = $tourneeFuture;

        return $this;
    }

    /**
     * Get tourneeFuture
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTournee 
     */
    public function getTourneeFuture()
    {
        return $this->tourneeFuture;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return ModeleTourneeTransfert
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
     * Set detail
     *
     * @param string $detail
     * @return ModeleTourneeTransfert
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get detail
     *
     * @return string 
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
