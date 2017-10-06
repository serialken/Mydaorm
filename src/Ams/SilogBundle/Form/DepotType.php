<?php

namespace Ams\SilogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class DepotType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adresse')
            ->add('commune','entity',array(
                'class' => 'AmsAdresseBundle:Commune',
                'property' => 'libelle',
                'multiple'=> false,
                'expanded' => false,
                'required' => true,
            ))
        ;

        
        $factory = $builder->getFormFactory();
        $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use ($factory){
                    $depot = $event->getData();
                    
                    if(null === $depot) return; // on sort la première fois
                    if($depot->getCode() == false ){
                        $event->getForm()
                                ->add($factory->createNamed('code', 'text', null, array(
                                                                                        'required'=>true,
                                                                                        'max_length' => 3,
                                                                                        'auto_initialize'=>false
                                                                                        )
                                                               )
                                        )
                                ->add($factory->createNamed('libelle','text',null,array(
                                                                                        'required'=>true,
                                                                                        'auto_initialize'=>false
                                                                                        )
                                                            )
                                    )
                                
                        ;
                    }
                });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\SilogBundle\Entity\Depot'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_silogbundle_depot';
    }
}
