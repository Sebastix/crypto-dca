<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service;

use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;

interface WithdrawServiceInterface
{
    public function withdraw(string $asset, float $balanceToWithdraw, string $addressToWithdrawTo): CompletedWithdraw;

    public function getAvailableBalance(string $assetToWithdraw): float;

    public function getWithdrawFeeInSatoshis(): float;

    public function supportsExchange(string $exchange): bool;
}
