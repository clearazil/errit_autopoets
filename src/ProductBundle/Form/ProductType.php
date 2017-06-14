<?php

namespace ProductBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['label' => 'PRODUCT_NAME',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 50]),
            ],
        ])
        ->add('description', TextareaType::class, ['label' => 'PRODUCT_DESCRIPTION',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 2000]),
            ],
        ])
        ->add('category', EntityType::class, [
            'class' => 'ProductBundle:ProductCategory',
            'choice_label' => 'name',
            'label' => 'PRODUCTCATEGORY_PRODUCTCATEGORY',
            'placeholder' => 'PRODUCTCATEGORY_OTHER',
            'translation_domain' => 'product_category',
        ])
        ->add('price', TextType::class, ['label' => 'PRODUCT_PRICE',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 10]),
            ],
        ])
        ->add('stock', TextType::class, ['label' => 'PRODUCT_STOCK',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 5]),
            ],
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ProductBundle\Entity\Product',
            'translation_domain' => 'product',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'productbundle_product';
    }
}
