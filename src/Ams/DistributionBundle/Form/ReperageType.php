<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\ReferentielBundle\Repository\RefReperageQualifRepository;
use Ams\ModeleBundle\Repository\ModeleTourneeRepository;

class ReperageType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    
    
    private $depot_id;
    
    public  function __construct($depot_id) {
        $this->depot_id = $depot_id;
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id =$this->depot_id;
        $builder
           
                ->add('topage', 'entity', array(
                    'class' => 'AmsReferentielBundle:RefReperageQualif',
                    'property' => 'topage',
                    'query_builder' => function(RefReperageQualifRepository $er) {
                     return $er->getTopage();
                    },
                ))
                ->add('cmtReponse', 'textarea', array('label' => 'Commentaire', 'required' => false))
                ->add('id', 'hidden')
               
               ->add('tournee','entity',array(
                    'class' => 'AmsModeleBundle:ModeleTournee',
                    'query_builder' => function(ModeleTourneeRepository $r)   {
                                            return $r->getListeTournee(array($this->depot_id), true);          
                                          },
                    'empty_value' => '',
                    'property' => 'libelle',
                    'label' => 'Code tournée',
                    'required' => false
                ))         
                            
              /*->add('tournee', 'entity', array(
                    'class' => 'AmsModeleBundle:ModeleTournee',
                    'empty_value' => '',
                    'property' => 'code',
                    'label' => 'Code tournée',
                    'required' => false
                ))*/
                ->add('qualif', 'entity', array(
                    'class' => 'AmsReferentielBundle:RefReperageQualif',
                    'property' => 'libelle',
                    'label' => 'Réponse',
                    'empty_value' => '-------------- Choisir une réponse --------------'
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\Reperage'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ams_distributionbundle_reperage';
    }

}
