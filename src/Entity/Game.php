<?php

namespace App\Entity;

use App\Repository\GameRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Le titre du jeu ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Le titre du jeu doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le titre du jeu ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Le slug du titre du jeu doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le slug du titre du jeu ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "L'adresse du jeu ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "L'adresse du jeu doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "L'adresse du jeu ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\NotBlank(message = "Le code postal du jeu ne doit pas être vide")
     * @Assert\Length(
     *      min=5,
     *      max=5,
     *      minMessage = "Le code postal du jeu doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le code postal du jeu ne doit pas éxéder {{ limit }} caractères !"
     * )
     * @Assert\Regex(pattern="/\d+/", message="Le {{ label }} ne doit être que des chiffres !")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message = "La ville du jeu ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "La ville du jeu doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "La ville du jeu ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

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
     * @Assert\NotBlank(message = "La date de création du jeu doit être renseignée")
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Instance::class, mappedBy="game")
     * @ORM\OrderBy({"startAt" = "DESC"})
     */
    private $instances;

    /**
     * @ORM\OneToMany(targetEntity=Checkpoint::class, mappedBy="game")
     * @ORM\OrderBy({"orderCheckpoint" = "ASC"})
     */
    private $checkpoints;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(
     *     type="bool",
     *     message="La valeur {{ value }} n'est pas du type : {{ type }}."
     * )
     */
    private $isTrashed;

    public function __construct()
    {
        $this->instances = new ArrayCollection();
        $this->checkpoints = new ArrayCollection();
        $this->isTrashed = false;
        $this->status = true;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getImage(): ?string
    {   
        if ($this->image) {
            return $this->image;
        } else {
            return 'default/game-default-image.jpg';
        }
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

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

    /**
     * @return Collection<int, Instance>
     */
    public function getInstances(): Collection
    {
        return $this->instances;
    }

    /**
     * @return Collection<int, Instance>
     */
    public function getUnTrashedInstances(): Collection
    {
        $criteria = Criteria::create()
        ->andWhere(Criteria::expr()->eq('isTrashed', false));
        return $this->instances->matching($criteria);
    }

    public function addInstance(Instance $instance): self
    {
        if (!$this->instances->contains($instance)) {
            $this->instances[] = $instance;
            $instance->setGame($this);
        }

        return $this;
    }

    public function removeInstance(Instance $instance): self
    {
        if ($this->instances->removeElement($instance)) {
            // set the owning side to null (unless already changed)
            if ($instance->getGame() === $this) {
                $instance->setGame(null);
            }
        }

        return $this;
    }

    public function getNextInstance(): Instance
    {
        $date = new DateTime();
        $date = $date->getTimestamp();

        $criteria = Criteria::create()
        ->andWhere(Criteria::expr()->eq('isTrashed', false));
        $instances = $this->instances->matching($criteria);

        $instances = array_reverse($instances->toArray());
            
        foreach($instances AS $instance)
        {
            if($date >= $instance->getStartAt()->getTimestamp() && $date <= $instance->getEndAt()->getTimestamp())
            {
                return $instance;
            }
        }

        $instance_return = new Instance();
        foreach($this->instances AS $instance)
        {
            if($date < $instance->getStartAt()->getTimestamp())
            {
                $instance_return = $instance;
            }
        }

        return $instance_return;
    }

    /**
     * @return Collection<int, Checkpoint>
     */
    public function getCheckpoints(): Collection
    {
        return $this->checkpoints;
    }

    /**
     * @return Collection<int, Checkpoint>
     */
    public function getUnTrashedCheckpoints(): Collection
    {
        $criteria = Criteria::create()
        ->andWhere(Criteria::expr()->eq('isTrashed', false));
        return $this->checkpoints->matching($criteria);
    }

    public function addCheckpoint(Checkpoint $checkpoint): self
    {
        if (!$this->checkpoints->contains($checkpoint)) {
            $this->checkpoints[] = $checkpoint;
            $checkpoint->setGame($this);
        }

        return $this;
    }

    public function removeCheckpoint(Checkpoint $checkpoint): self
    {
        if ($this->checkpoints->removeElement($checkpoint)) {
            // set the owning side to null (unless already changed)
            if ($checkpoint->getGame() === $this) {
                $checkpoint->setGame(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    /*public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }*/

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getIsTrashed(): ?bool
    {
        return $this->isTrashed;
    }

    public function setIsTrashed(bool $isTrashed): self
    {
        $this->isTrashed = $isTrashed;

        return $this;
    }
}
