<?php
namespace Ams\ModeleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltreTourneeType extends AbstractType {

    private $depots;
    private $fluxs;
    private $tournees;
    private $depot_id;
    private $flux_id;
    private $modele_tournee_id;

    public function __construct($depots = array(), $fluxs = array(), $tournees = array(), $depot_id, $flux_id, $modele_tournee_id) {
        $this->depots = $depots;
        $this->fluxs = $fluxs;
        $this->tournees = $tournees;
        $this->depot_id = $depot_id;
        $this->flux_id = $flux_id;
        $this->modele_tournee_id = $modele_tournee_id;
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
                ->add('modele_tournee_id', 'choice', array(
                    'label' => 'Tournée',
                    'choices' => $this->tournees,
                    'required' => true,
                    'data' => $this->modele_tournee_id,
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
