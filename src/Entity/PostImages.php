<?php

namespace App\Entity;

use App\Repository\PostImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostImagesRepository::class)]
class PostImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?posts $post_id = null;

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

    public function getPostId(): ?posts
    {
        return $this->post_id;
    }

    public function setPostId(?posts $post_id): static
    {
        $this->post_id = $post_id;

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
