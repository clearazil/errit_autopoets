<?php

namespace ProductBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class ProductFilterType extends AbstractType
{
    public const SORT_DEFAULT = 0;
    public const SORT_PRICE_LOW_TO_HIGH = 1;
    public const SORT_PRICE_HIGH_TO_LOW = 2;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('GET')
            ->add('categories', EntityType::class, [
                'label' => 'PRODUCTCATEGORY_PRODUCTCATEGORIES',
                'translation_domain' => 'product_category',
                'class' => 'ProductBundle:ProductCategory',
                'choice_label' => 'getNameWithProductsCount',
                'choice_value' => 'slug',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'product-filter'],
            ])
            ->add('sort', ChoiceType::class, [
                'translation_domain' => 'common',
                'choices' => $options['sort_options'],
                'label' => false,
                'attr' => ['class' => 'product-filter'],
            ]);

        if ($options['products_count_without_categories'] > 0) {
            $builder->add('other', CheckboxType::class, [
                'label' => $options['other_label'] . ' (' . $options['products_count_without_categories'] . ')',
                'attr' => ['class' => 'categories-select'],
                'translation_domain' => 'product_category',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'products_count_without_categories' => 0,
            'other_label' => 'Other',
            'sort_options' => [],
        ));
    }
}
