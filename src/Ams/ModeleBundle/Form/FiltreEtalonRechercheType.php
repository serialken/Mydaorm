<?php

namespace Ams\ModeleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreEtalonRechercheType extends AbstractType {

    private $employes;
    private $tournees;
    private $date_debut;
    private $date_fin;
    private $employe_id;
    private $tournee_id;

    public function __construct($employes = array(), $tournees = array(), $date_debut, $date_fin, $employe_id, $tournee_id) {
        $this->employes = $employes;
        $this->tournees = $tournees;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->employe_id = $employe_id;
        $this->tournee_id = $tournee_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('date_debut', 'text', array(
                    'label' => 'Début',
                    'attr' => array('class' => 'date'),
                    'required' => true,
                    'data' => $this->date_debut
                ))
                ->add('date_fin', 'text', array(
                    'label' => 'Fin',
                    'attr' => array('class' => 'date'),
                    'required' => true,
                    'data' => $this->date_fin
                ))
                ->add('employe_id', 'choice', array(
                    'label' => 'Employé',
                    'choices' => $this->employes,
                    'data' => $this->employe_id,
                ))
                ->add('tournee_id', 'choice', array(
                    'label' => 'Tournée',
                    'choices' => $this->tournees,
                    'data' => $this->tournee_id,
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => NULL
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'form_filtre';
    }

}
