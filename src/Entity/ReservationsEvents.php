<?php

namespace App\Entity;

use App\Repository\ReservationsEventsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsEventsRepository::class)]
class ReservationsEvents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Bills::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bills $bill = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?users $user = null;

    #[ORM\ManyToOne(targetEntity: Events::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?events $event = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_reservation = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column]
    private ?int $nb_places = null;

    #[ORM\Column]
    private ?float $totalDeposit = null;

    #[ORM\Column]
    private ?bool $isRefund = null;

    #[ORM\Column]
    private ?float $total_deposit_returned = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getBill(): ?bills
    {
        return $this->bill;
    }

    public function setBill(?Bills $bill): self
    {
        $this->bill = $bill;
        return $this;
    }

    public function getUser(): ?users
    {
        return $this->user;
    }

    public function setUser(?users $user): void
    {
        $this->user = $user;
    }

    public function getEvent(): ?events
    {
        return $this->event;
    }

    public function setEvent(?events $event): void
    {
        $this->event = $event;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->date_reservation;
    }

    public function setDateReservation(?\DateTimeImmutable $date_reservation): void
    {
        $this->date_reservation = $date_reservation;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): void
    {
        $this->is_active = $is_active;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(?int $nb_places): void
    {
        $this->nb_places = $nb_places;
    }

    public function getTotalDeposit(): ?float
    {
        return $this->totalDeposit;
    }

    public function setTotalDeposit(float $totalDeposit): static
    {
        $this->totalDeposit = $totalDeposit;

        return $this;
    }

    public function isRefund(): ?bool
    {
        return $this->isRefund;
    }

    public function setIsRefund(bool $isRefund): static
    {
        $this->isRefund = $isRefund;

        return $this;
    }

    public function getTotalDepositReturned(): ?float
    {
        return $this->total_deposit_returned;
    }

    public function setTotalDepositReturned(float $total_deposit_returned): static
    {
        $this->total_deposit_returned = $total_deposit_returned;

        return $this;
    }
}
