<?php

namespace App\Entity;

use App\Repository\RentalsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalsRepository::class)]
class Rentals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $nb_double_bed = null;

    #[ORM\Column]
    private ?int $nb_simple_bed = null;

    #[ORM\Column]
    private ?bool $has_shower = null;

    #[ORM\Column]
    private ?bool $has_toilet = null;

    #[ORM\Column]
    private ?bool $has_kitchen = null;

    #[ORM\Column]
    private ?bool $has_fridge = null;

    #[ORM\Column]
    private ?bool $has_heating = null;

    #[ORM\Column]
    private ?bool $pets_accepted = null;

    #[ORM\Column]
    private ?float $price_per_day = null;

    #[ORM\Column]
    private ?bool $is_on_promotion = null;

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

    public function getNbDoubleBed(): ?int
    {
        return $this->nb_double_bed;
    }

    public function setNbDoubleBed(int $nb_double_bed): static
    {
        $this->nb_double_bed = $nb_double_bed;

        return $this;
    }

    public function getNbSimpleBed(): ?int
    {
        return $this->nb_simple_bed;
    }

    public function setNbSimpleBed(int $nb_simple_bed): static
    {
        $this->nb_simple_bed = $nb_simple_bed;

        return $this;
    }

    public function hasShower(): ?bool
    {
        return $this->has_shower;
    }

    public function setHasShower(bool $has_shower): static
    {
        $this->has_shower = $has_shower;

        return $this;
    }

    public function hasToilet(): ?bool
    {
        return $this->has_toilet;
    }

    public function setHasToilet(bool $has_toilet): static
    {
        $this->has_toilet = $has_toilet;

        return $this;
    }

    public function hasKitchen(): ?bool
    {
        return $this->has_kitchen;
    }

    public function setHasKitchen(bool $has_kitchen): static
    {
        $this->has_kitchen = $has_kitchen;

        return $this;
    }

    public function hasFridge(): ?bool
    {
        return $this->has_fridge;
    }

    public function setHasFridge(bool $has_fridge): static
    {
        $this->has_fridge = $has_fridge;

        return $this;
    }

    public function hasHeating(): ?bool
    {
        return $this->has_heating;
    }

    public function setHasHeating(bool $has_heating): static
    {
        $this->has_heating = $has_heating;

        return $this;
    }

    public function isPetsAccepted(): ?bool
    {
        return $this->pets_accepted;
    }

    public function setPetsAccepted(bool $pets_accepted): static
    {
        $this->pets_accepted = $pets_accepted;

        return $this;
    }

    public function getPricePerDay(): ?float
    {
        return $this->price_per_day;
    }

    public function setPricePerDay(float $price_per_day): static
    {
        $this->price_per_day = $price_per_day;

        return $this;
    }

    public function isOnPromotion(): ?bool
    {
        return $this->is_on_promotion;
    }

    public function setIsOnPromotion(bool $is_on_promotion): static
    {
        $this->is_on_promotion = $is_on_promotion;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
