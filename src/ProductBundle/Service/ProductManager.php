<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Gregwar\ImageBundle\Services\ImageHandling;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductImage;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

class ProductManager
{
    private $em;

    private $formFactory;

    private $router;

    private $paginator;

    private $translator;

    private $imageHandling;

    public function __construct(FormFactoryInterface $formFactory, RouterInterface $router, EntityManagerInterface $em, Paginator $paginator,
                                TranslatorInterface $translator, ImageHandling $imageHandling)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $em;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->imageHandling = $imageHandling;
    }

    /**
     * @param Request $request
     * @return SlidingPagination
     * @throws \LogicException
     */
    public function getPaginatedProducts(Request $request)
    {
        $query = $this->em->getRepository('ProductBundle:Product')
            ->productsQuery();

        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $pagination;
    }

    /**
     * @param Form $form
     * @param Request $request
     * @return SlidingPagination
     * @throws \LogicException
     * @throws \OutOfBoundsException
     */
    public function getPaginatedFrontendProducts($form, $request)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $productsWithoutCategory = false;

            if ($form->has('other') && $form->get('other')->getData()) {
                $productsWithoutCategory = true;
            }

            $query = $this->em->getRepository('ProductBundle:Product')
                ->categoriesWithProducts($form->get('categories')->getData(), $productsWithoutCategory);
        } else {
            $query = $this->em->getRepository('ProductBundle:Product')
                ->productsQuery();
        }

        /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            9/*limit per page*/
        );

        $pagination->setTemplate('pagination.html.twig');

        return $pagination;
    }

    /**
     * @param SlidingPagination $pagination
     * @return array
     * @throws InvalidOptionsException
     * @throws InvalidParameterException
     * @throws MissingMandatoryParametersException
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function getDeleteForms($pagination)
    {
        $deleteForms = [];

        foreach ($pagination as $product) {
            $deleteForms[$product->getId()] = $this->createDeleteForm($product)->createView();
        }

        return $deleteForms;
    }


    /**
     * @param ProductImage $image
     * @param Product $product
     * @param $imagesDirectory
     * @return mixed
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws FileException
     */
    public function createProductImage($image, $product, $imagesDirectory)
    {
        // $file stores the uploaded Image file
        /** @var UploadedFile $file */
        $file = $image->getImage();

        // Generate a unique name for the file before saving it
        $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();

        // Move the file to the directory where product images are stored
        $file->move(
            $imagesDirectory,
            $fileName
        );

        $this->imageHandling->open($imagesDirectory . '/' . $fileName)
            ->forceResize(2000, 1300)
            ->save($imagesDirectory . '/' . $fileName);

        $this->imageHandling->open($imagesDirectory . '/' . $fileName)
            ->resize('50%')
            ->save($imagesDirectory . '/thumbnail/' . $fileName);

        // Update the 'image' property to store the image file name
        // instead of its contents
        $image->setImage($fileName);

        $image->setProduct($product);

        $this->em->persist($image);
        $this->em->flush();

        return $product;
    }

    public function getCategoriesTypeFormOptions()
    {
        $productNoCategoryCount = $this->em->getRepository('ProductBundle:Product')
            ->productsWithoutCategoryCount();

        return [
            'products_count_without_categories' => $productNoCategoryCount,
            'other_label' => $this->translator->trans('PRODUCTCATEGORY_OTHER', [], 'product_category')
        ];
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     * @throws InvalidOptionsException
     * @throws InvalidParameterException
     * @throws MissingMandatoryParametersException
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function createDeleteForm(Product $product)
    {
        $options = [
            'attr' => [
                'class' => 'delete',
                'data-confirm' => $this->translator->trans('COMMON_DELETE_CONFIRM', [], 'common')]
        ];

        return $this->formFactory->createBuilder(FormType::class, null, $options)
            ->setAction($this->router->generate('backend_product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Creates a form to delete a ProductImage entity.
     *
     * @param ProductImage $image
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     * @throws InvalidOptionsException
     * @throws InvalidParameterException
     * @throws MissingMandatoryParametersException
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function createDeleteImageForm(ProductImage $image)
    {
        $options = [
            'attr' => [
                'class' => 'delete',
                'data-confirm' => $this->translator->trans('COMMON_DELETE_CONFIRM', [], 'common')]
        ];

        return $this->formFactory->createBuilder(FormType::class, null, $options)
            ->setAction($this->router->generate('backend_product_image_delete', array('id' => $image->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}