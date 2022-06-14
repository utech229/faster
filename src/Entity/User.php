<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 100)]
    private $uid;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $phone;

    #[ORM\Column(type: 'float', nullable: true)]
    private $balance;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $profilePhoto;

    #[ORM\Column(type: 'string', length: 255)]
    private $apikey;

    #[ORM\Column(type: 'array', nullable: true)]
    private $country = [];

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isDlr;

    #[ORM\Column(type: 'array', nullable: true)]
    private $price = [];

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $postPay;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private $activeCode;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Usetting::class, cascade: ['persist', 'remove'])]
    private $usetting;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'users')]
    private $role;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'accountManager')]
    private $accountManager;


    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'brand')]
    private $Brand;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'users')]
    private $brand;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    #[ORM\ManyToOne(targetEntity: Router::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private $router;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: Brand::class)]
    private $brands;

    #[ORM\OneToMany(mappedBy: 'validator', targetEntity: Brand::class)]
    private $validator;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: Router::class)]
    private $routers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class)]
    private $logs;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: Role::class)]
    private $manageringRoles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ExtraAuthorization::class, orphanRemoval: true)]
    private $extraAuthorizations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Transaction::class, orphanRemoval: true)]
    private $transactions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Recharge::class, orphanRemoval: true)]
    private $recharges;

    #[ORM\OneToMany(mappedBy: 'rechargeBy', targetEntity: Recharge::class)]
    private $allToRecharge;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: SoldeNotification::class, cascade: ['persist', 'remove'])]
    private $soldeNotification;

    #[ORM\ManyToOne(targetEntity: Sender::class, inversedBy: 'users')]
    private $defaultSender;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: Sender::class)]
    private $senders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResellerRequest::class, orphanRemoval: true)]
    private $resellerRequests;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: ContactGroup::class, orphanRemoval: true)]
    private $contactGroups;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: SMSCampaign::class, orphanRemoval: true)]
    private $sMSCampaigns;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: SMSMessage::class)]
    private $sMSMessages;

    #[ORM\OneToOne(mappedBy: 'manager', targetEntity: Company::class, cascade: ['persist', 'remove'])]
    private $company;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $lastLoginAt;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'affiliates')]
    private $affiliateManager;

    #[ORM\OneToMany(mappedBy: 'affiliateManager', targetEntity: self::class)]
    private $affiliates;

    public function __construct()
    {
        $this->accountManager = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->validator = new ArrayCollection();
        $this->routers = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->manageringRoles = new ArrayCollection();
        $this->extraAuthorizations = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->recharges = new ArrayCollection();
        $this->allToRecharge = new ArrayCollection();
        $this->senders = new ArrayCollection();
        $this->resellerRequests = new ArrayCollection();
        $this->contactGroups = new ArrayCollection();
        $this->sMSCampaigns = new ArrayCollection();
        $this->sMSMessages = new ArrayCollection();
        $this->affiliates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): self
    {
        $this->profilePhoto = $profilePhoto;

        return $this;
    }

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(string $apikey): self
    {
        $this->apikey = $apikey;

        return $this;
    }

    public function getCountry(): ?array
    {
        return $this->country;
    }

    public function setCountry(?array $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getIsDlr(): ?bool
    {
        return $this->isDlr;
    }

    public function setIsDlr(?bool $isDlr): self
    {
        $this->isDlr = $isDlr;

        return $this;
    }

    public function getPrice(): ?array
    {
        return $this->price;
    }

    public function setPrice(?array $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isPostPay(): ?bool
    {
        return $this->postPay;
    }

    public function setPostPay(?bool $postPay): self
    {
        $this->postPay = $postPay;

        return $this;
    }

    public function getActiveCode(): ?string
    {
        return $this->activeCode;
    }

    public function setActiveCode(?string $activeCode): self
    {
        $this->activeCode = $activeCode;

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

    public function getUsetting(): ?Usetting
    {
        return $this->usetting;
    }

    public function setUsetting(?Usetting $usetting): self
    {
        // unset the owning side of the relation if necessary
        if ($usetting === null && $this->usetting !== null) {
            $this->usetting->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($usetting !== null && $usetting->getUser() !== $this) {
            $usetting->setUser($this);
        }

        $this->usetting = $usetting;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAccountManager(): ?self
    {
        return $this->accountManager;
    }

    public function setAccountManager(?self $accountManager): self
    {
        $this->accountManager = $accountManager;

        return $this;
    }

    public function addAccountManager(self $accountManager): self
    {
        if (!$this->accountManager->contains($accountManager)) {
            $this->accountManager[] = $accountManager;
            $accountManager->setAccountManager($this);
        }

        return $this;
    }

    public function removeAccountManager(self $accountManager): self
    {
        if ($this->accountManager->removeElement($accountManager)) {
            // set the owning side to null (unless already changed)
            if ($accountManager->getAccountManager() === $this) {
                $accountManager->setAccountManager(null);
            }
        }

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->Brand;
    }

    public function setBrand(?Brand $Brand): self
    {
        $this->Brand = $Brand;

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

    public function getRouter(): ?Router
    {
        return $this->router;
    }

    public function setRouter(?Router $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @return Collection<int, Brand>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(Brand $brand): self
    {
        if (!$this->brands->contains($brand)) {
            $this->brands[] = $brand;
            $brand->setManager($this);
        }

        return $this;
    }

    public function removeBrand(Brand $brand): self
    {
        if ($this->brands->removeElement($brand)) {
            // set the owning side to null (unless already changed)
            if ($brand->getManager() === $this) {
                $brand->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Brand>
     */
    public function getValidator(): Collection
    {
        return $this->validator;
    }

    public function addValidator(Brand $validator): self
    {
        if (!$this->validator->contains($validator)) {
            $this->validator[] = $validator;
            $validator->setValidator($this);
        }

        return $this;
    }

    public function removeValidator(Brand $validator): self
    {
        if ($this->validator->removeElement($validator)) {
            // set the owning side to null (unless already changed)
            if ($validator->getValidator() === $this) {
                $validator->setValidator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Router>
     */
    public function getRouters(): Collection
    {
        return $this->Routers;
    }

    public function addRouter(Router $router): self
    {
        if (!$this->routers->contains($router)) {
            $this->routers[] = $router;
            $router->setManager($this);
        }

        return $this;
    }

    public function removeRouter(Router $router): self
    {
        if ($this->routers->removeElement($router)) {
            // set the owning side to null (unless already changed)
            if ($router->getManager() === $this) {
                $router->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUser() === $this) {
                $log->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getManageringRoles(): Collection
    {
        return $this->manageringRoles;
    }

    public function addManageringRole(Role $manageringRole): self
    {
        if (!$this->manageringRoles->contains($manageringRole)) {
            $this->manageringRoles[] = $manageringRole;
            $manageringRole->setManager($this);
        }

        return $this;
    }

    public function removeManageringRole(Role $manageringRole): self
    {
        if ($this->manageringRoles->removeElement($manageringRole)) {
            // set the owning side to null (unless already changed)
            if ($manageringRole->getManager() === $this) {
                $manageringRole->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExtraAuthorization>
     */
    public function getExtraAuthorizations(): Collection
    {
        return $this->extraAuthorizations;
    }

    public function addExtraAuthorization(ExtraAuthorization $extraAuthorization): self
    {
        if (!$this->extraAuthorizations->contains($extraAuthorization)) {
            $this->extraAuthorizations[] = $extraAuthorization;
            $extraAuthorization->setUser($this);
        }

        return $this;
    }

    public function removeExtraAuthorization(ExtraAuthorization $extraAuthorization): self
    {
        if ($this->extraAuthorizations->removeElement($extraAuthorization)) {
            // set the owning side to null (unless already changed)
            if ($extraAuthorization->getUser() === $this) {
                $extraAuthorization->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recharge>
     */
    public function getRecharges(): Collection
    {
        return $this->recharges;
    }

    public function addRecharge(Recharge $recharge): self
    {
        if (!$this->recharges->contains($recharge)) {
            $this->recharges[] = $recharge;
            $recharge->setUser($this);
        }

        return $this;
    }

    public function removeRecharge(Recharge $recharge): self
    {
        if ($this->recharges->removeElement($recharge)) {
            // set the owning side to null (unless already changed)
            if ($recharge->getUser() === $this) {
                $recharge->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recharge>
     */
    public function getAllToRecharge(): Collection
    {
        return $this->allToRecharge;
    }

    public function addAllToRecharge(Recharge $allToRecharge): self
    {
        if (!$this->allToRecharge->contains($allToRecharge)) {
            $this->allToRecharge[] = $allToRecharge;
            $allToRecharge->setRechargeBy($this);
        }

        return $this;
    }

    public function removeAllToRecharge(Recharge $allToRecharge): self
    {
        if ($this->allToRecharge->removeElement($allToRecharge)) {
            // set the owning side to null (unless already changed)
            if ($allToRecharge->getRechargeBy() === $this) {
                $allToRecharge->setRechargeBy(null);
            }
        }

        return $this;
    }

    public function getSoldeNotification(): ?SoldeNotification
    {
        return $this->soldeNotification;
    }

    public function setSoldeNotification(?SoldeNotification $soldeNotification): self
    {
        // unset the owning side of the relation if necessary
        if ($soldeNotification === null && $this->soldeNotification !== null) {
            $this->soldeNotification->setRelation(null);
        }

        // set the owning side of the relation if necessary
        if ($soldeNotification !== null && $soldeNotification->getRelation() !== $this) {
            $soldeNotification->setRelation($this);
        }

        $this->soldeNotification = $soldeNotification;

        return $this;
    }

    public function getDefaultSender(): ?Sender
    {
        return $this->defaultSender;
    }

    public function setDefaultSender(?Sender $defaultSender): self
    {
        $this->defaultSender = $defaultSender;

        return $this;
    }

    /**
     * @return Collection<int, Sender>
     */
    public function getSenders(): Collection
    {
        return $this->senders;
    }

    public function addSender(Sender $sender): self
    {
        if (!$this->senders->contains($sender)) {
            $this->senders[] = $sender;
            $sender->setManager($this);
        }

        return $this;
    }

    public function removeSender(Sender $sender): self
    {
        if ($this->senders->removeElement($sender)) {
            // set the owning side to null (unless already changed)
            if ($sender->getManager() === $this) {
                $sender->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResellerRequest>
     */
    public function getResellerRequests(): Collection
    {
        return $this->resellerRequests;
    }

    public function addResellerRequest(ResellerRequest $resellerRequest): self
    {
        if (!$this->resellerRequests->contains($resellerRequest)) {
            $this->resellerRequests[] = $resellerRequest;
            $resellerRequest->setUser($this);
        }

        return $this;
    }

    public function removeResellerRequest(ResellerRequest $resellerRequest): self
    {
        if ($this->resellerRequests->removeElement($resellerRequest)) {
            // set the owning side to null (unless already changed)
            if ($resellerRequest->getUser() === $this) {
                $resellerRequest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContactGroup>
     */
    public function getContactGroups(): Collection
    {
        return $this->contactGroups;
    }

    public function addContactGroup(ContactGroup $contactGroup): self
    {
        if (!$this->contactGroups->contains($contactGroup)) {
            $this->contactGroups[] = $contactGroup;
            $contactGroup->setManager($this);
        }

        return $this;
    }

    public function removeContactGroup(ContactGroup $contactGroup): self
    {
        if ($this->contactGroups->removeElement($contactGroup)) {
            // set the owning side to null (unless already changed)
            if ($contactGroup->getManager() === $this) {
                $contactGroup->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SMSCampaign>
     */
    public function getSMSCampaigns(): Collection
    {
        return $this->sMSCampaigns;
    }

    public function addSMSCampaign(SMSCampaign $sMSCampaign): self
    {
        if (!$this->sMSCampaigns->contains($sMSCampaign)) {
            $this->sMSCampaigns[] = $sMSCampaign;
            $sMSCampaign->setManager($this);
        }

        return $this;
    }

    public function removeSMSCampaign(SMSCampaign $sMSCampaign): self
    {
        if ($this->sMSCampaigns->removeElement($sMSCampaign)) {
            // set the owning side to null (unless already changed)
            if ($sMSCampaign->getManager() === $this) {
                $sMSCampaign->setManager(null);
            }
        }

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
            $sMSMessage->setManager($this);
        }

        return $this;
    }

    public function removeSMSMessage(SMSMessage $sMSMessage): self
    {
        if ($this->sMSMessages->removeElement($sMSMessage)) {
            // set the owning side to null (unless already changed)
            if ($sMSMessage->getManager() === $this) {
                $sMSMessage->setManager(null);
            }
        }
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        // set the owning side of the relation if necessary
        if ($company->getManager() !== $this) {
            $company->setManager($this);
        }

        $this->company = $company;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getAffiliateManager(): ?self
    {
        return $this->affiliateManager;
    }

    public function setAffiliateManager(?self $affiliateManager): self
    {
        $this->affiliateManager = $affiliateManager;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getAffiliates(): Collection
    {
        return $this->affiliates;
    }

    public function addAffiliate(self $affiliate): self
    {
        if (!$this->affiliates->contains($affiliate)) {
            $this->affiliates[] = $affiliate;
            $affiliate->setAffiliateManager($this);
        }

        return $this;
    }

    public function removeAffiliate(self $affiliate): self
    {
        if ($this->affiliates->removeElement($affiliate)) {
            // set the owning side to null (unless already changed)
            if ($affiliate->getAffiliateManager() === $this) {
                $affiliate->setAffiliateManager(null);
            }
        }

        return $this;
    }
}
