<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

interface WithdrawAddressProviderInterface
{
    public function provide(): string;

    public function getAsset(): string;
}
