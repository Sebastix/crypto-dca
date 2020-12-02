<?php

declare(strict_types=1);

namespace Tests\Jorijn\Bitcoin\Dca\Provider;

use Jorijn\Bitcoin\Dca\Provider\BitcoinWithdrawAddressProvider;
use Jorijn\Bitcoin\Dca\Validator\ValidationException;
use Jorijn\Bitcoin\Dca\Validator\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Jorijn\Bitcoin\Dca\Provider\BitcoinWithdrawAddressProvider
 * @covers ::__construct
 *
 * @internal
 */
final class BitcoinWithdrawAddressProviderTest extends TestCase
{
    /** @var MockObject|ValidationInterface */
    private $validation;
    /** @var BitcoinWithdrawAddressProvider */
    private BitcoinWithdrawAddressProvider $provider;
    private string $configuredAddress;
    private string $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuredAddress = 'ca'.random_int(1000, 2000);
        $this->validation = $this->createMock(ValidationInterface::class);
        $this->asset = 'BTC';
        $this->provider = new BitcoinWithdrawAddressProvider($this->validation, $this->configuredAddress, $this->asset);
    }

    /**
     * @covers ::provide
     */
    public function testExpectExceptionWhenValidationFails(): void
    {
        $validationException = new ValidationException('error'.random_int(1000, 2000));

        $this->validation
            ->expects(static::once())
            ->method('validate')
            ->with($this->configuredAddress)
            ->willThrowException($validationException)
        ;

        $this->expectExceptionObject($validationException);

        $this->provider->provide();
    }

    /**
     * @covers ::provide
     */
    public function testExpectAddressToBeReturnedWhenValid(): void
    {
        $this->validation
            ->expects(static::once())
            ->method('validate')
            ->with($this->configuredAddress)
        ;

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
