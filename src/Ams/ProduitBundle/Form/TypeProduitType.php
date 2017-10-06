<?php

namespace Ams\ProduitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\ProduitBundle\Repository\ProduitTypeRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TypeProduitType extends AbstractType
{
//    private $type_id;
//    
//    public function __construct($type_id){
//        $this->type_id = $type_id;
//    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('libelle')
            ->add('horsPresse');
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Ams\ProduitBundle\Entity\ProduitType'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_produitbundle_type_produit';
    }
}