<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Model;

class CompletedWithdraw
{
    protected string $id;
    protected string $recipientAddress;
    protected float $netAmount;

    public function __construct(string $recipientAddress, float $netAmount, string $id)
    {
        $this->id = $id;
        $this->recipientAddress = $recipientAddress;
        $this->netAmount = $netAmount;
    }

    public function getRecipientAddress(): string
    {
        return $this->recipientAddress;
    }

    public function getNetAmount(): float
    {
        return $this->netAmount;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
