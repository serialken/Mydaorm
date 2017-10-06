<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CptrDistributionType extends AbstractType
{
    
    private $depots;
    
    public function __construct($depots){
        $this->depots = $depots;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('depotId', 'entity', array(
            'label' => 'Dépot',
            'class' => 'AmsSilogBundle:Depot',
            'choices' => $this->depots,
            'required' => true,))
                
            ->add('codeTournee')
                
            ->add('typeIncident', 'choice', array(
            'label' => 'Type d\'incident',
            'choices' => array('ras' => 'R.A.S.', 'retard' => 'Retard', 'non_livr' => 'Non Livré'),
            'expanded' => true,
            'required' => true,))
                
            ->add('nbExAbo', 'text', array(
                'label' => 'Nombre d\'exemplaires abonné non livré',
                'required' => true ))
                
            ->add('nbExDiff', 'text', array(
                'label' => 'Nombre d\'exemplaires diffuseur non livré',
                'required' => true ))
                
            ->add('heureFinDeTournee')
                
            ->add('cmtIncidentAb', 'textarea', array(
                'label' => 'Incident Abonné',
                'required' => true ))
                
            ->add('cmtIncidentDiff', 'textarea', array(
                'label' => 'Incident Diffuseur',
                'required' => true ))
                
            ->add('villes')
                
            ->add('dateCptRendu', 'date', array(
                'label' => 'Date et heure du compte rendu',
                'widget' => 'single_text',
                'data' => new \DateTime("now"),
                'format' => 'yyyy-MM-dd HH:mm',
                'required' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\CptrDistribution'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_cptrdistribution';
    }
}
