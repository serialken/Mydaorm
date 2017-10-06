<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmpCycle
 *
 * @ORM\Table(name="emp_cycle", uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_cycle", columns={"employe_id", "date_debut"})})
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpCycleRepository")
*/
class EmpCycle
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
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $employe;

    /**
     * @var string
     *
     * @ORM\Column(name="cyc_cod", type="decimal", precision=5, scale=0, nullable=false)
     */
    private $cycCod;

    /**
     * @var string
     *
     * @ORM\Column(name="cycle", type="string", length=7, nullable=false)
     */
    private $cycle;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="lundi", type="boolean", nullable=false)
     */
    private $lundi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mardi", type="boolean", nullable=false)
     */
    private $mardi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mercredi", type="boolean", nullable=false)
     */
    private $mercredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="jeudi", type="boolean", nullable=false)
     */
    private $jeudi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vendredi", type="boolean", nullable=false)
     */
    private $vendredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="samedi", type="boolean", nullable=false)
     */
    private $samedi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dimanche", type="boolean", nullable=false)
     */
    private $dimanche;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;



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
     * Set ddeb
     *
     * @param \DateTime $ddeb
     * @return EmpCycle
     */
    public function setDdeb($ddeb)
    {
        $this->ddeb = $ddeb;

        return $this;
    }

    /**
     * Get ddeb
     *
     * @return \DateTime 
     */
    public function getDdeb()
    {
        return $this->ddeb;
    }

    /**
     * Set dfin
     *
     * @param \DateTime $dfin
     * @return EmpCycle
     */
    public function setDfin($dfin)
    {
        $this->dfin = $dfin;

        return $this;
    }

    /**
     * Get dfin
     *
     * @return \DateTime 
     */
    public function getDfin()
    {
        return $this->dfin;
    }

    /**
     * Set cycCod
     *
     * @param string $cycCod
     * @return EmpCycle
     */
    public function setCycCod($cycCod)
    {
        $this->cycCod = $cycCod;

        return $this;
    }

    /**
     * Get cycCod
     *
     * @return string 
     */
    public function getCycCod()
    {
        return $this->cycCod;
    }

    /**
     * Set lundi
     *
     * @param boolean $lundi
     * @return EmpCycle
     */
    public function setLundi($lundi)
    {
        $this->lundi = $lundi;

        return $this;
    }

    /**
     * Get lundi
     *
     * @return boolean 
     */
    public function getLundi()
    {
        return $this->lundi;
    }

    /**
     * Set mardi
     *
     * @param boolean $mardi
     * @return EmpCycle
     */
    public function setMardi($mardi)
    {
        $this->mardi = $mardi;

        return $this;
    }

    /**
     * Get mardi
     *
     * @return boolean 
     */
    public function getMardi()
    {
        return $this->mardi;
    }

    /**
     * Set mercredi
     *
     * @param boolean $mercredi
     * @return EmpCycle
     */
    public function setMercredi($mercredi)
    {
        $this->mercredi = $mercredi;

        return $this;
    }

    /**
     * Get mercredi
     *
     * @return boolean 
     */
    public function getMercredi()
    {
        return $this->mercredi;
    }

    /**
     * Set jeudi
     *
     * @param boolean $jeudi
     * @return EmpCycle
     */
    public function setJeudi($jeudi)
    {
        $this->jeudi = $jeudi;

        return $this;
    }

    /**
     * Get jeudi
     *
     * @return boolean 
     */
    public function getJeudi()
    {
        return $this->jeudi;
    }

    /**
     * Set vendredi
     *
     * @param boolean $vendredi
     * @return EmpCycle
     */
    public function setVendredi($vendredi)
    {
        $this->vendredi = $vendredi;

        return $this;
    }

    /**
     * Get vendredi
     *
     * @return boolean 
     */
    public function getVendredi()
    {
        return $this->vendredi;
    }

    /**
     * Set samedi
     *
     * @param boolean $samedi
     * @return EmpCycle
     */
    public function setSamedi($samedi)
    {
        $this->samedi = $samedi;

        return $this;
    }

    /**
     * Get samedi
     *
     * @return boolean 
     */
    public function getSamedi()
    {
        return $this->samedi;
    }

    /**
     * Set dimanche
     *
     * @param boolean $dimanche
     * @return EmpCycle
     */
    public function setDimanche($dimanche)
    {
        $this->dimanche = $dimanche;

        return $this;
    }

    /**
     * Get dimanche
     *
     * @return boolean 
     */
    public function getDimanche()
    {
        return $this->dimanche;
    }

    /**
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return EmpCycle
     */
    public function setEmploye(\Ams\EmployeBundle\Entity\Employe $employe = null)
    {
        $this->employe = $employe;

        return $this;
    }

    /**
     * Get employe
     *
     * @return \Ams\EmployeBundle\Entity\Employe 
     */
    public function getEmploye()
    {
        return $this->employe;
    }
}
