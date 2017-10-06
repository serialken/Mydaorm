<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class FiltreReceptionDepotType extends AbstractType
{
    private $id;
    private $depot;
    private $fluxList;
    
    public function __construct($depot, $fluxList){
        $this->depot = $depot;
        $this->fluxList = $fluxList;
       // $this->id = $id;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('date', 'date', array(
            'input' => 'datetime',
            'widget' => 'single_text',
            'label' => 'Date ',
            'mapped' => false,
            'required' => false,
            'attr' => array('class' => 'date'),
            ))
            ->add('depot', 'choice', array(
            'label' => 'Dépot',
            'choices' => $this->depot,
            'required' => true,
            // 'empty_value'=>'Choisissez un dépot',
            ))
            ->add('flux', 'choice', array(
                    'label'=>'Flux',
                    'choices'=>$this->fluxList,
                    'empty_value'=>'Jour+Nuit',
                    ));
        
        
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_filtrereception';
    }
}
