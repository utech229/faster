<?php

namespace App\Entity;

use App\Repository\SMSCampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sMSCampaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private $manager;

    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: SMSMessage::class)]
    private $sMSMessages;

    #[ORM\Column(type: 'string', length: 15)]
    private $sender;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    public function __construct()
    {
        $this->sMSMessages = new ArrayCollection();
    }

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

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, SMSMessage>
     */
    public function getSMSMessages(): Collection
    {
        return $this->sMSMessages;
    }

    public function addSMSMessage(SMSMessage $sMSMessage): self
    {
        if (!$this->sMSMessages->contains($sMSMessage)) {
            $this->sMSMessages[] = $sMSMessage;
            $sMSMessage->setCampaign($this);
        }

        return $this;
    }

    public function removeSMSMessage(SMSMessage $sMSMessage): self
    {
        if ($this->sMSMessages->removeElement($sMSMessage)) {
            // set the owning side to null (unless already changed)
            if ($sMSMessage->getCampaign() === $this) {
                $sMSMessage->setCampaign(null);
            }
        }

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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

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
}
