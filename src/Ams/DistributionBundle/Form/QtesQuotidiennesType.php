<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class  QtesQuotidiennesType extends AbstractType
{
    private $champsForm;
    private $depots;
    private $produits;
    public function __construct($champsForm=array(), $depots=array(), $produits=array()) {
        $this->champsForm   = $champsForm;
        $this->depots   = $depots;
        $this->produits = $produits;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->champsForm as $champsFormId)
        {
            switch ($champsFormId) {
                case "dateDistrib":
                    $builder->add($champsFormId, 'date', array(
                                        'label' => 'Date de distribution',
                                        'required' => false,
                                        'widget' => 'single_text',
                                        'input' => 'datetime',
                                        'format' => 'dd/MM/yyyy',
                                        'required' => true,
                                        'mapped' => false,
                                        'attr' => array('class' => 'date'),
                                     ));
                    break;
                
                case "passe":
                    $builder->add($champsFormId, 'choice', array(
                                            'label' => 'Passe',
                                            'choices'   => array('0' => 'Sans', '1' => 'Avec'),
                                            'required'  => true,
                                        ));
                    
                    break;
                
                case "depot_id":
                    $builder->add($champsFormId, 'choice', array(
                                            'label' => 'Dépôt',
                                            'choices'   => $this->depots,
                                            'multiple' => false,
                                            'expanded' => false,
                                            'required' => true,
                                        ));
                    
                    break;
                
                case "produit_id":
                    $builder->add($champsFormId, 'choice', array(
                                            'label' => 'Produit',
                                            'choices'   => $this->produits,
                                            'multiple' => false,
                                            'expanded' => false,
                                            'required' => true,
                                        ));
                    
                    break;
                
                case "flux":
                   $builder->add('flux', 
                                'entity', 
                                array(
                                    'class' => 'AmsReferentielBundle:RefFlux',
                                    'property' => 'libelle',
                                    'empty_value' => 'Choisissez un flux...',
                                    'multiple' => false,
                                    'required' => false
                                ));
                    break;
            }
        }
        /*
        $builder    
            ->add('dateDistrib', 'date', array(
                'label' => 'Date de distribution',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'mapped' => false,
                'attr' => array('class' => 'date'),
             )) 
            ->add('passe', 'choice', array(
                                            'choices'   => array('0' => 'Sans', '1' => 'Avec'),
                                            'required'  => true,
                                        ))
            ; */
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    /*
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\PaquetVolume'
        ));
    }
    */

    /**
     * @return string
     */
    public function getName()
    {
        return 'qtesquotidiennes';
    }
}
