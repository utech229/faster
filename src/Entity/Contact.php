<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'string', length: 20)]
    private $phone;

    #[ORM\Column(type: 'boolean')]
    private $isImported;

    #[ORM\Column(type: 'array')]
    private $phoneCountry = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: ContactIndex::class, orphanRemoval: true)]
    private $contactIndices;

    public function __construct()
    {
        $this->contactIndices = new ArrayCollection();
    }

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function isIsImported(): ?bool
    {
        return $this->isImported;
    }

    public function setIsImported(bool $isImported): self
    {
        $this->isImported = $isImported;

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
     * @return Collection<int, ContactIndex>
     */
    public function getContactIndices(): Collection
    {
        return $this->contactIndices;
    }

    public function addContactIndex(ContactIndex $contactIndex): self
    {
        if (!$this->contactIndices->contains($contactIndex)) {
            $this->contactIndices[] = $contactIndex;
            $contactIndex->setContact($this);
        }

        return $this;
    }

    public function removeContactIndex(ContactIndex $contactIndex): self
    {
        if ($this->contactIndices->removeElement($contactIndex)) {
            // set the owning side to null (unless already changed)
            if ($contactIndex->getContact() === $this) {
                $contactIndex->setContact(null);
            }
        }

        return $this;
    }
}
