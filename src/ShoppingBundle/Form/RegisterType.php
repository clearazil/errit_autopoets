<?php

namespace ShoppingBundle\Form;

use ShoppingBundle\Validator\Constraints\UniqueEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'USER_EMAIL',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new UniqueEmail(),
                ],
            ])
            ->add('create_account', CheckboxType::class, ['label' => 'USER_CREATE_ACCOUNT'])
            ->add('submit', SubmitType::class, ['label' => 'USER_CONTINUE']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'user',
        ]);
    }
}
