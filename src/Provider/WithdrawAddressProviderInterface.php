<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider;

interface WithdrawAddressProviderInterface
{
  /**
   * Method should return a Bitcoin address for withdrawal.
   */
  public function provide(): string;
  
  public function getAsset(): string;
}