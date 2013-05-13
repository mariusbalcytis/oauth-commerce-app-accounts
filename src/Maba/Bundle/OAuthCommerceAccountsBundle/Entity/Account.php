<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;


class Account
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AccountType
     */
    protected $disposableAccountType;

    /**
     * @var AccountType
     */
    protected $reservationAccountType;

    /**
     * @var User
     */
    protected $owner;


    public function __construct()
    {
        $this->disposableAccountType = new AccountType();
        $this->reservationAccountType = new AccountType();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType $disposableAccountType
     *
     * @return $this
     */
    public function setDisposableAccountType($disposableAccountType)
    {
        $this->disposableAccountType = $disposableAccountType;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType
     */
    public function getDisposableAccountType()
    {
        return $this->disposableAccountType;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType $reservationAccountType
     *
     * @return $this
     */
    public function setReservationAccountType($reservationAccountType)
    {
        $this->reservationAccountType = $reservationAccountType;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType
     */
    public function getReservationAccountType()
    {
        return $this->reservationAccountType;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

}