<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Provider;


use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountEntry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\Registry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\AccountEntryRepository;

class StatementProvider
{
    protected $registry;
    protected $accountEntryRepository;


    public function __construct(Registry $registry, AccountEntryRepository $accountEntryRepository)
    {
        $this->registry = $registry;
        $this->accountEntryRepository = $accountEntryRepository;
    }

    /**
     * @param Account   $account
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement[]
     */
    public function getAccountStatements(Account $account, \DateTime $from = null, \DateTime $to = null)
    {
        $entries = $this->accountEntryRepository->findEntries($account->getDisposableAccountType(), $from, $to);
        return $this->processEntries($entries);
    }

    /**
     * @param Account   $account
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return \Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement[]
     */
    public function getAccountReservationStatements(Account $account, \DateTime $from = null, \DateTime $to = null)
    {
        $entries = $this->accountEntryRepository->findEntries($account->getReservationAccountType(), $from, $to);
        return $this->processEntries($entries);
    }

    /**
     * @param AccountEntry[] $entries
     * @return Statement[]
     */
    protected function processEntries($entries)
    {
        $statements = array();
        foreach ($entries as $entry) {
            $statement = $this->registry->getTransferManager($entry->getTransfer())->getStatement($entry);
            if ($statement !== null) {
                if ($statement->getDate() === null) {
                    $statement->setDate($entry->getDate());
                }
                if ($statement->getAmount() === null) {
                    $statement->setAmount($entry->getAmount());
                }
                if ($statement->getEntryId() === null) {
                    $statement->setEntryId($entry->getId());
                }
                if ($statement->getTransferId() === null) {
                    $statement->setTransferId($entry->getTransfer()->getId());
                }
                $statements[] = $statement;
            }
        }
        return $statements;
    }
}