<?php

namespace App\Entity;

use App\Repository\SMSMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SMSMessageRepository::class)]
class SMSMessage
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
    private $messageAmount;

    #[ORM\Column(type: 'boolean')]
    private $smsType;

    #[ORM\Column(type: 'boolean')]
    private $isParameterized;

    #[ORM\Column(type: 'string', length: 10)]
    private $timezone;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sMSMessages')]
    private $manager;

    #[ORM\ManyToOne(targetEntity: SMSCampaign::class, inversedBy: 'sMSMessages')]
    private $campaign;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    #[ORM\Column(type: 'string', length: 15)]
    private $sender;

    #[ORM\Column(type: 'string', length: 20)]
    private $phone;

    #[ORM\Column(type: 'array')]
    private $phoneCountry = [];

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'text')]
    private $originMessage;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $createBy;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private $createFrom;

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

    public function getMessageAmount(): ?float
    {
        return $this->messageAmount;
    }

    public function setMessageAmount(float $messageAmount): self
    {
        $this->messageAmount = $messageAmount;

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

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getCampaign(): ?SMSCampaign
    {
        return $this->campaign;
    }

    public function setCampaign(?SMSCampaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoneCountry(): ?array
    {
        return $this->phoneCountry;
    }

    public function setPhoneCountry(array $phoneCountry): self
    {
        $this->phoneCountry = $phoneCountry;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getOriginMessage(): ?string
    {
        return $this->originMessage;
    }

    public function setOriginMessage(string $originMessage): self
    {
        $this->originMessage = $originMessage;

        return $this;
    }

    public function getCreateBy(): ?string
    {
        return $this->createBy;
    }

    public function setCreateBy(?string $createBy): self
    {
        $this->createBy = $createBy;

        return $this;
    }

    public function getCreateFrom(): ?string
    {
        return $this->createFrom;
    }

    public function setCreateFrom(?string $createFrom): self
    {
        $this->createFrom = $createFrom;

        return $this;
    }
}
