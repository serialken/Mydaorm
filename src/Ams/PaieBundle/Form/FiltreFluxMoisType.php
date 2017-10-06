<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreFluxMoisType extends AbstractType {

    private $fluxs;
    private $anneemois;
    private $flux_id;
    private $anneemois_id;

    public function __construct($fluxs = array(), $anneemois = array(), $flux_id, $anneemois_id) {
        $this->fluxs = $fluxs;
        $this->anneemois = $anneemois;
        $this->flux_id = $flux_id;
        $this->anneemois_id = $anneemois_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('anneemois_id', 'choice', array(
                    'label' => 'Mois',
                    'choices' => $this->anneemois,
                    'required' => true,
                    'data' => $this->anneemois_id,
                ))
                ->add('flux_id', 'choice', array(
                    'label' => 'Flux',
                    'choices' => $this->fluxs,
                    'required' => true,
                    'data' => $this->flux_id,
                ))
        ;
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
