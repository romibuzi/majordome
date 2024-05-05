<?php

namespace Majordome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Majordome\Repository\RunRepository;

#[ORM\Entity(repositoryClass: RunRepository::class)]
class Run
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $accountId = null;

    #[ORM\Column(length: 255)]
    private ?string $provider = null;

    #[ORM\Column(length: 100)]
    private ?string $region = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Violation::class, mappedBy: 'run', fetch: 'EXTRA_LAZY')]
    private Collection $violations;

    public function __construct()
    {
        $this->violations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): static
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setViolations(Collection $violations): void
    {
        $this->violations = $violations;
    }

    public function addViolation(Violation $violation): void
    {
        $this->violations->add($violation);
    }

    public function getViolations(): Collection
    {
        return $this->violations;
    }
}
