<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;

use Maba\Bundle\OAuthCommerceCommonBundle\Entity\Application;
use Maba\Bundle\OAuthCommerceCommonBundle\Entity\Client;

class Transaction
{
    const STATUS_NEW = 0;
    const STATUS_RESERVED = 1;
    const STATUS_DONE = 2;
    const STATUS_REVOKED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_FAILED = 5;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var Transfer
     */
    protected $transfer;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Account
     */
    protected $payer;

    /**
     * @var Account
     */
    protected $beneficiary;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var int
     */
    protected $status = self::STATUS_NEW;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var int
     */
    protected $credentialsId;

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
     * @param \Maba\Bundle\OAuthCommerceCommonBundle\Entity\Application $application
     *
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceCommonBundle\Entity\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account $beneficiary
     *
     * @return $this
     */
    public function setBeneficiary($beneficiary)
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account
     */
    public function getBeneficiary()
    {
        return $this->beneficiary;
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
     * @param int $credentialsId
     *
     * @return $this
     */
    public function setCredentialsId($credentialsId)
    {
        $this->credentialsId = $credentialsId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCredentialsId()
    {
        return $this->credentialsId;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account $payer
     *
     * @return $this
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
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