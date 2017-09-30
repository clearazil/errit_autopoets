<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\Address;

class AddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'ADDRESS_FIRST_NAME',
            ])->add('lastName', TextType::class, [
                'label' => 'ADDRESS_LAST_NAME',
            ])->add('companyName', TextType::class, [
                'label' => 'ADDRESS_COMPANY_NAME',
            ])->add('address', TextType::class, [
                'label' => 'ADDRESS_ADDRESS',
            ])->add('city', TextType::class, [
                'label' => 'ADDRESS_CITY',
            ])->add('houseNumber', TextType::class, [
                'label' => 'ADDRESS_HOUSE_NUMBER',
            ])->add('zipCode', TextType::class, [
                'label' => 'ADDRESS_ZIP_CODE',
            ])->add('phoneNumber', TextType::class, [
                'label' => 'ADDRESS_PHONE_NUMBER',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'translation_domain' => 'address',
        ]);
    }
}
