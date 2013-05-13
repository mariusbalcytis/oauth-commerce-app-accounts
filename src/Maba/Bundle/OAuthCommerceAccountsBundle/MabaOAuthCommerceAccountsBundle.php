<?php

namespace Maba\Bundle\OAuthCommerceAccountsBundle;

use Maba\Bundle\OAuthCommerceEncryptedCredentialsBundle\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MabaOAuthCommerceAccountsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'maba_oauth_commerce_accounts.manager_registry',
            'maba_oauth_commerce_accounts.transfer_manager',
            'addTransferManager'
        ));
    }


}

