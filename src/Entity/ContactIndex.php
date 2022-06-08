<?php

namespace App\Entity;

use App\Repository\ContactIndexRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactIndexRepository::class)]
class ContactIndex
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 25)]
    private $uid;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $field1;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $field2;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $field3;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $field4;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $field5;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

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

    public function getField1(): ?string
    {
        return $this->field1;
    }

    public function setField1(?string $field1): self
    {
        $this->field1 = $field1;

        return $this;
    }

    public function getField2(): ?string
    {
        return $this->field2;
    }

    public function setField2(?string $field2): self
    {
        $this->field2 = $field2;

        return $this;
    }

    public function getField3(): ?string
    {
        return $this->field3;
    }

    public function setField3(?string $field3): self
    {
        $this->field3 = $field3;

        return $this;
    }

    public function getField4(): ?string
    {
        return $this->field4;
    }

    public function setField4(?string $field4): self
    {
        $this->field4 = $field4;

        return $this;
    }

    public function getField5(): ?string
    {
        return $this->field5;
    }

    public function setField5(?string $field5): self
    {
        $this->field5 = $field5;

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
}
