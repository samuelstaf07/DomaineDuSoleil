<?php

namespace App\Entity;

use App\Repository\CommentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentsRepository::class)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rentals $rentals = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $disabled_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $changed_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRentals(): ?Rentals
    {
        return $this->rentals;
    }

    public function setRentals(?Rentals $rentals): void
    {
        $this->rentals = $rentals;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): void
    {
        $this->user = $user;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): void
    {
        $this->rating = $rating;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): void
    {
        $this->is_active = $is_active;
    }

    public function getDisabledAt(): ?\DateTimeImmutable
    {
        return $this->disabled_at;
    }

    public function setDisabledAt(?\DateTimeImmutable $disabled_at): static
    {
        $this->disabled_at = $disabled_at;

        return $this;
    }

    public function getChangedAt(): ?\DateTimeImmutable
    {
        return $this->changed_at;
    }

    public function setChangedAt(?\DateTimeImmutable $changed_at): static
    {
        $this->changed_at = $changed_at;

        return $this;
    }


}
