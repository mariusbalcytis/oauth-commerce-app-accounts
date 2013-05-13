<?php

namespace Maba\Bundle\OAuthCommerceAccountsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Account;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\UserClient;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\FillManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Provider\StatementProvider;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\AccountEntryRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\UserClientRepository;
use Maba\Bundle\OAuthCommerceProxyBundle\Manager\ClientManager;
use Maba\OAuthCommerceClient\Entity\SignatureCredentials\AsymmetricCredentials;
use Maba\OAuthCommerceClient\Entity\SignatureCredentials\SymmetricCredentials;
use Symfony\Bridge\Twig\Extension\CodeExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;

class PageController extends BaseController
{
    public function operationsAction()
    {
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('from', 'date', array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('to', 'date', array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->getForm()
        ;
        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $data = $form->getData();
            $from = $data['from'];
            $to = $data['to'];
            if ($to !== null) {
                /** @var \DateTime $to */
                $to = $to->setTime(23, 59, 59);
            }
        } else {
            $from = null;
            $to = null;
        }

        /** @var StatementProvider $statementProvider */
        $statementProvider = $this->get('maba_oauth_commerce_accounts.statement_provider');
        $accountEntryRepository = $this->getAccountEntryRepository();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $statements = $statementProvider->getAccountStatements($account, $from, $to);
        $startingBalance = $from ? $accountEntryRepository->getBalance($account->getDisposableAccountType(), $from) : 0;
        $endingBalance = $accountEntryRepository->getBalance($account->getDisposableAccountType(), $to);

        $disposable = array(
            'statements' => $statements,
            'startingBalance' => $startingBalance,
            'endingBalance' => $endingBalance,
        );

        $statements = $statementProvider->getAccountReservationStatements($account, $from, $to);
        $startingBalance = $from ? $accountEntryRepository->getBalance($account->getReservationAccountType(), $from) : 0;
        $endingBalance = $accountEntryRepository->getBalance($account->getReservationAccountType(), $to);

        $reservation = array(
            'statements' => $statements,
            'startingBalance' => $startingBalance,
            'endingBalance' => $endingBalance,
        );

        return $this->render('MabaOAuthCommerceAccountsBundle:Page:operations.html.twig', array(
            'disposable' => $disposable,
            'reservation' => $reservation,
            'form' => $form->createView(),
        ));
    }

    public function fillAction($neededAmount = null)
    {
        $form = $this->createFormBuilder()
            ->add('amount', 'integer', array(
                'required' => false,
                'constraints' => new Range(array('min' => 1, 'max' => 10000)),
            ))
            ->getForm()
        ;
        if ($this->getRequest()->isMethod('POST')) {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $data = $form->getData();
                $amount = $data['amount'] * 100;
                /** @var FillManager $fillManager */
                $fillManager = $this->get('maba_oauth_commerce_accounts.manager.fill');
                $transfer = $fillManager->fillAccount($this->getUser()->getAccount(), $amount);
                $this->getEntityManager()->persist($transfer);
                $this->getEntityManager()->flush();
                return $this->redirect($this->getRequest()->getUri());
            }
        }

        return $this->render('MabaOAuthCommerceAccountsBundle:Page:fill.html.twig', array(
            'form' => $form->createView(),
            'balance' => $this->getCurrentBalance(),
            'neededAmount' => $neededAmount,
        ));
    }


    public function clientsAction()
    {
        $form = $this->createFormBuilder()
            ->add('title', 'text', array(
                'constraints' => new Length(array('min' => 5, 'max' => 200)),
            ))
            ->add('domain', 'text', array(
                'constraints' => new Url(),
            ))
            ->add('algorithm', 'choice', array(
                'choices' => array(
                    'hmac-sha-256' => 'hmac-sha-256',
                    'hmac-sha-512' => 'hmac-sha-512',
                    'rsa-pkcs1-sha-256' => 'rsa-pkcs1-sha-256',
                    'rsa-pkcs1-sha-512' => 'rsa-pkcs1-sha-512',
                ),
                'expanded' => true,
            ))
            ->add('publicKey', 'textarea', array('required' => false))
            ->getForm()
        ;

        $session = $this->get('session');
        if ($this->getRequest()->isMethod('POST')) {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                if (!$session->get('clients_form_seen')) {
                    return $this->redirect($this->getRequest()->getUri());
                }
                $session->remove('clients_form_seen');

                $data = $form->getData();
                $title = $data['title'];
                $domain = $data['domain'];
                $algorithm = $data['algorithm'];
                $publicKey = $data['publicKey'];

                $client = $this->getClientManager()->createClient($title, $domain, $algorithm, $publicKey);
                $this->getEntityManager()->persist(
                    UserClient::create()->setUser($this->getUser())->setClient($client)
                );
                $this->getEntityManager()->flush();

                $signatureCredentials = $client->getGeneratedCredentials()->getSignatureCredentials();
                $credentialsParams = array(
                    'access_token' => $signatureCredentials->getMacId(),
                    'mac_algorithm' => $signatureCredentials->getAlgorithm(),
                );
                if ($signatureCredentials instanceof SymmetricCredentials) {
                    $credentialsParams['mac_key'] = $signatureCredentials->getSharedKey();
                }

                $this->addSuccessFlash(
                    'Client and credentials created, please save them as they are provided only once'
                );

                return $this->render('MabaOAuthCommerceAccountsBundle:Page:credentials.html.twig', array(
                    'credentialsParams' => $credentialsParams,
                ));
            }
        }
        $session->set('clients_form_seen', true);

        $clients = $this->getUserClientRepository()->findClientsForUser($this->getUser());
        $credentials = array();
        foreach ($clients as $client) {
            $credentials[$client->getId()] = $this->getClientManager()->findCredentialsByClientId($client->getId());
        }

        return $this->render('MabaOAuthCommerceAccountsBundle:Page:clients.html.twig', array(
            'form' => $form->createView(),
            'clients' => $clients,
            'credentials' => $credentials,
            'account' => $this->getUser()->getAccount(),
        ));
    }


    public function aboutAction()
    {
        return $this->render('MabaOAuthCommerceAccountsBundle:Page:about.html.twig');
    }
}
