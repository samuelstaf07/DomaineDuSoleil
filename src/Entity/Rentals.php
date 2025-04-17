<?php

namespace App\Entity;

use App\Repository\RentalsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    /**
     * @var Collection<int, Images>
     */
    #[ORM\OneToMany(targetEntity: Images::class, mappedBy: 'rentals')]
    private Collection $Images;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\OneToMany(targetEntity: Comments::class, mappedBy: 'rentals')]
    private Collection $comments;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * @var Collection<int, ReservationsRentals>
     */
    #[ORM\OneToMany(targetEntity: ReservationsRentals::class, mappedBy: 'rentals')]
    private Collection $reservations;

    public function __construct()
    {
        $this->Images = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reservations = new ArrayCollection();
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

    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->Images;
    }

    public function addImage(Images $image): static
    {
        if (!$this->Images->contains($image)) {
            $this->Images->add($image);
            $image->setRentals($this);
        }

        return $this;
    }

    public function removeImage(Images $image): static
    {
        if ($this->Images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getRentals() === $this) {
                $image->setRentals(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setRentals($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getRentals() === $this) {
                $comment->setRentals(null);
            }
        }

        return $this;
    }

    public function getAverageRateAndCommentCount(): array
    {
        $totalRate = 0;
        $commentCount = 0;

        foreach ($this->comments as $comment) {
            if ($comment->getIsActive()) {
                $totalRate += $comment->getRating();
                $commentCount++;
            }
        }

        if ($commentCount > 0) {
            $averageRate = round($totalRate / $commentCount, 2);
        } else {
            $averageRate = 0;
        }

        return [
            'averageRate' => $averageRate,
            'commentCount' => $commentCount,
        ];
    }

    public function getHomePageImage(): Images | null
    {
        foreach ($this->getImages() as $image) {
            if ($image->isHomePage() === true) {
                return $image;
            }
        }

        return null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, ReservationsRentals>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(ReservationsRentals $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setRentals($this);
        }

        return $this;
    }

    public function removeReservation(ReservationsRentals $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getRentals() === $this) {
                $reservation->setRentals(null);
            }
        }

        return $this;
    }
}
