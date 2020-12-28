<?php

declare(strict_types=1);

namespace Tests\Jorijn\Bitcoin\Dca\Provider\Kraken;

use Jorijn\Bitcoin\Dca\Provider\Kraken\KrakenWithdrawAddressProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Jorijn\Bitcoin\Dca\Provider\Kraken\KrakenWithdrawAddressProvider
 * @covers ::__construct
 *
 * @internal
 */
final class KrakenWithdrawAddressProviderTest extends TestCase
{
    /** @var KrakenWithdrawAddressProvider */
    private KrakenWithdrawAddressProvider $provider;
    private string $configuredAddress;
    private string $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuredAddress = 'ca'.random_int(1000, 2000);
        $this->asset = 'XXBT';
        $this->provider = new KrakenWithdrawAddressProvider($this->configuredAddress);
    }

    /**
     * @covers ::provide
     */
    public function testExpectAddressToBeReturnedWhenValid(): void
    {
        static::assertSame($this->configuredAddress, $this->provider->provide());
    }

    /**
     * @covers ::getAsset
     */
    public function testExpectedAsset(): void
    {
        static::assertSame($this->asset, $this->provider->getAsset());
    }
}
