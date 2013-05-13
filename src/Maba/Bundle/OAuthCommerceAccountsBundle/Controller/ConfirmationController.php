<?php

namespace Maba\Bundle\OAuthCommerceAccountsBundle\Controller;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transaction;
use Maba\Bundle\OAuthCommerceProxyBundle\Exception\CodeRequestException;
use Maba\Bundle\OAuthCommerceProxyBundle\Exception\CodeRequestRedirectException;
use Symfony\Bridge\Twig\Extension\CodeExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Choice;

class ConfirmationController extends BaseController
{
    public function oauthEndpointAction()
    {
        try {
            $key = $this->getCodeManager()->handleRequest($this->getRequest()->query);
        } catch (CodeRequestRedirectException $exception) {
            return $this->redirect($exception->getRedirectUri());
        } catch (CodeRequestException $exception) {
            $this->addErrorFlash('Error in passed parameters: ' . $exception->getErrorCode());
            return $this->redirect($this->generateUrl('maba_o_auth_commerce_accounts_about'));
        }

        return $this->redirect(
            $this->generateUrl(
                'maba_o_auth_commerce_accounts.confirmation_confirm',
                array('key' => $key)
            )
        );
    }

    public function confirmAction($key)
    {
        $codeManager = $this->getCodeManager();

        $info = $codeManager->getConfirmationInfoByKey($key);
        $client = $info['client'];
        $scopes = $info['scopes'];

        $owner = $this->getUserClientRepository()->findUserForClient($client);

        $transactionManager = $this->getTransactionManager();
        $transactionRepository = $this->getTransactionRepository();

        $userInfoRequested = false;
        $transactions = array();
        $neededAmount = 0;
        foreach ($scopes as $scope) {
            if (strpos($scope, 'transaction:') === 0) {
                $transaction = $transactionRepository->findOneByKey(substr($scope, 12));
                if (!$transaction) {
                    return $codeManager->getErrorResponse($key, 'invalid_scope', 'Transaction not found');
                } elseif ($transaction->getStatus() !== Transaction::STATUS_NEW) {
                    return $codeManager->getErrorResponse($key, 'invalid_scope', 'Transaction status invalid');
                }
                $transactions[] = $transaction;
                $neededAmount += $transaction->getAmount();
            } elseif ($scope === 'user_info') {
                $userInfoRequested = true;
            } else {
                return $codeManager->getErrorResponse($key, 'invalid_scope', 'Unknown scope: ' . $scope);
            }
        }

        $balance = $this->getCurrentBalance();
        if ($balance < $neededAmount) {
            if (!$this->getRequest()->isMethod('POST')) {
                $this->addErrorFlash('Not enough funds, please fill your account before confirming transaction');
            }
            return $this->forward('MabaOAuthCommerceAccountsBundle:Page:fill', array('neededAmount' => $neededAmount));
        }

        $form = $this->createFormBuilder()
            ->add('action', 'hidden', array('constraints' => new Choice(array('choices' => array('accept', 'reject')))))
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['action'] === 'accept') {
                    $account = $this->getUser()->getAccount();
                    foreach ($transactions as $transaction) {
                        $transaction->setPayer($account);
                        $transactionManager->reserveTransaction($transaction);
                    }
                    $this->getEntityManager()->flush();
                    return $codeManager->getAcceptResponse($key, $this->getUser()->getId());

                } else {
                    foreach ($transactions as $transaction) {
                        $transactionManager->rejectTransaction($transaction);
                    }
                    $this->getEntityManager()->flush();
                    return $codeManager->getRejectResponse($key);
                }
            }
        }

        return $this->render(
            'MabaOAuthCommerceAccountsBundle:Confirmation:confirm.html.twig',
            array(
                'client' => $client,
                'userInfoRequested' => $userInfoRequested,
                'owner' => $owner,
                'neededAmount' => $neededAmount,
                'balance' => $balance,
                'transactions' => $transactions,
                'acceptForm' => $form->createView(),
                'rejectForm' => $form->createView(),
            )
        );
    }
}
