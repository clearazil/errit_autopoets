<?php

namespace ShoppingBundle\Controller;

use NumberFormatter;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Service\ShoppingCart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("cart")
 */
class ShoppingCartController extends Controller
{
    /**
     * @Route("/", name="cart_index")
     *
     * @param ShoppingCart $shoppingCart
     * @return Response
     */
    public function cartAction(ShoppingCart $shoppingCart)
    {
        return $this->render('ShoppingBundle:ShoppingCart:cart.html.twig', [
            'shoppingCart' => $shoppingCart,
        ]);
    }

    /**
     * @Route("/add/{id}/{amount}", name="cart_add")
     *
     * @param Product $product
     * @param Request $request
     * @param $amount
     * @param ShoppingCart $shoppingCart
     * @return RedirectResponse|Response
     */
    public function addAction(Product $product, Request $request, $amount, ShoppingCart $shoppingCart)
    {
        $shoppingCart->add($product, $amount);

        if ($request->isXmlHttpRequest()) {
            $shoppingCart->updateCart();

            return $this->render('ShoppingBundle:ShoppingCart:_cart-dropdown.html.twig');
        }

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/decrease/{id}/{amount}", name="cart_decrease")
     *
     * @param Product $product
     * @param $amount
     * @param ShoppingCart $shoppingCart
     * @return RedirectResponse
     */
    public function decreaseAction(Product $product, $amount, ShoppingCart $shoppingCart)
    {
        $shoppingCart->decrease($product, $amount);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/remove/{id}", name="cart_remove")
     *
     * @param Product $product
     * @param Request $request
     * @param ShoppingCart $shoppingCart
     * @return JsonResponse|RedirectResponse
     */
    public function removeAction(Product $product, Request $request, ShoppingCart $shoppingCart)
    {
        $shoppingCart->remove($product);

        if ($request->isXmlHttpRequest()) {
            $shoppingCart->updateCart();

            $numberFormatter = new NumberFormatter($request->getLocale(), NumberFormatter::CURRENCY);

            return new JsonResponse([
                'cart_product_count' => $shoppingCart->getProductAmount(),
                'cart_total_price' => $numberFormatter->formatCurrency($shoppingCart->getTotalPrice(), 'EUR'),
                'cart_dropdown' => $this->renderView('ShoppingBundle:ShoppingCart:_cart-dropdown.html.twig'),
                'cart_empty' => $shoppingCart->getProductAmount() <= 0,
            ]);
        }

        return $this->redirectToRoute('cart_index');
    }
}
