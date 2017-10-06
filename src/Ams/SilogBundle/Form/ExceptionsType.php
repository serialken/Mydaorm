<?php

namespace Ams\SilogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\AdresseBundle\Repository\CommuneRepository;
use Ams\ProduitBundle\Repository\ProduitRepository;
use \Ams\ProduitBundle\Repository\SocieteRepository;
use DateTime;
use DateInterval;
/**
 * formulaire d'ajout ou modification d'une repartition
 */
class ExceptionsType extends AbstractType {

    private $depot_id;
    private $dpts;
    private $sType;

    public function __construct($depot_id, $dpts, $date_fin, $sType) {
        $this->depot_id = $depot_id;
        $this->date_fin = $date_fin;
        $this->dpts = $dpts;
        $this->sType = $sType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $now = new \DateTime();
        $minDate = $now->add(new DateInterval('P3D'));
        $builder
                ->add('depotId', 'hidden', array(
                    'data' => $this->depot_id,
                ))
                ->add('sType', 'hidden', array(
                    'data' => $this->sType,
        ));

        if ($this->sType == 'produit') {
            $builder->add('produit', 'entity', array(
                'label' => 'Produit',
                'class' => 'AmsProduitBundle:Produit',
                'property' => 'libelle',
                'empty_value' => 'Choisir un produit',
                'query_builder' => function(ProduitRepository $sr) {
            return $sr->getProduitListe();
        },
                'multiple' => false
                    )
            );
        } else if ($this->sType == 'societe') {
            $builder->add('societe', 'entity', array(
                        'label' => 'Société',
                        'class' => 'AmsProduitBundle:Societe',
                        'property' => 'libelle',
                        'empty_value' => 'Société',
                        'query_builder' => function(SocieteRepository $sr) {
                    return $sr->getSocietesLibelles();
                },
                        'multiple' => false
                            )
                    )
                    ->add('flux', 'entity', array(
                        'class' => 'AmsReferentielBundle:RefFlux',
                        'property' => 'libelle',
                        'required' => true,
                        'multiple' => false
                            )
            );
        } else if ($this->sType == 'global') {
            $builder->add('flux', 'entity', array(
                'class' => 'AmsReferentielBundle:RefFlux',
                'property' => 'libelle',
                'required' => true,
                'multiple' => false
                    )
            );
        }

        $builder->add('dpt', 'choice', array(
                    'label' => 'Département',
                    'required' => true,
                    'choices' => $this->dpts,
                    'multiple' => false,
                    'expanded' => false,
                    'empty_value' => 'Choisir un département'
                        )
                )
                ->add('commune', 'entity', array(
                    'class' => 'AmsAdresseBundle:Commune',
                    'query_builder' => function(CommuneRepository $r) {
                return $r->getCommuneDepot($this->depot_id, $this->date_fin);
            },
                    'property' => 'libelle',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,
                ))
                ->add('dateDebut', 'date', array(
                    'widget' => 'single_text',
                    'data' => $minDate,
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'date'),
                ))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ams_exception';
    }

}
