<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//table qui fait la correspondance entre les codes reclam/rem info  entre NeoPress et Mroad
/**
 * CorespNeoPress 
 *
 * @ORM\Table(name="crm_corresp_demande_neopress")
 * @ORM\Entity
 */
class CrmCorrespDemandeNeopress
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
     * @var \CrmDemande
     *
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmDemande")
     * @ORM\JoinColumn(name="crm_demande_id", referencedColumnName="id", nullable=true)
     */
    private $crmDemande;


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
     * @return CorespNeoPress
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
     * @return CorespNeoPress
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
     * Set crmDemande
     *
     * @param \Ams\DistributionBundle\Entity\CrmDemande $crmDemande
     * @return CorespNeoPress
     */
    public function setCrmDemande(\Ams\DistributionBundle\Entity\CrmDemande $crmDemande = null)
    {
        $this->crmDemande = $crmDemande;

        return $this;
    }

    /**
     * Get crmDemande
     *
     * @return \Ams\DistributionBundle\Entity\CrmDemande 
     */
    public function getCrmDemande()
    {
        return $this->crmDemande;
    }
}
