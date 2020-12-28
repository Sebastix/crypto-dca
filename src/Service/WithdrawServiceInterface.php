<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service;

use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;

interface WithdrawServiceInterface
{
    public function withdraw(string $asset, int $amountToWithdraw, string $addressToWithdrawTo): CompletedWithdraw;

    public function getAvailableBalance(string $assetToWithdraw): int;

    public function getWithdrawFee(string $asset, int $amountToWithdraw, string $addressToWithdrawTo): int;

    public function getWithdrawFeeInSatoshis(): int;

    public function supportsExchange(string $exchange): bool;
}
