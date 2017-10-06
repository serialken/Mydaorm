<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreMoisType extends AbstractType {

    private $anneemois;
    private $anneemois_id;

    public function __construct($anneemois = array(), $anneemois_id) {
        $this->anneemois = $anneemois;
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
