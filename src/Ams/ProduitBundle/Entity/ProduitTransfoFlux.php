<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * ProduitTransfoFlux
 * Correspondance de transformation d un produit selon le flux
 * 
 *
 * @ORM\Table(name="produit_transfo_flux",
 *              uniqueConstraints={@UniqueConstraint(name="produit_unique",columns={"produit_id_init","flux_id"})}
 *              )
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\ProduitTransfoFluxRepository")
 */
class ProduitTransfoFlux
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
     * @var Ams\ProduitBundle\Entity\Produit
      * Le produit initial a transformer
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id_init", referencedColumnName="id", nullable=false)
     */
    private $produitInit;

    /**
     * @var Ams\ReferentielBundle\Entity\RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     */
    private $flux;
    
     /**
     * @var Ams\ProduitBundle\Entity\Produit
      * Le produit transforme selon de flux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id_transfo", referencedColumnName="id", nullable=false)
     */
    private $produitTransfo;
}
