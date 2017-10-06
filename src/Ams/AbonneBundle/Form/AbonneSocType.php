<?php

namespace Ams\AbonneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ams\ProduitBundle\Repository\SocieteRepository;
use Ams\SilogBundle\Repository\DepotRepository;
use Ams\AdresseBundle\Repository\CommuneRepository;

use Symfony\Component\Validator\Constraints as Assert;

class AbonneSocType extends AbstractType
{
    private $depots;
    
    public function __construct($depots) {
        $this->depots = $depots;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $depotIds = array_keys($this->depots);
        
        $builder
            ->add(
                'tournee', 
                'choice',
                array('label' => 'Tournee','required' => false,'mapped' => false)
            )    
            ->add(
                'dateDistrib', 
                'text',
                array('label' => 'Date de distribution','required' => true,'mapped' => false)
            )    
            ->add(
                'flux_id', 
                'entity',
                array(
                    'class' => 'AmsReferentielBundle:RefFlux',
                    'property' => 'libelle',
                    'required' => true,
                    'mapped' => false
                )
            )            
            ->add(
                'societe', 
                'entity', 
                array(
                    'class' => 'AmsProduitBundle:Societe',
                    'property' => 'libelle',
                    'empty_value' => 'Tous',
                    'required' => false,
                    'query_builder' => function(SocieteRepository $er){
                        return $er->getSocietesLibelles();
                    },
                    'mapped' => false
                )
            )
            ->add(
                'depot', 
                'entity', 
                array(
                    'class' => 'AmsSilogBundle:Depot',
                    'property' => 'libelle',
                    // 'empty_value' => 'Choisissez un dépôt',
                    'required' => true,
                    'query_builder' => function(DepotRepository $er) use ($depotIds){
                        return $er->getDepotsAllowFromSession($depotIds);
                    },
                    'mapped' => false
                )
            )
            ->add(
                'numaboExt',
                'text', 
                array(
                    'label' => 'Num. abonne', 
                    'required' => false,
                    'constraints' => array( $this->getLengthConstraint(3) )
                )
            )
            ->add(
                'vol1', 
                'text', 
                array(
                    'label' => 'Nom Prénom', 
                    'required' => false,
                    'constraints' => array( $this->getLengthConstraint(3) )
                )
            )
            ->add(
                'vol2',  
                'text', 
                array(
                    'label' => 'Rais. Sociale', 
                    'required' => false,
                    'constraints' => array( $this->getLengthConstraint(3) )
                )
            )
            ->add(
                'vol4',  
                'text', 
                array(
                    'label' => 'Adresse', 
                    'required' => false,
                    'mapped' => false,  
                    'attr' => array(
                        'size' => '30'
                    ),
                    'constraints' => array( $this->getLengthConstraint(3) )
                )
            )
            ->add(
                'cp', 
                'text', 
                array(
                    'label' => 'Cp', 
                    'required' => false, 
                    'mapped' => false,
                    'max_length' => 5,
                    'attr' => array(
                        'size' => '10'
                    )
                )
            )
            ->add(
                'ville', 
                'entity', 
                array(
                    'class' => 'AmsAdresseBundle:Commune',
                    'property' => 'libelleWithCp',
                    'empty_value' => 'Choisissez une ville',
                    'required' => false,
                    'query_builder' => function(CommuneRepository $er) use ($depotIds){
                        return $er->getCommuneByDepotIds($depotIds);
                    },
                    'mapped' => false
                )
            )
        ;
    }
    
    
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\AbonneBundle\Entity\AbonneSoc'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
    }
    
    public function getLengthConstraint($min)
    {
        return new Assert\Length(array(
                'min'        => $min,
                'minMessage' => 'Le champ doit avoir au moins {{ limit }} caractères'
            ));
    }
}
