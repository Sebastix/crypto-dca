<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service\Kraken;

use Jorijn\Bitcoin\Dca\Client\KrakenClientInterface;
use Jorijn\Bitcoin\Dca\Exception\KrakenClientException;
use Jorijn\Bitcoin\Dca\Exception\PendingBuyOrderException;
use Jorijn\Bitcoin\Dca\Model\CompletedBuyOrder;
use Jorijn\Bitcoin\Dca\Service\BuyServiceInterface;

class KrakenBuyService implements BuyServiceInterface
{
    public const FEE_STRATEGY_INCLUSIVE = 'include';
    public const FEE_STRATEGY_EXCLUSIVE = 'exclude';

    protected string $lastUserRef;
    protected KrakenClientInterface $client;
    protected string $baseCurrency;

    public function __construct(KrakenClientInterface $client, string $baseCurrency)
    {
        $this->client = $client;
        $this->baseCurrency = $baseCurrency;
    }

    public function supportsExchange(string $exchange): bool
    {
        return 'kraken' === $exchange;
    }

  /**
   * @param int $amount
   *   Set this boolean to true to simulate an buy order - see https://support.kraken.com/hc/en-us/articles/360000919926-Does-Kraken-offer-an-API-test-environment-
   *
   * @return CompletedBuyOrder
   * @throws PendingBuyOrderException
   */
    public function initiateBuy(int $amount, string $asset): CompletedBuyOrder
    {
        // Set this boolean to true to simulate an buy order - see https://support.kraken.com/hc/en-us/articles/360000919926-Does-Kraken-offer-an-API-test-environment-.
        $simulate = getenv('KRAKEN_SIMULATE');
        if ($simulate === null) {
          $simulate = 0;
        }

        // generate a 32-bit singed integer to track this order
        $this->lastUserRef = (string) random_int(0, 0x7FFFFFFF);

        $assetInfo = $this->getAssetInfo($asset);

        $volume =  $this->getAmountForStrategy($amount, self::FEE_STRATEGY_INCLUSIVE, $asset, $assetInfo['decimals']);

        // Check minimum order amount
        if ($volume < $this->getAssetPair($asset.$this->baseCurrency)['ordermin']) {
          $requiredMinimum = bcmul($this->getAssetPair($asset.$this->baseCurrency)['ordermin'], $this->getCurrentPrice($asset), 2);
          throw new KrakenClientException(sprintf('The amount you would like to buy is too low. A minimum of %s %d is required.', $this->baseCurrency, ceil($requiredMinimum)));
        }

        $addedOrder = $this->client->queryPrivate('AddOrder', [
            'pair' => $asset.$this->baseCurrency,
            'type' => 'buy',
            'ordertype' => 'market',
            'volume' => $volume,
            'oflags' => 'fciq', // prefer fee in quote currency
            'userref' => $this->lastUserRef,
            'validate' => $simulate
        ]);

        // Output for a test order
        if (isset($addedOrder['txid']) === false) {
          throw new KrakenClientException(sprintf('Test buy order - %s', $addedOrder['descr']['order']));
        }

        $orderId = $addedOrder['txid'][array_key_first($addedOrder['txid'])];

        // check that its closed
        $this->checkIfOrderIsFilled($orderId);

        return $this->getCompletedBuyOrder($orderId);
    }

    public function checkIfOrderIsFilled(string $orderId): CompletedBuyOrder
    {
        $trades = $this->client->queryPrivate('OpenOrders', ['userref' => $this->lastUserRef]);
        if (\count($trades['open'] ?? []) > 0) {
            throw new PendingBuyOrderException($orderId);
        }

        return $this->getCompletedBuyOrder($orderId);
    }

    public function cancelBuyOrder(string $orderId): void
    {
        $this->client->queryPrivate('CancelOrder', [
            'txid' => $orderId,
        ]);
    }

    protected function getAssetInfo(string $asset){
      $assetInfo = $this->client->queryPublic('Assets', [
        'asset' => $asset
      ]);

      return $assetInfo[array_key_first($assetInfo)];
    }

    protected function getAssetPair(string $assetPair) {
      $assetPairInfo = $this->client->queryPublic('AssetPairs', [
        'pair' => $assetPair
      ]);
      
      return $assetPairInfo[array_key_first($assetPairInfo)];
    }

    protected function getCurrentPrice(string $asset): string
    {
        $tickerInfo = $this->client->queryPublic('Ticker', [
            'pair' => $asset.$this->baseCurrency,
        ]);

        return $tickerInfo[array_key_first($tickerInfo)]['a'][0];
    }

    /**
     * Calculated the amount with respect to the given strategy. If an amount of 150 EUR would be bought:.
     *
     * - exclusive: returns 150 / <current price> => 150,00 + 0,39 fee = net yield 150 cost 150,39
     * - inclusive: returns (150 - 0,36) / <current price> => 150,00 - 0,36 = net yield 149,64 cost 149,99
     */
    protected function getAmountForStrategy(int $baseCurrencyAmount, string $feeStrategy, string $asset, int $decimals): string
    {
      $currentPrice = $this->getCurrentPrice($asset);

      switch ($feeStrategy) {
        case self::FEE_STRATEGY_EXCLUSIVE:
          return bcdiv((string) $baseCurrencyAmount, $currentPrice, $decimals);

        case self::FEE_STRATEGY_INCLUSIVE:
        default:
          $infoAssetPair = $this->getAssetPair($asset.$this->baseCurrency);
          // Returns the fee percentage, taker side. Multiplied by 10000 to ensure rounded integer. 0.26 -> 2600.
          $feePercentage = $infoAssetPair['fees'][0][1] ?? 0;
          $feePercentage *= 10000;
          $fee = $feePercentage / 10000;
          $feeInBaseCurrency = ($baseCurrencyAmount / 100) * $fee;

          return bcdiv((string) ($baseCurrencyAmount - $feeInBaseCurrency), $currentPrice, $decimals);
      }
    }

    protected function getCompletedBuyOrder(string $orderId): CompletedBuyOrder
    {
        $trades = $this->client->queryPrivate('TradesHistory', ['start' => time() - 900]);
        $orderInfo = null;

        foreach ($trades['trades'] ?? [] as $trade) {
            if ($trade['ordertxid'] === $orderId) {
                $orderInfo = $trade;

                break;
            }
        }

        if (null === $orderInfo) {
            throw new KrakenClientException('no open orders left yet order was not found, you should investigate this');
        }

        $pairInfo = $this->getAssetPair($orderInfo['pair']);

        return (new CompletedBuyOrder())
            ->setDisplayAmountBought($orderInfo['vol'].' '.$pairInfo['base'])
            ->setDisplayAmountSpent($orderInfo['cost'].' '.$this->baseCurrency)
            ->setDisplayAveragePrice($orderInfo['price'].' '.$this->baseCurrency)
            ->setDisplayFeesSpent($orderInfo['fee'].' '.$this->baseCurrency)
        ;
    }

    public function validateAsset(string $asset): void
    {
      // TODO: Implement validateAsset() method.
    }
}
