<?php

namespace Ams\ReportingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FormReporting extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('form');
    }

    public function getName()
    {
        return 'form';
    }
}

