<?php

namespace ShoppingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class PurchaseOrderLine extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
                'label' => false,
            ])
            ->add('subtotal', NumberType::class, [
                'label' => false,
            ])
            ->add('price', NumberType::class, [
                'label' => false,
            ])
            ->add('vat', NumberType::class, [
                'label' => false,
            ])
            ->add('amount', NumberType::class, [
                'label' => false,
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ShoppingBundle\Entity\PurchaseOrderLine',
            'translation_domain' => 'purchase_order',
        ]);
    }
}
