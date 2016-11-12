<?php

namespace Polymorph\User;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Polymorph\Application\Application;

class UserProvider implements UserProviderInterface
{
    /** @var Application */
    protected $app;

    /** @var Connection */
    protected $conn;

    /** @var User */
    protected $currentUser;

    /**
     * UserProvider constructor
     *
     * @param Application $app Application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->conn = $app['db']->connect('users');
        $this->currentUser = null;
    }

    /**
     * Returns a query builder for the user database
     *
     * @return QueryBuilder
     */
    protected function query()
    {
        return $this->conn->createQueryBuilder();
    }

    /**
     * Creates a password encoder
     *
     * @return BCryptPasswordEncoder
     */
    protected function getEncoder()
    {
        return new BCryptPasswordEncoder(10);
    }

    /**
     * Loads a user from the database
     *
     * @param string $username
     * @return User User object
     */
    public function loadUserByUsername($username)
    {
        $user = $this->query()
            ->select('*')
            ->from('User')
            ->where('username = ?')->setParameter(0, strtolower($username))
            ->execute()
            ->fetch();

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new User(
            $user['username'],
            $user['password'],
            explode(',', $user['roles']),
            $user['enabled'],
            !$user['expired'],
            !$user['credentialsExpired'],
            !$user['locked']
        );
    }

    /**
     * Reloads user data from the database
     *
     * @param UserInterface $user
     * @return User User object
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Verifies the user object class
     *
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }

    /**
     * Adds a user reference to the provider and the session
     *
     * @param $user
     * @return bool TRUE on success
     */
    public function setCurrentUser($user)
    {
        if (is_string($user)) {
            $user = $this->loadUserByUsername($user);
        }
        $this->currentUser = $user;
        /** @var Session $session */
        $session = $this->app['session'];
        if ($session) {
            $session->set('user', $user->getUsername());
        }
        return true;
    }

    /**
     * Returns the currently active user
     *
     * @param bool $loadFromSession Whether to init the user from the session cookie
     * @return User Logged-in user or guest user object
     */
    public function getCurrentUser($loadFromSession = true)
    {
        // load from session
        if ($this->currentUser === null && $loadFromSession) {
            $this->loadUserFromSession();
        }
        // return user or guest user
        return $this->currentUser
            ? $this->currentUser
            : new User('guest', '', ['guest'], true, true, true, true);
    }

    /**
     * Initializes the current user from the session
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    protected function loadUserFromSession()
    {
        /** @var Session $session */
        $session = $this->app['session'];

        if (!$session) {
            return false;
        }

        $username = $session->get('user');
        if (!$username) {
            return false;
        }

        try {
            $user = $this->loadUserByUsername($username);
            $this->setCurrentUser($user);
            return true;
        } catch (UsernameNotFoundException $exception) {
            return false;
        }
    }

    /**
     * Validates given username and (plain) password
     *
     * @param $username
     * @param $password
     * @return bool TRUE if valid, FALSE otherwise
     */
    public function validateCredentials($username, $password)
    {
        try {
            $user = $this->loadUserByUsername($username);
            return $this->getEncoder()->isPasswordValid($user->getPassword(), $password, '');
        } catch (UsernameNotFoundException $exception) {
            return false;
        }
    }

    /**
     * Encodes a password using the configured encoder
     *
     * @param $password
     * @return string
     */
    public function encodePassword($password)
    {
        return $this->getEncoder()->encodePassword($password, null);
    }

    /**
     * Checks if the current or passed user has a specific role
     *
     * @param string $role A role, e.g. "admin"
     * @param User|null $user
     * @return bool TRUE on success, FALSE otherwise
     */
    public function hasRole($role, User $user = null)
    {
        if ($user === null) {
            $user = $this->getCurrentUser();
        }

        if (!$user->isEnabled() ||
            !$user->isAccountNonExpired() ||
            !$user->isAccountNonLocked() ||
            !$user->isCredentialsNonExpired()) {
            return false;
        }

        return in_array($role, $user->getRoles());
    }

    /**
     * Checks whether a username is already stored in the database
     *
     * @param $username
     * @return bool TRUE if user exists, FALSE otherwise
     */
    public function usernameExists($username)
    {
        $user = $this->query()
            ->select('username')
            ->from('User')
            ->where('username = ?')->setParameter(0, strtolower($username))
            ->execute()
            ->fetch();
        return !!$user;
    }

    /**
     * Deletes a user from the database
     *
     * @param User $user
     * @return \Doctrine\DBAL\Driver\Statement|int Number of deleted rows
     */
    public function deleteUser(User $user)
    {
        return $this->query()
            ->delete('User')
            ->where('username = ?')->setParameter(0, $user->getUsername())
            ->execute();
    }

    /**
     * Inserts or replaces a user in the database
     * @param User $user
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function saveUser(User $user)
    {
        if (!$this->usernameExists($user->getUsername())) {
            return $this->insertUser($user);
        } else {
            return $this->updateUser($user);
        }
    }

    /**
     * Inserts a user into the database
     *
     * @param User $user
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    protected function insertUser(User $user)
    {
        return $this->query()
            ->insert('User')
            ->setValue('username', '?')             ->setParameter(0, strtolower($user->getUsername()))
            ->setValue('password', '?')             ->setParameter(1, $user->getPassword())
            ->setValue('roles', '?')                ->setParameter(2, join(',', $user->getRoles()))
            ->setValue('enabled', '?')              ->setParameter(3, $user->isEnabled())
            ->setValue('expired', '?')              ->setParameter(4, !$user->isAccountNonExpired())
            ->setValue('credentialsExpired', '?')   ->setParameter(5, !$user->isCredentialsNonExpired())
            ->setValue('locked', '?')               ->setParameter(6, !$user->isAccountNonLocked())
            ->execute();
    }

    /**
     * Updates a user entry in the database
     *
     * @param User $user
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    protected function updateUser(User $user)
    {
        return $this->query()
            ->update('User')
            ->where('username = ?')->setParameter(0, strtolower($user->getUsername()))
            ->set('password', '?')             ->setParameter(1, $user->getPassword())
            ->set('roles', '?')                ->setParameter(2, join(',', $user->getRoles()))
            ->set('enabled', '?')              ->setParameter(3, $user->isEnabled())
            ->set('expired', '?')              ->setParameter(4, !$user->isAccountNonExpired())
            ->set('credentialsExpired', '?')   ->setParameter(5, !$user->isCredentialsNonExpired())
            ->set('locked', '?')               ->setParameter(6, !$user->isAccountNonLocked())
            ->execute();
    }
}
