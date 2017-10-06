<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreInterfaceType extends AbstractType {

    private $anneemois;
    private $interfaces;
    private $anneemois_id;
    private $idtrt;

    public function __construct($anneemois = array(), $interfaces = array(), $anneemois_id, $idtrt) {
        $this->anneemois = $anneemois;
        $this->interfaces = $interfaces;
        $this->anneemois_id = $anneemois_id;
        $this->idtrt = $idtrt;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('anneemois_id', 'choice', array(
                    'label' => 'Mois',
                    'choices' => $this->anneemois,
                    'required' => true,
                    'data' => $this->anneemois_id,
        ));
        if ($this->idtrt > 0)
            $builder->add('idtrt', 'choice', array(
                'label' => 'Interface',
                'choices' => $this->interfaces,
                'required' => true,
                'data' => $this->idtrt,
            ));
        else
            $builder->add('idtrt', 'choice', array(
                'label' => 'Interface',
                'choices' => $this->interfaces,
                'required' => true,
                'empty_value' => "Pas d'interface",
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
