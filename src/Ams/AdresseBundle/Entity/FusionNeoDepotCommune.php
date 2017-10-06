<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;
use Ams\ExtensionBundle\Validator\Constraints as AmsAssert;
use Doctrine\ORM\Mapping\EntityListeners;
use DateTime;

/**
 * DepotCommune
 *
 * @ORM\Table(name="fusion_neo_depot_commune")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\DepotCommuneRepository")
 * @ORM\EntityListeners({ "Ams\AdresseBundle\Listener\DepotCommuneListener" })
 * 
 */
class FusionNeoDepotCommune
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
     private $id;
    
     /**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
     */
    private $depot;
    
    /**
     * @var \Commune
     *
     * @ORM\ManyToOne(targetEntity="Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;  
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_modif", referencedColumnName="id", nullable=true)
     */
    private $utilisateurModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime", nullable=true)
     * @AmsAssert\DatePosterieure
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=true)
     */
    private $dateFin;
    
    
    /**
     * @var Ams\ReferentielBundle\Entity\RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;

    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=5, nullable=true)
     */
    private $insee;
    
    /**
     * le code ne doit pas faire plus de 3 caracteres
     * @var string
     *
     * @ORM\Column(name="depot_code", type="string", length=3, nullable=true)
     */
    private $depotCode;
    
}