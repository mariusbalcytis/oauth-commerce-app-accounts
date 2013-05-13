<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountEntry;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\AccountType;

class AccountEntryRepository extends EntityRepository
{

    /**
     * @param AccountType $type
     * @param \DateTime   $from
     * @param \DateTime   $to
     *
     * @return AccountEntry[]
     */
    public function findEntries(AccountType $type, \DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->createQueryBuilder('ae')
            ->select('ae')
            ->where('ae.accountType = :type')
            ->setParameter('type', $type)
        ;
        if ($from !== null) {
            $qb->andWhere('ae.date >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('ae.date < :to')->setParameter('to', $to);
        }
        return $qb->getQuery()->getResult();
    }

    public function getBalance(AccountType $type, \DateTime $date = null)
    {
        $qb = $this->createQueryBuilder('ae')
            ->select('SUM(ae.amount)')
            ->where('ae.accountType = :type')
            ->setParameter('type', $type)
        ;
        if ($date !== null) {
            $qb->andWhere('ae.date < :date')->setParameter('date', $date);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}