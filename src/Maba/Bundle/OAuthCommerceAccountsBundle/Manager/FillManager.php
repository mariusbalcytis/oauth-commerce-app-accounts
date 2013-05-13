<?php

namespace Maba\Bundle\OAuthCommerceAccountsBundle\Manager;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountEntry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer;

class FillManager implements TransferManagerInterface
{

    /**
     * @param Account $account
     * @param int     $amount
     *
     * @throws \InvalidArgumentException
     * @return Transfer
     */
    public function fillAccount(Account $account, $amount)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
        return Transfer::create()
            ->setManagerKey($this->getKey())
            ->addEntry(
                AccountEntry::create()
                    ->setAccountType($account->getDisposableAccountType())
                    ->setAmount($amount)
            )
        ;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'fill';
    }

    /**
     * @param AccountEntry $entry
     *
     * @return Statement|null
     */
    public function getStatement(AccountEntry $entry)
    {
        return Statement::create()->setDescription('Account fill');
    }

}