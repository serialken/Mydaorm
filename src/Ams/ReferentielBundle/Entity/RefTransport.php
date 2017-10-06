<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefTransport
 *
 * @ORM\Table(name="ref_transport")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefTransportRepository")
 */
class RefTransport
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
     * @ORM\Column(name="code", type="string", length=3, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * @var boolean
     *
     * @ORM\Column(name="km_paye", type="boolean", nullable=false)
     */
    private $km_paye;

    /**
     * Set id
     *
     * @param integer $id
     * @return RefTransport
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
     * Set code
     *
     * @param string $code
     * @return RefTransport
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
     * Set libelle
     *
     * @param string $libelle
     * @return RefTransport
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
     * Set km_paye
     *
     * @param boolean $kmPaye
     * @return RefTransport
     */
    public function setKmPaye($kmPaye)
    {
        $this->km_paye = $kmPaye;

        return $this;
    }

    /**
     * Get km_paye
     *
     * @return boolean 
     */
    public function getKmPaye()
    {
        return $this->km_paye;
    }
}
