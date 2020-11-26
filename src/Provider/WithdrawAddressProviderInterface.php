<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

interface WithdrawAddressProviderInterface
{
    /**
     * Method should return the asset of the withdrawal asset.
     */
    public function getAsset(): string;
    /**
     * Method should return a address for withdrawal.
     */
    public function provide(): string;
}
