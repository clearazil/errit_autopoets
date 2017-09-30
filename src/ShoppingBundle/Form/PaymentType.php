<?php

namespace ShoppingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use ShoppingBundle\Entity\PurchaseOrder;

class PaymentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('payment', ChoiceType::class, [
            'choices' => [
                'CHECKOUT_BANK_TRANSFER' => PurchaseOrder::PAYMENT_METHOD_BANK_TRANSFER,
                'CHECKOUT_IDEAL' => PurchaseOrder::PAYMENT_METHOD_IDEAL,
            ],
            'expanded' => true,
            'multiple' => false,
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'checkout',
        ]);
    }
}
