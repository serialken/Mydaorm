<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrdCaractType
 *
 * @ORM\Table(name="prd_caract_type")
 * @ORM\Entity
 */
class PrdCaractType
{
    const CODE_TYPE_FLOAT       = "float";
    const CODE_TYPE_INT         = "int";
    const CODE_TYPE_VARCHAR     = "varchar";
    const CODE_TYPE_DATE        = "date";
    const CODE_TYPE_DATETIME    = "datetime";
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, nullable=false)
     */
    private $code;


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
     * @return PrdCaractType
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
     * Set code
     *
     * @param string $code
     * @return PrdCaractType
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
    
    public function getSymfonyFieldType()
    {
        $symfonyFieldType = null;
        
        switch ($this->getCode()) {
            case self::CODE_TYPE_INT :
                $symfonyFieldType = "integer";
                break;
            case self::CODE_TYPE_FLOAT :
                $symfonyFieldType = "number";
                break;
            case self::CODE_TYPE_VARCHAR :
                $symfonyFieldType = "text";
                break;
            case self::CODE_TYPE_DATE :
                $symfonyFieldType = "date";
                break;
            case self::CODE_TYPE_DATETIME :
                $symfonyFieldType = "datetime";
                break;
        }
        
        return $symfonyFieldType;
    }
    
    public function getFieldTypeOptions() {
        $options = array();
        
        switch ($this->getCode()) {
            case self::CODE_TYPE_DATE :
                $options = array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'date'),
                    //'constraints' => array()
                );
                break;
            case self::CODE_TYPE_DATETIME :
                $options = array(
                    //'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy HH:mm',
                    //'attr' => array('class' => 'date'),
                    //'constraints' => array()
                );
                break;
        }
        
        return $options;
    }
    
    public function __toString() {
        return $this->getLibelle();
    }
}
