<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider\Kraken;

use Jorijn\Bitcoin\Dca\Provider\WithdrawAddressProviderInterface;

class CardanoWithdrawAddressProvider implements WithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    protected string $asset;

    public function __construct(?string $configuredAddress, string $asset = 'ADA')
    {
        $this->configuredAddress = $configuredAddress;
        $this->asset = $asset;
    }

    public function provide(): string
    {
        return $this->configuredAddress;
    }

    public function getAsset(): string
    {
        return $this->asset;
    }
}
