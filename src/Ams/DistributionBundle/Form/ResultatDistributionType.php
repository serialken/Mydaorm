<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResultatDistributionType extends AbstractType
{
    private $data;
    
    public function __construct($parameters){
        $this->data = array();
        $this->data['depot'] = $parameters['depot'];
        $this->data['nb_paquet'] = $parameters['nb_paquet'];
        $this->data['nb_appoint'] = $parameters['nb_appoint'];
        $this->data['conditionnement'] = $parameters['conditionnement'];
        $this->data['produit'] = $parameters['produit_id'];
        $this->data['date_distrib'] = $parameters['date_distrib'];
        $this->data['passe'] = $parameters['passe'];
        $this->data['passe_value'] = $parameters['passe_value'];
    }
    
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nbPaquet', 'text', array(
            'required' => true,
            'data' => $this->data['nb_paquet']))
                
                ->add('nbAppoint', 'text', array(
            'required' => true,
            'data' => $this->data['nb_appoint']))
                
                ->add('conditionnement', 'text', array(
            'required' => true,
            'data' => $this->data['conditionnement']))
                
                ->add('dateDistribution', 'text', array(
            'required' => true,
            'data' => $this->data['date_distrib']))
                
                ->add('passe', 'text', array(
            'required' => true,
            'data' => $this->data['passe']))
                
                ->add('passeValue', 'text', array(
            'required' => true,
            'data' => $this->data['passe_value']))
                
                ->add('depot', 'entity', array(
            'class' => 'AmsSilogBundle:Depot',
            'required' => true,
            'data' => $this->data['depot'] ))
                
                ->add('produit', 'entity', array(
            'class' => 'AmsProduitBundle:Produit',
            'required' => true,
            'data' => $this->data['produit'] ))
                
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\ResultatDistribution'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_resultatdistribution';
    }
}
