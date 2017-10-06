<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CrmCorrespReponseNeopress
 *
 * @ORM\Table(name="crm_corresp_reponse_neopress")
 * @ORM\Entity
 */
class CrmCorrespReponseNeopress
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
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    private $libelle;
    
     /**
     * @var \CrmReponse
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmReponse")
     * @ORM\JoinColumn(name="crm_reponse_id", referencedColumnName="id", nullable=true)
     */
    private $crmReponse;


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
     * @return CrmCorrespReponseNeopress
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
     * @return CrmCorrespReponseNeopress
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
     * Set crmReponse
     *
     * @param \Ams\DistributionBundle\Entity\CrmReponse $crmReponse
     * @return CrmCorrespReponseNeopress
     */
    public function setCrmReponse(\Ams\DistributionBundle\Entity\CrmReponse $crmReponse = null)
    {
        $this->crmReponse = $crmReponse;

        return $this;
    }

    /**
     * Get crmReponse
     *
     * @return \Ams\DistributionBundle\Entity\CrmReponse 
     */
    public function getCrmReponse()
    {
        return $this->crmReponse;
    }
}
