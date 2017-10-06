<?php
/**
 * Description of ContainsAlphanumeric
 *
 * @author DDEMESSENCE
 *
 */
namespace Ams\ExtensionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsAlphanumeric extends Constraint
{
    public $message = 'La chaîne "%string%" contient un caractère non autorisé : elle ne peut contenir que des lettres et des chiffres.';
    
    public function validatedBy() {
        return 'contains_alphanumeric';
    }
}


