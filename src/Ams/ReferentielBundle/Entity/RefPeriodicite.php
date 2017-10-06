<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefPeriodicite
 *
 * @ORM\Table(name="ref_periodicite")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefPeriodiciteRepository")
 */
class RefPeriodicite
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
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="export_nb_sem", type="integer")
     */
    private $exportNbSem;


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
     * Set libelle
     *
     * @param string $libelle
     * @return Periodicite
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
     * Set exportNbSem
     *
     * @param integer $exportNbSem
     * @return Periodicite
     */
    public function setExportNbSem($exportNbSem)
    {
        $this->exportNbSem = $exportNbSem;

        return $this;
    }

    /**
     * Get exportNbSem
     *
     * @return integer 
     */
    public function getExportNbSem()
    {
        return $this->exportNbSem;
    }
}
