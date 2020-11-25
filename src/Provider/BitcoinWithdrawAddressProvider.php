<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

use Jorijn\Bitcoin\Dca\Validator\ValidationInterface;

class BitcoinWithdrawAddressProvider implements WithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    protected ValidationInterface $validation;

    public function __construct(ValidationInterface $validation, ?string $configuredAddress)
    {
        $this->configuredAddress = $configuredAddress;
        $this->validation = $validation;
    }

    public function provide(): string
    {
        $this->validation->validate($this->configuredAddress);

        return $this->configuredAddress;
    }
}
