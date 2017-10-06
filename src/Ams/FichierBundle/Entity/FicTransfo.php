<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index AS index;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * FicTransfo
 * Parametrage des transformations de fichiers a l'entree de MROAD
 *
 * @ORM\Table(name="fic_transfo"
 *              , uniqueConstraints={@UniqueConstraint(name="unique_code",columns={"code"})}
 *              , indexes={ @index(name="idx_code", columns={"code"})
 *                                  }
 *                  )
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicTransfoRepository")
 */
class FicTransfo
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=150, nullable=false)
     */
    private $code;
    
    /**
     * Flux de fichier. Ceci est necessaire afin de connaitre le repertoire de traitement ou se trouvent les fichiers a transformer
     * Exemple de valeur : JADE_CAS, JADE_RECLAM, ...
     * @var string
     *
     * @ORM\Column(name="fic_flux_code", type="string", length=45, nullable=false)
     */
    private $ficFluxCode;

    /**
     * @var string
     *
     * @ORM\Column(name="regex_fic", type="string", length=255, nullable=false)
     */
    private $regexFic;

    /**
     * Format du nom de fichier genere
     * @var string
     *
     * @ORM\Column(name="nom_fic_genere", type="string", length=255, nullable=false)
     */
    private $nomFicGenere;

    /**
     * Commentaire concernant la transformation
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
     * Set code
     *
     * @param string $code
     * @return FicTransfo
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
     * Set ficFluxCode
     *
     * @param string $ficFluxCode
     * @return FicTransfo
     */
    public function setFicFluxCode($ficFluxCode)
    {
        $this->ficFluxCode = $ficFluxCode;

        return $this;
    }

    /**
     * Get ficFluxCode
     *
     * @return string 
     */
    public function getFicFluxCode()
    {
        return $this->ficFluxCode;
    }

    /**
     * Set regexFic
     *
     * @param string $regexFic
     * @return FicTransfo
     */
    public function setRegexFic($regexFic)
    {
        $this->regexFic = $regexFic;

        return $this;
    }

    /**
     * Get regexFic
     *
     * @return string 
     */
    public function getRegexFic()
    {
        return $this->regexFic;
    }

    /**
     * Set nomFicGenere
     *
     * @param string $nomFicGenere
     * @return FicTransfo
     */
    public function setNomFicGenere($nomFicGenere)
    {
        $this->nomFicGenere = $nomFicGenere;

        return $this;
    }

    /**
     * Get nomFicGenere
     *
     * @return string 
     */
    public function getNomFicGenere()
    {
        return $this->nomFicGenere;
    }
    
    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return FicTransfo
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
}
