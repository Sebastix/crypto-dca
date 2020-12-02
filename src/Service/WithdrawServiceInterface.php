<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service;

use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;

interface WithdrawServiceInterface
{
    public function withdraw(string $asset, float $amountToWithdraw, string $addressToWithdrawTo): CompletedWithdraw;

    public function getAvailableBalance(string $assetToWithdraw): float;

    public function getWithdrawFee(string $asset, float $amountToWithdraw, string $addressToWithdrawTo): float;

    public function getWithdrawFeeInSatoshis(): int;

    public function supportsExchange(string $exchange): bool;
}
