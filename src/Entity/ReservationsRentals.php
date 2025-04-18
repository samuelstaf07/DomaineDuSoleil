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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bills $bill_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?users $user = null;

    #[ORM\Column]
    private ?bool $has_cleaning_deposit = null;

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

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status_reservation = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rentals $rentals = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBillId(): ?Bills
    {
        return $this->bill_id;
    }

    public function setBillId(Bills $bill_id): static
    {
        $this->bill_id = $bill_id;

        return $this;
    }

    public function getUser(): ?users
    {
        return $this->user;
    }

    public function setUser(?users $user_id): static
    {
        $this->user = $user_id;

        return $this;
    }

    public function hasCleaningDeposit(): ?bool
    {
        return $this->has_cleaning_deposit;
    }

    public function setHasCleaningDeposit(bool $has_cleaning_deposit): static
    {
        $this->has_cleaning_deposit = $has_cleaning_deposit;

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

    public function getStatusReservation(): ?int
    {
        return $this->status_reservation;
    }

    public function setStatusReservation(int $status_reservation): static
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
}
