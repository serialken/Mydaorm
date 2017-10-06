<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltrePaiType extends AbstractType {

    private $depots;
    private $fluxs;
    private $depot_id;
    private $flux_id;
    private $date_distrib;

    public function __construct($depots = array(), $fluxs = array(), $depot_id, $flux_id, $date_distrib) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->date_distrib = $date_distrib;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('depot_id', 'choice', array(
                    'label' => 'Dépôt',
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
                ->add('date_distrib', 'text', array(
                    'label' => 'Date distrib',
                    'attr' => array('class' => 'date'),
                    'data' => $this->date_distrib
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
