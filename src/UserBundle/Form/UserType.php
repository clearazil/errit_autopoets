<?php

namespace UserBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use UserBundle\Entity\User;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $passwordConstraints = [];

        if ($options['password_required']) {
            $passwordConstraints[] = new NotBlank();
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'USER_USERNAME',
            ])
            ->add('email', EmailType::class, [
                'label' => 'USER_EMAIL',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'VALIDATORS_PASSWORD_MUST_MATCH',
                'first_options' => ['label' => 'USER_PASSWORD'],
                'second_options' => ['label' => 'USER_PASSWORD_REPEAT'],
                'constraints' => $passwordConstraints,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'USER_IS_ACTIVE',
            ])->add('userRoles', EntityType::class, [
                'label' => 'ROLE_ROLES',
                'class' => 'UserBundle:UserRole',
                'choice_value' => 'getRoleId',
                'choice_label' => 'getLabel',
                'choices' => $options['role_choices'],
                'multiple' => true,
                'expanded' => true,
                'translation_domain' => 'role',
            ])
            ->add('addresses', CollectionType::class, [
                'entry_type' => AddressType::class,
                'constraints' => [new Valid()],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'user',
            'password_required' => true,
            'role_choices' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'userbundle_user';
    }
}
