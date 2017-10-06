<?php
namespace Ams\EmployeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreRequestType extends AbstractType 
{
    private $depots;
    private $fluxs; 
    private $anneemois; 
    private $depot_id;
    private $flux_id;
    private $anneemois_id;
    private $requests;
    private $request_id;
    
    public function __construct($depots=array(), $fluxs=array(), $anneemois=array(), $requests = array(), $depot_id, $flux_id, $anneemois_id, $request_id){
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->anneemois = $anneemois;
        $this->requests = $requests;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->anneemois_id = $anneemois_id;
        $this->request_id = $request_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder           
            ->add('depot_id', 'choice', array(
                'label' => 'Dépot',
                'choices' => $this->depots,
                'required' => true,
                'data' => $this->depot_id,
                ))
            ->add('flux_id', 'choice',array(
                'label' => 'Flux',
                'choices' => $this->fluxs,
                'required' => true,
                'data' => $this->flux_id,
            ))   
            ->add('anneemois_id', 'choice', array(
                'label' => 'Mois',
                'choices' => $this->anneemois,
                'required' => true,
                'data' => $this->anneemois_id,       
             )) 
            ->add('request_id', 'choice', array(
                'label' => 'Requête',
                'choices' => $this->requests,
                'required' => true,
                'data' => $this->request_id,
            ))
            ; 
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL
        ));
    }

    public function getName()
    {
        return 'form_filtre';
    }
}
