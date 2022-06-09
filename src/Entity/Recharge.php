<?php

namespace App\Entity;

use App\Repository\RechargeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RechargeRepository::class)]
class Recharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\OneToOne(inversedBy: 'recharge', targetEntity: Transaction::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $transaction;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'recharges')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'allToRecharge')]
    private $rechargeBy;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'recharges')]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    #[ORM\Column(type: 'float')]
    private $beforeCommission;

    #[ORM\Column(type: 'float')]
    private $commission;

    #[ORM\Column(type: 'float')]
    private $afterCommission;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRechargeBy(): ?User
    {
        return $this->rechargeBy;
    }

    public function setRechargeBy(?User $rechargeBy): self
    {
        $this->rechargeBy = $rechargeBy;

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

    public function getBeforeCommission(): ?float
    {
        return $this->beforeCommission;
    }

    public function setBeforeCommission(float $beforeCommission): self
    {
        $this->beforeCommission = $beforeCommission;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(float $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getAfterCommission(): ?float
    {
        return $this->afterCommission;
    }

    public function setAfterCommission(float $afterCommission): self
    {
        $this->afterCommission = $afterCommission;

        return $this;
    }
}
