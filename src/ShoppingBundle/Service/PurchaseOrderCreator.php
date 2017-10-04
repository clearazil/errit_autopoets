<?php

namespace ShoppingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ShoppingBundle\Entity\PurchaseOrder;
use ShoppingBundle\Entity\PurchaseOrderLine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

class PurchaseOrderCreator
{
    /**
     * @var Address
     */
    private $unpersistedAddress;

    /**
     * @var PurchaseOrder
     */
    private $unpersistedPurchaseOrder;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var ShoppingCart
     */
    private $shoppingCart;

    /**
     * @var User
     */
    private $user;

    /**
     * PurchaseManager constructor.
     * @param RequestStack $requestStack
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $entityManager
     * @param ShoppingCart $shoppingCart
     * @throws BadRequestHttpException
     */
    public function __construct(RequestStack $requestStack, AuthorizationCheckerInterface $authChecker,
                                TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, ShoppingCart $shoppingCart)
    {
        $this->authChecker = $authChecker;

        $this->entityManager = $entityManager;

        $request = $requestStack->getCurrentRequest();

        $token = $tokenStorage->getToken();

        if ($request === null || $token === null) {
            throw new BadRequestHttpException();
        }

        $this->session = $request->getSession();

        if ($this->session === null) {
            throw new BadRequestHttpException();
        }

        $this->user = $token->getUser();

        $this->shoppingCart = $shoppingCart;
    }

    /**
     * @param array $userData
     * @return Address
     */
    private function setUnpersistedAddress($userData)
    {
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

        $this->unpersistedAddress = $address;

        return $address;
    }

    /**
     * @return Address
     */
    private function getUnpersistedAddress()
    {
        return $this->unpersistedAddress;
    }

    /**
     * @param array $paymentData
     * @param array $userData
     * @return PurchaseOrder
     */
    private function setUnpersistedPurchaseOrder($paymentData, $userData)
    {
        $purchaseOrder = new Purchaseorder();

        $address = $this->setUnpersistedAddress($userData);

        $purchaseOrder->getAddresses()->add($address);
        $purchaseOrder->setShippingCosts($this->shoppingCart->getShippingCost());
        $purchaseOrder->setEmail($userData['email']);

        // TODO: payment received from ideal?
        $purchaseOrder->setStatus(PurchaseOrder::STATUS_PAYMENT_NOT_RECEIVED);
        $purchaseOrder->setPaymentMethod($paymentData['payment']);

        $this->unpersistedPurchaseOrder = $purchaseOrder;

        return $purchaseOrder;
    }

    /**
     * @return PurchaseOrder
     */
    private function getUnpersistedPurchaseOrder()
    {
        return $this->unpersistedPurchaseOrder;
    }

    /**
     * @param array $paymentData
     * @param array $userData
     */
    public function createPurchaseOrder($paymentData, $userData)
    {
        $purchaseOrder = $this->setUnpersistedPurchaseOrder($paymentData, $userData);

        if ($userData['createAccount'] || $this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getOrCreateUser($userData['createAccount']);

            $purchaseOrder->setUser($user);
        }

        $this->entityManager->persist($purchaseOrder);

        $this->createPurchaseOrderLines($purchaseOrder);

        $this->entityManager->persist($this->getUnpersistedAddress());
        $this->entityManager->flush();

        $this->session->set('purchaseOrder', $purchaseOrder);
    }

    /**
     * @param $createAccount
     * @return User
     */
    private function getOrCreateUser($createAccount)
    {
        if ($createAccount) {
            $user = new User();

            $user->setEmail($this->getUnpersistedPurchaseOrder()->getEmail());
            $user->setUsername($user->getEmail());
            $user->addAddress($this->getUnpersistedAddress());

            $user->setIsActive(true);
            $user->setGeneratedPassword();

            $this->entityManager->persist($user);

            $this->sendEmailPassword($user);

            return $user;
        }

        return $this->user;
    }

    /**
     * @param $purchaseOrder
     */
    private function createPurchaseOrderLines($purchaseOrder)
    {
        foreach ($this->shoppingCart->getProducts() as $product) {
            $purchaseOrderLine = new PurchaseOrderLine();

            $purchaseOrderLine->setName($product->getName());
            $purchaseOrderLine->setSubtotal(0);
            $purchaseOrderLine->setPrice($product->getPrice());
            $purchaseOrderLine->setVat(21);
            $purchaseOrderLine->setAmount($product->getAmount());

            $productEntity = $this->entityManager->getRepository('ProductBundle:Product')
                ->find($product->getProductId());

            $purchaseOrderLine->setProduct($productEntity);

            $purchaseOrderLine->setPurchaseOrder($purchaseOrder);

            $this->entityManager->persist($purchaseOrderLine);
        }
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