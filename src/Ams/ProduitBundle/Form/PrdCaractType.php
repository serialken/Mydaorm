<?php

namespace Ams\ProduitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PrdCaractType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('code')
            ->add('produitType')
            ->add('caractType')
            ->add('saisie')
            //->add('unite','text',array('mapped'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\ProduitBundle\Entity\PrdCaract'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_produitbundle_prdcaract';
    }
}
