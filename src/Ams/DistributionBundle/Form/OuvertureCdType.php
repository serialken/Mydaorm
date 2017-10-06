<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\DistributionBundle\Repository\OuvertureCdRepository;
use Ams\SilogBundle\Repository\DepotRepository;


class OuvertureCdType extends AbstractType
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
        
        $builder->add('nom', 'text', array(
            'label' => 'Nom',
            'required' => true,))
                
                ->add('prenom', 'text', array(
            'label' => 'Prénom',
            'required' => true,))
                
                ->add('telephone', 'text', array(
            'label' => 'Téléphone',
            'required' => true,))
                
           ->add('depot', 'entity', array(
            'label' => 'Dépot',
            'class' => 'AmsSilogBundle:Depot',
            'choices' => $this->depots,
            'required' => true,));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\OuvertureCd'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_ouverturecd';
    }
}
