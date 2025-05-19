<?php

namespace App\Entity;

use App\Repository\ReservationsRentalsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsRentalsRepository::class)]
class ReservationsRentals
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

    #[ORM\Column]
    private ?float $total_deposit_returned = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status_base_deposit = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_reservation = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_start = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_end = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $status_reservation = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rentals $rentals = null;

    #[ORM\Column]
    private ?float $totalPrice = null;

    #[ORM\Column]
    private ?bool $isRefund = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBill(): ?Bills
    {
        return $this->bill;
    }

    public function setBill(?Bills $bill): static
    {
        $this->bill = $bill;

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

    public function getTotalDepositReturned(): ?float
    {
        return $this->total_deposit_returned;
    }

    public function setTotalDepositReturned(float $total_deposit_returned): static
    {
        $this->total_deposit_returned = $total_deposit_returned;

        return $this;
    }

    public function getStatusBaseDeposit(): ?int
    {
        return $this->status_base_deposit;
    }

    public function setStatusBaseDeposit(int $status_base_deposit): static
    {
        $this->status_base_deposit = $status_base_deposit;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeImmutable $date_reservation): static
    {
        $this->date_reservation = $date_reservation;

        return $this;
    }

    public function getDateStart(): ?\DateTimeImmutable
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeImmutable $date_start): static
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTimeImmutable $date_end): static
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getStatusReservation(): ?bool
    {
        return $this->status_reservation;
    }

    public function setStatusReservation(bool $status_reservation): static
    {
        $this->status_reservation = $status_reservation;

        return $this;
    }

    public function getRentals(): ?Rentals
    {
        return $this->rentals;
    }

    public function setRentals(?Rentals $rentals): static
    {
        $this->rentals = $rentals;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

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
}
