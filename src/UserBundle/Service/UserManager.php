<?php

namespace UserBundle\Service;

use ShoppingBundle\Form\RegisterType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;
use UserBundle\Form\LoginType;

class UserManager
{
    /**
     * @var AuthenticationUtils
     */
    private $authUtils;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var null|Request
     */
    private $request;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * UserManager constructor.
     * @param AuthenticationUtils $authUtils
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @throws BadRequestHttpException
     */
    public function __construct(AuthenticationUtils $authUtils, FormFactoryInterface $formFactory, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->authUtils = $authUtils;
        $this->formFactory = $formFactory;
        $this->translator = $translator;

        $this->request = $requestStack->getCurrentRequest();

        if ($this->request === null) {
            throw new BadRequestHttpException();
        }

        $this->session = $this->request->getSession();

        if ($this->session === null) {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     * @throws InvalidOptionsException
     * @throws InvalidArgumentException
     */
    public function handleLoginForm()
    {
        $defaultData = ['username' => $this->authUtils->getLastUsername()];

        $loginForm = $this->formFactory->create(LoginType::class, $defaultData);

        $loginForm->handleRequest($this->request);

        $lastAuthError = $this->getLastAuthenticationError();

        if ($lastAuthError !== null) {
            $loginForm->addError($lastAuthError);
        }

        return $loginForm;
    }

    /**
     * @return null|\Symfony\Component\Form\FormInterface
     * @throws InvalidOptionsException
     * @throws \OutOfBoundsException
     */
    public function handleRegisterForm()
    {
        $registerForm = $this->formFactory->create(RegisterType::class);

        $registerForm->handleRequest($this->request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $this->session->set('userData', ['email' => $registerForm->get('email')->getData(), 'createAccount' => $registerForm->get('create_account')->getData()]);

            return null;
        }

        return $registerForm;
    }

    /**
     * @return null|FormError
     * @throws InvalidArgumentException
     */
    private function getLastAuthenticationError()
    {
        $lastAuthError = $this->authUtils->getLastAuthenticationError();
        if ($lastAuthError !== null) {
            $message = $this->translator->trans($lastAuthError->getMessageKey(), [], 'security');
            return new FormError($message);
        }

        return null;
    }
}