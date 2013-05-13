<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\CredentialsHandler;

use Doctrine\ORM\EntityRepository;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transaction;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User;
use Maba\Bundle\OAuthCommerceAccountsBundle\Repository\TransactionRepository;
use Maba\Bundle\OAuthCommerceCommonBundle\Security\OAuthCredentialsToken;
use Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\CredentialsHandler\CredentialsHandlerInterface;
use Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\Exception\InvalidCredentialsException;
use Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\Exception\InvalidScopeException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PasswordCredentialsHandler implements CredentialsHandlerInterface
{
    /**
     * @var string
     */
    protected $siteName;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Maba\Bundle\OAuthCommerceAccountsBundle\Repository\TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var EntityRepository
     */
    protected $userRepository;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    public function __construct(
        $siteName,
        TransactionRepository $transactionRepository,
        EncoderFactoryInterface $encoderFactory,
        EntityRepository $userRepository,
        SecurityContextInterface $securityContext
    ) {
        $this->siteName = $siteName;
        $this->transactionRepository = $transactionRepository;
        $this->encoderFactory = $encoderFactory;
        $this->userRepository = $userRepository;
        $this->securityContext = $securityContext;
    }

    /**
     * @param string       $credentialsType
     * @param ParameterBag $parameters
     *
     * @return bool
     */
    public function supportsSession($credentialsType, ParameterBag $parameters)
    {
        return $credentialsType === 'password' && $parameters->get('site') === $this->siteName;
    }

    /**
     * @param ParameterBag $credentials
     * @param ParameterBag $scopes      can modify scopes
     *
     * @throws \RuntimeException
     * @throws \Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\Exception\InvalidCredentialsException
     * @throws \Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\Exception\InvalidScopeException
     * @return int|null user id
     */
    public function handleCredentials(ParameterBag $credentials, ParameterBag $scopes)
    {
        $token = $this->securityContext->getToken();
        if (!$token instanceof OAuthCredentialsToken) {
            throw new \RuntimeException('Expected OAuthCredentialsToken in security context');
        }

        foreach ($scopes as $scope) {
            if (strpos($scope, 'transaction:') === 0) {
                $transaction = $this->transactionRepository->findOneByKey(substr($scope, 12));
                if (!$transaction) {
                    throw new InvalidScopeException('Transaction not found by provided key in scope');
                } elseif ($transaction->getCredentialsId() !== $token->getCredentialsId()) {
                    throw new InvalidScopeException('Transaction is created by another client');
                } elseif ($transaction->getStatus() !== Transaction::STATUS_NEW) {
                    throw new InvalidScopeException('Transaction is in wrong status');
                }
            }
        }

        $username = $credentials->get('username');
        $password = $credentials->get('password');
        if (!$username || !$password) {
            throw new InvalidCredentialsException('username or password secret field is missing');
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(array('username' => $username));
        if (!$user) {
            throw new InvalidCredentialsException('user not found by this username');
        }

        $valid = $this->encoderFactory->getEncoder($user)
            ->isPasswordValid($user->getPassword(), $password, $user->getSalt());
        if (!$valid) {
            throw new InvalidCredentialsException('password invalid');
        }

        return $user->getId();
    }


}