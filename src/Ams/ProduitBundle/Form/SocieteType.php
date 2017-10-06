<?php

namespace Ams\ProduitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SocieteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder
                ->add('code','text')
                ->add('libelle','text')
                ->add('image','entity',array(
                    'class' => 'AmsExtensionBundle:Fichier',
                    'property' => 'name',
                    'multiple'=> false,
                    'expanded' => false,
                    'required' => true,
                    'empty_value' => 'Choisissez le logo',
                ))
             ;
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Ams\ProduitBundle\Entity\Societe'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_produitbundle_societe';
    }
}
