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

    public function withdraw(string $asset, float $amountToWithdraw, string $addressToWithdrawTo): CompletedWithdraw
    {
        $netAmountToWithdraw = $amountToWithdraw - $this->getWithdrawFee($asset, $amountToWithdraw, $addressToWithdrawTo);
        // https://www.kraken.com/features/api#withdraw-funds
        $response = $this->client->queryPrivate('Withdraw', [
          'asset' => $asset,
          'key' => $addressToWithdrawTo,
          'amount' => $amountToWithdraw
        ]);

        return new CompletedWithdraw($addressToWithdrawTo, $netAmountToWithdraw, $response['refid']);
    }

    public function getAvailableBalance(string $assetToWithdraw): float
    {
      $balanceInfo = $this->client->queryPrivate('Balance');

      return (float) $balanceInfo[$assetToWithdraw];
    }

    public function getWithdrawFee(string $asset, float $amountToWithdraw, string $addressToWithdrawTo): float
    {
      /**
       * https://support.kraken.com/hc/en-us/articles/360000767986-Cryptocurrency-withdrawal-fees-and-minimums
       */
      // https://www.kraken.com/features/api#get-withdrawal-info
      $withDrawInfo = $this->client->queryPrivate('WithdrawInfo', [
        'asset' => $asset,
        'key' => $addressToWithdrawTo,
        'amount' => $amountToWithdraw
      ]);

      return (float) $withDrawInfo['fee'];
    }

    public function getWithdrawFeeInSatoshis(): float
    {
      // TODO: recalculate to satoshis.
      return 0;
    }

    public function supportsExchange(string $exchange): bool
    {
        return 'kraken' === $exchange;
    }

}
