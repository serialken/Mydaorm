<?php

namespace Ams\AdresseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\AdresseBundle\Repository\CommuneRepository;

class AdresseRnvpType extends AbstractType
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
                'adresse', 
                'text', 
                array(
                    'label' => "Adresse", 
                    'attr' => array(
                        'required' => true,
                        'size' => '50'
                    )
                )
            )
            ->add(
                'cAdrs', 
                'text', 
                array(
                    'label' => "Compl. adresse", 
                    'required' => false, 
                    'attr' => array(
                        'size' => '20'
                    )
                )
            )
            ->add(
                'lieuDit', 
                'text', 
                array(
                    'label' => "Lieu dit", 
                    'required' => false, 
                    'attr' => array(
                        'size' => '20'
                    )
                )
            )
            ->add(
                'cp', 
                'text',
                array(
                    'max_length' => 5, 
                    'attr' => array(
                        'size' => '10'
                    )
                )
            )
            ->add(
                'commune', 
                'entity', 
                array(
                    'class' => 'AmsAdresseBundle:Commune',
                    'property' => 'libelleWithCp',
                    'empty_value' => 'Choisissez une ville',
                    'required' => false,
                    'query_builder' => function(CommuneRepository $er) use ($depotIds){
                        return $er->getCommuneByDepotIds($depotIds);
                    }
                )
            )
            ->add(
                'ville', 
                'hidden'
            )
        ;
                
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function(FormEvent $event) {
                $data = $event->getData();
                $commune = $data->getCommune();
                $data->setVille($commune->getLibelle());
            }
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\AdresseBundle\Entity\AdresseRnvp'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_adressebundle_adressernvp';
    }
}
