<?php

namespace ProductBundle\Controller;

use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product controller.
 *
 * @Route("products")
 */
class ProductController extends Controller
{
    /**
     * Lists all product entities.
     *
     * @Route("/", name="product_index")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $productNoCategoryCount = $em->getRepository('ProductBundle:Product')
            ->productsWithoutCategoryCount();

        $form = $this->createForm('ProductBundle\Form\SelectCategoriesType', null, ['products_count_without_categories' => $productNoCategoryCount, 'other_label' => $this->get('translator')->trans('PRODUCTCATEGORY_OTHER', [], 'product_category')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productsWithoutCategory = false;

            if ($form->has('other') && $form->get('other')->getData()) {
                $productsWithoutCategory = true;
            }

            $query = $em->getRepository('ProductBundle:Product')
                ->categoriesWithProducts($form->get('categories')->getData(), $productsWithoutCategory);
        } else {
            $query = $em->getRepository('ProductBundle:Product')
                ->productsQuery();
        }

        $paginator = $this->get('knp_paginator');

        /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            9/*limit per page*/
        );

        $pagination->setTemplate('pagination.html.twig');

        return $this->render('ProductBundle:Product:index.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}", name="product_show")
     * @Method("GET")
     *
     * @param Product $product
     * @return Response
     */
    public function showAction(Product $product)
    {
        return $this->render('ProductBundle:Product:show.html.twig', [
            'product' => $product,
        ]);
    }
}
