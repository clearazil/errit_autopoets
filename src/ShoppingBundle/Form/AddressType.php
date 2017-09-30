<?php

namespace ShoppingBundle\Form;

use ShoppingBundle\Validator\Constraints\UniqueEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\User;

class AddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_FIRST_NAME',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_LAST_NAME',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('companyName', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_COMPANY_NAME',
                ],
                'label' => false,
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_ADDRESS',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('houseNumber', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_HOUSE_NBR',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_CITY',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('zipCode', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_ZIP_CODE',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'attr' => [
                    'placeholder' => 'ADDRESS_PHONE_NUMBER',
                ],
                'label' => false,
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'USER_EMAIL',
                ],
                'label' => false,
                'translation_domain' => 'user',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new UniqueEmail(),
                ],
            ]);
    }

    /**
     * @param User $user
     * @return $this
     */
    public function fillWithUserData($user)
    {
        $this->get('firstName')->setData($user->getAddress()->getFirstName());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'address',
        ]);
    }
}
