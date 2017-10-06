<?php

namespace Ams\InvenduBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * Invendu
 *
  * @ORM\Table(name="invendu",
 *              uniqueConstraints={@UniqueConstraint(name="lv_prod_parution",columns={"num_lieu_vente", "code_produit", "date_parution"})}
 *          )
 * @ORM\Entity(repositoryClass="Ams\InvenduBundle\Repository\InvenduRepository")
 * 
 * 
 */
class Invendu {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Ams\InvenduBundle\Entity\LieuVente
     *
     * @ORM\ManyToOne(targetEntity="\Ams\InvenduBundle\Entity\LieuVente")
     * @ORM\JoinColumn(name="num_lieu_vente", referencedColumnName="numero", nullable=false)
     */
    private $lieuVente;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_parution", type="date", nullable=false)
     */
    private $dateParution;

    /**
     * @var string
     *
     * @ORM\Column(name="code_produit", type="string", length=10)
     */
    private $codeProduit;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_produit", type="string", length=50)
     */
    private $libelleProduit;

    /**
     * @var string
     *
     * @ORM\Column(name="code_societe", type="string" ,length=3)
     */
    private $codeSociete;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_societe", type="string", length=50)
     */
    private $libelleSociete;

    /**
     * @var string
     *
     * @ORM\Column(name="code_titre", type="string", length=50)
     */
    private $codeTitre;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="libelle_abrege", type="string", length=20, nullable=true)
     */
    private $libelleAbrege;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte_livree", type="integer", nullable=false)
     */
    private $qteLivree;

    /**
     * @var float
     *
     * @ORM\Column(name="prix", type="float")
     */
    private $prix;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte_invendue", type="integer", nullable=true)
     */
    private $qteInvendu;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="date_import", type="datetime", nullable=false)
     */
    private $dateImport;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateurId", referencedColumnName="id", nullable=true)
     */
    private $utilisateurId;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_export_dcs", type="date", nullable=true)
     */
    private $dateExportDcs;

}
