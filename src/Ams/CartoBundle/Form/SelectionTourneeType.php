<?php

namespace Ams\CartoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Ams\SilogBundle\Repository\DepotRepository;

class SelectionTourneeType extends AbstractType
{
    private $depots;
    
    public function __construct($depots) {
        $this->depots = $depots;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $depotIds = array_keys($this->depots);
        
        $builder
            ->add('date', 'text', 
                    array(
                        'required' => true, 
                        'attr'=>array('class' => 'date')
                        )
                )
            ->add('depot', 'entity', 
                array(
                    'class' => 'AmsSilogBundle:Depot',
                    'property' => 'libelle',
                    // 'empty_value' => 'Choisissez un dépôt...',
                    'query_builder' => function(DepotRepository $er) use ($depotIds) {
                        return $er->getDepotsAllowFromSession($depotIds);
                    },
                    'multiple' => false
                )
            )
            ->add('flux', 
                  'entity', 
                  array(
                      'class' => 'AmsReferentielBundle:RefFlux',
                      'property' => 'libelle',
                      'empty_value' => 'Choisissez un flux...',
                      'multiple' => false
                  )
            )
        ;
    }

    public function getName() {
        return 'ams_cartobundle_selectiontourneetype';
    }

}
