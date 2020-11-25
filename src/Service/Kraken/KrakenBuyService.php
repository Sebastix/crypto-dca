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
    private const SATOSHIS_IN_A_BITCOIN = '100000000';
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
   * @param bool $simulate
   *   Set this boolean to true to simulate an buy order - see https://support.kraken.com/hc/en-us/articles/360000919926-Does-Kraken-offer-an-API-test-environment-
   *
   * @return CompletedBuyOrder
   * @throws PendingBuyOrderException
   */
    public function initiateBuy(int $amount, string $asset, bool $simulate = false): CompletedBuyOrder
    {
        // generate a 32-bit singed integer to track this order
        $this->lastUserRef = (string) random_int(0, 0x7FFFFFFF);

        $assetInfo = $this->getAssetInfo($asset);
        $volume = bcdiv((string) $amount, $this->getCurrentPrice($asset), $assetInfo['decimals']);
        // Check minimum order amount
        if ($volume < $this->getAssetPair($asset.$this->baseCurrency)['ordermin']) {
          $requiredMinimum = bcmul($this->getAssetPair($asset.$this->baseCurrency)['ordermin'], $this->getCurrentPrice($asset), 2);
          throw new KrakenClientException(sprintf('Your amount is too low. A minimum of %s %d is required.', $this->baseCurrency, ceil($requiredMinimum)));
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
