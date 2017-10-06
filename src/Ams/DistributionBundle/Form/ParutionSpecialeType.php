<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParutionSpecialeType extends AbstractType
{    
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
               
                ->add('dateParution', 'date', array(
                        'label' => 'Date',
                        'widget' => 'single_text',
                        'input' => 'datetime',
                        'format' => 'dd/MM/yyyy',
                        'attr' => array('class' => 'date' )))
               //  ->add('libelleCourt', 'text')
                
                 ->add('libelle', 'textarea', array(
                     'attr' => array('cols' => 30)  
                  ))
                ->add('evenement', 'entity', array(
                     'class' => 'AmsReferentielBundle:RefEvenement',
                     'required' => true,
                     'property' => 'libelle',
                     'empty_value' => 'Choisissez un evenement'
                ))
                ->add('produit', 'entity', array(
                     'class' => 'AmsProduitBundle:Produit',
                     'required' => true,
                     'property' => 'libelle',
                     'empty_value' => 'Choisissez un produit'
                ))
                
              /*   ->add('conditionnement', 'textarea', array(
                    'attr' => array('cols' => 30)  
                 ))*/
                
                 ->add('zoneDistribution', 'textarea', array(
                    'attr' => array('cols' => 30)
                 ))
                
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\ParutionSpeciale'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_parutionspeciale';
    }
}
