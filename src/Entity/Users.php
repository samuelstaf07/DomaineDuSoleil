<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $last_log_at = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column]
    private ?bool $is_email_authentificated = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Images $image = null;

    /**
     * @var Collection<int, Bills>
     */
    #[ORM\OneToMany(targetEntity: Bills::class, mappedBy: 'user')]
    private Collection $bills;

    /**
     * @var Collection<int, ReservationsEvents>
     */
    #[ORM\OneToMany(targetEntity: ReservationsEvents::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reservationsEvents;

    /**
     * @var Collection<int, ReservationsRentals>
     */
    #[ORM\OneToMany(targetEntity: ReservationsRentals::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reservationsRentals;


    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $birth_date = null;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\OneToMany(targetEntity: Comments::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $comments;

    private ?string $googleAuthenticatorSecret = null;

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->googleAuthenticatorSecret;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $secret): void
    {
        $this->googleAuthenticatorSecret = $secret;
    }

    public function __construct()
    {
        $this->bills = new ArrayCollection();
        $this->reservationsEvents = new ArrayCollection();
        $this->reservationsRentals = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getImage(): ?images
    {
        return $this->image;
    }

    public function setImage(images $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLastLogAt(): ?\DateTimeImmutable
    {
        return $this->last_log_at;
    }

    public function setLastLogAt(\DateTimeImmutable $last_log_at): static
    {
        $this->last_log_at = $last_log_at;

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

    public function isEmailAuthentificated(): ?bool
    {
        return $this->is_email_authentificated;
    }

    public function setIsEmailAuthentificated(bool $is_email_authentificated): static
    {
        $this->is_email_authentificated = $is_email_authentificated;

        return $this;
    }

    /**
     * @return Collection<int, Bills>
     */
    public function getBills(): Collection
    {
        return $this->bills;
    }

    public function addBill(Bills $bill): static
    {
        if (!$this->bills->contains($bill)) {
            $this->bills->add($bill);
            $bill->setUser($this);
        }

        return $this;
    }

    public function removeBill(Bills $bill): static
    {
        if ($this->bills->removeElement($bill)) {
            // set the owning side to null (unless already changed)
            if ($bill->getUser() === $this) {
                $bill->setUser(null);
            }
        }

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birth_date;
    }

    public function setBirthDate(\DateTimeImmutable $birth_date): static
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    /**
     * @return Collection<int, ReservationsEvents>
     */
    public function getReservationsEvents(): Collection
    {
        return $this->reservationsEvents;
    }

    public function addReservationsEvent(ReservationsEvents $reservationEvent): static
    {
        if (!$this->reservationsEvents->contains($reservationEvent)) {
            $this->reservationsEvents->add($reservationEvent);
            $reservationEvent->setUser($this);
        }

        return $this;
    }

    public function removeReservationsEvent(ReservationsEvents $reservationEvent): static
    {
        if ($this->reservationsEvents->removeElement($reservationEvent)) {
            if ($reservationEvent->getUser() === $this) {
                $reservationEvent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservationsRentals>
     */
    public function getReservationsRentals(): Collection
    {
        return $this->reservationsRentals;
    }

    public function addReservationsRental(ReservationsRentals $reservationRental): static
    {
        if (!$this->reservationsRentals->contains($reservationRental)) {
            $this->reservationsRentals->add($reservationRental);
            $reservationRental->setUser($this);
        }

        return $this;
    }

    public function removeReservationsRental(ReservationsRentals $reservationRental): static
    {
        if ($this->reservationsRentals->removeElement($reservationRental)) {
            if ($reservationRental->getUser() === $this) {
                $reservationRental->setUser(null);
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

}
