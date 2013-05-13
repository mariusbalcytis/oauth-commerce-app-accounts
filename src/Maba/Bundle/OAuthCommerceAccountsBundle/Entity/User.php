<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    const CLASS_NAME = 'Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Account
     */
    protected $account;


    public function __construct()
    {
        $this->setAccount(new Account());
    }


    public static function create()
    {
        return new static();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }


    /**
     * Returns the roles granted to the user.
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * Returns the password used to authenticate the user.
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * This can return null if the password was not encoded using a salt.
     * @return string The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     * @return void
     */
    public function eraseCredentials()
    {
        // no sensitive information
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account $account
     *
     * @return $this
     */
    public function setAccount($account)
    {
        $this->account = $account;
        $account->setOwner($this);

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }


}