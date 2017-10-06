<?php

namespace Ams\ExtensionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DatePosterieureValidator extends ConstraintValidator {
        
    public function validate($value, Constraint $constraint){
        $t = new \DateTime('now');
        if($t > $value || $value == '' || !isset($value)){
            $this->context->addViolation($constraint->message, array('{{ date_limit }}' => $t->format('d/m/Y')));
        }
    }
}
