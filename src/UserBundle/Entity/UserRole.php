<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRole.
 *
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRoleRepository")
 */
class UserRole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="role_id", type="integer")
     */
    private $roleId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userRoles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    const ROLE_ADMIN = 1;
    const ROLE_USER = 2;

    public function __construct($roleId)
    {
        $this->setRoleId($roleId);
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

    public function roleNames()
    {
        return [
            self::ROLE_ADMIN => 'ROLE_ADMIN',
            self::ROLE_USER => 'ROLE_USER',
        ];
    }

    public function roleLabels()
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_USER => 'User',
        ];
    }

    /**
     * Set roleId.
     *
     * @param int $roleId
     *
     * @return UserRole
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getLabel()
    {
        $labels = $this->roleLabels();

        return $labels[$this->getRoleId()];
    }

    public function getName()
    {
        $names = $this->roleNames();

        return $names[$this->getRoleId()];
    }

    /**
     * Get roleId.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set user.
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return UserRole
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
