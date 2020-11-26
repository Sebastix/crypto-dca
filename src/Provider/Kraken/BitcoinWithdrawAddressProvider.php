<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider\Kraken;

use Jorijn\Bitcoin\Dca\Provider\WithdrawAddressProviderInterface;

class BitcoinWithdrawAddressProvider implements WithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    public string $asset;

    public function __construct(?string $configuredAddress, string $asset = 'XXBT')
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
