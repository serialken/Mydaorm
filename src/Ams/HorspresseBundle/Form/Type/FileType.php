<?php

namespace Ams\HorspresseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('fichier', 'file', [
            'label' => 'fichier',
            'attr' => [
                'class' => ''
            ]
        ])
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ams_horspressebundle_file';
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\HorspresseBundle\Entity\Fichier',
            'csrf_protection' => false,
        ));
    }

}
