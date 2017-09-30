<?php

namespace ShoppingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueEmail extends Constraint
{
    public $message = 'UNIQUE_EMAIL_NOT_UNIQUE';

    public function validatedBy()
    {
        return UniqueEmailValidator::class;
    }
}
