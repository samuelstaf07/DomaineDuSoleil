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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?bills $bill_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?users $user_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?events $event_id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_reservation = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_start = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_end = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBillId(): ?bills
    {
        return $this->bill_id;
    }

    public function setBillId(bills $bill_id): static
    {
        $this->bill_id = $bill_id;

        return $this;
    }

    public function getUserId(): ?users
    {
        return $this->user_id;
    }

    public function setUserId(?users $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getEventId(): ?events
    {
        return $this->event_id;
    }

    public function setEventId(?events $event_id): static
    {
        $this->event_id = $event_id;

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

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }
}
