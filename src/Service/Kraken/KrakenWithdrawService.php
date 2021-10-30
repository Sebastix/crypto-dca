<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service\Kraken;

use Jorijn\Bitcoin\Dca\Client\KrakenClientInterface;
use Jorijn\Bitcoin\Dca\Exception\KrakenClientException;
use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;
use Jorijn\Bitcoin\Dca\Service\WithdrawServiceInterface;
use Psr\Log\LoggerInterface;

class KrakenWithdrawService implements WithdrawServiceInterface
{

  public static $ASSETS = array("ETH"=>"XETH", "ADA"=>"ADA", "XBT"=>"XXBT");

    protected KrakenClientInterface $client;
    protected LoggerInterface $logger;
    protected string $asset;

    public function __construct(KrakenClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function withdraw(string $asset, int $amountToWithdraw, string $addressToWithdrawTo): CompletedWithdraw
    {
      $netAmountToWithdraw = $amountToWithdraw - $this->getWithdrawFee($asset, $amountToWithdraw, $addressToWithdrawTo);
      $assetInfo = $this->getAssetInfo($asset);
      $divisor = str_pad('1', $assetInfo['decimals'], '0') . '0';
      // https://www.kraken.com/features/api#withdraw-funds
        $response = $this->client->queryPrivate('Withdraw', [
          'asset' => $asset,
          'key' => $addressToWithdrawTo,
          'amount' => bcdiv((string) $netAmountToWithdraw, $divisor, $assetInfo['display_decimals'])
        ]);

        return new CompletedWithdraw($addressToWithdrawTo, $netAmountToWithdraw, $response['refid']);
    }

    public function getAvailableBalance(string $assetToWithdraw): int
    {
      try {
        $response = $this->client->queryPrivate('Balance');

        $assetToWithdraw = self::$ASSETS[$assetToWithdraw];

        foreach ($response as $symbol => $available) {
          if ($assetToWithdraw === $symbol) {
            $assetInfo = $this->getAssetInfo($assetToWithdraw);
            $divisor = str_pad('1', $assetInfo['decimals'], '0') . '0';
            return (int) bcmul($available, $divisor, $assetInfo['decimals']);
          }
        }
      } catch (KrakenClientException $exception) {
        return 0;
      }
      return 0;
    }

    public function getWithdrawFee(string $asset, int $amountToWithdraw, string $addressToWithdrawTo): int
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

      $assetInfo = $this->getAssetInfo($asset);
      $divisor = str_pad('1', $assetInfo['decimals'], '0') . '0';

      return (int) bcmul($withDrawInfo['fee'], $divisor, $assetInfo['decimals']);
    }

    public function getWithdrawFeeInSatoshis(): int
    {
      // TODO: recalculate asset to to satoshis.
      return 0;
    }

    public function supportsExchange(string $exchange): bool
    {
        return 'kraken' === $exchange;
    }

    public function getAssetInfo(string $asset)
    {
      $assetInfo = $this->client->queryPublic('Assets', [
        'asset' => $asset
      ]);
      return $assetInfo[array_key_first($assetInfo)];
    }

}
