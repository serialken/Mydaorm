<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class FiltreFeuillePortageType extends AbstractType
{
    private $depots; 
    private $fluxs; 
    private $date_distrib;
    
    public function __construct($depots=array(), $fluxs=array(), $date_distrib){
        $this->depots = $depots;
        $this->fluxs = $fluxs;

        $this->date_distrib = $date_distrib;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder           
            ->add('depot_id', 'choice', array(
                'choices' => $this->depots,
                'required' => true,
                'label' => 'Dépot',
             
                ))
            ->add('flux_id', 'choice',array(
                'choices' => $this->fluxs,
                'required' => true,
                'label' => 'Flux',
              
            ))                
            ->add('date_distrib', 'text', array(
                'label' => 'Date distrib',
                'attr' => array('class' => 'date'),
                'data' => $this->date_distrib            
             )) 
          ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL
        ));
    }

    public function getName()
    {
        return 'form_filtre';
    }
}

