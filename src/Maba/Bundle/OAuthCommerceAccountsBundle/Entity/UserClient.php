<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;

use Maba\Bundle\OAuthCommerceCommonBundle\Entity\Client;

class UserClient
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var User
     */
    protected $user;


    public static function create()
    {
        return new static();
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceCommonBundle\Entity\Client $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceCommonBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }


}