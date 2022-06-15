<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $code;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: User::class)]
    private $users;

    // #[ORM\OneToMany(mappedBy: 'status', targetEntity: Log::class)]
    // private $logs;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Authorization::class)]
    private $authorizations;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Transaction::class)]
    private $transactions;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: SMSMessage::class)]
    private $sMSMessages;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Recharge::class)]
    private $recharges;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Role::class)]
    private $roles;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Permission::class)]
    private $permissions;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Sender::class)]
    private $senders;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Brand::class)]
    private $brands;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Company::class)]
    private $companies;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Router::class)]
    private $routers;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private $label;


    public function __construct()
    {
        $this->users = new ArrayCollection();
        // $this->logs = new ArrayCollection();
        $this->authorizations = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->sMSMessages = new ArrayCollection();
        $this->recharges = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->senders = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->routers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setStatus($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getStatus() === $this) {
                $user->setStatus(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, Authorization>
     */
    public function getAuthorizations(): Collection
    {
        return $this->authorizations;
    }

    public function addAuthorization(Authorization $authorization): self
    {
        if (!$this->authorizations->contains($authorization)) {
            $this->authorizations[] = $authorization;
            $authorization->setStatus($this);
        }

        return $this;
    }

    public function removeAuthorization(Authorization $authorization): self
    {
        if ($this->authorizations->removeElement($authorization)) {
            // set the owning side to null (unless already changed)
            if ($authorization->getStatus() === $this) {
                $authorization->setStatus(null);
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
            $transaction->setStatus($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getStatus() === $this) {
                $transaction->setStatus(null);
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
            $sMSMessage->setStatus($this);
        }

        return $this;
    }

    public function removeSMSMessage(SMSMessage $sMSMessage): self
    {
        if ($this->sMSMessages->removeElement($sMSMessage)) {
            // set the owning side to null (unless already changed)
            if ($sMSMessage->getStatus() === $this) {
                $sMSMessage->setStatus(null);
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
            $recharge->setStatus($this);
        }

        return $this;
    }

    public function removeRecharge(Recharge $recharge): self
    {
        if ($this->recharges->removeElement($recharge)) {
            // set the owning side to null (unless already changed)
            if ($recharge->getStatus() === $this) {
                $recharge->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setStatus($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getStatus() === $this) {
                $role->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
            $permission->setStatus($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getStatus() === $this) {
                $permission->setStatus(null);
            }
        }

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
            $sender->setStatus($this);
        }

        return $this;
    }

    public function removeSender(Sender $sender): self
    {
        if ($this->senders->removeElement($sender)) {
            // set the owning side to null (unless already changed)
            if ($sender->getStatus() === $this) {
                $sender->setStatus(null);
            }
        }

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
            $brand->setStatus($this);
        }

        return $this;
    }

    public function removeBrand(Brand $brand): self
    {
        if ($this->brands->removeElement($brand)) {
            // set the owning side to null (unless already changed)
            if ($brand->getStatus() === $this) {
                $brand->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->setStatus($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getStatus() === $this) {
                $company->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Router>
     */
    public function getRouters(): Collection
    {
        return $this->routers;
    }

    public function addRouter(Router $router): self
    {
        if (!$this->routers->contains($router)) {
            $this->routers[] = $router;
            $router->setStatus($this);
        }

        return $this;
    }

    public function removeRouter(Router $router): self
    {
        if ($this->routers->removeElement($router)) {
            // set the owning side to null (unless already changed)
            if ($router->getStatus() === $this) {
                $router->setStatus(null);
            }
        }

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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

}
