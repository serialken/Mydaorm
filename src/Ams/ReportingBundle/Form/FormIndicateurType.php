<?php

namespace Ams\ReportingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Ams\AdresseBundle\Repository\CommuneRepository;
use Ams\ProduitBundle\Repository\ProduitRepository;
use \Ams\ProduitBundle\Repository\SocieteRepository;
use \Ams\SilogBundle\Repository\DepotRepository;
use DateTime;
use DateInterval;

/**
 * 
 */
class FormIndicateurType extends AbstractType {

    public function __construct($date_debut, $date_fin, $depot_id, $depots) {
        $this->depot_id = $depot_id;
        $this->date_fin = $date_fin;
        $this->date_debut = $date_debut;
        $this->depots = $depots;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $now = new \DateTime();
        $minDate = $now->add(new DateInterval('P3D'));

        $builder->add('societe', 'entity', array(
                    'label' => 'Société',
                    'class' => 'AmsProduitBundle:Societe',
                    'property' => 'libelle',
                    'empty_value' => 'Société',
                    'query_builder' => function(SocieteRepository $sr) {
                return $sr->getSocietesLibelles();
            },
                    'multiple' => false
                   )
                )
                ->add('dateDebut', 'date', array(
                    'widget' => 'single_text',
                    'data' => $this->date_debut,
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'date'),
                ))
                ->add('dateFin', 'date', array(
                    'widget' => 'single_text',
                    'data' => $this->date_fin,
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'date'),
                ))
                    
                    
                ->add('depot', 'entity', array(
                'label' => 'Dépot',
                'class' => 'AmsSilogBundle:Depot',
                'choices' => $this->depots,
               //'preferred_choices' =>array($this->depot),
                'required' => false,))
                
                    
                ->add('reclamation', 'submit', array(
                    'attr' => array('label' => 'Réclamation', 'class' => 'btn btn-primary'),
                ))
                    
                ->add('reperage', 'submit', array(
                    'attr' => array('label' => 'Repérage', 'class' => 'btn btn-primary'),
                ))
                
                
             
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'indicateur_form';
    }

}
