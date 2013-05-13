<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Manager;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountEntry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement;

interface TransferManagerInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @param AccountEntry $entry
     *
     * @return Statement|null
     */
    public function getStatement(AccountEntry $entry);

}