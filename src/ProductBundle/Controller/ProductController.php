<?php

namespace ProductBundle\Controller;

use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ProductBundle\Service\ProductManager;
use ProductBundle\Form\SelectCategoriesType;

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
     * @param ProductManager $productManager
     * @return Response
     * @throws \LogicException
     * @throws \OutOfBoundsException
     */
    public function indexAction(Request $request, ProductManager $productManager)
    {
        $form = $this->createForm(
            SelectCategoriesType::class,
            null,
            $productManager->getCategoriesTypeFormOptions()
        );

        $form->handleRequest($request);

        return $this->render('ProductBundle:Product:index.html.twig', array(
            'pagination' => $productManager->getPaginatedFrontendProducts($form, $request),
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
