<?php

namespace App\Entity;

use App\Repository\CheckpointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Game;

/**
 * @ORM\Entity(repositoryClass=CheckpointRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Checkpoint
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Le titre du checkpoint ne doit pas être vide")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Le titre du checkpoint doit au minimum faire {{ limit }} caractères !",
     *      maxMessage = "Le titre du checkpoint ne doit pas éxéder {{ limit }} caractères !"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message = "Le message de succés du checkpoint ne doit pas être vide")
     */
    private $successMessage;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "L'ordre du checkpoint doit être renseigné")
     * @Assert\Type(
     *     type="integer",
     *     message="La valeur {{ value }} n'est pas de type {{ type }}."
     * )
     * @Assert\Positive(message = "L'ordre du checkpoint doit être un chiffre positif")
     */
    private $orderCheckpoint;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank(message = "La date de création du checkpoint doit être renseignée")
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Assert\Type(type="\DateTimeInterface", message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="checkpoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\OneToMany(targetEntity=ScanQR::class, mappedBy="checkpoint")
     */
    private $scanQRs;

    /**
     * @ORM\OneToMany(targetEntity=Enigma::class, mappedBy="checkpoint")
     * @ORM\OrderBy({"orderEnigma" = "ASC"})
     */
    private $enigmas;

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
        $this->scanQRs = new ArrayCollection();
        $this->enigmas = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isTrashed = false;
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

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(string $successMessage): self
    {
        $this->successMessage = $successMessage;

        return $this;
    }

    public function getOrderCheckpoint(): ?int
    {
        return $this->orderCheckpoint;
    }

    public function setOrderCheckpoint(int $orderCheckpoint): self
    {
        $this->orderCheckpoint = $orderCheckpoint;

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
     * @return Collection<int, ScanQR>
     */
    public function getScanQRs(): Collection
    {
        return $this->scanQRs;
    }

    public function addScanQR(ScanQR $scanQR): self
    {
        if (!$this->scanQRs->contains($scanQR)) {
            $this->scanQRs[] = $scanQR;
            $scanQR->setCheckpoint($this);
        }

        return $this;
    }

    public function removeScanQR(ScanQR $scanQR): self
    {
        if ($this->scanQRs->removeElement($scanQR)) {
            // set the owning side to null (unless already changed)
            if ($scanQR->getCheckpoint() === $this) {
                $scanQR->setCheckpoint(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Enigma>
     */
    public function getEnigmas(): Collection
    {
        return $this->enigmas;
    }

    public function addEnigma(Enigma $enigma): self
    {
        if (!$this->enigmas->contains($enigma)) {
            $this->enigmas[] = $enigma;
            $enigma->setCheckpoint($this);
        }

        return $this;
    }

    public function removeEnigma(Enigma $enigma): self
    {
        if ($this->enigmas->removeElement($enigma)) {
            // set the owning side to null (unless already changed)
            if ($enigma->getCheckpoint() === $this) {
                $enigma->setCheckpoint(null);
            }
        }

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
