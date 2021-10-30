<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Service;

use Jorijn\Bitcoin\Dca\Event\WithdrawSuccessEvent;
use Jorijn\Bitcoin\Dca\Exception\NoExchangeAvailableException;
use Jorijn\Bitcoin\Dca\Exception\NoRecipientAddressAvailableException;
use Jorijn\Bitcoin\Dca\Model\CompletedWithdraw;
use Jorijn\Bitcoin\Dca\Provider\WithdrawAddressProviderInterface;
use Jorijn\Bitcoin\Dca\Repository\TaggedIntegerRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class WithdrawService
{
    /** @var WithdrawAddressProviderInterface[] */
    protected iterable $addressProviders;
    protected TaggedIntegerRepositoryInterface $balanceRepository;
    protected EventDispatcherInterface $dispatcher;
    protected LoggerInterface $logger;
    protected string $configuredExchange;
    /** @var WithdrawServiceInterface[] */
    protected iterable $configuredServices;

    public function __construct(
        iterable $addressProviders,
        iterable $configuredServices,
        TaggedIntegerRepositoryInterface $balanceRepository,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger,
        string $configuredExchange
    ) {
        $this->addressProviders = $addressProviders;
        $this->balanceRepository = $balanceRepository;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->configuredServices = $configuredServices;
        $this->configuredExchange = $configuredExchange;
    }

    public function getWithdrawFee(string $asset, int $amountToWithdraw, string $addressToWithdrawTo): int
    {
      return $this->getActiveService()->getWithdrawFee($asset, $amountToWithdraw, $addressToWithdrawTo);
    }

    public function getWithdrawFeeInSatoshis(): int
    {
        return $this->getActiveService()->getWithdrawFeeInSatoshis();
    }

    public function getAssetInfo(string $asset)
    {
        return $this->getActiveService()->getAssetInfo($asset);
    }

    public function withdraw(string $asset, int $balanceToWithdraw, string $addressToWithdrawTo, string $tag = null): CompletedWithdraw
    {
        try {
            $completedWithdraw = $this->getActiveService()->withdraw($asset, $balanceToWithdraw, $addressToWithdrawTo);

            $this->dispatcher->dispatch(
                new WithdrawSuccessEvent(
                    $completedWithdraw,
                    $tag
                )
            );

            $this->logger->info('withdraw to {address} successful, processing as ID {data.id}', [
                'tag' => $tag,
                'asset' => $asset,
                'balance' => $balanceToWithdraw,
                'address' => $addressToWithdrawTo,
                'data' => ['id' => $completedWithdraw->getId()],
            ]);

            return $completedWithdraw;
        } catch (\Throwable $exception) {
            $this->logger->error('withdraw to {address} failed', [
                'tag' => $tag,
                'asset' => $asset,
                'balance' => $balanceToWithdraw,
                'address' => $addressToWithdrawTo,
                'reason' => $exception->getMessage() ?: \get_class($exception),
            ]);

            throw $exception;
        }
    }

    public function getBalance(string $assetToWithdraw, string $tag = null): int
    {
        $maxAvailableBalance = $this->getActiveService()->getAvailableBalance($assetToWithdraw);

        if ($tag) {
            $tagBalance = $this->balanceRepository->get($tag);

            // limit the balance to what comes first: the tagged balance, or the maximum balance
            return $tagBalance <= $maxAvailableBalance ? $tagBalance : $maxAvailableBalance;
        }

        return $maxAvailableBalance;
    }

    public function getRecipientAddress(string $assetToWithdraw): string
    {
        // Return configured address by asset.
        foreach ($this->addressProviders as $addressProvider) {
            try {
                if ($addressProvider->getAsset() == $assetToWithdraw) {
                    return $addressProvider->provide();
                }
            } catch (\Throwable $exception) {
                // allowed to fail
            }
        }

        throw new NoRecipientAddressAvailableException(sprintf('Unable to determine address to withdraw your assets, did you configure any? Or maybe the token %s is not supported by this tool.', $assetToWithdraw));
    }

    protected function getActiveService(): WithdrawServiceInterface
    {
        foreach ($this->configuredServices as $configuredService) {
            if ($configuredService->supportsExchange($this->configuredExchange)) {
                return $configuredService;
            }
        }

        $errorMessage = 'no exchange was available to perform this withdraw';
        $this->logger->error($errorMessage);

        throw new NoExchangeAvailableException($errorMessage);
    }

    public function getExchange(): string
    {
      return $this->configuredExchange;
    }
}
