<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\AbonneBundle\Repository\AbonneSocRepository;
use Ams\AdresseBundle\Repository\AdresseRnvpRepository;

class InfoPortageType extends AbstractType
{
    
    private $portageId;
    public function __construct($portageId){
        $this->portageId = $portageId;  
    } 
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder    
            ->add('typeInfoPortage', 'entity', array(
                'class' => 'AmsDistributionBundle:TypeInfoPortage',
                'property' => 'libelle',
                'required' => false,
            ))
            ->add('valeur')
            ->add('abonnes','entity',array(
                'class' => 'AmsAbonneBundle:AbonneSoc',
                'query_builder' => function( AbonneSocRepository  $er) {
                        return $er->getAbonnePortage($this->portageId);
                 },
                'property' => 'vol1',
                'multiple'=> true,
                'expanded' => true,
                'required' => false,
            ))
            ->add('adresses','entity',array(
                'class' => 'AmsAdresseBundle:AdresseRnvp',
                'query_builder' => function( AdresseRnvpRepository  $er) {
                        return $er->getAdressesPortage($this->portageId);
                 },
                'multiple'=> true,
                'expanded' => false,
                'required' => false,
            ))
            ->add('livraisons','entity',array(
                'class' => 'AmsAdresseBundle:AdresseRnvp',
                'query_builder' => function( AdresseRnvpRepository  $er) {
                        return $er->getLivraisonsPortage($this->portageId);
                 },
                'multiple'=> true,
                'expanded' => true,
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
            'data_class' => 'Ams\DistributionBundle\Entity\InfoPortage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_infoportage';
    }
}
