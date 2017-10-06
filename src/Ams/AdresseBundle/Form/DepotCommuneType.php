<?php

namespace Ams\AdresseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\AdresseBundle\Repository\CommuneRepository;
use DateTime;

class DepotCommuneType extends AbstractType
{
    private $depot_id;
    private $date_fin;
    
    public function __construct($depot_id,$date_fin){
        $this->depot_id = $depot_id;
        $this->date_fin = $date_fin;
    }
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebut', 'date', array(
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
             ))
            ->add('commune','entity',array(
                'class' => 'AmsAdresseBundle:Commune',
                'query_builder' => function(CommuneRepository $r) {
                                            return $r->getCommuneDepot($this->depot_id,$this->date_fin);          
                                          },
                'property' => 'libelle',
                'multiple'=> false,
                'expanded' => false,
                'required' => true,
            ))
            ->add('flux','entity',array(
                'class' => 'AmsReferentielBundle:RefFlux',
                'property' => 'libelle',
                'multiple'=> false,
                'expanded' => false,
                'required' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\AdresseBundle\Entity\DepotCommune'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_adressebundle_depotcommune';
    }
}
