<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Entity;


class Transfer
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AccountEntry[]
     */
    protected $entries;

    /**
     * @var string
     */
    protected $managerKey;


    public static function create()
    {
        return new static();
    }


    public function addEntry(AccountEntry $entry)
    {
        $this->entries[] = $entry;
        $entry->setTransfer($this);

        return $this;
    }

    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $managerKey
     *
     * @return $this
     */
    public function setManagerKey($managerKey)
    {
        $this->managerKey = $managerKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getManagerKey()
    {
        return $this->managerKey;
    }



}