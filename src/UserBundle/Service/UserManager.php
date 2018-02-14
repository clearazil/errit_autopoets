<?php

namespace UserBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use ShoppingBundle\Form\RegisterType;
use Swift_Mailer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use UserBundle\Entity\User;
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
     * @var null|SessionInterface|Session
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * UserManager constructor.
     * @param AuthenticationUtils $authUtils
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param Swift_Mailer $mailer
     * @param Twig_Environment $twig
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param Paginator $paginator
     * @throws BadRequestHttpException
     */
    public function __construct(AuthenticationUtils $authUtils, FormFactoryInterface $formFactory, RequestStack $requestStack,
                                TranslatorInterface $translator, EntityManagerInterface $entityManager, Swift_Mailer $mailer,
                                Twig_Environment $twig, UserPasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage,
                                EventDispatcherInterface $eventDispatcher, Paginator $paginator)
    {
        $this->authUtils = $authUtils;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->paginator = $paginator;

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
     * @param User $user
     * @throws InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function registerUser($user)
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
        $user->setIsActive(false);
        $user->setConfirmUserToken();

        $this->entityManager->persist($user);

        $address = $user->getAddress();

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        $message = new \Swift_Message($this->translator->trans('USER_THANKS_FOR_REGISTERING', [], 'user'));

        $message->setFrom('erritsjoerd@hotmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    'UserBundle::Email/registration.html.twig', [
                        'user' => $user,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param User $user
     */
    public function createUser($user)
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

        $this->entityManager->persist($user);

        foreach ($user->getUserRoles() as $role) {
            $role->setUser($user);
            $this->entityManager->persist($role);
        }

        $this->entityManager->persist($user->getAddress());

        $this->entityManager->flush();
    }

    /**
     * @param User $user
     */
    public function updateUser($user)
    {
        if (empty($user->getPassword())) {
            $user->setPassword($user->getOriginalPassword());
        } else {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
        }

        foreach ($user->getUserRoles() as $role) {
            $role->setUser($user);
            $this->entityManager->persist($role);
        }

        if ($user->getAddress() !== null) {
            $this->entityManager->persist($user->getAddress());
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @throws \InvalidArgumentException
     */
    public function activateUser($user)
    {
        $user->setIsActive(true);
        $user->setConfirmUserToken('');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $event = new InteractiveLoginEvent($this->request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);

        $this->session->getFlashBag()->add('activate_success', $this->translator->trans('USER_ACTIVATE_SUCCESS', [], 'user'));
    }

    /**
     * @param string $email
     * @throws InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws NonUniqueResultException
     */
    public function recoverPassword($email)
    {
        $repository = $this->entityManager->getRepository('UserBundle:User');

        $user = $repository->findUserByEmail($email);

        if ($user !== null) {
            $this->session->getFlashBag()->add(
                'success', $this->translator->trans('USER_RECOVER_PASSWORD_EMAIL_SENT', [], 'user'));

            $user->setRecoverPasswordToken();
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $message = new \Swift_Message($this->translator->trans('USER_NEW_PASSWORD_REQUESTED', [], 'user'));

            $message->setFrom('erritsjoerd@hotmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->twig->render(
                        'UserBundle::Email/recover-password.html.twig', [
                            'user' => $user,
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        } else {
            $this->session->getFlashBag()->add(
                'error', $this->translator->trans('USER_RECOVER_PASSWORD_EMAIL_UNKNOWN', [], 'user'));
        }
    }

    /**
     * @param User $user
     * @throws InvalidArgumentException
     */
    public function setNewPassword($user)
    {
        $user->setRecoverPasswordToken('');
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->session->getFlashBag()->add(
            'success', $this->translator->trans('USER_RECOVER_PASSWORD_SUCCESS', [], 'user'));
    }

    /**
     * @return SlidingPagination
     * @throws \LogicException
     */
    public function getPaginatedUsers()
    {
        $query = $this->entityManager->getRepository('UserBundle:User')
            ->usersQuery();

        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $this->request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/,
            ['defaultSortFieldName' => 'user.created_at', 'defaultSortDirection' => 'desc']
        );

        return $pagination;
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
