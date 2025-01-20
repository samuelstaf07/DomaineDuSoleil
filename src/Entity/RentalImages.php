<?php

namespace App\Entity;

use App\Repository\RentalImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentalImagesRepository::class)]
class RentalImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?rentals $rental_id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?images $image_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getRentalId(): ?rentals
    {
        return $this->rental_id;
    }

    public function setRentalId(?rentals $rental_id): static
    {
        $this->rental_id = $rental_id;

        return $this;
    }

    public function getImageId(): ?images
    {
        return $this->image_id;
    }

    public function setImageId(images $image_id): static
    {
        $this->image_id = $image_id;

        return $this;
    }
}
