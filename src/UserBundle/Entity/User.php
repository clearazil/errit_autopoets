<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
     * @ORM\OneToMany(targetEntity="Address", mappedBy="user")
     */
    private $addresses;

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
     * @var string
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;

    /**
     * @var string
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

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

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

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
        ]);
    }

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
     * @param \UserBundle\Entity\Address $address
     *
     * @return User
     */
    public function addAddress(\UserBundle\Entity\Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address.
     *
     * @param \UserBundle\Entity\Address $address
     */
    public function removeAddress(\UserBundle\Entity\Address $address)
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
            $address = new Address();
            $address->setIsBilling(false);

            $this->getAddresses()->add($address);

            return $address;
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
     * @param \UserBundle\Entity\UserRole $userRole
     *
     * @return User
     */
    public function addUserRole(\UserBundle\Entity\UserRole $userRole)
    {
        $this->userRoles[] = $userRole;

        return $this;
    }

    /**
     * Remove userRole.
     *
     * @param \UserBundle\Entity\UserRole $userRole
     */
    public function removeUserRole(\UserBundle\Entity\UserRole $userRole)
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
}
