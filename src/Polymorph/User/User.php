<?php

namespace Polymorph\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Polymorph User class
 */
class User implements AdvancedUserInterface
{
    private $username;
    private $password;
    private $roles;
    private $enabled;
    private $expired;
    private $credentialsExpired;
    private $locked;

    public function __construct(
        $username,
        $password,
        array $roles = array(),
        $enabled = true,
        $expired = false,
        $credentialsExpired = false,
        $locked = false
    ) {
        if ('' === $username || null === $username) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->expired = $expired;
        $this->credentialsExpired = $credentialsExpired;
        $this->locked = $locked;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return !$this->expired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return !$this->credentialsExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @param string $password Password (already encoded)
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
