<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Etats des traitements de prospection
 INSERT INTO france_routage_prospection_etat 
	(id, libelle, etat)
VALUES 
	(0, 'Non traité', '-')
	, (11, 'Transformation pour format MROAD - En cours', '-')
	, (15, 'Transformation pour format MROAD - OK', 'OK')
	, (16, 'Transformation pour format MROAD - KO', 'KO')
	, (21, 'Récupération du fichier au format MROAD - En cours', '-')
	, (25, 'Récupération du fichier au format MROAD - OK', 'OK')
	, (26, 'Récupération du fichier au format MROAD - KO', 'KO')
	, (31, 'Comparaison d''adresses - En cours', '-')
	, (36, 'Comparaison d''adresses - KO', 'KO')
	, (50, 'Annulé', '-')
	, (90, 'Terminé', 'OK')
 *
 * @ORM\Table(name="france_routage_prospection_etat"
 *                          )
 * @ORM\Entity
 */
class FranceRoutageProspectionEtat
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
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=10, nullable=false)
     */
    private $etat;
    
    

    /**
     * Set id
     *
     * @param integer $id
     * @return FranceRoutageProspectionEtat
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
     * Set libelle
     *
     * @param string $libelle
     * @return FranceRoutageProspectionEtat
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
     * Set etat
     *
     * @param string $etat
     * @return FranceRoutageProspectionEtat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string 
     */
    public function getEtat()
    {
        return $this->etat;
    }
}
