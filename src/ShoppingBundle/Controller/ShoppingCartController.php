<?php

namespace ShoppingBundle\Controller;

use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Service\ShoppingCart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use NumberFormatter;

/**
 * @Route("cart")
 */
class ShoppingCartController extends Controller
{
    /**
     * @Route("/", name="cart_index")
     */
    public function cartAction(ShoppingCart $shoppingCart)
    {
        return $this->render('ShoppingBundle:ShoppingCart:cart.html.twig', [
            'shoppingCart' => $shoppingCart,
        ]);
    }

    /**
     * @Route("/add/{id}/{amount}", name="cart_add")
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
     */
    public function decreaseAction(Product $product, $amount, ShoppingCart $shoppingCart)
    {
        $shoppingCart->decrease($product, $amount);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/remove/{id}", name="cart_remove")
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

    private function entityToJson($entity)
    {
        $encoders = [new JsonEncoder];
        $normalizers = [new ObjectNormalizer];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($entity, 'json');

        return $jsonContent;
    }
}
