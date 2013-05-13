<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Twig;


class AccountsExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'maba_accounts';
    }

    public function getFilters()
    {
        return array(
            'money_amount' => new \Twig_Filter_Method($this, 'amount', array('is_safe' => array('html'))),
        );
    }

    public function amount($amount, $prependIfZeroOrPositive = '', $prependIfZero = '', $prependIfNegative = '')
    {
        $prefix = $amount > 0
            ? $prependIfZeroOrPositive
            : ($amount < 0 ? $prependIfNegative : ($prependIfZero ?: $prependIfZeroOrPositive));
        return $prefix . number_format($amount / 100, 2);
    }

}