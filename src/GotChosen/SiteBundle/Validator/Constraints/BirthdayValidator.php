<?php

namespace GotChosen\SiteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BirthdayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $limit = new \DateTime('midnight 18 years ago');
        
        if ( $limit < $value ) {
            $this->context->addViolation("You must be 18 years or older to register.");
        }
    }
}
