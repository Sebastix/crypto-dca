<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service;

use Jorijn\Bitcoin\Dca\Exception\PendingBuyOrderException;
use Jorijn\Bitcoin\Dca\Model\CompletedBuyOrder;

interface BuyServiceInterface
{
    /**
     * Should return true or false depending on if this service will support provided exchange name.
     */
    public function supportsExchange(string $exchange): bool;

    /**
     * Method should buy $amount of $baseCurrency in given token. Should only return a CompletedBuyOrder object when the
     * (market) order was filled. Should throw PendingBuyOrderException if it is not filled yet.
     *
     * @param int $amount
     *  The amount that should be bought.
     * @param string $asset
     *  The asset to be bought.
     *
     * @throws PendingBuyOrderException
     */
    public function initiateBuy(int $amount, string $asset): CompletedBuyOrder;

    /**
     * Method should check if the given $orderId is filled already. Should only return a CompletedBuyOrder object when
     * the (market) order was filled. Should throw PendingBuyOrderException if it is not filled yet.
     *
     * @param string $orderId the order id of the order that is being checked
     *
     * @throws PendingBuyOrderException
     */
    public function checkIfOrderIsFilled(string $orderId): CompletedBuyOrder;

    /**
     * Method should cancel the order corresponding with this order id. Method will be called if the order was not
     * filled within set timeout.
     *
     * @param string $orderId the order id of the order that is being cancelled
     */
    public function cancelBuyOrder(string $orderId): void;

    /**
     * Method to check if provided asset is a valid asset.
     *
     * @param string $asset
     */
    public function validateAsset(string $asset): void;
}
