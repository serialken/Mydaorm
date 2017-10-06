<?php
namespace Ams\SilogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\SilogBundle\Repository\DepotRepository;

class  FiltreDepotType extends AbstractType
{

    private $depot;
    private $depots;
  
    
    public function __construct($depot, $depots){
     
        $this->depot = $depot;
        $this->depots = $depots;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder   
            ->add('depot', 'entity', array(
                'label' => 'Dépot',
                'class' => 'AmsSilogBundle:Depot',
                'choices' => $this->depots,
                'preferred_choices' =>array($this->depot),
                'required' => true,));
           
              /*->add('depot', 'entity', array(
                                            'class'=>'AmsSilogBundle:Depot',
                                            'property'=>'libelle',
                                            'label'=>'Dépôt',
                                            'preferred_choices' =>array($this->depot),
                                            'query_builder' => function(DepotRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                            ->orderBy('c.libelle', 'ASC');},
                                          
                                          )); */
   
       
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
