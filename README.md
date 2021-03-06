##Example system, using OAuth Commerce model 

This is example system, implementing proposed OAuth commerce model, provided in thesis. System uses Symfony2 framework.

Accounts system lets you:

  - Register on the system or log in using form
  - Fill your virtual account
  - See operation list
  - Create API clients and view created ones
  - Confirm or reject permission request to make transaction from your account

You can [access this system online] or use [e-shop system] to try confirming transactions.

  [access this system online]: https://accounts.maba.lt/
  [e-shop system]: https://shop.maba.lt/

This system has dependencies on related components:

  - [oauth-commerce-node-proxy] - the server must run for this system to work properly
  - [oauth-commerce-bundle-proxy] - used integrating proxy server to Symfony2 framework
  - [oauth-commerce-bundle-encrypted-credentials] - used for encrypted credentials authorization grant type
  - [oauth-commerce-bundle-common] - common classes for model-related systems

Installing:

    cd /var/www/accounts
    git clone https://github.com/mariusbalcytis/oauth-commerce-app-accounts.git .
    composer install


  [oauth-commerce-node-proxy]: https://github.com/mariusbalcytis/oauth-commerce-node-proxy
  [oauth-commerce-bundle-encrypted-credentials]: https://github.com/mariusbalcytis/oauth-commerce-bundle-encrypted-credentials
  [oauth-commerce-bundle-proxy]: https://github.com/mariusbalcytis/oauth-commerce-bundle-proxy
  [oauth-commerce-bundle-common]: https://github.com/mariusbalcytis/oauth-commerce-bundle-common