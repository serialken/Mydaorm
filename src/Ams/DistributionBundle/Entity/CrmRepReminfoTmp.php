<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * CRM - Detail des demandes ou des remontees d'info
 *
 * @ORM\Table(name="crm_rep_reminfo_tmp", indexes={@Index(name="idx_crm_id_int", columns={"crm_id_int"})
 *                                              , @Index(name="idx_crm_id_ext", columns={"crm_id_ext"})
 *                                              , @Index(name="idx_numaboext_soc", columns={"numabo_ext","soc_code_ext"})
 *                                              , @Index(name="idx_crm_ext", columns={"soc_code_ext","crm_id_editeur"})
 *                                                      }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\CrmRepReminfoTmpRepository")
 */
class CrmRepReminfoTmp
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
     * ID de la remontee d information dans MROAD
     * @var string
     *
     * @ORM\Column(name="crm_id_int", type="string", length=15, nullable=true)
     */
    private $crmIdInt;

    /**
     * ID de la remontee d information dans JADE
     * @var string
     *
     * @ORM\Column(name="crm_id_ext", type="string", length=15, nullable=true)
     */
    private $crmIdExt;

    /**
     * ID de la remontee d information chez l'editeur
     * @var string
     *
     * @ORM\Column(name="crm_id_editeur", type="string", length=38, nullable=true)
     */
    private $crmIdEditeur;

    /**
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap;

    /**
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=20, nullable=true)
     */
    private $numaboExt;
    
    /**
     * @var integer
     * Si 0, c'est abonne
     * Si 1, c'est Lieu de vente
     * 
     *
     * @ORM\Column(name="client_type", type="integer", nullable=true)
     */
    private $clientType;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=true)
     */
    private $socCodeExt;

    /**
     * @var string
     *
     * @ORM\Column(name="prd_code_ext", type="string", length=20, nullable=true)
     */
    private $prdCodeExt;

    /**
     * Code demande
     * @var string
     *
     * @ORM\Column(name="code_demande", type="string", length=10, nullable=true)
     */
    private $codeDemande;

    /**
     * Commentaire de demande ou de remontee d'info
     * @var string
     *
     * @ORM\Column(name="cmt_demande", type="string", length=255, nullable=true)
     */
    private $cmtDemande;

    /**
     * Code reponse - code de JADE
     * @var string
     *
     * @ORM\Column(name="code_reponse", type="string", length=10, nullable=true)
     */
    private $codeReponse;
    
    /**
     * @var \Ams\DistributionBundle\Entity\CrmReponse
     * @ORM\ManyToOne(targetEntity="\Ams\DistributionBundle\Entity\CrmReponse")
     * @ORM\JoinColumn(name="crm_reponse_id", referencedColumnName="id", nullable=true)
     **/
    private $crmReponse;

    /**
     * Commentaire concernant la reponse
     * @var string
     *
     * @ORM\Column(name="cmt_reponse", type="string", length=255, nullable=true)
     */
    private $cmtReponse;

    /**
     * Date de reponse
     * @var \DateTime
     *
     * @ORM\Column(name="date_reponse", type="datetime", nullable=true)
     */
    private $dateReponse;
    
    /**
     * @var \Ams\AbonneBundle\Entity\AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;

    /**
     * Repondu par ....
     * @var string
     *
     * @ORM\Column(name="utl_reponse", type="string", length=255, nullable=true)
     */
    private $utlReponse;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;
    
    
}
