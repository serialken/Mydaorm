<?php

namespace Ams\ExtensionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class DatePosterieure  extends Constraint {
    public $message = 'La date doit être postérieure à la date d\'aujourd\'hui: {{ date_limit }}.';
    
    public function validatedBy() {
        return 'date_posterieure';
    }
}
