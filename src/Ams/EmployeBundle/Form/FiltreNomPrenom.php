<?php
namespace Ams\EmployeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class  FiltreNomPrenom extends AbstractType
{
    private $employe_nom;
    private $employe_prenom; 
    
    public function __construct($nom, $prenom){
        $this->employe_nom = $nom;
        $this->employe_prenom = $prenom;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder           
            ->add('employe_nom', 'text', array(
                'label' => 'Nom',
                'required' => false,
                'data' => $this->employe_nom    
                ))
            ->add('employe_prenom', 'text',array(
                'label' => 'Prénom',
                'required' => false,
                'data' => $this->employe_prenom    
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
