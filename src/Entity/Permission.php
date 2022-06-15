<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 5)]
    private $code;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $UpdatedAt;

    #[ORM\OneToMany(mappedBy: 'permission', targetEntity: Authorization::class)]
    private $authorizations;

    #[ORM\OneToMany(mappedBy: 'permission', targetEntity: ExtraAuthorization::class)]
    private $extraAuthorizations;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    public function __construct()
    {
        $this->authorizations = new ArrayCollection();
        $this->extraAuthorizations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
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
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

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
            $authorization->setPermission($this);
        }

        return $this;
    }

    public function removeAuthorization(Authorization $authorization): self
    {
        if ($this->authorizations->removeElement($authorization)) {
            // set the owning side to null (unless already changed)
            if ($authorization->getPermission() === $this) {
                $authorization->setPermission(null);
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
            $extraAuthorization->setPermission($this);
        }

        return $this;
    }

    public function removeExtraAuthorization(ExtraAuthorization $extraAuthorization): self
    {
        if ($this->extraAuthorizations->removeElement($extraAuthorization)) {
            // set the owning side to null (unless already changed)
            if ($extraAuthorization->getPermission() === $this) {
                $extraAuthorization->setPermission(null);
            }
        }

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
}
