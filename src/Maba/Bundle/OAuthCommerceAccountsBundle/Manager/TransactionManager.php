<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Manager;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountEntry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Statement;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transaction;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\TransactionRepository;
use Maba\Bundle\OAuthCommerceCommonBundle\Security\OAuthCredentialsToken;
use Maba\OAuthCommerceClient\Random\DefaultRandomProvider;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * Class TransactionManager
 *
 * Payer        Beneficiary
 *
 * main-- [title]                   payer_from_disposal
 * reservation++                    payer_to_reservation
 *
 *                                  payer_from_reservation
 *                                  beneficiary_to_disposal
 *
 *                                  payer_from_reservation
 *                                  payer_return_disposal
 *
 * reservation--
 *              main++ [title]
 *
 * reservation--
 * main++
 *
 *
 */
class TransactionManager implements TransferManagerInterface
{
    const PAYER_FROM_DISPOSAL = 'payer_from_disposal';
    const PAYER_TO_RESERVATION = 'payer_to_reservation';
    const PAYER_FROM_RESERVATION = 'payer_from_reservation';
    const BENEFICIARY_TO_DISPOSAL = 'beneficiary_to_disposal';
    const PAYER_RETURN_DISPOSAL = 'payer_return_disposal';

    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;


    public function __construct(TransactionRepository $transactionRepository, SecurityContextInterface $securityContext)
    {
        $this->transactionRepository = $transactionRepository;
        $this->securityContext = $securityContext;
    }


    /**
     * @param int     $amount
     * @param Account $beneficiary
     * @param string  $description
     *
     * @return Transaction
     * @throws \RuntimeException
     */
    public function createTransaction($amount, Account $beneficiary, $description)
    {
        $securityToken = $this->securityContext->getToken();
        if (!$securityToken instanceof OAuthCredentialsToken) {
            throw new \RuntimeException('Expected OAuthCredentialsToken in security context');
        }
        $securityToken->getCredentialsId();

        $transaction = new Transaction();
        return $transaction
            ->setAmount($amount)
            ->setBeneficiary($beneficiary)
            ->setDescription($description)
            ->setKey($this->generateKey())
            ->setCredentialsId($securityToken->getCredentialsId())
            ->setApplication(
                $securityToken->getType() === OAuthCredentialsToken::TYPE_APPLICATION
                    ? $securityToken->getCredentials()
                    : null
            )
            ->setClient(
                $securityToken->getType() === OAuthCredentialsToken::TYPE_CLIENT
                    ? $securityToken->getCredentials()
                    : null
            )
        ;
    }


    public function reserveTransaction(Transaction $transaction)
    {
        if ($transaction->getStatus() === Transaction::STATUS_NEW) {
            $transaction->setTransfer(Transfer::create()
                ->setManagerKey($this->getKey())
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount(-$transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getDisposableAccountType())
                        ->setKey(self::PAYER_FROM_DISPOSAL)
                )
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount($transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getReservationAccountType())
                        ->setKey(self::PAYER_TO_RESERVATION)
                )
            )->setStatus(Transaction::STATUS_RESERVED);
        } elseif ($transaction->getStatus() !== Transaction::STATUS_RESERVED) {
            throw new \RuntimeException('Unexpected transaction status');
        }
    }

    public function confirmTransaction(Transaction $transaction)
    {
        if ($transaction->getStatus() === Transaction::STATUS_RESERVED) {
            $transaction->setStatus(Transaction::STATUS_DONE)->getTransfer()
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount(-$transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getReservationAccountType())
                        ->setKey(self::PAYER_FROM_RESERVATION)
                )
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount($transaction->getAmount())
                        ->setAccountType($transaction->getBeneficiary()->getDisposableAccountType())
                        ->setKey(self::BENEFICIARY_TO_DISPOSAL)
                )
            ;
        } elseif ($transaction->getStatus() !== Transaction::STATUS_DONE) {
            throw new \RuntimeException('Unexpected transaction status');
        }
    }

    public function revokeTransaction(Transaction $transaction)
    {
        if ($transaction->getStatus() === Transaction::STATUS_RESERVED) {
            $transaction->setStatus(Transaction::STATUS_REVOKED)->getTransfer()
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount(-$transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getReservationAccountType())
                        ->setKey(self::PAYER_FROM_RESERVATION)
                )
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount($transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getDisposableAccountType())
                        ->setKey(self::PAYER_RETURN_DISPOSAL)
                )
            ;
        } elseif ($transaction->getStatus() !== Transaction::STATUS_REVOKED) {
            throw new \RuntimeException('Unexpected transaction status');
        }
    }

    public function rejectTransaction(Transaction $transaction)
    {
        if ($transaction->getStatus() === Transaction::STATUS_NEW) {
            $transaction->setStatus(Transaction::STATUS_REJECTED);
        } elseif ($transaction->getStatus() !== Transaction::STATUS_REJECTED) {
            throw new \RuntimeException('Unexpected transaction status');
        }
    }

    public function failTransaction(Transaction $transaction)
    {
        if ($transaction->getStatus() === Transaction::STATUS_NEW) {
            $transaction->setStatus(Transaction::STATUS_FAILED);
        } elseif ($transaction->getStatus() === Transaction::STATUS_RESERVED) {
            $transaction->setStatus(Transaction::STATUS_FAILED)->getTransfer()
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount(-$transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getReservationAccountType())
                        ->setKey(self::PAYER_FROM_RESERVATION)
                )
                ->addEntry(
                    AccountEntry::create()
                        ->setAmount($transaction->getAmount())
                        ->setAccountType($transaction->getPayer()->getDisposableAccountType())
                        ->setKey(self::PAYER_RETURN_DISPOSAL)
                )
            ;
        } elseif ($transaction->getStatus() !== Transaction::STATUS_REJECTED) {
            throw new \RuntimeException('Unexpected transaction status');
        }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'transaction';
    }

    /**
     * @param AccountEntry $entry
     *
     * @return Statement|null
     */
    public function getStatement(AccountEntry $entry)
    {
        $transaction = $this->transactionRepository->findOneByTransfer($entry->getTransfer());

        if (
            $transaction->getStatus() === Transaction::STATUS_RESERVED
            && $entry->getKey() === self::PAYER_FROM_DISPOSAL
        ) {
            $prefix = 'Reserving funds for transaction: ';
        } elseif ($entry->getKey() === self::PAYER_RETURN_DISPOSAL) {
            $prefix = 'Returning funds: ';
        } else {
            $prefix = '';
        }

        $otherParty = $entry->getKey() === self::BENEFICIARY_TO_DISPOSAL
            ? $transaction->getPayer()
            : $transaction->getBeneficiary();

        return Statement::create()
            ->setDescription($prefix . $transaction->getDescription())
            ->setOtherParty($otherParty)
        ;
    }

    protected function generateKey()
    {
        $random = new DefaultRandomProvider();
        return $random->generateStringForRanges(32, array(
            array('from' => ord('0'), 'to' => ord('9')),
            array('from' => ord('a'), 'to' => ord('z')),
        ));
    }
}