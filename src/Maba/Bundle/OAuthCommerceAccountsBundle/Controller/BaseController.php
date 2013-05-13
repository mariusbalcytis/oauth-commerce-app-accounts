<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\TransactionManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\AccountEntryRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\TransactionRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\UserClientRepository;
use Maba\Bundle\OAuthCommerceProxyBundle\Manager\AccessTokenCodeManager;
use Maba\Bundle\OAuthCommerceProxyBundle\Manager\ClientManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;

abstract class BaseController extends Controller
{

    /**
     * @return AccessTokenCodeManager
     */
    protected function getCodeManager()
    {
        return $this->get('maba_oauth_commerce_accounts.access_token_code_manager');
    }

    /**
     * @return UserClientRepository
     */
    protected function getUserClientRepository()
    {
        return $this->get('doctrine.orm.default_entity_manager')
            ->getRepository('MabaOAuthCommerceAccountsBundle:UserClient');
    }

    /**
     * @return AccountEntryRepository
     */
    protected function getAccountEntryRepository()
    {
        return $this->get('doctrine.orm.default_entity_manager')
            ->getRepository('MabaOAuthCommerceAccountsBundle:AccountEntry');
    }

    /**
     * @return TransactionRepository
     */
    protected function getTransactionRepository()
    {
        return $this->get('doctrine.orm.default_entity_manager')
            ->getRepository('MabaOAuthCommerceAccountsBundle:Transaction');
    }

    /**
     * @return TransactionManager
     */
    protected function getTransactionManager()
    {
        return $this->get('maba_oauth_commerce_accounts.manager.transaction');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }

    protected function addErrorFlash($error)
    {
        $this->get('session')->getFlashBag()->set('error', $error);
    }

    protected function addSuccessFlash($error)
    {
        $this->get('session')->getFlashBag()->set('success', $error);
    }

    /**
     * @return ClientManager
     */
    protected function getClientManager()
    {
        return $this->get('maba_oauth_commerce_proxy.client_manager');
    }

    /**
     * @return FormFactoryInterface
     */
    protected function getFormFactory()
    {
        return $this->container->get('form.factory');
    }

    /**
     * @return int
     */
    protected function getCurrentBalance()
    {
        return $this->getAccountEntryRepository()->getBalance(
            $this->getUser()->getAccount()->getDisposableAccountType()
        );
    }

}