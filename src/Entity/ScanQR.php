<?php

namespace App\Entity;

use App\Repository\ScanQRRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ScanQRRepository::class)
 */
class ScanQR
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank(message = "La date du scan doit être renseignée")
     * @Assert\DateTime(message = "La date {{value}} du champ {{label}} n'est pas au bon format")
     */
    private $scanAt;

    /**
     * @ORM\ManyToOne(targetEntity=Checkpoint::class, inversedBy="scanQRs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $checkpoint;

    /**
     * @ORM\ManyToOne(targetEntity=Round::class, inversedBy="scanQRs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $round;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScanAt(): ?\DateTimeImmutable
    {
        return $this->scanAt;
    }

    public function setScanAt(\DateTimeImmutable $scanAt): self
    {
        $this->scanAt = $scanAt;

        return $this;
    }

    public function getCheckpoint(): ?Checkpoint
    {
        return $this->checkpoint;
    }

    public function setCheckpoint(?Checkpoint $checkpoint): self
    {
        $this->checkpoint = $checkpoint;

        return $this;
    }

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function setRound(?Round $round): self
    {
        $this->round = $round;

        return $this;
    }
}
