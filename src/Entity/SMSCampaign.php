<?php

namespace App\Entity;

use App\Repository\SMSCampaignRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SMSCampaignRepository::class)]
class SMSCampaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $sendingAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: 'text')]
    private $message;

    #[ORM\Column(type: 'float')]
    private $campaignAmount;

    #[ORM\Column(type: 'boolean')]
    private $smsType;

    #[ORM\Column(type: 'boolean')]
    private $isParameterized;

    #[ORM\Column(type: 'string', length: 10)]
    private $timezone;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSendingAt(): ?\DateTimeImmutable
    {
        return $this->sendingAt;
    }

    public function setSendingAt(?\DateTimeImmutable $sendingAt): self
    {
        $this->sendingAt = $sendingAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCampaignAmount(): ?float
    {
        return $this->campaignAmount;
    }

    public function setCampaignAmount(float $campaignAmount): self
    {
        $this->campaignAmount = $campaignAmount;

        return $this;
    }

    public function isSmsType(): ?bool
    {
        return $this->smsType;
    }

    public function setSmsType(bool $smsType): self
    {
        $this->smsType = $smsType;

        return $this;
    }

    public function isIsParameterized(): ?bool
    {
        return $this->isParameterized;
    }

    public function setIsParameterized(bool $isParameterized): self
    {
        $this->isParameterized = $isParameterized;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
