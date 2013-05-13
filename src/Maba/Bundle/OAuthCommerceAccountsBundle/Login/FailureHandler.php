<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Login;

use Doctrine\ORM\EntityManager;
use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class FailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    protected $defaultFailureHandler;

    /**
     * @var HttpKernelInterface
     */
    protected $httpKernel;

    /**
     * @var ContainerInterface
     */
    protected $container;


    public function __construct(
        ContainerInterface $container,
        AuthenticationFailureHandlerInterface $defaultFailureHandler,
        EncoderFactoryInterface $encoderFactory,
        EntityManager $entityManager,
        HttpKernelInterface $httpKernel
    ) {
        $this->container = $container;
        $this->defaultFailureHandler = $defaultFailureHandler;
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
        $this->httpKernel = $httpKernel;
    }


    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @throws \RuntimeException
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $token = $exception->getToken();
        if ($token instanceof UsernamePasswordToken) {
            $existingUser = $this->entityManager->getRepository(User::CLASS_NAME)
                ->findOneBy(array('username' => $token->getUser()));
            if (!$existingUser) {
                $user = new User();
                $user
                    ->setUsername($token->getUser())
                    ->setPassword(
                        $this->encoderFactory->getEncoder($user)
                            ->encodePassword($token->getCredentials(), $user->getSalt())
                    )
                ;

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->httpKernel->handle(
                    $this->getCurrentRequest()->duplicate(),
                    HttpKernelInterface::MASTER_REQUEST
                );
            }
        }

        return $this->defaultFailureHandler->onAuthenticationFailure($request, $exception);
    }

    /**
     * @return Request
     */
    protected function getCurrentRequest()
    {
        return $this->container->get('request');
    }


}