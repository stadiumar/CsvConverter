<?php

namespace App\Entity;

use App\Repository\TblProductDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: TblProductDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TblProductData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $intProductDataId = null;

    #[ORM\Column(length: 50)]
    private ?string $strProductName = null;

    #[ORM\Column(length: 255)]
    private ?string $strProductDesc = null;

    #[ORM\Column(length: 10)]
    private ?string $strProductCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dtmAdded = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dtmDiscontinued = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $stmTimestamp = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    private ?float $costGbp = null;

    public function getIntProductDataId(): ?int
    {
        return $this->intProductDataId;
    }

    public function setIntProductDataId(int $intProductDataId): static
    {
        $this->intProductDataId = $intProductDataId;

        return $this;
    }

    public function getStrProductName(): ?string
    {
        return $this->strProductName;
    }

    public function setStrProductName(string $strProductName): static
    {
        $this->strProductName = $strProductName;

        return $this;
    }

    public function getStrProductDesc(): ?string
    {
        return $this->strProductDesc;
    }

    public function setStrProductDesc(string $strProductDesc): static
    {
        $this->strProductDesc = $strProductDesc;

        return $this;
    }

    public function getStrProductCode(): ?string
    {
        return $this->strProductCode;
    }

    public function setStrProductCode(string $strProductCode): static
    {
        $this->strProductCode = $strProductCode;

        return $this;
    }

    public function getDtmAdded(): ?\DateTimeInterface
    {
        return $this->dtmAdded;
    }

    public function setDtmAdded(?\DateTimeInterface $dtmAdded): static
    {
        $this->dtmAdded = $dtmAdded;

        return $this;
    }

    public function getDtmDiscontinued(): ?\DateTimeInterface
    {
        return $this->dtmDiscontinued;
    }

    public function setDtmDiscontinued(?\DateTimeInterface $dtmDiscontinued): static
    {
        $this->dtmDiscontinued = $dtmDiscontinued;

        return $this;
    }

    public function getStmTimestamp(): ?\DateTimeInterface
    {
        return $this->stmTimestamp;
    }

    public function setStmTimestamp(\DateTimeInterface $stmTimestamp): static
    {
        $this->stmTimestamp = $stmTimestamp;

        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(?string $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCostGbp(): ?float
    {
        return $this->costGbp;
    }

    public function setCostGbp(?float $costGbp): static
    {
        $this->costGbp = $costGbp;

        return $this;
    }

    #[ORM\PrePersist]
    public function setDtmAddedValue(): void
    {
       $this->dtmAdded = new DateTimeImmutable();
       $this->stmTimestamp = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setCreatedAtValue(): void
    {
       $this->stmTimestamp = new DateTimeImmutable();
    }
}
