<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

use Jorijn\Bitcoin\Dca\Validator\ValidationInterface;

class BitcoinWithdrawAddressProvider implements WithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    protected ValidationInterface $validation;
    protected string $asset;

    public function __construct(ValidationInterface $validation, ?string $configuredAddress, string $asset = 'BTC')
    {
        $this->configuredAddress = $configuredAddress;
        $this->validation = $validation;
        $this->asset = $asset;
    }

    public function provide(): string
    {
        $this->validation->validate($this->configuredAddress);

        return $this->configuredAddress;
    }

    public function getAsset(): string
    {
        return $this->asset;
    }
}
