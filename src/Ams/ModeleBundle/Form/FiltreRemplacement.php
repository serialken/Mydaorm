<?php

namespace Ams\ModeleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreRemplacement extends AbstractType {

    private $depots;
    private $fluxs;
    private $anneemois;
    private $remplacements;
    private $depot_id;
    private $flux_id;
    private $anneemois_id;
    private $remplacement_id;

    public function __construct($depots = array(), $fluxs = array(), $anneemois = array(), $remplacements = array(), $depot_id, $flux_id, $anneemois_id, $remplacement_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->anneemois = $anneemois;
        $this->remplacements = $remplacements;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->anneemois_id = $anneemois_id;
        $this->remplacement_id = $remplacement_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('depot_id', 'choice', array(
                    'label' => 'Dépot',
                    'choices' => $this->depots,
                    'required' => true,
                    'data' => $this->depot_id,
                ))
                ->add('flux_id', 'choice', array(
                    'label' => 'Flux',
                    'choices' => $this->fluxs,
                    'required' => true,
                    'data' => $this->flux_id,
                ))
                ->add('anneemois_id', 'choice', array(
                    'label' => 'Mois',
                    'choices' => $this->anneemois,
                    'required' => true,
                    'data' => $this->anneemois_id,
                ))
                ->add('remplacement_id', 'choice', array(
                    'label' => 'Mois',
                    'choices' => $this->remplacements,
                    'required' => true,
                    'data' => $this->remplacement_id,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => NULL
        ));
    }

    public function getName() {
        return 'form_filtre';
    }

}
