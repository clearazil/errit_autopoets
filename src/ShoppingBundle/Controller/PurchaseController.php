<?php

namespace ShoppingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Form\AddressType;
use ShoppingBundle\Form\PaymentType;
use ShoppingBundle\Service\PurchaseManager;
use ShoppingBundle\Service\PurchaseOrderCreator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use UserBundle\Service\UserManager;

class PurchaseController extends Controller
{
    /**
     * @Route("/checkout", name="purchase_checkout")
     *
     * @param Request $request
     * @param PurchaseManager $purchaseManager
     * @return RedirectResponse|Response
     * @throws AuthenticationCredentialsNotFoundException
     * @throws BadRequestHttpException
     */
    public function checkoutAction(Request $request, PurchaseManager $purchaseManager)
    {
        $addressData = $purchaseManager->getUserDataSession(true);

        if ($addressData === null) {
            return $this->redirectToRoute('purchase_login_or_register');
        }

        $addressForm = $this->createForm(AddressType::class, $addressData);

        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $purchaseManager->updateUserDataSession($addressForm->getData());

            return $this->redirectToRoute('purchase_checkout_confirm');
        }

        return $this->render('ShoppingBundle:Purchase:checkout.html.twig', [
            'errors' => $addressForm->getErrors(),
            'addressForm' => $addressForm->createView(),
        ]);
    }

    /**
     * @Route("/checkout/confirm", name="purchase_checkout_confirm")
     *
     * @param Request $request
     * @param PurchaseManager $purchaseManager
     * @param PurchaseOrderCreator $purchaseOrderCreator
     * @return RedirectResponse|Response
     */
    public function confirmAction(Request $request, PurchaseManager $purchaseManager, PurchaseOrderCreator $purchaseOrderCreator)
    {
        $userData = $purchaseManager->getUserDataSession();

        if ($userData === null) {
            return $this->redirectToRoute('home');
        }

        $paymentForm = $this->createForm(PaymentType::class);

        $paymentForm->handleRequest($request);

        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $purchaseOrderCreator->createPurchaseOrder($paymentForm->getData(), $userData);

            return $this->redirectToRoute('purchase_checkout_success');
        }

        return $this->render('ShoppingBundle:Purchase:checkout-confirm.html.twig', [
            'userData' => $userData,
            'paymentForm' => $paymentForm->createView(),
        ]);
    }

    /**
     * @Route("/checkout/success", name="purchase_checkout_success")
     *
     * @return Response
     */
    public function successAction()
    {
        $session = new Session;

        $session->remove('userData');
        $session->remove('shoppingCart');

        $purchaseOrder = $session->get('purchaseOrder', null);

        if ($purchaseOrder === null) {
            return $this->redirectToRoute('home');
        }

        return $this->render('ShoppingBundle:Purchase:checkout-success.html.twig', [
            'purchaseOrder' => $purchaseOrder,
        ]);

    }

    /**
     * @Route("/checkout/register", name="purchase_login_or_register")
     *
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     * @throws \OutOfBoundsException
     * @throws InvalidOptionsException
     * @throws InvalidArgumentException
     */
    public function loginOrRegisterAction(UserManager $userManager)
    {
        $registerResponse = $userManager->handleRegisterForm();

        // registerForm is handled
        if ($registerResponse === null) {
            return $this->redirectToRoute('purchase_checkout');
        }

        $loginForm = $userManager->handleLoginForm();

        return $this->render('ShoppingBundle:Purchase:login-or-register.html.twig', [
            'loginForm' => $loginForm->createView(),
            'registerForm' => $registerResponse->createView(),
        ]);
    }

    /**
     *
     * @Route("/checkout/register/check", name="purchase_login_or_register_check")
     */
    public function loginOrRegisterCheckAction()
    {
    }
}
