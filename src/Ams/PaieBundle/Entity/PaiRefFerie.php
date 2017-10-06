<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * PaiRefFerie
 *
 * @ORM\Table(name="pai_ref_ferie"
 *            ,uniqueConstraints={@ORM\UniqueConstraint(name="pai_ref_ferie_uni1", columns={"societe_id", "jfdate"})
 *                     }
 *            )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefFerieRepository")
 */
class PaiRefFerie
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
     * @var \RefSociete
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmpSociete")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $societe;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="jfdate", type="date")
     */
    private $jfDate;


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
     * Set jfDate
     *
     * @param \DateTime $jfDate
     * @return PaiRefFerie
     */
    public function setJfDate($jfDate)
    {
        $this->jfDate = $jfDate;

        return $this;
    }

    /**
     * Get jfDate
     *
     * @return \DateTime 
     */
    public function getJfDate()
    {
        return $this->jfDate;
    }
}
