<?php

namespace Ams\PaieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreDateType extends AbstractType {

    private $date_distrib;

    public function __construct($date_distrib) {
        $this->date_distrib = $date_distrib;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
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
