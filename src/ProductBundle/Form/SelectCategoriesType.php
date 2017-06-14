<?php

namespace ProductBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SelectCategoriesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET')
            ->add('categories', EntityType::class, [
                'label' => 'PRODUCTCATEGORY_PRODUCTCATEGORIES',
                'class' => 'ProductBundle:ProductCategory',
                //'choices' => [$newChoice],
                'choice_label' => 'getNameWithProductsCount',
                'choice_value' => 'slug',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'categories-select'],
            ]);

        if ($options['products_count_without_categories'] > 0) {
            $builder->add('other', CheckboxType::class, [
                'label' => $options['other_label'] . ' (' . $options['products_count_without_categories'] .')',
                'attr' => ['class' => 'categories-select'],
                'translation_domain' => 'product_category',
            ]);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'product_category',
            'products_count_without_categories' => 0,
            'other_label' => 'Other',
        ));
    }
}
