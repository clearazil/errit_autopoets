<?php

namespace ShoppingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Entity\User;

class UniqueEmailValidator extends ConstraintValidator
{
    private $entityManager;
    private $tokenStorage;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = $this->entityManager->getRepository('UserBundle:User')->findOneBy([
            'email' => $value]);

        if ($user !== null) {
            $currentUser = $this->tokenStorage->getToken()->getUser();

            if (!$currentUser instanceof User || $currentUser->getEmail() !== $value) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
