<?php

namespace ShoppingBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UserBundle\Entity\User;

class PurchaseManager
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var User
     */
    private $user;

    /**
     * PurchaseManager constructor.
     * @param RequestStack $requestStack
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface $tokenStorage
     * @throws BadRequestHttpException
     */
    public function __construct(RequestStack $requestStack, AuthorizationCheckerInterface $authChecker,
                                TokenStorageInterface $tokenStorage)
    {
        $this->authChecker = $authChecker;

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
    }

    /**
     * @param array $data
     * @throws BadRequestHttpException
     */
    public function updateUserDataSession($data)
    {
        if ($this->session->get('userData') === null) {
            throw new BadRequestHttpException();
        }

        $userData = array_merge($this->session->get('userData'), $data);

        $this->session->set('userData', $userData);
    }

    /**
     * @param bool $canCreateSession
     * @return array|null
     */
    public function getUserDataSession($canCreateSession = false)
    {
        $userData = $this->session->get('userData');

        if ($userData === null && $canCreateSession && $this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userAddress = $this->user->getAddress();

            if ($userAddress !== null) {
                $userData = [
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

            $userData['email'] = $this->user->getEmail();
            $userData['createAccount'] = false;

            $this->session->set('userData', $userData);
        }

        return $userData;
    }
}