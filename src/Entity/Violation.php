<?php

namespace Majordome\Entity;

use Doctrine\ORM\Mapping as ORM;
use Majordome\Repository\ViolationRepository;
use Majordome\Resource\DefaultResource;
use Majordome\Resource\Resource;

#[ORM\Entity(repositoryClass: ViolationRepository::class)]
class Violation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $resourceId = null;

    #[ORM\Column(length: 255)]
    private ?string $resourceType = null;

    #[ORM\ManyToOne(targetEntity: Run::class, inversedBy: 'violations')]
    #[ORM\JoinColumn(name: 'run_id', referencedColumnName: 'id')]
    private Run|null $run = null;

    #[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'violations')]
    #[ORM\JoinColumn(name: 'rule_id', referencedColumnName: 'id')]
    private Rule|null $rule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function setResourceId(string $resourceId): static
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType): static
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getRun(): ?Run
    {
        return $this->run;
    }

    public function setRun(?Run $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setRule(?Rule $rule): static
    {
        $this->rule = $rule;

        return $this;
    }

    public function getResource(): Resource
    {
        return new DefaultResource($this->getResourceId(), $this->getResourceType(), []);
    }
}
