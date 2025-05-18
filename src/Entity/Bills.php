<?php

namespace App\Entity;

use App\Repository\BillsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillsRepository::class)]
class Bills
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?float $total_price = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?bool $status = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'bills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?users $user = null;

    #[ORM\OneToMany(targetEntity: ReservationsEvents::class, mappedBy: 'bill')]
    private Collection $reservationsEvents;

    #[ORM\OneToMany(targetEntity: ReservationsRentals::class, mappedBy: 'bill')]
    private Collection $reservationsRentals;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentIntentId = null;

    public function __construct()
    {
        $this->reservationsEvents = new ArrayCollection();
        $this->reservationsRentals = new ArrayCollection();
    }

    public function getReservationsEvents(): Collection
    {
        return $this->reservationsEvents;
    }

    public function setReservationsEvents(Collection $reservationsEvents): void
    {
        $this->reservationsEvents = $reservationsEvents;
    }

    public function getReservationsRentals(): Collection
    {
        return $this->reservationsRentals;
    }

    public function setReservationsRentals(Collection $reservationsRentals): void
    {
        $this->reservationsRentals = $reservationsRentals;
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): static
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?users
    {
        return $this->user;
    }

    public function setUser(?users $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getAllReservations(): array
    {
        return array_merge(
            $this->reservationsEvents->toArray(),
            $this->reservationsRentals->toArray()
        );
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(?string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;

        return $this;
    }
}
