<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class FiltreOuvertureType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('filtre', 'date', array(
            'input' => 'datetime',
            'widget' => 'single_text',
            'label' => 'Date',
            'mapped' => false,
            'required' => false,
            'attr' => array('class' => 'date'),))
                
            ->add('flux','choice',array('label'=>'Flux',
                                        'choices'=> array('1'=>'Nuit','2'=>'Jour')
                                        ))
            
            ;
        
        
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_filtreouverture';
    }
}
