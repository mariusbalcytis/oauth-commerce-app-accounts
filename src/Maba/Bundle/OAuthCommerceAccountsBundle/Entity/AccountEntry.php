<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;


class AccountEntry
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AccountType
     */
    protected $accountType;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var Transfer
     */
    protected $transfer;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $key;


    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public static function create()
    {
        return new static();
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType $accountType
     *
     * @return $this
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer $transfer
     *
     * @return $this
     */
    public function setTransfer($transfer)
    {
        $this->transfer = $transfer;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

}