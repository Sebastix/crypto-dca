<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service\Kraken;

use Jorijn\Bitcoin\Dca\Client\KrakenClientInterface;
use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;
use Jorijn\Bitcoin\Dca\Service\WithdrawServiceInterface;
use Psr\Log\LoggerInterface;

class KrakenWithdrawService implements WithdrawServiceInterface
{
    protected KrakenClientInterface $client;
    protected LoggerInterface $logger;

    public function __construct(KrakenClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function withdraw(string $asset, float $balanceToWithdraw, string $addressToWithdrawTo): CompletedWithdraw
    {
        $netAmountToWithdraw = $balanceToWithdraw - $this->getWithdrawFeeInSatoshis();

        $response = $this->client->queryPrivate('Withdraw', [
          'asset' => $asset,
          'key' => $addressToWithdrawTo,
          'amount' => $balanceToWithdraw
        ]);

        return new CompletedWithdraw($addressToWithdrawTo, $netAmountToWithdraw, $response['refid']);
    }

    public function getAvailableBalance(string $assetToWithdraw): float
    {
      $balanceInfo = $this->client->queryPrivate('Balance');

      return (float) $balanceInfo[$assetToWithdraw];
    }

    public function getWithdrawFeeInSatoshis(): float
    {
        // https://support.kraken.com/hc/en-us/articles/360000767986-Cryptocurrency-withdrawal-fees-and-minimums
        // TODO get fees data with WithdrawInfo call
        // It's 0.6 for ADA
        return 0.6;
    }

    public function supportsExchange(string $exchange): bool
    {
        return 'kraken' === $exchange;
    }
}
