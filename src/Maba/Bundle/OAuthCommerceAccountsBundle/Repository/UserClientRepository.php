<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\UserClient;
use Maba\Bundle\OAuthCommerceCommonBundle\Entity\Client;

class UserClientRepository extends EntityRepository
{

    /**
     * @param User $user
     *
     * @return Client[]
     */
    public function findClientsForUser(User $user)
    {
        /** @var UserClient[] $userClients */
        $userClients = $this->createQueryBuilder('uc')
            ->where('uc.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;

        $clients = array();
        foreach ($userClients as $userClient) {
            $clients[] = $userClient->getClient();
        }

        return $clients;
    }

    /**
     * @param Client $client
     *
     * @return User
     */
    public function findUserForClient(Client $client)
    {
        /** @var UserClient $userClients */
        $userClient = $this->createQueryBuilder('uc')
            ->where('uc.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleResult()
        ;
        return $userClient->getUser();
    }
}