<?php

namespace App\Entity;

use App\Repository\PaymentRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRequestRepository::class)]
class PaymentRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 5)]
    private ?string $currency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $cardBin = null;

    #[ORM\Column(length: 15)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?array $gatewayResponse = null;

    #[ORM\Column(length: 15)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCardBin(): ?string
    {
        return $this->cardBin;
    }

    public function setCardBin(?string $cardBin): static
    {
        $this->cardBin = $cardBin;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGatewayResponse(): ?array
    {
        return $this->gatewayResponse;
    }

    public function setGatewayResponse(?array $gatewayResponse): static
    {
        $this->gatewayResponse = $gatewayResponse;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
