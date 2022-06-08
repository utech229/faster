<?php

namespace App\Entity;

use App\Repository\ContactGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactGroupRepository::class)]
class ContactGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'string', length: 50)]
    private $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contactGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private $manager;

    #[ORM\OneToOne(mappedBy: 'contactGroup', targetEntity: ContactGroupField::class, cascade: ['persist', 'remove'])]
    private $contactGroupField;

    #[ORM\OneToMany(mappedBy: 'contactGroup', targetEntity: ContactIndex::class, orphanRemoval: true)]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getContactGroupField(): ?ContactGroupField
    {
        return $this->contactGroupField;
    }

    public function setContactGroupField(ContactGroupField $contactGroupField): self
    {
        // set the owning side of the relation if necessary
        if ($contactGroupField->getContactGroup() !== $this) {
            $contactGroupField->setContactGroup($this);
        }

        $this->contactGroupField = $contactGroupField;

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
            $contactIndex->setContactGroup($this);
        }

        return $this;
    }

    public function removeContactIndex(ContactIndex $contactIndex): self
    {
        if ($this->contactIndices->removeElement($contactIndex)) {
            // set the owning side to null (unless already changed)
            if ($contactIndex->getContactGroup() === $this) {
                $contactIndex->setContactGroup(null);
            }
        }

        return $this;
    }
}
