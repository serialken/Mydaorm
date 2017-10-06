<?php

namespace Ams\AdresseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class RequeteExportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', 'text')
            ->add('commentaire', 'textarea', array(
                    'attr' => array('cols' => '75', 'rows' => '15'),
            ))
            ->add('requete', 'hidden');   

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\AdresseBundle\Entity\RequeteExport'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_adressebundle_requete_export';
    }
}
