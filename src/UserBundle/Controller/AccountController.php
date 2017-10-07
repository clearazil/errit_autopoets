<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Entity\PurchaseOrder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;
use UserBundle\Form\AccountType;
use UserBundle\Form\NewPasswordType;
use UserBundle\Form\RecoverPasswordType;
use UserBundle\Form\RegisterType;
use UserBundle\Service\UserManager;

class AccountController extends Controller
{
    /**
     * @Route("/login", name="login")
     *
     * @param UserManager $userManager
     * @return Response
     * @throws InvalidOptionsException
     * @throws InvalidArgumentException
     */
    public function loginAction(UserManager $userManager)
    {
        $form = $userManager->handleLoginForm();

        return $this->render('UserBundle::Account/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     * @param UserManager $userManager
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws InvalidArgumentException
     */
    public function registerAction(Request $request, UserManager $userManager)
    {
        $user = new User();

        $address = new Address();
        $address->setIsBilling(false);

        $user->getAddresses()->add($address);

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->registerUser($user);

            return $this->render('UserBundle::Account/register-success.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('UserBundle::Account/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account", name="account")
     *
     * @param UserInterface $user
     * @return Response
     */
    public function accountAction(UserInterface $user)
    {
        return $this->render('UserBundle::Account/account.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/edit", name="account_edit")
     *
     * @param Request $request
     * @param User|UserInterface $user
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     */
    public function editAccountAction(Request $request, UserInterface $user, UserManager $userManager)
    {
        if ($user->getAddress() === null) {
            $address = new Address;
            $address->setIsBilling(false);

            $user->getAddresses()->add($address);
        }

        $form = $this->createForm(AccountType::class, $user);

        $user->setOriginalPassword($user->getPassword());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            return $this->redirectToRoute('account');
        }

        return $this->render('UserBundle::Account/account-edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account/orders", name="account_orders")
     *
     * @param UserInterface $user
     * @return Response
     */
    public function accountOrdersAction(UserInterface $user)
    {
        return $this->render('UserBundle::Account/orders.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/orders/view/{id}", name="account_orders_view")
     * @ParamConverter("purchaseOrder", options={"mapping": {"id" : "id"}})
     *
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function viewAccountOrderAction(PurchaseOrder $purchaseOrder)
    {
        return $this->render('UserBundle::Account/order-view.html.twig', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    /**
     * @Route("/activate/{confirmUserToken}", name="activate")
     * @ParamConverter("user", options={"mapping": {"confirmUserToken" : "confirmUserToken"}})
     *
     * @param User $user
     * @param UserManager $userManager
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     */
    public function activateAccountAction(User $user, UserManager $userManager)
    {
        $userManager->activateUser($user);

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/recover-password", name="recover_password")
     *
     * @param Request $request
     * @param UserManager $userManager;
     * @return Response
     * @throws \OutOfBoundsException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws InvalidArgumentException
     */
    public function recoverPasswordAction(Request $request, UserManager $userManager)
    {
        $form = $this->createForm(RecoverPasswordType::class, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->recoverPassword($form->get('email')->getData());
        }

        return $this->render('UserBundle::Account/recover-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new-password/{recoverPasswordToken}", name="new_password")
     * @ParamConverter("user", options={"mapping": {"recoverPasswordToken" : "recoverPasswordToken"}})
     *
     * @param Request $request
     * @param User $user
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     * @throws InvalidArgumentException
     */
    public function setPasswordAction(Request $request, User $user, UserManager $userManager)
    {
        $form = $this->createForm(NewPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->setNewPassword($user);

            return $this->redirectToRoute('login');
        }

        return $this->render('UserBundle::Account/new-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
