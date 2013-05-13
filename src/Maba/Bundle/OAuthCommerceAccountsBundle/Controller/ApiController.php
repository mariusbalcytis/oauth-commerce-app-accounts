<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Controller;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transaction;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User;
use Maba\Bundle\OAuthCommerceAccountsBundle\Manager\TransactionManager;
use Maba\Bundle\OAuthCommerceCommonBundle\Security\OAuthCredentialsToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ApiController extends BaseController
{

    public function createTransactionAction()
    {
        $form = $this->getFormFactory()->createNamedBuilder(null, 'form', null, array(
                'csrf_protection' => false,
        ))
            ->add('amount', 'integer', array(
                'constraints' => array(
                    new Range(array('min' => 1, 'max' => 1000000)),
                    new NotBlank(),
                ),
            ))
            ->add('description', 'text', array(
                'constraints' => array(
                    new Length(array('min' => 5, 'max' => 255)),
                    new NotBlank(),
                ),
            ))
            ->add('beneficiary', 'integer', array(
                'constraints' => array(
                    new Range(array('min' => 1)),
                    new NotBlank(),
                ),
            ))
            ->getForm()
        ;

        $form->bind($this->getRequest());

        if (!$form->isValid()) {
            return new JsonResponse(array(
                'error' => 'invalid_request',
                'error_description' => 'Invalid data: ' . $form->getErrorsAsString(),
            ), 400);
        }

        $data = $form->getData();
        $amount = intval($data['amount']);
        $beneficiaryAccount = $this->getEntityManager()->find(
            'MabaOAuthCommerceAccountsBundle:Account',
            $data['beneficiary']
        );
        if (!$beneficiaryAccount) {
            return new JsonResponse(array(
                'error' => 'not_found',
                'error_description' => 'Beneficiary account not found',
            ), 404);
        }
        $description = $data['description'];

        $transaction = $this->getTransactionManager()->createTransaction($amount, $beneficiaryAccount, $description);
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush($transaction);

        return $this->mapTransaction($transaction);
    }

    public function confirmTransactionAction($key)
    {
        $transaction = $this->findTransaction($key);
        if ($transaction instanceof Response) {
            return $transaction;
        }

        if ($transaction->getStatus() === Transaction::STATUS_RESERVED) {
            $this->getTransactionManager()->confirmTransaction($transaction);
            $this->getEntityManager()->flush($transaction);
        } elseif ($transaction->getStatus() !== Transaction::STATUS_DONE) {
            return new JsonResponse(array(
                'error' => 'wrong_status',
                'error_description' => 'Transaction with current status cannot be confirmed',
            ), 409);
        }

        return $this->mapTransaction($transaction);
    }

    public function revokeTransactionAction($key)
    {
        $transaction = $this->findTransaction($key);
        if ($transaction instanceof Response) {
            return $transaction;
        }

        if ($transaction->getStatus() === Transaction::STATUS_RESERVED) {
            $this->getTransactionManager()->revokeTransaction($transaction);
            $this->getEntityManager()->flush($transaction);
        } elseif ($transaction->getStatus() !== Transaction::STATUS_REVOKED) {
            return new JsonResponse(array(
                'error' => 'wrong_status',
                'error_description' => 'Transaction with current status cannot be revoked',
            ), 409);
        }

        return $this->mapTransaction($transaction);
    }

    public function reserveTransactionAction($key)
    {
        $transaction = $this->findTransaction($key);
        if ($transaction instanceof Response) {
            return $transaction;
        }

        if ($transaction->getStatus() === Transaction::STATUS_NEW) {
            $userId = $this->getCurrentAccessToken()->getUserId();
            /** @var User $user */
            $user = $this->getEntityManager()->find('MabaOAuthCommerceAccountsBundle:User', $userId);
            if (!$user || !$user->getAccount()) {
                return new JsonResponse(array(
                    'error' => 'not_found',
                    'error_description' => 'User, related to this access token, was not found',
                ), 404);
            }
            $balance = $this->getAccountEntryRepository()->getBalance($user->getAccount()->getDisposableAccountType());
            if ($balance < $transaction->getAmount()) {
                return new JsonResponse(array(
                    'error' => 'insufficient_funds',
                    'error_description' => 'Not enough balance in user account',
                ), 400);
            }
            $transaction->setPayer($user->getAccount());
            $this->getTransactionManager()->reserveTransaction($transaction);
            $this->getEntityManager()->flush($transaction);
        } elseif ($transaction->getStatus() !== Transaction::STATUS_RESERVED) {
            return new JsonResponse(array(
                'error' => 'wrong_status',
                'error_description' => 'Transaction with current status cannot be revoked',
            ), 409);
        }

        return $this->mapTransaction($transaction);
    }

    public function getTransactionAction($key)
    {
        $transaction = $this->findTransaction($key, false);
        if ($transaction instanceof Response) {
            return $transaction;
        }

        return $this->mapTransaction($transaction);
    }

    protected function findTransaction($key, $requireAccessToken = true)
    {
        $transaction = $this->getTransactionRepository()->findOneByKey($key);
        if (!$transaction) {
            return new JsonResponse(array(
                'error' => 'not_found',
                'error_description' => 'Transaction with this key not found',
            ), 404);
        }
        if ($this->getCurrentCredentialsId() !== $transaction->getCredentialsId()) {
            return new JsonResponse(array(
                'error' => 'unauthorized_client',
                'error_description' => 'This transaction is created with other credentials than currently active',
            ), 403);
        }

        if ($requireAccessToken) {
            $accessToken = $this->getCurrentAccessToken();
            if (!$accessToken) {
                return new JsonResponse(array(
                    'error' => 'unauthorized_client',
                    'error_description' => 'You need to use access token to perform this action',
                ), 403);
            }
            $validScope = false;
            foreach ($accessToken->getScopes() as $scope) {
                if ($scope === 'transaction:' . $transaction->getKey()) {
                    $validScope = true;
                    break;
                }
            }
            if (!$validScope) {
                return new JsonResponse(array(
                    'error' => 'unauthorized_client',
                    'error_description' => 'Current access token is not related to this transaction',
                ), 403);
            }
        }

        return $transaction;
    }

    protected function getCurrentCredentialsId()
    {
        $securityContext = $this->get('security.context');
        $token = $securityContext->getToken();
        if ($token instanceof OAuthCredentialsToken) {
            return $token->getCredentialsId();
        } else {
            return null;
        }
    }

    /**
     * @return \Maba\Bundle\OAuthCommerceCommonBundle\Security\AccessTokenData|null
     */
    protected function getCurrentAccessToken()
    {
        $securityContext = $this->get('security.context');
        $token = $securityContext->getToken();
        if ($token instanceof OAuthCredentialsToken) {
            return $token->getAccessTokenData();
        } else {
            return null;
        }
    }

    protected function mapTransaction(Transaction $transaction)
    {
        $statusMap = array(
            Transaction::STATUS_NEW => 'new',
            Transaction::STATUS_REVOKED => 'revoked',
            Transaction::STATUS_DONE => 'done',
            Transaction::STATUS_RESERVED => 'reserved',
            Transaction::STATUS_REJECTED => 'rejected',
            Transaction::STATUS_FAILED => 'failed',
        );
        return new JsonResponse(array(
            'beneficiary' => $transaction->getBeneficiary()->getId(),
            'amount' => $transaction->getAmount(),
            'description' => $transaction->getDescription(),
            'key' => $transaction->getKey(),
            'status' => $statusMap[$transaction->getStatus()],
        ));
    }

}