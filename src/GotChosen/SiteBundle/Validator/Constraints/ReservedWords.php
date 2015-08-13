<?php

namespace GotChosen\SiteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ReservedWords extends Constraint
{
    public $loose_message = "You cannot use the word '%word%' in your username.";
    public $exact_message = "You cannot use '%word%' as your username.";
}
