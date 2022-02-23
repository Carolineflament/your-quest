<?php

namespace App\Entity;

use App\Repository\RoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoundRepository::class)
 */
class Round
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\ManyToOne(targetEntity=Instance::class, inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instance;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=ScanQR::class, mappedBy="round")
     */
    private $scanQRs;

    public function __construct()
    {
        $this->scanQRs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getInstance(): ?Instance
    {
        return $this->instance;
    }

    public function setInstance(?Instance $instance): self
    {
        $this->instance = $instance;

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
            $scanQR->setRound($this);
        }

        return $this;
    }

    public function removeScanQR(ScanQR $scanQR): self
    {
        if ($this->scanQRs->removeElement($scanQR)) {
            // set the owning side to null (unless already changed)
            if ($scanQR->getRound() === $this) {
                $scanQR->setRound(null);
            }
        }

        return $this;
    }
}
