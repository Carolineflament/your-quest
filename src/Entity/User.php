<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, message="Un compte utilise déjà cet email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message = "L\'email ne doit pas être vide")
     * @Assert\Email(
     *      message = "L'email {{ value }} n'est pas valide"
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "L'email de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "L'email de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank(message = "Le pseudo de l'utilisateur ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 180,
     *      minMessage = "Le pseudo de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le pseudo de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Le nom de l'utilisateur ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 180,
     *      minMessage = "Le nom de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le nom de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Le prénom de l'utilisateur ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 180,
     *      minMessage = "Le prénom de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le prénom de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "L'adresse de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "L'adresse de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Length(
     *      min=5,
     *      max=5,
     *      minMessage = "Le code postal de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le code postal de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "La ville de l'utilisateur doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "La ville de l'utilisateur ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(
     *     type="bool",
     *     message="La valeur {{ value }} n'est pas du type : {{ type }}."
     * )
     */
    private $status;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank(message = "La date de création doit être renseignée")
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="user")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $games;

    /**
     * @ORM\OneToMany(targetEntity=Round::class, mappedBy="user")
     * @ORM\OrderBy({"startAt" = "ASC"})
     */
    private $rounds;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // Get rôle contain in role table insteed of role JSON give by make:user
        $roles = array($this->role->getSlug());
        // guarantee every user at least has ROLE_USER
        //$roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setUser($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getUser() === $this) {
                $game->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Round>
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): self
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds[] = $round;
            $round->setUser($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getUser() === $this) {
                $round->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
