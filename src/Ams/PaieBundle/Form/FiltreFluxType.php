<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreFluxType extends AbstractType {

    private $fluxs;
    private $flux_id;

    public function __construct($fluxs = array(), $flux_id) {
        $this->fluxs = $fluxs;
        $this->flux_id = $flux_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
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
