<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Manager;

use Maba\Bundle\OAuthCommerceAccountsBundle\Entity\Transfer;

class Registry
{
    /**
     * @var TransferManagerInterface[]
     */
    protected $transferManagers = array();

    /**
     * @param TransferManagerInterface $transferManager
     */
    public function addTransferManager(TransferManagerInterface $transferManager)
    {
        $this->transferManagers[$transferManager->getKey()] = $transferManager;
    }

    /**
     * @param Transfer $transfer
     *
     * @return TransferManagerInterface
     * @throws \InvalidArgumentException
     */
    public function getTransferManager(Transfer $transfer)
    {
        $key = $transfer->getManagerKey();
        if (!isset($this->transferManagers[$key])) {
            throw new \InvalidArgumentException('No manager with such key: ' . $key);
        }

        return $this->transferManagers[$key];
    }
}