<?php

namespace Ams\ProduitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ams\ProduitBundle\Repository\ProduitRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProduitType extends AbstractType
{
    private $societe_id;
    
    public function __construct($societe_id){
        $this->societe_id = $societe_id;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('libelle')
            ->add('socCodeExt')
            ->add('prdCodeExt')     
            ->add('dateDebut', 'date', array(
                'label' => 'Date de début',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))
            ->add('dateFin', 'date', array(
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))
            ->add('sprCodeExt','text',array('required'=>false))
            ->add('passe','integer',array('required'=>true))
            ->add('parents','entity',array(
                'class' => 'AmsProduitBundle:Produit',
                'label' => 'Dépendance',
                'query_builder' => function(ProduitRepository $r) {
                                            return $r->getParentsForm($this->societe_id);          
                                          },
                'property' => 'libelle',
                'multiple'=> true,
                'expanded' => true,
                'required' => false,
            ))
            ->add('image','entity',array(
                'class' => 'AmsExtensionBundle:Fichier',
                'property' => 'name',
                'multiple'=> false,
                'expanded' => false,
                'required' => true,
                'empty_value' => 'Choisissez le logo',
            ))
            ->add('flux','entity',array(
                'class' => 'AmsReferentielBundle:RefFlux',
                'property' => 'libelle',
                'multiple'=> false,
                'expanded' => false,
                'required' => true,
            ))
            ->add('periodicite','entity',array(
                'class' => 'AmsReferentielBundle:RefPeriodicite',
                'property' => 'libelle',
                'multiple'=> false,
                'expanded' => false,
                'required' => false,
            ));
            // empecher la modification du produit type une fois qu'il a été définit
            $factory = $builder->getFormFactory();
            $builder->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function(FormEvent $event) use ($factory){
                        $produit = $event->getData();
                        if (null === $produit) return;// on sort la premiére fois
                        if($produit->getProduitType() == false){
                            $event->getForm()
                                    ->add('produitType','entity',array(
                                                                    'class' => 'AmsProduitBundle:ProduitType',
                                                                    'property' => 'libelle',
                                                                    'multiple'=> false,
                                                                    'expanded' => false,
                                                                    'required' => false,
                                                                ));
                        }
                    });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Ams\ProduitBundle\Entity\Produit'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_produitbundle_produit';
    }
}
