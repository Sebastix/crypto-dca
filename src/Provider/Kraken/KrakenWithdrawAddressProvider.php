<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Provider\Kraken;

class KrakenWithdrawAddressProvider implements KrakenWithdrawAddressProviderInterface
{
    protected ?string $configuredAddress;
    protected string $asset;

    public function __construct(?string $configuredAddress)
    {
        $this->configuredAddress = $configuredAddress;
        $this->setAsset($configuredAddress);
    }

    public function provide(): string
    {
        return $this->configuredAddress;
    }

    public function setAsset(string $configuredAddress)
    {
        $envVariables = getenv();
        foreach ($envVariables as $varKey => $varValue){
            // Match Kraken withdraw addresses.
            if(0 === strpos($varKey, "KRAKEN_WITHDRAW_ADDRESS_")) {
                if(getenv($varKey) === $configuredAddress) {
                    // String to array conversion.
                    $arr = explode('_', $varKey);
                    // Get last element which is the asset of the provided address.
                    $asset = end($arr);
                }

            }
        }
        $this->asset = $asset;
    }

    public function getAsset(): string
    {
        return $this->asset;
    }
}
