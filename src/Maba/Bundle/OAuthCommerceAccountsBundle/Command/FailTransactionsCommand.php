<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Command;

use Doctrine\ORM\EntityManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\TransactionManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FailTransactionsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('oauth-commerce-account:fail-transactions')
            ->setDescription('Fails transactions which was not confirmed for too long')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TransactionRepository $repository */
        $repository = $this->getContainer()->get('maba_oauth_commerce_accounts.transaction_repository');
        /** @var TransactionManager $manager */
        $manager = $this->getContainer()->get('maba_oauth_commerce_accounts.manager.transaction');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        do {
            $found = false;
            foreach ($repository->findForFailing(100, 'P1D') as $transaction) {
                $found = true;
                $manager->failTransaction($transaction);
            }
            $entityManager->flush();
            $entityManager->clear();
        } while ($found);
    }

}