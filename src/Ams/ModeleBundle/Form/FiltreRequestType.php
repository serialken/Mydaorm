<?php
namespace Ams\ModeleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreRequestType extends AbstractType {

    private $depots;
    private $fluxs;
    private $depot_id;
    private $flux_id;
    private $requests;
    private $request_id;

    public function __construct($depots = array(), $fluxs = array(), $requests = array(), $depot_id, $flux_id, $request_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->requests = $requests;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->request_id = $request_id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('depot_id', 'choice', array(
                    'label' => 'Dépôt',
                    'choices' => $this->depots,
                    'required' => true,
                    'data' => $this->depot_id,
                ))
                ->add('flux_id', 'choice', array(
                    'label' => 'Flux',
                    'choices' => $this->fluxs,
                    'required' => true,
                    'data' => $this->flux_id,
                ))
                ->add('request_id', 'choice', array(
                    'label' => 'Requête',
                    'choices' => $this->requests,
                    'required' => true,
                    'data' => $this->request_id,
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