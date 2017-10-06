<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CRM - Referentiel des codes de categorie (reclam ou remontee d'info)
 *
 * @ORM\Table(name="crm_categorie")
 * @ORM\Entity
 */
class CrmCategorie
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", unique=true, type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * Reclamation / Demande Client | Remontee d'information
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;
    

    /**
     * Set id
     *
     * @param integer $id
     * @return CrmCategorie
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * @return CrmCategorie
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
}
