<?php

namespace Ams\AdresseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdresseType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vol3', 'text', array('label' => "Volet3", 'required' => false, 'attr' => array('size' => '25') ))
            ->add('vol4', 'text', array('label' => "Volet4", 'required' => false, 'attr' => array('size' => '25')))
            ->add('vol5', 'text', array('label' => "Volet5", 'required' => false, 'attr' => array('size' => '10')) )
            ->add('cp', 'text' ,array('max_length' => 5, 'attr' => array('size' => '10')) )
            ->add('ville','text', array( 'required' => false, 'attr' => array('size' => '25')) ) 
               
           // ->add('submit', 'submit', array('label' => 'Rechercher'));
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\AdresseBundle\Entity\Adresse'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_adressebundle_adresse';
    }
}