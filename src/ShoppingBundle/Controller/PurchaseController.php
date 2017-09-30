<?php

namespace ShoppingBundle\Controller;

use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Entity\PurchaseOrder;
use ShoppingBundle\Entity\PurchaseOrderLine;
use ShoppingBundle\Form\AddressType;
use ShoppingBundle\Service\ShoppingCart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

class PurchaseController extends Controller
{
    /**
     * @Route("/checkout", name="purchase_checkout")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function checkoutAction(Request $request)
    {
        $defaultData = [];

        $createAccount = false;

        $authChecker = $this->get('security.authorization_checker');
        if (!$authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $session = new Session();

            $userData = $session->get('userData');

            if ($userData === null) {
                return $this->redirectToRoute('purchase_login_or_register');
            } else {
                $createAccount = $userData['createAccount'];

                $defaultData['email'] = $userData['email'];
            }
        } else {
            /* @var User $user */
            $user = $this->getUser();

            $userAddress = $user->getAddress();

            if ($userAddress !== null) {
                $defaultData = [
                    'firstName' => $userAddress->getFirstName(),
                    'lastName' => $userAddress->getLastName(),
                    'companyName' => $userAddress->getCompanyName(),
                    'address' => $userAddress->getAddress(),
                    'houseNumber' => $userAddress->getHouseNumber(),
                    'city' => $userAddress->getCity(),
                    'zipCode' => $userAddress->getZipCode(),
                    'phoneNumber' => $userAddress->getPhoneNumber(),
                ];
            }

            $defaultData['email'] = $user->getEmail();
        }

        $addressForm = $this->createForm(AddressType::class, $defaultData);

        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $session = new Session();

            $session->set('userData', array_merge($addressForm->getData(), ['createAccount' => $createAccount]));

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
     * @param ShoppingCart $shoppingCart
     * @return RedirectResponse|Response
     */
    public function confirmAction(Request $request, ShoppingCart $shoppingCart)
    {
        $session = new Session;

        $userData = $session->get('userData');

        if ($userData === null) {
            return $this->redirectToRoute('home');
        }

        $paymentForm = $this->createForm('ShoppingBundle\Form\PaymentType');

        $paymentForm->handleRequest($request);

        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $formData = $paymentForm->getData();

            $em = $this->getDoctrine()->getManager();

            $purchaseOrder = new Purchaseorder();

            $address = new Address();
            $address->setIsBilling(false);

            $address->setFirstName($userData['firstName']);
            $address->setLastName($userData['lastName']);
            $address->setCompanyName($userData['companyName']);
            $address->setAddress($userData['address']);
            $address->setHouseNumber($userData['houseNumber']);
            $address->setCity($userData['city']);
            $address->setZipCode($userData['zipCode']);
            $address->setPhoneNumber($userData['phoneNumber']);

            $purchaseOrder->getAddresses()->add($address);
            $purchaseOrder->setShippingCosts($shoppingCart->getShippingCost());
            $purchaseOrder->setEmail($userData['email']);

            // TODO: payment received from ideal?
            $purchaseOrder->setStatus(PurchaseOrder::STATUS_PAYMENT_NOT_RECEIVED);
            $purchaseOrder->setPaymentMethod($formData['payment']);

            $authChecker = $this->get('security.authorization_checker');
            if ($authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $user = $this->get('security.token_storage')->getToken()->getUser();

                $purchaseOrder->setUser($user);
            } else {
                $user = new User();

                $user->setEmail($userData['email']);
                $user->setUsername($user->getEmail());
                $user->addAddress($address);

                if ($userData['createAccount']) {
                    $user->setIsActive(true);
                    $user->setGeneratedPassword();

                    $em->persist($user);

                    $this->sendEmailPassword($user);
                }
            }

            $em->persist($purchaseOrder);

            foreach ($shoppingCart->getProducts() as $product) {
                $purchaseOrderLine = new PurchaseOrderLine();

                $purchaseOrderLine->setName($product->getName());
                $purchaseOrderLine->setSubtotal(0);
                $purchaseOrderLine->setPrice($product->getPrice());
                $purchaseOrderLine->setVat(21);
                $purchaseOrderLine->setAmount($product->getAmount());

                $productEntity = $this->getDoctrine()
                    ->getRepository(Product::class)
                    ->find($product->getProductId());

                $purchaseOrderLine->setProduct($productEntity);

                $purchaseOrderLine->setPurchaseOrder($purchaseOrder);

                $em->persist($purchaseOrderLine);
            }

            $em->persist($address);
            $em->flush();

            $session->set('purchaseOrder', $purchaseOrder);

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
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function loginOrRegisterAction(Request $request)
    {
        $session = new Session();

        $registerForm = $this->createForm('ShoppingBundle\Form\RegisterType');

        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $session->set('userData', ['email' => $registerForm->get('email')->getData(), 'createAccount' => $registerForm->get('create_account')->getData()]);

            return $this->redirectToRoute('purchase_checkout');
        }

        $authUtils = $this->get('security.authentication_utils');

        $defaultData = ['username' => $authUtils->getLastUsername()];

        $loginForm = $this->createForm('UserBundle\Form\LoginType', $defaultData);
        $loginForm->handleRequest($request);

        $lastAuthError = $authUtils->getLastAuthenticationError();
        if ($lastAuthError !== null) {
            $message = $this->get('translator')->trans($lastAuthError->getMessageKey(), [], 'security');
            $authenticationError = new FormError($message);
            $loginForm->addError($authenticationError);
        }

        return $this->render('ShoppingBundle:Purchase:login-or-register.html.twig', [
            'loginForm' => $loginForm->createView(),
            'registerForm' => $registerForm->createView(),
        ]);
    }

    /**
     *
     * @Route("/checkout/register/check", name="purchase_login_or_register_check")
     */
    public function loginOrRegisterCheckAction()
    {
    }

    /**
     * TODO
     * @param $user User
     */
    private function sendEmailPassword($user)
    {
        $generatedPassword = $user->getGeneratedPassword();

        if ($generatedPassword !== null) {
            // TODO send email
        }

        // TODO throw error
    }
}
