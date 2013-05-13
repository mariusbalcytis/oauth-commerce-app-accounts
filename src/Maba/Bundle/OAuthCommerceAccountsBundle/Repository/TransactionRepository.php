<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transaction;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\TransactionManager;

class TransactionRepository extends EntityRepository
{

    /**
     * @param Transfer $transfer
     *
     * @return Transaction
     */
    public function findOneByTransfer(Transfer $transfer)
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.transfer = :transfer')
            ->setParameter('transfer', $transfer)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * @param string $key
     *
     * @return Transaction|null
     */
    public function findOneByKey($key)
    {
        return $this->findOneBy(array('key' => $key));
    }

    public function findForFailing($limit = null, $intervalSpec = 'P1D')
    {
        $date = new \DateTime();
        $date = $date->sub(new \DateInterval($intervalSpec));
        return $this->createQueryBuilder('t')
            ->select('t, tf')
            ->join('t.transfer', 'tf')
            ->join('tf.entries', 'e')
            ->where('t.status = :status')
            ->andWhere('e.key = :key')
            ->andWhere('e.date < :date')
            ->setParameter('status', Transaction::STATUS_RESERVED)
            ->setParameter('key', TransactionManager::PAYER_FROM_DISPOSAL)
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}