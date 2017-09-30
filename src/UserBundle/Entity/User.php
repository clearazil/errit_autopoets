<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RandomLib\Factory as RandomLibFactory;
use ShoppingBundle\Entity\PurchaseOrder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Address")
     * @ORM\JoinTable(name="addresses_users",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id", unique=true)}
     *     )
     */
    private $addresses;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="ShoppingBundle\Entity\PurchaseOrder", mappedBy="user")
     */
    private $purchaseOrders;

    /**
     * @ORM\OneToMany(targetEntity="UserRole", mappedBy="user", orphanRemoval=true)
     */
    private $userRoles;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\Email
     * @Assert\NotBlank
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min = 8
     * )
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     */
    private $generatedPassword;
    /**
     * @ORM\Column(name="confirm_user_token", type="string", nullable=true)
     *
     * @var [type]
     */
    private $confirmUserToken;

    /**
     * @ORM\Column(name="recover_password_token", type="string", nullable=true)
     *
     * @var [type]
     */
    private $recoverPasswordToken;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    public function __construct()
    {
        $this->isActive = true;

        $this->addresses = new ArrayCollection();

        $this->userRoles = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getIsActive();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function setGeneratedPassword()
    {
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();

        $password = $generator->generateString(10);

        $this->setPassword($password);
        $this->generatedPassword = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getGeneratedPassword()
    {
        return $this->generatedPassword;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = [];

        foreach ($this->userRoles as $role) {
            $roles[] = $role->getName();
        }

        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        return $roles;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ) = unserialize($serialized);
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * Add address.
     *
     * @param Address $address
     *
     * @return User
     */
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address.
     *
     * @param Address $address
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return Address|null
     */
    public function getAddress()
    {
        if ($this->getAddresses() === null) {
            return null;
        }

        $address = $this->getAddresses()
            ->filter(function (Address $address) {
                return $address->getIsBilling() === false;
            });

        if (empty($address[0])) {
            return null;
        }

        return $address[0];
    }

    public function getBillingAddress()
    {
        if ($this->getAddresses() === null) {
            return null;
        }

        $address = $this->getAddresses()
            ->filter(function (Address $address) {
                return $address->getIsBilling() === true;
            });

        return $address;
    }

    /**
     * Set confirmUserToken.
     *
     * @param string $confirmUserToken
     *
     * @return User
     */
    public function setConfirmUserToken($confirmUserToken = null)
    {
        if ($confirmUserToken === null) {
            $this->confirmUserToken = bin2hex(random_bytes(20));

            return $this;
        }

        $this->confirmUserToken = $confirmUserToken;

        return $this;
    }

    /**
     * Get confirmUserToken.
     *
     * @return string
     */
    public function getConfirmUserToken()
    {
        return $this->confirmUserToken;
    }

    /**
     * Set recoverPasswordToken.
     *
     * @param string $recoverPasswordToken
     *
     * @return User
     */
    public function setRecoverPasswordToken($recoverPasswordToken = null)
    {
        if ($recoverPasswordToken === null) {
            $this->recoverPasswordToken = bin2hex(random_bytes(20));

            return $this;
        }

        $this->recoverPasswordToken = $recoverPasswordToken;

        return $this;
    }

    /**
     * Get recoverPasswordToken.
     *
     * @return string
     */
    public function getRecoverPasswordToken()
    {
        return $this->recoverPasswordToken;
    }

    /**
     * Add userRole.
     *
     * @param UserRole $userRole
     *
     * @return User
     */
    public function addUserRole(UserRole $userRole)
    {
        $this->userRoles[] = $userRole;

        return $this;
    }

    /**
     * Remove userRole.
     *
     * @param UserRole $userRole
     */
    public function removeUserRole(UserRole $userRole)
    {
        $this->userRoles->removeElement($userRole);
    }

    /**
     * Get userRoles.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * Get role choices for the form.
     *
     * @return array
     */
    public function getRoleChoices()
    {
        $rolesFound = [
            UserRole::ROLE_ADMIN => false,
            UserRole::ROLE_USER => false,
        ];

        $roles = [];
        foreach ($this->getUserRoles() as $role) {
            $roles[] = $role;
            $rolesFound[$role->getId()] = true;
        }

        foreach ($rolesFound as $key => $found) {
            if (!$found) {
                $roles[] = new UserRole($key);
            }
        }

        asort($roles);

        return $roles;
    }

    /**
     * Add purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return User
     */
    public function addPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrders[] = $purchaseOrder;

        return $this;
    }

    /**
     * Remove purchaseOrder
     *
     * @param \ShoppingBundle\Entity\PurchaseOrder $purchaseOrder
     */
    public function removePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrders->removeElement($purchaseOrder);
    }

    /**
     * Get purchaseOrders
     *
     * @return Collection
     */
    public function getPurchaseOrders()
    {
        return $this->purchaseOrders;
    }
}
