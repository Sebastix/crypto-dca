<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider\Kraken;

use Jorijn\Bitcoin\Dca\Provider\WithdrawAddressProviderInterface;

interface KrakenWithdrawAddressProviderInterface extends WithdrawAddressProviderInterface
{
    public function getAsset(): string;
}
