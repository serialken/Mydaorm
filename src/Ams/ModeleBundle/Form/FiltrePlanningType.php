<?php

namespace Ams\ModeleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltrePlanningType extends AbstractType {

    private $depots;
    private $fluxs;
    private $employes;
    private $depot_id;
    private $flux_id;
    private $employe_id;

    public function __construct($depots = array(), $fluxs = array(), $employes = array(), $depot_id, $flux_id, $employe_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->employes = $employes;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->employe_id = $employe_id;
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
                ->add('employe_id', 'choice', array(
                    'label' => 'Employé',
                    'choices' => $this->employes,
                    'required' => true,
                    'data' => $this->employe_id,
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => NULL
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'form_filtre';
    }

}
