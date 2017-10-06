<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Referentiel des 
 *
 * @ORM\Table(name="reperage_qualif")
 *  @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefReperageQualifRepository")
 */
class RefReperageQualif
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="topage", type="string", length=1, nullable=false)
     */
    private $topage;
    

    /**
     * Set id
     *
     * @param integer $id
     * @return RefReperageQualif
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
     * @return RefReperageQualif
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
     * Set topage
     *
     * @param string $topage
     * @return RefReperageQualif
     */
    public function setTopage($topage)
    {
        $this->topage = $topage;

        return $this;
    }

    /**
     * Get topage
     *
     * @return string 
     */
    public function getTopage()
    {
        return $this->topage;
    }
}
