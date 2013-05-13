<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;


class Statement
{
    const TYPE_NORMAL = 'normal';
    const TYPE_RESERVATION = 'reservation';

    /**
     * @var Account
     */
    protected $otherParty;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var int
     */
    protected $entryId;

    /**
     * @var int
     */
    protected $transferId;


    public static function create()
    {
        return new static();
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
     * @param \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account $otherParty
     *
     * @return $this
     */
    public function setOtherParty($otherParty)
    {
        $this->otherParty = $otherParty;

        return $this;
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account
     */
    public function getOtherParty()
    {
        return $this->otherParty;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @param int $entryId
     *
     * @return $this
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param int $transferId
     *
     * @return $this
     */
    public function setTransferId($transferId)
    {
        $this->transferId = $transferId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTransferId()
    {
        return $this->transferId;
    }


}