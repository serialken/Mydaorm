<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltrePlanningType extends AbstractType {

    private $depots;
    private $fluxs;
    private $anneemois;
    private $employes;
    private $depot_id;
    private $flux_id;
    private $anneemois_id;
    private $employe_id;

    public function __construct($depots = array(), $fluxs = array(), $anneemois = array(), $employes = array(), $depot_id, $flux_id, $anneemois_id, $employe_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->anneemois = $anneemois;
        $this->employes = $employes;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->anneemois_id = $anneemois_id;
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
                ->add('anneemois_id', 'choice', array(
                    'label' => 'Mois',
                    'choices' => $this->anneemois,
                    'required' => true,
                    'data' => $this->anneemois_id,
                ))
                ->add('employe_id', 'choice', array(
                    'label' => 'Employé',
                    'choices' => $this->employes,
                    'required' => false,
                    'data' => $this->employe_id,
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
