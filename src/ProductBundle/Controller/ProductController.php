<?php

namespace ProductBundle\Controller;

use ProductBundle\Entity\Product;
use ProductBundle\Form\ProductFilterType;
use ProductBundle\Service\ProductManager;
use ProductBundle\Service\SelectedProductView;
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
     * @param ProductManager $productManager
     * @return Response
     */
    public function indexAction(Request $request, ProductManager $productManager): Response
    {
        $form = $this->createForm(
            ProductFilterType::class,
            null,
            $productManager->getProductFilterOptions()
        );

        $form->handleRequest($request);

        return $this->render('ProductBundle:Product:index.html.twig', array(
            'selectedProductView' => SelectedProductView::getInstance($request->getSession()),
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
    public function showAction(Product $product): Response
    {
        return $this->render('ProductBundle:Product:show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/ajax/switch-view/{view}", name="product_switch_view")
     * @Method("GET")
     *
     * @param string $view
     * @param Request $request
     * @param
     * @return Response
     */
    public function switchViewAction(string $view, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $selectedProductView = SelectedProductView::getInstance($request->getSession());
            if ($view === 'grid') {
                $selectedProductView->selectGridView();
            }

            if ($view === 'list') {
                $selectedProductView->selectListView();
            }
        }

        return new Response();
    }
}
