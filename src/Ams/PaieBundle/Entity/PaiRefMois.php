<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefMois
 *
 * @ORM\Table(name="pai_ref_mois"
 *                ,indexes={@ORM\Index(name="idx1_pai_ref_mois", columns={"date_debut","date_fin"})
 *                          }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefMoisRepository")
 */
class PaiRefMois
{
    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $anneemois;
    /**
     * @var string
     *
     * @ORM\Column(name="annee", type="string", length=4, nullable=false)
     */
    private $annee;

    /**
     * @var string
     *
     * @ORM\Column(name="mois", type="string", length=2, nullable=false)
     */
    private $mois;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;


    /**
     * Set anneemois
     *
     * @param string $anneemois
     * @return PaiRefMois
     */
    public function setAnneemois($anneemois)
    {
        $this->anneemois = $anneemois;

        return $this;
    }

    /**
     * Get anneemois
     *
     * @return string 
     */
    public function getAnneemois()
    {
        return $this->anneemois;
    }

    /**
     * Set annee
     *
     * @param string $annee
     * @return PaiRefMois
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return string 
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set mois
     *
     * @param string $mois
     * @return PaiRefMois
     */
    public function setMois($mois)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get mois
     *
     * @return string 
     */
    public function getMois()
    {
        return $this->mois;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return PaiRefMois
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
     * Set date_debut
     *
     * @param \DateTime $dateDebut
     * @return PaiRefMois
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
     * @return PaiRefMois
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
     * Set date_extrait
     *
     * @param \DateTime $dateExtrait
     * @return PaiRefMois
     */
    public function setDateExtrait($dateExtrait)
    {
        $this->date_extrait = $dateExtrait;

        return $this;
    }

    /**
     * Get date_extrait
     *
     * @return \DateTime 
     */
    public function getDateExtrait()
    {
        return $this->date_extrait;
    }
}
