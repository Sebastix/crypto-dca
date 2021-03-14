<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider\Kraken;

class KrakenWithdrawAddressProvider implements KrakenWithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    protected string $asset;

    public function __construct(?string $configuredAddress, string $asset)
    {
        $this->configuredAddress = $configuredAddress;
        $this->setAsset($asset);
    }

    public function provide(): string
    {
        return $this->configuredAddress;
    }

    public function setAsset(string $asset)
    {
        $this->asset = $asset;
    }

    public function getAsset(): string
    {
        return $this->asset;
    }
}
