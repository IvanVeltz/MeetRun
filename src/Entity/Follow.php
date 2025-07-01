<?php

namespace App\Entity;

use App\Repository\FollowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowRepository::class)]
class Follow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'follows')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userSource = null;

    #[ORM\ManyToOne(inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userTarget = null;

    #[ORM\Column]
    private ?bool $followAccepted = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserSource(): ?User
    {
        return $this->userSource;
    }

    public function setUserSource(?User $userSource): static
    {
        $this->userSource = $userSource;

        return $this;
    }

    public function getUserTarget(): ?User
    {
        return $this->userTarget;
    }

    public function setUserTarget(?User $userTarget): static
    {
        $this->userTarget = $userTarget;

        return $this;
    }

    public function isFollowAccepted(): ?bool
    {
        return $this->followAccepted;
    }

    public function setFollowAccepted(bool $followAccepted): static
    {
        $this->followAccepted = $followAccepted;

        return $this;
    }
}
