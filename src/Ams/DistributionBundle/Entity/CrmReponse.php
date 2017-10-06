<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * CRM - Referentiel des codes de reponse
 *
 * @ORM\Table(name="crm_reponse")
 * @ORM\Entity
 */
class CrmReponse
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
     * @ORM\Column(name="code", type="string", length=10, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;

    /**
     * Categorie : Reclam | Remontee d'info
     * @var \Ams\DistributionBundle\Entity\CrmCategorie
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmCategorie")
     * @ORM\JoinColumn(name="crm_categorie_id", referencedColumnName="id", nullable=true)
     **/
    private $crmCategorie;

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
     * @return CrmReponse
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
     * @return CrmReponse
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
     * Set crmCategorie
     *
     * @param \Ams\DistributionBundle\Entity\CrmCategorie $crmCategorie
     * @return CrmReponse
     */
    public function setCrmCategorie(\Ams\DistributionBundle\Entity\CrmCategorie $crmCategorie = null)
    {
        $this->crmCategorie = $crmCategorie;

        return $this;
    }

    /**
     * Get crmCategorie
     *
     * @return \Ams\DistributionBundle\Entity\CrmCategorie 
     */
    public function getCrmCategorie()
    {
        return $this->crmCategorie;
    }
}
