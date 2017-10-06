<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreAnnexeTourneeType extends AbstractType {

    private $depots;
    private $fluxs;
    private $employes;
    private $debut;
    private $fin;
    private $employe_id;
    private $depot_id;
    private $flux_id;

    public function __construct($depots = array(), $fluxs = array(), $employes = array(), $debut, $fin, $depot_id, $flux_id, $employe_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->employes = $employes;
        $this->debut = $debut;
        $this->fin = $fin;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->employe_id = $employe_id;
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
                ->add('debut', 'text', array(
                    'label' => 'Date distrib',
                    'attr' => array('class' => 'date'),
                    'data' => $this->debut
                ))
                ->add('fin', 'text', array(
                    'label' => 'Date fin',
                    'attr' => array('class' => 'date'),
                    'data' => $this->fin
                ))

        ;

        if ($this->employe_id > 0)
            $builder->add('employe_id', 'choice', array(
                'choices' => $this->employes,
                'required' => true,
                'label' => 'Employé',
                'preferred_choices' => array($this->employe_id),
            ))
            ;
        else
            $builder->add('employe_id', 'choice', array(
                'choices' => $this->employes,
                'required' => true,
                'label' => 'Employé',
                'empty_value' => "Pas d'employés",
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
