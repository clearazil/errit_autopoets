<?php

namespace ShoppingBundle\Form;

use ShoppingBundle\Entity\PurchaseOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use UserBundle\Form\AddressType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PurchaseOrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
                'label' => 'PURCHASE_ORDER_EMAIL',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => PurchaseOrder::getStatusOptions(),
                'label' => 'PURCHASE_ORDER_STATUS',
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => PurchaseOrder::getPaymentMethodOptions(),
                'label' => 'PURCHASE_ORDER_PAYMENT_METHOD',
            ])
            ->add('shippingCosts', TextType::class, [
                'label' => 'PURCHASE_ORDER_SHIPPING_COSTS',
            ])
            ->add('addresses', CollectionType::class, [
                'entry_type' => AddressType::class,
                'constraints' => [new Valid()],
            ])
            ->add('user', EntityType::class, [
                'class' => 'UserBundle:User',
                'choice_label' => 'username',
                'label' => 'USER_USER',
                'placeholder' => 'USER_NONE',
                'translation_domain' => 'user',
            ])
            ->add('purchaseOrderLines', CollectionType::class, [
                'entry_type' => PurchaseOrderLine::class,
                'constraints' => [new Valid()],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ShoppingBundle\Entity\PurchaseOrder',
            'translation_domain' => 'purchase_order',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'shoppingbundle_purchaseorder';
    }
}
