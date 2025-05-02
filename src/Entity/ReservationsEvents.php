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
    private ?bills $bill = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?users $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?events $event = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_reservation = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date_start = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column]
    private ?int $nb_places = null;

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
        return $this->bill;
    }

    public function setBillId(bills $bill): static
    {
        $this->bill = $bill;

        return $this;
    }

    public function getUserId(): ?users
    {
        return $this->user;
    }

    public function setUserId(?users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEventId(): ?events
    {
        return $this->event;
    }

    public function setEventId(?events $event): static
    {
        $this->event = $event;

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

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }
}
