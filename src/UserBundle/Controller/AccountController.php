<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Entity\PurchaseOrder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

class AccountController extends Controller
{
    /**
     * @Route("/login", name="login")
     *
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $authUtils = $this->get('security.authentication_utils');

        $defaultData = ['username' => $authUtils->getLastUsername()];

        $form = $this->createForm('UserBundle\Form\LoginType', $defaultData);

        $lastAuthError = $authUtils->getLastAuthenticationError();
        if ($lastAuthError !== null) {
            $message = $this->get('translator')->trans($lastAuthError->getMessageKey(), [], 'security');
            $authenticationError = new FormError($message);
            $form->addError($authenticationError);
        }

        $form->handleRequest($request);

        return $this->render('UserBundle::Account/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $address = new Address();
        $address->setIsBilling(false);

        $user->getAddresses()->add($address);

        $form = $this->createForm('UserBundle\Form\RegisterType', $user);
        $form->handleRequest($request);

        $user->setIsActive(false);
        $user->setConfirmUserToken();

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->container->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            $address->setUser($user);
            $em->persist($address);
            $em->flush();

            $message = new \Swift_Message($this->get('translator')->trans('USER_THANKS_FOR_REGISTERING', [], 'user'));

            $message->setFrom('erritsjoerd@hotmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'UserBundle::Email/registration.html.twig',
                        [
                            'user' => $user,
                        ]
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);

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
     * @return RedirectResponse|Response
     */
    public function editAccountAction(Request $request, UserInterface $user)
    {
        if ($user->getAddress() === null) {
            $address = new Address;
            $address->setIsBilling(false);

            $user->getAddresses()->add($address);
        }

        $form = $this->createForm('UserBundle\Form\AccountType', $user);

        $originalPassword = $user->getPassword();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($user->getPassword())) {
                $user->setPassword($originalPassword);
            } else {
                $encoder = $this->container->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

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
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function activateAccountAction(Request $request, User $user)
    {
        $user->setIsActive(true);
        $user->setConfirmUserToken('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('activate_success', $this->get('translator')->trans('USER_ACTIVATE_SUCCESS', [], 'user'));

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/recover-password", name="recover_password")
     *
     * @param Request $request
     * @return Response
     */
    public function recoverPasswordAction(Request $request)
    {
        $form = $this->createForm('UserBundle\Form\RecoverPasswordType', []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('UserBundle:User');

            $user = $repository->findUserByEmail($form->get('email')->getData());

            /** @var Session $session */
            $session = $request->getSession();

            if ($user !== null) {
                $session->getFlashBag()->add('recover_password_status', ['status' => 'alert-success', 'message' => $this->get('translator')->trans('USER_RECOVER_PASSWORD_EMAIL_SENT', [], 'user')]);

                $user->setRecoverPasswordToken();
                $em->persist($user);
                $em->flush();

                $message = new \Swift_Message($this->get('translator')->trans('USER_NEW_PASSWORD_REQUESTED', [], 'user'));

                $message->setFrom('erritsjoerd@hotmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'UserBundle::Email/recover-password.html.twig',
                            [
                                'user' => $user,
                            ]
                        ),
                        'text/html'
                    );

                $this->get('mailer')->send($message);
            } else {
                $session->getFlashBag()->add('recover_password_status', ['status' => 'alert-warning', 'message' => $this->get('translator')->trans('USER_RECOVER_PASSWORD_EMAIL_UNKNOWN', [], 'user')]);
            }
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
     * @return RedirectResponse|Response
     */
    public function setPasswordAction(Request $request, User $user)
    {
        $form = $this->createForm('UserBundle\Form\NewPasswordType', $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRecoverPasswordToken('');
            $encoder = $this->container->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('recover_password_status', ['status' => 'alert-success', 'message' => $this->get('translator')->trans('COMMON_RECOVER_PASSWORD_SUCCESS', [], 'common')]);

            return $this->redirectToRoute('login');
        }

        return $this->render('UserBundle::Account/new-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
