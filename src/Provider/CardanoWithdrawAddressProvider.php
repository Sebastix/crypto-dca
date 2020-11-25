<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

class CardanoWithdrawAddressProvider implements WithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;

    public function __construct(?string $configuredAddress)
    {
        $this->configuredAddress = $configuredAddress;
    }

    public function provide(): string
    {
        return $this->configuredAddress;
    }
}
