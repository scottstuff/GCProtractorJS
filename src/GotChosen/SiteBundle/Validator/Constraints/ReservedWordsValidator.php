<?php

namespace GotChosen\SiteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservedWordsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $value = strtolower($value);
        
        $loose_match = ['gotchosen', 'gotscholarship', 'gmesh', 'evolutiongames',
            'damn', 'bitch', 'cunt', 'fuck', 'faggot', 'nigger', 'shit', 'piss',
            'cocksucker', 'tits'];
        
        $exact_match = ['admin', '_wdt', '_profiler', 'evogames', 'profile',
            'register', 'scholarship', 'home', 'terms', 'news', 'login', 'logout',
            'resetting'];
        
        foreach ($loose_match as $word) {
            if (strpos($value, $word) !== false) {
                $this->context->addViolation($constraint->loose_message, ['%word%' => $word]);
            }
        }
        
        foreach ($exact_match as $word) {
            if ($value == $word) {
                $this->context->addViolation($constraint->exact_message, ['%word%' => $word]);
            }
        }
    }
}
