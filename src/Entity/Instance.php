<?php

namespace App\Entity;

use App\Repository\InstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InstanceRepository::class)
 */
class Instance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Le titre de l'instance ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Le titre de l'instance doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le titre de l'instance ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Le slug de l'instance doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le slug de l'instance ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank(message = "La date de début de l'instance doit être renseignée")
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank(message = "La date de fin de l'instance doit être renseignée")
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $endAt;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="instances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\OneToMany(targetEntity=Round::class, mappedBy="instance")
     * @ORM\OrderBy({"startAt" = "ASC"})
     */
    private $rounds;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

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
            $round->setInstance($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getInstance() === $this) {
                $round->setInstance(null);
            }
        }

        return $this;
    }
}
