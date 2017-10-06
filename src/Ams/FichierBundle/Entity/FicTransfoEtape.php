<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * FicTransfoEtape
 * La liste des etapes de transformation de fichier
 *
 * @ORM\Table(name="fic_transfo_etape"
 *              , uniqueConstraints={@UniqueConstraint(name="code_regex_methode",columns={"fic_transfo_id", "ordre"})}
 *                  )
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicTransfoEtapeRepository")
 */
class FicTransfoEtape
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
     * @var \Ams\FichierBundle\Entity\FicTransfo
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicTransfo")
     * @ORM\JoinColumn(name="fic_transfo_id", referencedColumnName="id", nullable=false)
     */
    private $ficTransfo;

    /**
     * Le nom de la methode a appeler. La classe de cette methode depend du flux
     * @var string
     *
     * @ORM\Column(name="methode_nom", type="string", length=255, nullable=false)
     */
    private $methodeNom;

    /**
     * Exemples du contenu de ce champ : 
     *      Expl 1 : $decalDate="-2"; // on decale un champ de date a -2 (valeur de $decalDate) [Si date_distrib='2015-06-02', elle devient '2015-05-31' si le decalage est en jour]
     *      Expl 2 : $nbJourRef="-1";$comp="<="; // on fait une action pour les lignes repondant aux criteres : dates inferieures ou egales (valeur de $comp) a J-1 (valeur de $nbJourRef)
     * @var string
     *
     * @ORM\Column(name="methode_param", type="string", length=255, nullable=true)
     */
    private $methodeParam;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false, options={"default":1})
     */
    private $ordre;  
   
    /**
     * @var boolean
     *
     * @ORM\Column(name="actif", type="smallint", nullable=false, options={"default":1})
     */
    private $actif;

    /**
     * Commentaires de chaque etape
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;  
    

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
     * Set methodeNom
     *
     * @param string $methodeNom
     * @return FicTransfoEtape
     */
    public function setMethodeNom($methodeNom)
    {
        $this->methodeNom = $methodeNom;

        return $this;
    }

    /**
     * Get methodeNom
     *
     * @return string 
     */
    public function getMethodeNom()
    {
        return $this->methodeNom;
    }

    /**
     * Set methodeParam
     *
     * @param string $methodeParam
     * @return FicTransfoEtape
     */
    public function setMethodeParam($methodeParam)
    {
        $this->methodeParam = $methodeParam;

        return $this;
    }

    /**
     * Get methodeParam
     *
     * @return string 
     */
    public function getMethodeParam()
    {
        return $this->methodeParam;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return FicTransfoEtape
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     * @return FicTransfoEtape
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return integer 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return FicTransfoEtape
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set ficTransfo
     *
     * @param \Ams\FichierBundle\Entity\FicTransfo $ficTransfo
     * @return FicTransfoEtape
     */
    public function setFicTransfo(\Ams\FichierBundle\Entity\FicTransfo $ficTransfo)
    {
        $this->ficTransfo = $ficTransfo;

        return $this;
    }

    /**
     * Get ficTransfo
     *
     * @return \Ams\FichierBundle\Entity\FicTransfo 
     */
    public function getFicTransfo()
    {
        return $this->ficTransfo;
    }
}
