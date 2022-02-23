<?php

namespace App\Entity;

use App\Repository\CheckpointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CheckpointRepository::class)
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
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $successMessage;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderCheckpoint;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
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
     */
    private $enigmas;

    public function __construct()
    {
        $this->scanQRs = new ArrayCollection();
        $this->enigmas = new ArrayCollection();
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
}
